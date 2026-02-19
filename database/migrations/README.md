# Database Migrations

This directory contains incremental SQL migration files for schema changes made after the initial `schema.sql` was created.

## How to Apply Migrations

Run each migration file in order against your database:

```bash
mysql -u <user> -p <database> < 001_add_poll_categories.sql
mysql -u <user> -p <database> < 002_add_category_to_polls.sql
```

## Migration Files

| File | Description |
|------|-------------|
| `001_add_poll_categories.sql` | Creates the `poll_categories` table and inserts default categories |
| `002_add_category_to_polls.sql` | Adds `category_id` column to `polls` table and adds the foreign key constraint |

## Notes

- Migrations must be applied in numeric order.
- Each migration is idempotent where possible (`CREATE TABLE IF NOT EXISTS`, `INSERT IGNORE`, `ADD COLUMN IF NOT EXISTS`).
- Always back up your database before applying migrations.
- The full current schema is available in `../schema.sql`.
