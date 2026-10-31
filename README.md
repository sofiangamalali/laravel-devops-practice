# Minimal Laravel DevOps / Docker Practice Application

A minimal, lightweight Laravel application built for DevOps and Docker practice without authentication, frontend assets, or unnecessary packages.

## Quick Start

1. **Install dependencies**:
   ```bash
   composer install
   ```

2. **Configure environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Run database migrations**:
   ```bash
   php artisan migrate --force
   ```

4. **Start the local server**:
   ```bash
   php artisan serve
   ```

---

## Environment Variables

| Variable | Description | Example / Default |
|---|---|---|
| `APP_NAME` | Application name | `Laravel` |
| `APP_ENV` | Environment name | `production` / `local` |
| `APP_KEY` | Application encryption key | `base64:...` |
| `APP_DEBUG` | Debug mode (`false` recommended for production) | `false` |
| `APP_URL` | Base application URL | `http://localhost:8000` |
| `ENABLE_CRASH` | Controls `/crash` behavior (`1` enables termination, `0` returns 404) | `0` |
| `DB_CONNECTION` | Database driver | `pgsql` |
| `DB_HOST` | PostgreSQL hostname | `localhost` |
| `DB_PORT` | PostgreSQL port | `5432` |
| `DB_DATABASE` | PostgreSQL database name | `laravel` |
| `DB_USERNAME` | PostgreSQL database user | `postgres` |
| `DB_PASSWORD` | PostgreSQL database password | `secret` |
| `SESSION_DRIVER` | Session store (uses `array` so DB is not touched) | `array` |
| `CACHE_STORE` | Cache store (uses `array` so DB is not touched) | `array` |
| `QUEUE_CONNECTION` | Queue driver (uses `sync` so DB is not touched) | `sync` |
| `REDIS_CLIENT` | Redis client driver (`predis` eliminates PHP ext requirement) | `predis` |
| `REDIS_HOST` | Redis hostname | `localhost` |
| `REDIS_PORT` | Redis port | `6379` |
| `REDIS_PASSWORD` | Redis password (if required) | `null` |

---

## Endpoints & Curl Examples

### 1. GET `/health`
Health check endpoint. Returns hardcoded constant version. Does not touch DB or Redis.

```bash
curl -X GET http://localhost:8000/health
```

**Response (200 OK):**
```json
{
  "status": "ok",
  "version": "1.0.0"
}
```

---

### 2. POST `/notes`
Creates a new note stored in PostgreSQL. Validates input (`text` is required, string, max 500 characters). Returns 503 if PostgreSQL is unavailable.

```bash
curl -X POST http://localhost:8000/notes \
  -H "Content-Type: application/json" \
  -d '{"text": "First note for DevOps practice"}'
```

**Response (201 Created):**
```json
{
  "id": 1,
  "text": "First note for DevOps practice",
  "created_at": "2026-10-29T00:00:00.000000Z",
  "updated_at": "2026-10-29T00:00:00.000000Z"
}
```

**Validation Error Response (422 Unprocessable Content):**
```json
{
  "message": "The text field is required.",
  "errors": {
    "text": [
      "The text field is required."
    ]
  }
}
```

---

### 3. GET `/notes`
Retrieves the latest 50 notes ordered by newest first (`created_at desc`, then `id desc`). Returns 503 if PostgreSQL is unavailable.

```bash
curl -X GET http://localhost:8000/notes
```

**Response (200 OK):**
```json
[
  {
    "id": 1,
    "text": "First note for DevOps practice",
    "created_at": "2026-10-29T00:00:00.000000Z",
    "updated_at": "2026-10-29T00:00:00.000000Z"
  }
]
```

---

### 4. GET `/stats`
Atomically increments a hit counter in Redis using `INCR stats:hits`. Returns 503 if Redis is unavailable.

```bash
curl -X GET http://localhost:8000/stats
```

**Response (200 OK):**
```json
{
  "hits": 1
}
```

---

### 5. GET `/crash`
Terminates the PHP process immediately with exit code 1 if `ENABLE_CRASH=1`. Otherwise returns 404 JSON.

```bash
curl -X GET http://localhost:8000/crash
```

**Response when `ENABLE_CRASH=0` (404 Not Found):**
```json
{
  "error": "not_found"
}
```

**Behavior when `ENABLE_CRASH=1`:**
Terminates process with non-zero exit code (`exit(1)`).

---

## Service Unavailable Behavior

When PostgreSQL or Redis is down, connections timeout within ~3 seconds and return clean 503 JSON responses rather than hanging or returning 500 stack traces:

- Database failure: `{"error": "database_unavailable"}` (HTTP 503)
- Redis failure: `{"error": "redis_unavailable"}` (HTTP 503)
