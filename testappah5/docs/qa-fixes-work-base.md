# QA Fixes — Work Base Section
**Date:** 2026-05-24  
**Status:** All fixes applied and verified

This document records every bug found and fixed in the **Work Base** sidebar section during the QA pass. Use this as a checklist when verifying the app after a fresh XAMPP setup or a code merge.

---

## How to Verify After Setup

1. Start XAMPP → Apache + MySQL
2. Open `http://localhost/testappah5` → log in as `troaautohub@gmail.com / 123`
3. Go through each checklist below

---

## A. Internal Bill (Issue Note)

### Bugs Fixed

| # | Severity | Bug | File Changed |
|---|---|---|---|
| 1 | CRITICAL | Adding a product to an internal bill returned **404** — route `internalBill/addProductToBill` was missing | `application/config/routes.php` |
| 2 | CRITICAL | Product filter (by brand/category/supplier) returned **404** — route `internalBill/loadServiceProductsFilterListByProduct` was missing | `application/config/routes.php` |

**Routes added (lines 138-139):**
```php
$route['internalBill/addProductToBill'] = 'internalBill/addProductToBill';
$route['internalBill/loadServiceProductsFilterListByProduct'] = 'internalBill/loadServiceProductsFilterListByProduct';
```

### Verification Checklist
- [ ] Go to **Work Base → Issue Note**
- [ ] Select a brand or category from the filter dropdowns — product list should filter (no 404)
- [ ] Click the **+** button on any product — quantity modal should appear
- [ ] Confirm quantity and check the cart updates with the item
- [ ] Click **Complete Bill** — bill should save and appear in Issue Note List
- [ ] Open **Issue Note List** — the saved bill should appear with correct employee, date, and total

---

## B. Services Job

### Bugs Fixed (Round 1)

| # | Severity | Bug | File Changed |
|---|---|---|---|
| 3 | CRITICAL | Job list showed **0 / NULL** for Amount, Paid, Balance columns — `final_bill_amount`, `total_paid`, `total_balance` in `services_job` were never written to DB after item changes | `application/controllers/Job.php` |
| 4 | CRITICAL | `updateItemDiscount()` returned `{success: true}` but client JS checked `response.status === 'success'` — discount updates appeared to fail silently | `application/controllers/Job.php` + `application/views/job/services-job-items-list.php` |
| 5 | HIGH | Mobile validation error message said "required for new customer" but it applied to **all** job saves | `application/controllers/Job.php` |
| 6 | HIGH | Stock deduction on status → Ongoing had **no transaction wrapper** — a mid-loop DB error could leave stock partially deducted | `application/controllers/Job.php` |
| 7 | MEDIUM | Deleting an outsource part deleted **all** `services_job_items` rows with the same description — if two parts had the same name, both were deleted | `application/controllers/Job.php` |
| 8 | MEDIUM | 6 AJAX methods (add item, add product, add others, add outsource, add discount, confirm invoice) had **no permission checks** | `application/controllers/Job.php` |

**Key code changes in `application/controllers/Job.php`:**

- Added private `_recalculateJobTotals($jobId)` method — called after every item add, delete, or discount change. Queries `SUM(total_price)` from `services_job_items` (virtual column) and updates `services_job` with fresh `job_cost`, `final_bill_amount`, `total_paid`, `total_balance`.
- `addServicePackageToJob()` — changed from returning `bool` to echoing JSON, so AJAX caller gets a proper response.
- `updateJobStatus()` — all operations (status update, item reset, stock deduction) now wrapped in `trans_start() / trans_complete()`.
- `deleteOutsourcePart()` — added `$this->db->limit(1)` before the `services_job_items` delete.
- Permission checks added: `addServiceItemToJob` → `job.edit`, `addProductToJob` → `job.edit`, `addOthersItemToJob` → `job.edit`, `addOutsourcePartToJob` → `job.edit`, `addDiscountToJob` → `job.edit`, `confirmInvoice` → `job.invoice`.

### Bugs Fixed (Round 2 — this session)

| # | Severity | Bug | File Changed |
|---|---|---|---|
| 9 | CRITICAL | **SQL syntax error** in `loadvehicles()` — trailing comma after `customers.mobile AS customer_mobile,` in SELECT caused the query to fail, breaking the vehicle dropdown in the New Job modal | `application/models/Services_model.php` |
| 10 | HIGH | **Service type filter didn't work** — `WHERE service_type = 'N'` never matched jobs with comma-separated types like `"N,M"`. Changed to `FIND_IN_SET('N', service_type)` | `application/models/Services_model.php` |
| 11 | MEDIUM | **`$final_total_amount` used before initialization** — used in PHP `onclick` and `style` at line 92 (inside the items table loop) before it was computed at line 137, producing PHP notices and wrong cursor styling | `application/views/job/services-job-items-list.php` |
| 12 | LOW | **Duplicate HTML `id` on confirm/unconfirm buttons** — both the "confirm" and "undo-confirm" buttons had `id="confirm_<itemId>"` which is invalid HTML. Undo button renamed to `id="unconfirm_<itemId>"` | `application/views/job/services-job-items-list.php` |
| 13 | LOW | **Dead `if (true)` wrapper** around invoice totals rows — pointless always-true condition removed | `application/views/job/services-job-items-list.php` |
| 14 | LOW | **Dead `'sparepart'` item_type branch** — `elseif ($item->item_type == 'sparepart')` never executes since this type is never stored; branch removed | `application/views/job/services-job-items-list.php` |

### Verification Checklist
- [ ] Go to **Work Base → New Job** → click **New Jobs** button → modal opens → **vehicle dropdown must populate** (fix #9)
- [ ] In the vehicle dropdown, search for a vehicle with a known type (e.g. Normal) → the type filter on the list page should now also work (fix #10)
- [ ] Create a new job (existing or new customer)
- [ ] Open the job panel → add a **Service Item** — job list should show updated Amount immediately after closing the panel
- [ ] Add a **Product** (spare part) — Amount should update again
- [ ] Add an **Other/Labour** charge — Amount should update
- [ ] Add a **Service Package** — Amount should update
- [ ] Open a job item's discount modal → set a discount % → click Save → toast should show **"Discount updated successfully"** (not silent failure)
- [ ] Delete a job item — Amount should recalculate correctly
- [ ] Change a job status from **New → Ongoing** — stock should be deducted; if the DB query fails partway, no partial stock deduction should remain
- [ ] Add an **Outsource Part** with the same name as an existing outsource part → delete one → only ONE row should be removed from the job items list
- [ ] On the job panel items table, confirm an item → the **confirm button** should show check icon; undo → should show undo icon (no duplicate-id JS errors)
- [ ] Log in as a user without `job.edit` permission → try adding an item via URL directly → should be blocked
- [ ] On the **Type** filter dropdown in the job list, select "Normal" → only Normal jobs (including "Normal + Mechanical" combo jobs) should appear

---

## C. Quick Bill

### Bugs Fixed (Round 1)

| # | Severity | Bug | File Changed |
|---|---|---|---|
| 15 | HIGH | Edit form had **duplicate `id="editPaymentMethod"`** — the main form and the dynamically created payment-edit modal used the same ID, causing jQuery to target the wrong element | `application/views/quick-bill/edit-quick-bill.php` |
| 16 | MEDIUM | Edit payment modal had **no credit due date field** — when editing a credit payment, the due date could not be viewed or changed | `application/views/quick-bill/edit-quick-bill.php` |

**Changes in `application/views/quick-bill/edit-quick-bill.php`:**
- Main form payment method select renamed: `id="editPaymentMethod"` → `id="billPaymentMethod"`
- JS references updated: `$('#editPaymentMethod')` → `$('#billPaymentMethod')` in the enable-edit handler and `formData.append()`
- `showEditPaymentModal()`: added `needsCredit` flag; added `creditDueDateField` div with `#editCreditDueDate` input
- `toggleEditPaymentFields()`: now also toggles `#creditDueDateField` visibility
- `updatePaymentBtn` click handler: validates credit due date required when method = Credit; passes `credit_pay_date` to `updatePayment()` AJAX call

### Bugs Fixed (Round 2 — this session)

| # | Severity | Bug | File Changed |
|---|---|---|---|
| 17 | LOW | **`foreach ($products as $products)` variable collision** — loop variable shadowed the outer `$products` array; renamed to `$product` | `application/views/quick-bill/quick-bill.php` |

### Verification Checklist
- [ ] Go to **Work Base → Quick Bill** → product list should render (all columns correct)
- [ ] Select a brand/category/supplier filter → product list should filter correctly
- [ ] Add products to cart → bill summary should update
- [ ] Complete bill with Cash payment → success toast, bill saved
- [ ] Complete bill with Credit payment → credit due date field required
- [ ] Go to **Work Base → Quick Bill List** → open any existing bill
- [ ] Click **Edit** — page should enable editing without JS errors in the browser console
- [ ] Change the **Payment Method** dropdown — it should respond (not be stuck)
- [ ] Click **Edit** on an existing **Credit** payment → the modal should show a **Credit Due Date** field
- [ ] Change the due date and save → changes should persist
- [ ] Click **Edit** on a **Cash** payment → Credit Due Date field should be **hidden**

---

## D. Intentional Design Decisions (Not Bugs)

These were flagged during QA but confirmed as intentional:

| Item | Why It Is Correct |
|---|---|
| **Credit payments excluded from `total_paid`** | Credit means "pay later" — the customer has not actually paid cash. The balance correctly shows the full outstanding amount (including the credit portion). The `is_credit` badge on the job card signals this visually. |
| **Status 5 = Approved AND Invoiced (same value)** | Once a manager approves a job, it is immediately invoiceable. The system uses `confirmed_by`, `confirmed_at`, `print_access_*`, and `total_paid` as sub-state flags within status 5 — there is no need for a separate status 6. |

---

## Files Changed Summary

| File | What Changed |
|---|---|
| `application/config/routes.php` | Added 2 missing Internal Bill AJAX routes |
| `application/controllers/Job.php` | Added `_recalculateJobTotals()`, permission checks, transaction wrapper, LIMIT 1 on outsource delete, fixed response keys, fixed error message |
| `application/views/job/services-job-items-list.php` | Fixed JS `response.success` → `response.status === 'success'`; initialized `$final_total_amount` and `$bill_discounted_amount` at top; removed dead `'sparepart'` branch; fixed duplicate confirm/unconfirm button IDs; removed `if (true)` dead wrapper |
| `application/views/quick-bill/edit-quick-bill.php` | Fixed duplicate ID, added credit due date to payment edit modal |
| `application/views/quick-bill/quick-bill.php` | Fixed `foreach ($products as $products)` → `foreach ($products as $product)` |
| `application/models/Services_model.php` | Fixed trailing comma SQL syntax error in `loadvehicles()`; fixed service_type filter to use `FIND_IN_SET` |

---

## Next Steps (Not Yet Done)

The following sections still need QA review in future sessions:
- [ ] **Purchasing** section (Supplier, Purchase Order, Stock, Products)
- [ ] **Wallet** section (Payments, Cash, Bank, Credit, Loan, Expenses)
- [ ] **General** section (Service Packages, Members, Settings)
- [ ] **Others** section (Customers, Vehicles)
- [ ] **Reports** section
- [ ] **Admin Panel** section (Roles, Users)
- [ ] **Dashboard** (charts, stats accuracy)
