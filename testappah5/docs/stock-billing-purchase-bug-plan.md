# Stock ↔ Billing ↔ Purchase — Bug Analysis & Fix Plan

**Date:** 2026-05-24  
**Status:** Analysis complete — fixes in progress  
**Analyst:** Claude

---

## 1. How the System Is Supposed to Work

```
PURCHASE ORDER
  Draft → add items (available_stock set = quantity, but not visible in QB/Job yet)
  Completed → updateAvailableStockForCompletedPO() resets available_stock = quantity
  Archived → after full payment; items remain in purchase_order_items with live stock tracking

STOCK SOURCE OF TRUTH: purchase_order_items.available_stock
  ↑ Increases when: PO completed, bill deleted (restore), internal bill deleted (restore)
  ↓ Decreases when: Quick Bill saved, Job → Ongoing, product added to ongoing job, Internal Bill saved

QUICK BILL (/quick-bill)
  Products shown: purchase_order_items WHERE po.completed_by > 0 AND available_stock > 0
  Stock deducted: immediately on saveCompletedBill() — GREATEST(available_stock - qty, 0)

JOB (/job → Products/Spare Parts tab)
  Products shown: same as Quick Bill (loadAvailablePurchaseProducts)
  Stock deducted: on job→Ongoing OR when product added to already-ongoing job
  NOT deducted: when product added to a New job (only validated, not reserved)

INTERNAL BILL (/internal-bill)
  Stock deducted: immediately on bill save (reduceStockForItem)
  Stock restored on delete: should add back — currently BROKEN (see BUG-04)

STOCK VIEW (/item-stock)
  Shows: products from purchase_order_items grouped by product_id
  Calculates: open_balance + stock_in - stock_out for a date range
```

---

## 2. Root Cause of "4 products in QB vs 1 in Stock"

| Screen | SQL filter used |
|--------|----------------|
| Quick Bill products | `po.completed_by > 0` — includes **Completed AND Archived** POs |
| Stock view | `po.status = 'Completed'` — includes **only Completed** POs |

When a PO is fully paid, its status changes `Completed → Archived`. Its items:
- **Still appear** in Quick Bill and Job (because `completed_by > 0` still true)
- **Disappear** from Stock view (because `status = 'Completed'` is now false)

So with test data where 3 out of 4 POs have been archived, the stock page shows only 1 row.

---

## 3. Bug Inventory

### BUG-01 [CRITICAL] — Stock view excludes Archived POs
**File:** `application/models/Purchase_model.php` → `loadPurchaseProductsAsGroup()`  
**Line:** `$this->db->where('po.status', 'Completed');`  
**Problem:** After a PO is fully paid, its status becomes `Archived`. The stock view uses `status = 'Completed'` so archived POs vanish from the stock page — even though their `available_stock` is still being updated by Quick Bill / Job deductions.  
**Impact:** Stock page shows far fewer products than QB. Closing balance is wrong because partial data.  
**Fix:** Change to `po.completed_by > 0` (same as QB/Job) OR `po.status IN ('Completed', 'Archived')`.

---

### BUG-02 [CRITICAL] — Stock GROUP BY doesn't SUM available_stock
**File:** `application/models/Purchase_model.php` → `loadPurchaseProductsAsGroup()`  
**Line:** `$this->db->group_by('poi.product_id');` — with `SELECT poi.*`  
**Problem:** When a product appears in multiple POs (batch 1: 5 qty, batch 2: 3 qty), GROUP BY picks one row but doesn't SUM the available_stock across batches. The stock total shown is just one batch's value, not the real total.  
**Impact:** Stock quantity shown is wrong for any product purchased more than once.  
**Fix:** Add `SUM(poi.available_stock) as total_available_stock` in the SELECT. Use this in the view instead of `poi.available_stock`. Also sum `quantity` for stock_in reference.

---

### BUG-03 [CRITICAL] — `addProductToJob()`: `insert_id()` called after UPDATE (wrong ID)
**File:** `application/controllers/Job.php` → `addProductToJob()` ~line 488  
**Code:**
```php
if ($this->db->insert('services_job_items', $data)) {
    $job = $this->db->get_where(...)->row();
    if ($job && intval($job->status) === 1) {
        // deduct stock
        $this->db->update('purchase_order_items');      // ← UPDATE runs here

        $insertedId = $this->db->insert_id();           // ← BUG: insert_id() after UPDATE = 0
        $this->db->where('id', $insertedId);            // WHERE id = 0 → no row found
        $this->db->update('services_job_items', ['confirmed_by' => ...]); // ← never runs!
    }
}
```
**Problem:** `insert_id()` returns the ID of the last INSERT query. After an UPDATE runs, it returns 0. So the `confirmed_by` field is never set on the newly added job item.  
**Impact:** Product added to an ongoing job has:
- Stock deducted ✓
- `confirmed_by = 0` ✗ (appears unconfirmed)
- If `confirmJobItem(status=1)` is later clicked, stock is deducted **again** (double deduction)
- If job status is set to Ongoing again somehow, stock deducted a third time  

**Fix:** Capture `$insertedId = $this->db->insert_id()` immediately after the `$this->db->insert()` call, before any other queries.

---

### BUG-04 [HIGH] — `InternalBill` delete restores wrong stock amount
**File:** `application/controllers/InternalBill.php` → `deleteBill()` ~line 462  
**Code:**
```php
// WRONG: sets available_stock = item->quantity (original purchase qty), not current + restored
$this->purchase_model->updateAvailableStockForItem($item->po_item_id, $item->quantity);
```
**`updateAvailableStockForItem()` does:**
```php
UPDATE purchase_order_items SET available_stock = $new_quantity WHERE po_item_id = ?
```
**Problem:** This SETS `available_stock` to the bill item's quantity instead of ADDING it back. Example:
- Purchase qty = 10, sold 3 via QB → available = 7
- Internal bill uses 2 → available = 5
- Delete the internal bill → should restore to 7
- Bug: `available_stock` is SET to 2 (the bill quantity) → wrong!

The model's own `delete_internal_bill()` has correct `available_stock + qty` logic, but the controller bypasses it.  
**Fix:** In the controller's `deleteBill()`, replace `updateAvailableStockForItem` with inline `available_stock + item->quantity` increment, OR call the model's own `delete_internal_bill()` instead.

---

### BUG-05 [HIGH] — Stock report doesn't include Internal Bill consumption
**File:** `application/models/Purchase_model.php` → `getStockInOutSummary()` and `getOpenBalance()`  
**Problem:** Stock-out calculation counts:
- ✓ Job items (confirmed)
- ✓ Quick Bill items
- ✗ Internal Bill items — completely missing

**Impact:** Stock report shows inflated "closing balance" — items used internally are invisible.  
**Fix:** Add a third query for `internal_bill_items` grouped by `item_id` (which maps to `product_id` via `purchase_order_items`), and include in `stock_out` total. Same fix needed in `getOpenBalance()`.

---

### BUG-06 [MEDIUM] — No stock reservation for products added to New jobs (race condition)
**File:** `application/controllers/Job.php` → `addProductToJob()`  
**Problem:** When a job is in New status and a product is added, the stock is validated (check available_stock ≥ qty) but NOT deducted. If two users simultaneously add the same product to two different jobs and both jobs later go Ongoing, both deductions will run. `GREATEST(available_stock - qty, 0)` silently clamps to 0 without error — the second job's product is essentially "free" from stock that doesn't exist.  
**Impact:** Stock can silently go negative (clamped to 0) with no warning to staff.  
**Fix:** No perfect fix without locking, but a mitigation is: re-check stock at the moment job transitions to Ongoing, before deducting. If stock is insufficient, block the status change with a clear error message showing which items are short.

---

### BUG-07 [LOW] — Draft PO items get `available_stock` set immediately (misleading raw data)
**File:** `application/controllers/Purchase.php` → `addPurchaseOrderItem()`  
**Line:** `'available_stock' => $this->input->post('quantity')`  
**Problem:** Items in Draft POs get `available_stock` populated even though the PO is not completed. The item won't appear in QB/Job (because `completed_by = 0` filter blocks it), but raw DB data looks like stock exists.  
**Impact:** Low — doesn't cause functional errors because all queries filter by `completed_by > 0`. Misleading for direct DB inspection.  
**Fix:** Set `available_stock = 0` for Draft PO items. Only `updateAvailableStockForCompletedPO()` should set it when PO is completed. This is a cleanup, not urgent.

---

## 4. Fix Implementation Plan

Fixes are ordered by severity. Each should be implemented and tested before moving to the next.

| # | Bug | File to Change | Effort |
|---|-----|---------------|--------|
| F-01 | Stock view PO status filter | `Purchase_model.php` | 1 line |
| F-02 | Stock GROUP BY missing SUM | `Purchase_model.php` + `available-stock-list.php` view | 3 lines |
| F-03 | addProductToJob insert_id bug | `Job.php` | Move 1 line |
| F-04 | InternalBill delete stock restore | `InternalBill.php` | 3 lines |
| F-05 | Stock report missing internal bill | `Purchase_model.php` | ~20 lines |
| F-06 | Race condition mitigation | `Job.php` | ~15 lines |
| F-07 | Draft PO stock = 0 | `Purchase.php` | 1 line |

---

## 5. Verification Checklist (after all fixes)

### Purchase → Stock flow
- [ ] Create PO → add items → Complete PO → items appear in stock view with correct qty
- [ ] Same product purchased in 2 separate POs → stock view shows SUM of both batches
- [ ] Confirm PO payments → status = Archived → product still appears in stock view

### Quick Bill → Stock deduction
- [ ] Create Quick Bill with 2 units of product X → stock decreases by 2
- [ ] Delete that quick bill → stock increases by 2
- [ ] Edit quick bill (reduce quantity) → difference is returned to stock

### Job → Stock deduction
- [ ] Add product to New job → stock NOT changed
- [ ] Job → Ongoing → stock deducted for all products
- [ ] Add product to already-Ongoing job → stock deducted immediately AND confirmed_by is set
- [ ] Delete a confirmed product from job → stock returned
- [ ] Manually confirm/unconfirm a product item → stock deducted/returned correctly, no double deduction

### Internal Bill → Stock deduction
- [ ] Create Internal Bill with 3 units → stock decreases by 3
- [ ] Delete that internal bill → stock increases by 3 (NOT reset to original purchase qty)

### Stock report
- [ ] Stock-out column reflects QB + Job + Internal Bill consumption (not just QB + Job)
- [ ] Closing balance matches actual available_stock in purchase_order_items

---

## 6. Files to Change

| File | Bugs Fixed |
|------|-----------|
| `application/models/Purchase_model.php` | F-01, F-02, F-05 |
| `application/controllers/Job.php` | F-03, F-06 |
| `application/controllers/InternalBill.php` | F-04 |
| `application/controllers/Purchase.php` | F-07 |
| `application/views/stock/available-stock-list.php` | F-02 (use new `total_available_stock` field) |
