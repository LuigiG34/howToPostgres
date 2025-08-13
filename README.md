# howToPostgres

A tiny OOP PHP project that shows basic CRUD **and** Postgres-only features (UPSERT, RETURNING, ILIKE, JSONB, arrays, DISTINCT ON) using Docker.

---

## 1) Requirements

1. **Docker**
2. **Docker Compose**
3. (Windows) **WSL2 enabled**

---

## 2) Installation / Run

1. Start the PostgreSQL

```
docker compose up -d db
```

2. Build the PHP container
```
docker compose build php
```

3. Run the demo script and remove the container

```
docker compose run --rm php
```

---

### PostgreSQL reminder

Quick, simple notes about the Postgres features used here:

* **UPSERT** — *Insert or update in one shot.* When a unique key (e.g., email) already exists, update instead of failing.
  Example: `INSERT ... ON CONFLICT (email) DO UPDATE SET last_seen = EXCLUDED.last_seen`.

* **RETURNING** — *Get rows back from writes.* Add `RETURNING ...` to `INSERT/UPDATE/DELETE` to immediately get IDs or changed data.
  Example: `UPDATE todos SET done = TRUE WHERE ... RETURNING id, title`.

* **ILIKE** — *Case‑insensitive LIKE.* No need to fiddle with collations.
  Example: `WHERE title ILIKE '%hello%'`.

* **JSONB `@>`** — *JSON contains JSON.* Check if a JSON column includes a given fragment.
  Example: `WHERE data @> '{"type":"signup"}'::jsonb`.

* **Arrays + `ANY()`** — *Real array columns.* Test membership without a join table.
  Example: `WHERE 'php' = ANY(labels)` (for `labels text[]`).

* **`DISTINCT ON`** — *Pick one row per group using your ordering.* Great for “latest per device”.
  Example: `SELECT DISTINCT ON (device_id) ... ORDER BY device_id, created_at DESC`.
