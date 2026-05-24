# CLAUDE.md — AutoHub Management System

## What This Project Is
**AutoHub** is a business management system for automotive service centers (garages/workshops).
It manages the entire business: service jobs, invoicing, inventory, purchasing, employee attendance, and financial accounts.

Built with **CodeIgniter 3** (PHP MVC) + **MySQL** database.

---

## Local Development Setup

### Requirements
- XAMPP installed at `/Applications/XAMPP/`
- PHP 8.2, MariaDB, Apache (all bundled in XAMPP)

### How to Start the App
1. Open XAMPP Manager → start **Apache** and **MySQL**
2. App is served from: `/Applications/XAMPP/htdocs/testappah5/`
3. Visit: `http://localhost/testappah5`

### Login Credentials (Local)
- **Email:** `troaautohub@gmail.com`
- **Password:** `123`

### Database (Local)
- **Host:** localhost
- **Database:** `trojwfss_autohubtest`
- **User:** `root`
- **Password:** (empty)
- **Config file:** `application/config/database.php`
- **SQL dump:** `/Users/binodbandara/Desktop/trojan/trojwfss_autohubtest.sql`

### Important Permissions (must be set after copying to XAMPP)
```bash
chmod -R 755 /Applications/XAMPP/htdocs/testappah5/
chmod 777 /Applications/XAMPP/htdocs/testappah5/application/cache/sessions/
xattr -cr /Applications/XAMPP/htdocs/testappah5/
```

### Key Config Files
| File | Purpose |
|---|---|
| `application/config/config.php` | Base URL (`http://localhost/testappah5/`) |
| `application/config/database.php` | DB credentials |
| `application/config/routes.php` | All URL routes |
| `.htaccess` | URL rewriting (CodeIgniter clean URLs) |

---

## Technology Stack
| Layer | Technology |
|---|---|
| Backend | PHP — CodeIgniter 3 (MVC framework) |
| Database | MySQL / MariaDB |
| Frontend | HTML, Bootstrap 4, JavaScript |
| PDF Generation | DOMPDF (via Composer) |
| Charts | ApexCharts, Chart.js |
| Tables | DataTables |
| Rich Text | CKEditor 5 |
| Icons | Font Awesome, Feather Icons |
| Notifications | SweetAlert2, Toastify |

---

## Project Structure
```
testappah5/
├── index.php                        ← Entry point (all requests go here)
├── .htaccess                        ← URL rewriting
├── composer.json                    ← PHP dependencies
├── application/
│   ├── config/
│   │   ├── config.php               ← Base URL, timezone, session, encryption key
│   │   ├── database.php             ← DB connection credentials
│   │   └── routes.php               ← Custom URL route definitions
│   ├── controllers/                 ← 31 controllers (one per module)
│   ├── models/                      ← 19 models (all DB queries live here)
│   ├── views/                       ← 117 HTML template files
│   │   └── layout/                  ← Shared: header, footer, sidebar, navbar
│   ├── core/
│   │   └── MY_Controller.php        ← Base controller: handles login + permission checks
│   ├── helpers/
│   │   └── auth_helper.php          ← has_permission(), require_permission() functions
│   └── libraries/
│       ├── Encrypt.php              ← XOR encryption (used for passwords)
│       └── Pdf.php                  ← PDF generation wrapper
├── system/                          ← CodeIgniter core — do NOT modify
└── assets/                          ← CSS, JS, images, libraries
```

---

## How the MVC Flow Works
```
Browser Request
  → .htaccess rewrites to index.php
  → CodeIgniter Router (routes.php)
  → Controller method
      → checks login session (MY_Controller)
      → checks permission (require_permission)
      → calls Model for DB queries
      → loads View with data
  → HTML response to browser
```

---

## Authentication & Permissions System

### Login
- Session-based login using CodeIgniter sessions
- Sessions stored in `application/cache/sessions/` (needs 777 permissions)
- Login controller: `application/controllers/Login.php`
- Login model: `application/models/Login_model.php`
- Passwords encrypted with XOR + base64 using key from `config.php`

### Role-Based Access Control (RBAC)
- Every user has one Role (Admin, Manager, Cashier, etc.)
- Every role has a set of Permissions
- `MY_Controller.php` checks session on every page load
- `require_permission('permission.name')` called at top of each controller method
- Helper functions in `auth_helper.php`: `has_permission()`, `require_permission()`

### Key Permission Names
- `quickbill.create`, `quickbill.view`
- `job.create`, `job.view`, `job.approve`
- `purchase.create`, `purchase.view`
- `reports.view`
- `admin.users`, `admin.roles`

---

## Main Modules & Their URLs

### Work Base (Daily Operations)
| Module | URL | Controller |
|---|---|---|
| Dashboard | `/home` | `Home.php` |
| Quick Bill (create) | `/quick-bill` | `QuickBill.php` |
| Quick Bill (list) | `/quick-bill-list` | `QuickBill.php` |
| Service Jobs (list) | `/job` | `Job.php` |
| Job Approval | `/job/approval` | `Job.php` |
| Internal Use | `/internal-bill` | `InternalBill.php` |

### Purchasing & Inventory
| Module | URL | Controller |
|---|---|---|
| Suppliers | `/supplier` | `Supplier.php` |
| Purchase Orders | `/purchase` | `Purchase.php` |
| Stock View | `/stock` | `StockUpdate.php` |
| Products | `/products` | `Products.php` |

### Finance / Wallet
| Module | URL | Controller |
|---|---|---|
| Payments | `/payments` | `Payments.php` |
| Accounts | `/accounts` | `Accounts.php` |
| Expenses | `/expenses` | `Expenses.php` |
| Advance (salary) | `/advance` | `Advance.php` |

### General / Master Data
| Module | URL | Controller |
|---|---|---|
| Customers | `/customers` | `Customer.php` |
| Vehicles | `/vehicles` | `Vehicle.php` |
| Vehicle Categories | `/vehicle-category` | `VehicleCategory.php` |
| Vehicle Brands | `/vehicle-brand` | `VehicleBrand.php` |
| Item Categories | `/item-category` | `ItemCategory.php` |
| Item Brands | `/item-brand` | `ItemBrand.php` |
| Services | `/services` | `Services.php` |
| Service Packages | `/service-packages` | `Services.php` |
| Service Item Categories | `/service-item-category` | `ServiceItemCategory.php` |
| Employees | `/employee` | `EmployeeSection.php` |
| Attendance | `/attendance` | `Attendance.php` |
| Expenses Categories | `/expenses-category` | `ExpensesCategory.php` |

### Reports
| Module | URL | Controller |
|---|---|---|
| Daily Report | `/reports/daily` | `Reports.php` |
| Monthly Report | `/reports/monthly` | `Reports.php` |
| Stock Report | `/reports/stock` | `Reports.php` |
| Expense Report | `/reports/expenses` | `Reports.php` |

### Administration
| Module | URL | Controller |
|---|---|---|
| System Roles | `/system-role` | `SystemRole.php` |
| System Users | `/system-user` | `SystemUser.php` |

---

## Database — Key Tables
| Table | Purpose |
|---|---|
| `users` | Staff login accounts |
| `system_roles` | Role definitions |
| `system_permissions` | All permission names |
| `system_role_permissions` | Which role has which permission |
| `customers` | Customer records |
| `vehicles` | Vehicle records (linked to customers) |
| `services_job` | Service job cards |
| `services_job_items` | Products/services on a job |
| `services_job_payments` | Payments for jobs |
| `quick_bill` | Quick invoice records |
| `quick_bill_items` | Items on a quick bill |
| `quick_bill_payments` | Payments for quick bills |
| `purchase_orders` | Supplier purchases |
| `purchase_order_items` | Products in a purchase |
| `products` | Product/spare part catalog |
| `inventory` | Stock levels per product |
| `accounts` | Financial accounts (cash, bank, loan) |
| `account_transactions` | All money in/out per account |
| `expenses` | Business expense records |
| `expenses_categories` | Expense category definitions |
| `attendance` | Employee attendance per day |
| `item_brands` | Product brands |
| `item_categories` | Product categories |
| `vehicle_brands` | Vehicle brands |
| `vehicle_categories` | Vehicle types |
| `service_items` | Services offered (labour) |
| `service_items_category` | Service categories |
| `service_packages` | Bundled service packages |
| `suppliers` | Supplier/vendor records |

---

## Service Job Workflow
```
NEW → ONGOING → COMPLETED → APPROVED → INVOICED
```
- **New:** Job card created
- **Ongoing:** Work in progress
- **Completed:** Work done, awaiting manager approval
- **Approved:** Manager signed off, invoice can be generated
- **Invoiced:** Customer billed and paid

---

## How Stock Works
- Stock **increases** when a Purchase Order is saved
- Stock **decreases** when products are used in a Service Job or Quick Bill
- Current stock levels visible at `/stock`

---

## How Payments Work
- Supported methods: Cash, Card, Bank Transfer, Cheque, Credit
- Each payment is linked to either a Quick Bill or a Service Job
- Payments update the relevant financial Account balance automatically

---

## Key Models to Know
| Model | Purpose |
|---|---|
| `General_model.php` | Shared/reusable DB queries used across modules |
| `Login_model.php` | Authentication, password management |
| `Services_model.php` | Service jobs, job items, job payments |
| `Products_model.php` | Product catalog and inventory |
| `Purchase_model.php` | Purchase orders and supplier payments |
| `QuickBill_model.php` | Quick billing logic |
| `Wallet_model.php` | Financial accounts and transactions |
| `Payment_model.php` | Payment recording and history |
| `SystemPermissions_model.php` | Permission management |
| `SystemRolePermissions_model.php` | Role-permission assignments |

---

## Shared Layout Files (edit these to change global UI)
- `application/views/layout/header.php` — HTML head, CSS imports
- `application/views/layout/top_navbar.php` — Top navigation bar
- `application/views/layout/left_sidebar.php` — Sidebar menu
- `application/views/layout/footer.php` — JS imports, closing tags

---

## Notes for Future Improvements
- When adding a new module: create Controller + Model + View + add route in `routes.php`
- When adding a new permission: add it to `SystemPermissions_model.php` → `ensurePermissionsExist()`
- When changing the sidebar menu: edit `application/views/layout/left_sidebar.php`
- When changing the top bar: edit `application/views/layout/top_navbar.php`
- The `.htaccess` sets `CI_ENV=production` — change to `development` locally to see detailed PHP errors
- Sessions directory must always have 777 permissions on local XAMPP
