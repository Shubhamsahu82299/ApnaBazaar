# PHP SPA Admin Dashboard

This is a lightweight PHP + MySQL single-page admin for:
- Product & Inventory Reports
- Sales Analytics (top products, revenue, profit)
- Customer Insights
- Operational Insights
- Variant management (CRUD + recompute product stock for dependent-managed products)

## Structure
```
/public/index.html   -> SPA UI (Tailwind + Chart.js via CDN)
/api/db.php          -> DB connection & JSON helpers  (edit credentials)
/api/analytics.php   -> Analytics endpoints
/api/products.php    -> Inventory endpoints
/api/variants.php    -> Variant CRUD
```

## Setup
1. Copy the `public` and `api` directories to your PHP host (Apache/Nginx).
2. Update DB credentials in `api/db.php`.
3. Make sure your database has the `products`, `product_variants`, `orders` tables (from your dumps).
4. Access `http(s)://your-host/public/index.html`.

If your document root is the project root, adjust the `API` base in `index.html` (line near top) to just `'/api'`.
