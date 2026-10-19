# Picklymart

A Laravel-powered ecommerce storefront and local catalog management workspace for Picklymart. Browse and search products in the storefront, manage catalog data and media mappings in the admin panel, and connect trusted systems through an authenticated REST API.

## Features

- **Storefront:** Browse active products, search the catalog, and filter by category.
- **Catalog admin:** Create and manage products, category hierarchies, and brands.
- **Media repository:** Upload images or register existing images on a configured storage disk; map assets to products and categories.
- **Catalog API:** Manage products, categories, brands, and media assets with Laravel Sanctum administrator tokens.
- **Catalog import:** Import approved CSV exports using stable external source IDs.
- **Media maintenance:** Scan a configured disk for images and reconcile registered or missing media without deleting files.
- **Access control:** Admin-only panel and API; initial administrator is created locally and no default credentials are included.

## Tech stack

- PHP 8.3+
- Laravel 13
- MySQL or compatible MariaDB
- Laravel Sanctum
- Vite, Tailwind CSS 4, and JavaScript

## Requirements

Install PHP 8.3 or later with Composer, Node.js and npm, and a running MySQL/MariaDB server. Create a local database for the application before setup.

## Local setup

1. Clone the repository and enter the project directory:

   ```bash
   git clone https://github.com/Taofiq007/Picklymart-E-commerce-.git
   cd Picklymart-E-commerce-
   ```

2. Install dependencies and create your local environment file:

   ```bash
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   ```

3. Update `.env` with your local database details. For example:

   ```dotenv
   APP_NAME=Picklymart
   APP_URL=http://127.0.0.1:8000
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=picklymart_local
   DB_USERNAME=your_local_database_user
   DB_PASSWORD=your_local_database_password
   ```

4. Run the migrations, create the public storage link, and build the frontend:

   ```bash
   php artisan migrate
   php artisan storage:link
   npm run build
   ```

5. Create your administrator account. The password is entered through a hidden prompt and must be at least 12 characters:

   ```bash
   php artisan picklymart:create-admin
   ```

6. Start the local development services:

   ```bash
   composer run dev
   ```

   Open the storefront at [http://127.0.0.1:8000](http://127.0.0.1:8000), sign in at `/login`, and manage the catalog at `/admin`.

## Database setup options

The recommended setup is to create an empty local database, configure `.env`, and run `php artisan migrate`.

Alternatively, `database/Picklymart_Local.sql` is a standalone local install with fictional demo catalog records. **It contains `DROP TABLE` statements and will erase data in the tables it recreates. Only import it into a disposable, empty local database. Never import it into a production or shared database.** The dump does not create an administrator account; use `php artisan picklymart:create-admin` afterward.

## Catalog CSV import

Example CSV templates are in [`sample-data/`](sample-data/README.md). Review and adapt the files to your approved export, preserving stable `source_id` values, then run:

```bash
php artisan picklymart:import-catalog /path/to/approved-csv-directory
```

The importer updates matching source IDs and does not delete local catalog records. For the sample image to resolve, copy the contents of `sample-data/media/` into `storage/app/public/media/` before importing.

## Media repository

Images can be uploaded to the `public` disk or registered using a relative path on a configured Laravel filesystem disk. Existing files are checked for existence and image type before registration. Supported image formats are JPEG, PNG, WebP, and GIF.

Configure any central repository disk in `config/filesystems.php` and provide its credentials through local environment variables or a secret manager. Never commit storage credentials. Admin users can map registered assets to products and categories from the media page.

The scan command registers images it finds on the selected disk. Reconciliation marks registered files that no longer exist as missing and reports unregistered images; it does not delete files:

```bash
php artisan picklymart:scan-media media --disk=public
php artisan picklymart:reconcile-media media --disk=public
```

## API

All `/api/v1` endpoints require an administrator-owned Sanctum token. Create a token from **Admin → API**, then send it as a bearer token:

```http
Authorization: Bearer <ADMIN_TOKEN>
Accept: application/json
```

| Resource | Routes | Purpose |
| --- | --- | --- |
| Products | `/api/v1/products` | List, create, retrieve, update, and delete products |
| Categories | `/api/v1/categories` | Manage the category directory and hierarchy |
| Brands | `/api/v1/brands` | Manage brands |
| Media assets | `/api/v1/media-assets` | List, retrieve, upload, or register repository assets |
| Product media | `/api/v1/products/{product}/media` | Attach or detach product media |
| Category media | `/api/v1/categories/{category}/media` | Attach or detach category media |

To register a file that already exists on a configured disk, send JSON to `POST /api/v1/media-assets`:

```json
{
  "source_id": "central-image-123",
  "disk": "public",
  "path": "catalog/products/image.png"
}
```

For a new image, send the `file` field as multipart form data. A ready-to-import collection is available at [`postman/Picklymart_Local_API.postman_collection.json`](postman/Picklymart_Local_API.postman_collection.json).

## Tests

Run the automated test suite with:

```bash
php artisan test
```

Build the production frontend assets with:

```bash
npm run build
```

## Security

- Keep `.env`, database credentials, API tokens, and storage secrets private.
- `.env` is excluded from Git; use `.env.example` as a template.
- Create administrator credentials locally; never add passwords or tokens to source code.
- Treat the SQL dump as destructive and local-only.
- API access is restricted to administrator Sanctum tokens.

## License

This project currently has no repository-level `LICENSE` file. Contact the repository owner for permission before redistributing or reusing the project.
