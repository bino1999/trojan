# Stock Management — Complete Fix & Implementation Guide

**Date:** 2026-05-25  
**Status:** Full audit complete — all bugs catalogued, fix steps defined  
**Scope:** Stock consistency across Stock Report (`/item-stock`), Quick Bill (`/quick-bill`), and Stock Update page (`/stock`)

---

## Part 1 — Understanding the Architecture First

### What is `products.stock_quantity` and why it says "not used"?

The `products` table has a column called `stock_quantity`. At first glance this looks like where the system stores how many units you have. **It does not.** It is a legacy/unused field that was never wired up to any deduction or addition logic. No controller reads it when selling, no model updates it when purchasing. It is just a number that sits in the database doing nothing.

**The real stock number lives in `purchase_order_items.available_stock`.**

Every time you buy a product, it creates a row in `purchase_order_items`. That row has an `available_stock` column which starts at 0 (Draft) and becomes the purchased quantity when the PO is marked Completed. Every sale, job, or internal use deducts from this column. Every return or deletion adds back to it.

This means:
- One product can have stock spread across **multiple PO rows** (different batches, different suppliers).
- The "real stock" for any product is `SUM(available_stock)` across all its completed PO rows.
- `products.stock_quantity` can be ignored for all operational purposes.

### Why must all three surfaces show the same stock number?

| Surface | Where it reads stock from |
|---|---|
| Stock Report (`/item-stock`) | `SUM(poi.available_stock)` from `purchase_order_items` where PO is completed |
| Quick Bill product list | `poi.available_stock > 0` from same table |
| Stock Update page (`/stock`) | `SUM(poi.available_stock)` from same table |

All three read from **the same source of truth**. If any query uses a different filter (e.g. wrong PO status), the number diverges and staff see inconsistent data across screens.

### Stock flow diagram (correct behavior)

```
PURCHASE ORDER (Draft)
  └── items added → available_stock = 0 (stock NOT visible anywhere)
        ↓
PURCHASE ORDER (Completed)
  └── updateAvailableStockForCompletedPO() → available_stock = quantity
        ↓ stock now visible in all 3 surfaces ↑
        |
        ├── Quick Bill saved → available_stock -= qty (immediate)
        ├── Quick Bill deleted → available_stock += qty
        ├── Quick Bill item returned (approved) → available_stock += return_qty
        |
        ├── Job → Ongoing → available_stock -= qty for all product items
        ├── Job item added while Ongoing → available_stock -= qty immediately
        ├── Job item deleted (if confirmed) → available_stock += qty
        |
        └── Internal Bill saved → available_stock -= qty (immediate)
            Internal Bill deleted → available_stock += qty

PURCHASE ORDER (Archived) ← after full payment
  └── available_stock tracking continues unchanged
      (PO status = Archived, but completed_by > 0 still true)
```

---

## Part 2 — Complete Bug Inventory

All bugs are listed here. Bugs from the previous plan (`stock-billing-purchase-bug-plan.md`) are merged and deduplicated.

---

### BUG-01 · CRITICAL · Unsafe PO item deletion URL route bypasses all stock checks

**File:** `application/controllers/Purchase.php` lines 437–453  
**Method:** `deletePurchaseOrderItem($po_item_id)` (URL-based, GET route)

There are two delete methods for PO items. The safe one (`deletePoItem()` at line 286) checks job/bill usage and adjusts stock before deleting. This second method accepts the ID from the URL and does a raw delete with no checks at all — no usage guard, no `available_stock` zeroing.

**Impact:** Any user who knows the URL pattern `/purchase/deletePurchaseOrderItem/123` can delete a live PO item, leaving dangling `po_item_id` references in `services_job_items`, `quick_bill_items`, and `internal_bill_items`.

**Fix:** Remove or block the `deletePurchaseOrderItem($po_item_id)` URL route entirely. All deletion must go through `deletePoItem()` via POST.

```php
// In Purchase.php — DELETE or add a hard block to this method:
public function deletePurchaseOrderItem($po_item_id = null)
{
    // REMOVE THIS METHOD ENTIRELY — use deletePoItem() via POST instead
    show_error('Direct deletion not allowed. Use the POST route.');
}
```

---

### BUG-02 · CRITICAL · Job status oscillation causes double stock deduction

**File:** `application/controllers/Job.php` lines 919–970  
**Method:** `updateJobStatus()`

When any status change happens, `confirmed_by` is reset to `0` for ALL job items unconditionally (line 939–940). Stock is only deducted when moving **to** Ongoing (status=1). Stock is **never restored** when moving away from Ongoing.

**The loop:**
```
Job → Ongoing (status 1):  stock deducted, confirmed_by = userId
Job → Completed (status 2): confirmed_by reset to 0, stock NOT restored  ← BUG
Job → Ongoing (status 1):  confirmed_by=0 again → stock deducted AGAIN  ← DOUBLE DEDUCTION
```

**Fix:** Before resetting `confirmed_by`, restore stock for any currently confirmed items when moving away from Ongoing.

```php
// In updateJobStatus(), BEFORE the $updateData2 update, add:
$currentJob = $this->db->get_where('services_job', ['job_id' => $jobId])->row();
if ($currentJob && intval($currentJob->status) === 1 && intval($status) !== 1) {
    // Moving AWAY from Ongoing: restore stock for all confirmed product items
    $confirmedItems = $this->db->get_where('services_job_items', [
        'service_job_id' => $jobId,
        'item_type'      => 'product',
    ])->result();
    foreach ($confirmedItems as $ci) {
        if (intval($ci->confirmed_by) > 0 && $ci->po_item_id > 0) {
            $this->db->set('available_stock', 'available_stock + ' . (float)$ci->quantity, false);
            $this->db->where('po_item_id', $ci->po_item_id);
            $this->db->update('purchase_order_items');
        }
    }
}
// Then run the existing $updateData2 reset and status change
```

---

### BUG-03 · CRITICAL · `addProductToJob` captures `insert_id()` after an UPDATE (returns 0)

**File:** `application/controllers/Job.php` around line 484–496  
**Method:** `addProductToJob()`

When a product is added to an already-Ongoing job, the code:
1. Does `$this->db->insert(...)` — correct
2. Runs a `$this->db->update(purchase_order_items)` for stock deduction
3. Then calls `$this->db->insert_id()` — **this now returns 0**, because `insert_id()` reflects the last INSERT, which was overwritten by the UPDATE

Result: `confirmed_by` is never set on the job item. Stock is deducted but the item reads as unconfirmed. If someone clicks "confirm" on it later, stock is deducted again.

**Fix:** Capture `insert_id()` immediately after the insert, before any other query.

```php
// BEFORE (wrong):
if ($this->db->insert('services_job_items', $data)) {
    $job = $this->db->get_where(...)->row();
    if ($job && intval($job->status) === 1) {
        $this->db->update('purchase_order_items');   // <-- runs here
        $insertedId = $this->db->insert_id();         // <-- BUG: returns 0

// AFTER (correct):
if ($this->db->insert('services_job_items', $data)) {
    $insertedId = $this->db->insert_id();             // <-- capture FIRST
    $job = $this->db->get_where(...)->row();
    if ($job && intval($job->status) === 1) {
        $this->db->update('purchase_order_items');
        // now $insertedId is correct
```

This fix is already applied in the current code (line 486 captures `$insertedId` before the get_where). Verify it remains in place and is not regressed.

---

### BUG-04 · CRITICAL · Stock view and Quick Bill use different PO status filters → inconsistent product counts

**File:** `application/models/Purchase_model.php`  
**Methods:** `loadPurchaseProductsAsGroup()` and `loadAvailablePurchaseProducts()` (in Services_model)

When a PO is fully paid, its status changes from `Completed` to `Archived`. The two screens use different filters:

| Screen | Filter used | Result for Archived PO |
|---|---|---|
| Quick Bill / Job product list | `po.completed_by > 0` | ✅ Still shows |
| Stock Report | was `po.status = 'Completed'` | ❌ Disappears |

This was partially fixed in a previous session (the model now uses `po.completed_by > 0`). **Verify** that both `loadPurchaseProductsAsGroup()` and `getStockInOutSummary()` use `completed_by > 0` and NOT `status = 'Completed'`.

**Correct filter (must be in all stock queries):**
```php
$this->db->where('po.completed_by >', 0);
// NOT: $this->db->where('po.status', 'Completed');
```

---

### BUG-05 · HIGH · Editing a Draft PO item pre-sets `available_stock`, making it appear sellable

**File:** `application/controllers/Purchase.php` lines 274–278  
**Method:** `updatePoItem()`

```php
// For Draft POs, this INCORRECTLY sets available_stock = quantity:
if (!$is_completed) {
    $this->purchase_model->updateAvailableStockForItem($po_item_id, $quantity);
}
```

`loadAvailablePurchaseProducts()` filters by `available_stock > 0`. After this bug triggers, a Draft PO item (not yet received) appears in the Quick Bill and Job product picker as if the stock is in hand.

**Fix:** Remove the `updateAvailableStockForItem` call for non-completed POs. Draft items should always have `available_stock = 0`.

```php
// REMOVE this block entirely:
if (!$is_completed) {
    $this->purchase_model->updateAvailableStockForItem($po_item_id, $quantity);
}
// Only adjust stock for completed POs:
if ($is_completed && $quantity_diff != 0) {
    $this->purchase_model->adjustStockForItemChange($po_item_id, $quantity_diff);
}
```

---

### BUG-06 · HIGH · `deletePoItem` and `deletePurchase` don't check `internal_bill_items`

**File:** `application/controllers/Purchase.php` lines 305–313 and 552–571

Both deletion guards check `services_job_items` and `quick_bill_items` but skip `internal_bill_items`. A PO item used only in an internal bill can be deleted, orphaning the `internal_bill_items` records.

**Fix:** Add the check for `internal_bill_items` in both methods.

```php
// In deletePoItem() — add after existing $bill_usage line:
$internal_usage = $this->db->where('po_item_id', $po_item_id)
                            ->count_all_results('internal_bill_items');
if ($job_usage > 0 || $bill_usage > 0 || $internal_usage > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot delete — item used in jobs, bills, or internal bills.']);
    return;
}

// In deletePurchase() — same pattern after the $bill_usage count:
$internal_usage = $this->db->where_in('po_item_id', $po_item_ids)
                            ->count_all_results('internal_bill_items');
if ($job_usage > 0 || $bill_usage > 0 || $internal_usage > 0) { ... }
```

---

### BUG-07 · HIGH · Bill deletion over-restores stock when returns were already approved

**File:** `application/models/QuickBill_model.php` lines 132–137  
**Method:** `deleteQuickBillWithReason()`

```php
// WRONG — restores full quantity, ignoring already-returned stock:
$this->db->set('available_stock', 'available_stock + ' . $item->quantity, false);
```

If a return was approved before bill deletion, `return_quantity` units were already added back to stock. Deleting the bill then adds the full `quantity` again — stock is over-restored by `return_quantity`.

**Example:** Sold 10, returned 3 (stock +3 already). Bill deleted → stock +10. Net phantom stock: +3.

**Fix:**
```php
// CORRECT — only restore the net-sold amount:
$net_restore = $item->quantity - floatval($item->return_quantity ?? 0);
if ($net_restore > 0) {
    $this->db->set('available_stock', 'available_stock + ' . $net_restore, false);
    $this->db->where('po_item_id', $item->po_item_id);
    $this->db->update('purchase_order_items');
}
```

---

### BUG-08 · HIGH · Return quantity overwrites instead of accumulates on second return

**File:** `application/models/QuickBill_model.php` lines 225–229  
**Method:** `approveReturn()`

```php
// WRONG — sets return_quantity, doesn't add to it:
$this->db->update('quick_bill_items', [
    'return_quantity' => $item->return_quantity,
    ...
]);
```

Second partial return overwrites the first. Stock is correctly restored both times, but `quick_bill_items.return_quantity` only reflects the last return. The stock report then calculates `bill_out = SUM(quantity - return_quantity)` using the understated value, overstating what was sold.

**Fix:** Use SQL to accumulate:
```php
$this->db->where('id', $item->quick_bill_item_id);
$this->db->set('return_quantity', 'COALESCE(return_quantity, 0) + ' . (float)$item->return_quantity, false);
$this->db->update('quick_bill_items', [
    'return_by' => $approvedBy,
    'return_at' => date('Y-m-d H:i:s')
]);
```

---

### BUG-09 · HIGH · Opening balance in stock report includes Draft/unconfirmed POs

**File:** `application/models/Purchase_model.php` lines 383–390  
**Method:** `getOpenBalance()`

```php
// WRONG — no filter for PO completion:
$this->db->from('purchase_order_items');
$this->db->where('product_id', $product_id);
$this->db->where('purchase_date <', $sdate);
```

Draft PO quantities are counted as "purchased" in the opening balance calculation. This inflates the opening balance figure on the stock report.

**Fix:** Join `purchase_orders` and filter by `completed_by > 0`:
```php
$this->db->select_sum('poi.quantity');
$this->db->from('purchase_order_items poi');
$this->db->join('purchase_orders po', 'po.po_id = poi.po_id', 'left');
$this->db->where('poi.product_id', $product_id);
$this->db->where('po.completed_by >', 0);
$this->db->where('poi.purchase_date <', $sdate);
$purchased = $this->db->get()->row()->quantity ?? 0;
```

---

### BUG-10 · MEDIUM · Internal bill cart has no stock validation

**File:** `application/controllers/InternalBill.php` lines 78–141  
**Method:** `addProductToBill()`

No check whether requested quantity exceeds `available_stock`. The Quick Bill cart validates this (lines 142–158 in QuickBill controller) but InternalBill skips it. Over-requested quantities silently clamp to 0 via `GREATEST()`.

**Fix:** Add the same validation as QuickBill:
```php
// After fetching $poItem in addProductToBill():
$availableStock = floatval($poItem->available_stock ?? 0);
if (floatval($quantity) > $availableStock) {
    echo json_encode(['status' => 'error', 
        'message' => 'Quantity exceeds available stock. Available: ' . number_format($availableStock, 2)]);
    return;
}
```

---

### BUG-11 · MEDIUM · `updateAvailableStock()` utility ignores internal bills and unconfirmed jobs

**File:** `application/models/Purchase_model.php` lines 480–512  
**Method:** `updateAvailableStock()`

This recalculation utility (used for data repair) computes:
```php
$available = $qty - ($job_issued + $quick_issued);
// internal_bill_items never included ← BUG
// job_issued not filtered by confirmed_by > 0 ← BUG
```

If called, it restores stock consumed by internal bills and counts unconfirmed job items as deductions.

**Fix:**
```php
// Get issued from jobs (only confirmed)
$this->db->select_sum('quantity');
$this->db->from('services_job_items');
$this->db->where('po_item_id', $po_item_id);
$this->db->where('item_type', 'product');
$this->db->where('confirmed_by >', 0);
$job_issued = $this->db->get()->row()->quantity ?? 0;

// Get issued from internal bills
$this->db->select_sum('quantity');
$this->db->from('internal_bill_items');
$this->db->where('po_item_id', $po_item_id);
$internal_issued = $this->db->get()->row()->quantity ?? 0;

$available = $qty - ($job_issued + $quick_issued + $internal_issued);
```

---

### BUG-12 · MINOR · Date field inconsistency in stock report queries

**File:** `application/models/Purchase_model.php` lines 382–477

Stock report mixes two different date fields:
- **Purchases** filter on `purchase_date` — a manually entered field (user can backdate)
- **Issues (job/bill/internal)** filter on `DATE(created_at)` — auto-set system timestamp

A backdated purchase shifts it into a different period than the system-recorded consumption, causing the report to not reconcile.

**Recommendation:** Standardise to `created_at` for all queries, or document that `purchase_date` is the business date and ensure staff enter it accurately. At minimum, add a note in the UI that Purchase Date drives the stock report grouping.

---

## Part 3 — Delete Stock Feature (New)

### What "delete stock" means

From the stock view (`/item-stock`), a manager needs the ability to **write off** a product's available stock — for example when goods are damaged, expired, or physically lost. This sets `available_stock = 0` across all PO batches for that product.

This is different from deleting a PO item (which removes the purchase history). A stock write-off zeroes the available quantity while keeping the purchase records intact for audit purposes.

### Backend — add to `Purchase.php`

```php
public function writeOffProductStock()
{
    $this->require_permission('stock.manage');
    $product_id = (int)$this->input->post('product_id');
    $reason     = trim($this->input->post('reason'));

    if (!$product_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
        return;
    }
    if (empty($reason)) {
        echo json_encode(['status' => 'error', 'message' => 'A reason is required for stock write-off']);
        return;
    }

    // Zero out available_stock for all completed PO batches of this product
    $this->db->join('purchase_orders po', 'po.po_id = purchase_order_items.po_id');
    $this->db->where('purchase_order_items.product_id', $product_id);
    $this->db->where('po.completed_by >', 0);
    $this->db->update('purchase_order_items', [
        'available_stock'    => 0,
        'available_stock_at' => date('Y-m-d H:i:s'),
    ]);

    $affected = $this->db->affected_rows();

    // Log it
    $this->load->model('logs_model');
    $this->logs_model->log_activity(
        'Stock Write-Off',
        'Product ID: ' . $product_id . ' | Reason: ' . $reason . ' | Batches zeroed: ' . $affected
    );

    echo json_encode(['status' => 'success', 'message' => 'Stock written off successfully. ' . $affected . ' batch(es) zeroed.']);
}
```

**Add route in `application/config/routes.php`:**
```php
$route['purchase/writeOffProductStock'] = 'purchase/writeOffProductStock';
```

### Frontend — changes to `available-stock-list.php`

1. Add an **Actions** column header to the table.
2. Add a **Write-Off** button on each row (shown only when `closing_balance > 0`).
3. Add a confirmation modal with a reason field.
4. Wire up the AJAX call.

See the implementation in the view file — the delete button uses a trash icon and triggers a SweetAlert2 modal asking for a reason before submitting.

---

## Part 4 — Stock Consistency Verification

After all fixes are applied, these three numbers must always match for any given product:

```
Stock Report: Closing Balance
    = Open Balance + Stock In - (Job Out + Bill Out + Internal Out)

Quick Bill product list: Available column
    = purchase_order_items.available_stock  (live value)

Stock Update page: Total Stock column
    = SUM(purchase_order_items.available_stock) per product_id
```

The closing balance on the stock report SHOULD equal the live `available_stock` **for the current date range** (with today as the end date and the earliest purchase as the start date). If these diverge, one of the bugs above is still active.

---

## Part 5 — Implementation Order (Priority)

Work through bugs in this order. Each is a self-contained change. Test the verification scenario after each one before moving to the next.

| Priority | Bug | File | Lines affected |
|---|---|---|---|
| 1 | BUG-03: insert_id captured too late | `Job.php` | ~1 line move |
| 2 | BUG-02: Job oscillation double deduction | `Job.php` | ~15 lines added |
| 3 | BUG-01: Unsafe delete URL route | `Purchase.php` | 1 method blocked |
| 4 | BUG-05: Draft PO edit pre-sets stock | `Purchase.php` | 3 lines removed |
| 5 | BUG-04: Stock view PO status filter | `Purchase_model.php` | verify filter |
| 6 | BUG-06: Internal bill not checked in delete | `Purchase.php` | 2 usage checks |
| 7 | BUG-07: Bill delete over-restores | `QuickBill_model.php` | 3 lines |
| 8 | BUG-08: Return qty overwrites not accumulates | `QuickBill_model.php` | 3 lines |
| 9 | BUG-09: Opening balance includes Draft POs | `Purchase_model.php` | join + where |
| 10 | BUG-10: Internal bill no stock validation | `InternalBill.php` | ~8 lines |
| 11 | BUG-11: updateAvailableStock utility broken | `Purchase_model.php` | ~10 lines |
| 12 | Delete stock feature (write-off) | `Purchase.php` + view | new feature |

---

## Part 6 — Verification Checklist (run after all fixes)

### Purchase → Stock
- [ ] Create PO → add item → Stock page shows 0 (Draft, not visible)
- [ ] Complete PO → item appears in Stock page, Quick Bill, and Stock Report with correct qty
- [ ] Edit item qty on Draft PO → Stock page still shows 0 (BUG-05 fixed)
- [ ] Same product in 2 POs → Stock shows SUM of both batches
- [ ] Confirm PO payment → status Archived → product still visible on all 3 screens (BUG-04 fixed)

### Quick Bill → Stock
- [ ] Create bill with 5 units of product X → Stock decreases by 5 on all 3 screens
- [ ] Delete bill → Stock increases by 5 on all 3 screens
- [ ] Create bill with 10, approve return of 3 → Stock shows 7 sold (return_qty accumulates)
- [ ] Delete bill (after 3-unit return already approved) → Stock increases by 7 not 10 (BUG-07 fixed)

### Job → Stock
- [ ] Add product to New job → Stock unchanged on all 3 screens
- [ ] Job → Ongoing → Stock decreases immediately
- [ ] Add product to already-Ongoing job → Stock decreases AND confirmed_by is set (BUG-03 fixed)
- [ ] Job → Completed (from Ongoing) → Stock NOT further changed, confirmed_by reset
- [ ] Job → Ongoing again → Stock NOT double-deducted (BUG-02 fixed)
- [ ] Delete confirmed product from job → Stock restored

### Internal Bill → Stock
- [ ] Create Internal Bill with 4 units → Stock decreases by 4
- [ ] Request qty > available_stock → Error shown, not silently clamped (BUG-10 fixed)
- [ ] Delete Internal Bill → Stock increases by 4 exactly

### Stock Write-off (new feature)
- [ ] Click Write-Off on a product row → modal asks for reason
- [ ] Submit → available_stock goes to 0 for all PO batches of that product
- [ ] Stock page reloads and shows 0
- [ ] Activity log records the write-off with reason

### Stock Report Accuracy
- [ ] `Closing Balance` on stock report equals live `available_stock` in DB for all products (run for "current month" date range)
- [ ] `bill_out` includes only net sold (qty - total returns), not full qty (BUG-08 fixed)
- [ ] `Opening Balance` does not include quantities from Draft POs (BUG-09 fixed)
- [ ] Internal bill consumption appears in `stock_out` column

---

## Part 7 — Files Changed Summary

| File | Bugs addressed |
|---|---|
| `application/controllers/Purchase.php` | BUG-01, BUG-05, BUG-06, new write-off method |
| `application/controllers/Job.php` | BUG-02, BUG-03 |
| `application/controllers/InternalBill.php` | BUG-10 |
| `application/models/Purchase_model.php` | BUG-04, BUG-09, BUG-11 |
| `application/models/QuickBill_model.php` | BUG-07, BUG-08 |
| `application/views/stock/available-stock-list.php` | Delete/write-off button |
| `application/config/routes.php` | New write-off route |
