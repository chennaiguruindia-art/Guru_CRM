# Guru CRM

**Grow stronger customer relationships and run your horticulture business with confidence.**

Guru CRM is a business management system designed for horticulture companies. It brings customer relationships, plant and inventory management, sales, projects, maintenance, and day-to-day operations together in one place—helping your team stay organized and deliver dependable service.

## Features

- **Customer management:** Organize customer records, leads, visits, and follow-up activities.
- **Horticulture management:** Track plants, inventory, maintenance, and annual maintenance contracts (AMCs).
- **Sales and projects:** Manage estimations, quotations, projects, and work orders.
- **Business operations:** Coordinate employees, tasks, attendance, vendors, and purchases.
- **Finance and support:** Keep invoices, payments, expenses, complaints, and documents organized.
- **Visibility and control:** Use reports, a shared calendar, role-based permissions, and audit logs to support your team.

## Technology

- PHP 8.2 or later
- Laravel 12
- Node.js and npm

## Getting started

1. Install the required versions of PHP, Composer, Node.js, and npm.
2. Clone the repository and open a terminal in the project directory.
3. Configure your database connection in `.env`. If `.env` does not exist, copy `.env.example` to `.env`.
4. Install dependencies, generate the application key, run migrations, and build the frontend:

   ```bash
   composer run setup
   ```

5. Start the development environment:

   ```bash
   composer run dev
   ```

The application and its development services will start locally. Follow the URL printed by Laravel to open Guru CRM in your browser.

## Production build

Build the frontend assets for production with:

```bash
npm run build
```
