# Hostinger Deployment

## 1. Upload
Upload the **contents** of this ZIP into Hostinger `public_html`.

## 2. MySQL
Create a MySQL database and user in Hostinger, then import:

`sql/schema.sql`

For an existing installation, run only the needed migration files in `sql/`.

## 3. Database credentials
Edit:

`config/database.php`

Set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS`.

## 4. Domain
Edit:

`config/config.php`

Set `APP_URL` to your real HTTPS domain, for example:

`https://yourdomain.com`

Do not add a trailing slash.

## 5. Admin
Open:

`https://yourdomain.com/backend/login.php`

## 6. Customer QR menu
The QR for each table points to:

`https://yourdomain.com/index.html?table=TOKEN`

## 7. Important folders
- `index.html` = customer frontend
- `assets/css/` = CSS
- `assets/js/` = frontend JavaScript
- `api/` = JSON/API backend
- `backend/` = PHP admin/staff/kitchen pages
- `config/` = database + application configuration
- `sql/` = database schema and migrations
- `uploads/` = uploaded restaurant logos
