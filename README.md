# FrequentCRM

**Minimal, self-hosted CRM focused on speed and clarity.**

> A compact CRM with Customers, Deals (Pipeline), Tasks, Products, Notes, Files, Users, and a small API.

---

## 🔎 Table of Contents

- Quick Start
- Tech Stack
- Project Structure
- Features
- Database Tables (summary)
- API Endpoints (summary)
- Pages & Frontend
- Development Notes & Security
- Contributing

---

## 🚀 Quick Start

Prerequisites:
- Windows (XAMPP recommended) or any LAMP/LEMP stack
- PHP 7.4+ / PHP 8.x
- MySQL / MariaDB

Setup:
1. Place project in your web root (e.g., `c:\xampp\htdocs\crm_pro`).
2. Create a database and import the schema: `crm_pro.sql` (phpMyAdmin or `mysql -u root -p crm_pro < crm_pro.sql`).
3. Update DB credentials in `db.php` so PDO connects to your DB.
4. Ensure `uploads/` folder is writable by the webserver (for file & avatar uploads).
5. Start Apache/MySQL and open `landing.php` or `login.php` in your browser.

---

## 🧰 Tech Stack

- Backend: PHP (PDO with MySQL)
- Frontend: Bootstrap 5.3, Bootstrap Icons
- JS libs: Sortable (kanban), Chart.js (charts), FullCalendar
- Fonts: Google Fonts (Inter, Space Mono)
- Database: MySQL (schema in `crm_pro.sql`)

---

## 📁 Project Structure (high level)

- `index.php` — Main Dashboard & application UI
- `landing.php` — Landing / Marketing page + lead form
- `login.php`, `logout.php`, `forgot_password.php`, `reset_password.php`, `profile.php`
- `api.php` — JSON API endpoints (main application API)
- `public_api.php` — simplified endpoint for external web forms
- `db.php` — database / PDO connection (configure here)
- `functions.php` — utility functions (session start, redirect, helpers)
- `uploads/` — uploaded files & avatars
- `crm_pro.sql` — database schema dump

---

## ✅ Core Features

- Customer 360 (profile with deals, tasks, notes, files)
- Visual pipeline (Kanban drag-n-drop)
- Role-based access (admin, manager, sales_rep)
- Global search across Customers, Deals, Tasks, Products
- File uploads (attachments & avatars)
- Audit logs for changes
- CSV export (Customers / Deals)

---

## 🗄 Database Tables (summary)

| Table | Key Columns | Purpose |
|---|---:|---|
| `users` | `id`, `full_name`, `email`, `password`, `role`, `avatar` | App users and RBAC fields |
| `customers` | `id`, `first_name`, `last_name`, `email`, `company`, `status`, `potential_value`, `score`, `assigned_to` | Leads / accounts |
| `deals` | `id`, `customer_id`, `product_id`, `title`, `value`, `cost`, `profit`, `stage`, `due_date`, `assigned_to` | Pipeline deals |
| `products` | `id`, `name`, `type`, `price`, `cost`, `description` | Catalog items |
| `tasks` | `id`, `title`, `description`, `due_date`, `status`, `assigned_to`, `related_to`, `related_id` | Tasks & reminders |
| `notes` | `id`, `related_to`, `related_id`, `note`, `created_by`, `created_at` | Notes linked to entities |
| `files` | `id`, `filepath`, `related_to`, `related_id`, `created_at` | Uploaded files |
| `email_templates` | `id`, `subject`, `body` | Email templates used for previews |
| `audit_logs` | `id`, `user_id`, `entity_type`, `entity_id`, `field`, `old_value`, `new_value`, `created_at` | Change history |

> Note: The SQL file contains the authoritative schema. Use it as the source of truth.

---

## 🔗 Table Relations (Foreign Keys & Cardinality)

A concise summary of the relationships between tables (foreign keys and cardinality):

| From Table | FK Column | To Table | Cardinality | Notes |
|---|---|---|---|---|
| `customers` | `assigned_to` -> `users.id` | `users` | 1 (user) → * (customers) | Customer assignment to a user (nullable when unassigned)
| `deals` | `customer_id` -> `customers.id` | `customers` | 1 → * | A customer can have many deals
| `deals` | `product_id` -> `products.id` | `products` | 1 → * | Optional link to a product
| `deals` | `assigned_to` -> `users.id` | `users` | 1 → * | Assignee for a deal (sales rep)
| `tasks` | `assigned_to` -> `users.id` | `users` | 1 → * | Task assignee
| `tasks` | `related_to`,`related_id` | polymorphic | 1 → * | `related_to` indicates `customer`, `deal`, or `product`
| `notes` | `created_by` -> `users.id` | `users` | 1 → * | Author of note
| `notes` | `related_to`,`related_id` | polymorphic | 1 → * | Notes attached to customers/deals/tasks/etc.
| `files` | `related_to`,`related_id` | polymorphic | 1 → * | File attachments (customer, deal, task, product)
| `audit_logs` | `user_id` -> `users.id` | `users` | 1 → * | Which user made the change

> Polymorphic fields (`related_to`, `related_id`) are used across `tasks`, `notes`, and `files` to allow attaching records to multiple entity types.    

**Tips:**
- Add indexes on `assigned_to`, `customer_id`, `product_id`, and a composite index on (`related_to`,`related_id`) to improve query performance.
- Consider enforcing FK constraints in the database for stronger integrity (e.g., `deals.customer_id` → `customers.id`) and choose appropriate ON DELETE behavior (SET NULL or CASCADE) based on business rules.

---

## 🔗 API Endpoints (summary)

All requests go to `api.php?action=...`. Most write operations use POST.

| Action | Method | Important Params | Description |
|---|---|---|---|
| `get_stats` | GET | — | Dashboard counts and revenue |
| `get_chart_data` | GET | — | Pipeline & trend data for charts |
| `get_customers` | GET | `search` optional | Returns customer list |
| `add_customer` | POST | `first_name`, `last_name`, `email`, `company`, ... | Create customer |
| `update_customer` | POST | `id`, `first_name`, ... | Update customer |
| `get_deals` | GET | — | List deals |
| `add_deal` | POST | `customer_id`, `deal_title`, `deal_value`, ... | Create deal, auto-add task |
| `update_deal` | POST | `deal_id`, `deal_title`, `deal_value`, ... | Update deal (logs audit changes) |
| `update_deal_stage` | POST | `deal_id`, `new_stage` | Move deal across stages |
| `get_products` | GET | — | List products |
| `add_product` | POST | `prod_name`, `prod_price`, ... | Create product |
| `get_tasks` | GET | — | List tasks |
| `add_task` | POST | `task_title`, `task_date`, ... | Create task |
| `get_users` | GET | — | List users (includes `avatar`) |
| `add_user` | POST | `full_name`, `email`, `role` | Create user (Admins only)
| `upload_avatar` | POST (file) | `file` | Upload user avatar; updates `users.avatar` and session |
| `upload_file` | POST (file) | `file`, `related_to`, `related_id` | Upload attachment |
| `global_search` | GET | `q` | Search across customers, deals, tasks, products |
| `export_data` | GET | `type=customers|deals` | Download CSV |

Refer to `api.php` for full parameter lists and behaviors.

---

## 🧩 Frontend Pages (brief)

| Page | Purpose |
|---|---|
| `landing.php` | Public marketing page + lead capture form |
| `login.php` | Authentication |
| `index.php` | Main app dashboard (clients see sidebar, pipeline, charts) |
| `profile.php` | User profile & avatar upload |
| `public_api.php` | Public endpoint for third-party forms |

---

## 🛠 Developer Notes & Security (⚠️ important)

- DB: Most queries use prepared statements, but a few places interpolate GET params directly (e.g., `getCustomer360` uses `id` directly). Audit and replace with prepared statements to prevent SQL injection.
- Files: All uploads go to `uploads/`. Filenames are sanitized, but verify permissioning and file type checks in `api.php` when handling uploads.
- Sessions: `functions.php` contains `start_secure_session()` used to initialize sessions; check cookie settings (httponly/secure) in production.
- RBAC: Role enforcement exists in API (e.g., addUser restricts non-admins); verify all endpoints check roles where appropriate.
- Email templates: `generateEmailPreview` is used for previewing templates; no real SMTP send is included (there is a mock `sendEmailMock`).

---

## 🔐 Credentials & Configuration

**Where credentials are set (edit these locally)**
- **Database:** `db.php` — edit `$host`, `$db`, `$user`, `$pass` so PDO can connect to your DB.
- **SMTP (email sending):** `forgot_password.php` and `forgot_passwordEX.php` — update `$mail->Host`, `$mail->Username`, `$mail->Password`, and `$mail->Port` / encryption as needed (placeholders `YOUR_GMAIL_ADDRESS@gmail.com` / `YOUR_GMAIL_APP_PASSWORD` must be replaced).
- **Public form API key:** `public_api.php` — update `$validKey` (the embed example in `index.php` uses `crm_secret_key_123`).

**Security recommendations**
- **Never commit secrets** to the repo. Use environment variables (getenv / `$_ENV`) or a local `config.local.php` that is added to `.gitignore` and required by `db.php` and `forgot_password.php`.
- Prefer Gmail App Passwords or a dedicated SMTP service; do not use your main mailbox password.
- Rotate keys regularly and use long random strings for API keys.

**Quick example (safe local file)**
```php
// config.local.php (DO NOT COMMIT)
define('DB_HOST', 'localhost');
define('DB_NAME', 'crm_pro');
define('DB_USER', 'user');
define('DB_PASS', 'pass');
define('SMTP_USER', 'no-reply@example.com');
define('SMTP_PASS', 'xxxxxxxxxxxxxxxx');
```
Then update `db.php` and `forgot_password.php` to use these constants or `getenv()`.

> Note: `email_log.txt` contains development email output (useful for troubleshooting); remove or secure it in production.

- Public embed key: The public form sample includes a hardcoded API key `crm_secret_key_123` — rotate or secure this before production.

---

## ✅ Testing & Manual Checks

- Manual smoke tests: create customer, create deal, drag deal across pipeline, upload file/avatar, global search, export CSV.
- Verify file upload/remove and avatar changes reflect immediately (session refresh is handled in `index.php`).

---

## 🤝 Contributing

- Fork, open a feature branch, submit PR with clear description and tests (where appropriate).
- Keep changes small and document DB migrations.


---

**By Thai Jashe Group**
