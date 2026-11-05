# Wellness API (Laravel) — Documentation

## Base URL
- Local example: `http://127.0.0.1:8000`
- All endpoints below are prefixed with `/api`

## Standard Response Envelope
All JSON responses are standardized to:

```json
{
  "success": true,
  "data": {},
  "message": "Optional message",
  "errors": {}
}
```

- **success**: `true` for HTTP status < 400, otherwise `false`
- **data**: response payload (object/array/pagination payload)
- **message**: optional string (usually used for errors or confirmations)
- **errors**: validation errors object (422), otherwise empty object

## Authentication
This API uses **Laravel Sanctum** personal access tokens.

- Send token as: `Authorization: Bearer <token>`

### Roles
- **User (Client)**: authenticates via `/api/user/*`
- **Coach**: authenticates via `/api/coach/*`
- **Admin**: authenticates via `/api/admin/*`

## Route Groups & Middleware
- **Public**: no auth
- **User**: `auth:sanctum`
- **Premium**: `auth:sanctum` + `subscription`
- **Coach**: `auth:sanctum` + `coach`
- **Admin**: `auth:sanctum` + `admin`

---

## Public Routes

### User Auth (Public)
- `POST /api/user/register`
- `POST /api/user/login`
- `GET /api/auth/google`
- `GET /api/auth/google/callback`

### Coach Auth (Public)
- `POST /api/coach/login`

### Admin Auth (Public)
- `POST /api/admin/login`

### Subscription Plans (Public)
- `GET /api/subscriptions/plans`

---

## User Routes (auth:sanctum)

### User Auth (User)
- `POST /api/user/logout`
- `GET /api/user/me`
- `PUT /api/user/profile`

Password reset tokens expire after 15 minutes. Google identity verification redirects verified, existing Google accounts to the reset-password page with a reset token. Configure the Google OAuth redirect URI as `http://127.0.0.1:8000/api/auth/google/callback` and set `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and `FRONTEND_URL` in the backend environment.

### Subscriptions (User)
- `POST /api/subscriptions/submit-payment`
- `GET /api/subscriptions/my-submissions`
- `GET /api/subscriptions/status`

---

## Premium Routes (auth:sanctum + subscription)

### Matching
- `POST /api/matches/preferences`
- `GET /api/matches/suggestions`
- `GET /api/matches/coaches`
- `GET /api/matches/coaches/{id}`
- `POST /api/matches/select`
- `GET /api/matches/my`

### Client Workouts
- `GET /api/client/workouts/plans`
- `GET /api/client/workouts/plans/{id}`
- `POST /api/client/workouts/logs`
- `GET /api/client/workouts/logs`
- `GET /api/client/workouts/stats`

### Client Meals
- `GET /api/client/meals/plans`
- `GET /api/client/meals/plans/{id}`
- `POST /api/client/meals/logs`
- `POST /api/client/meals/water`
- `GET /api/client/meals/logs`
- `GET /api/client/meals/stats`

### Client Progress
- `GET /api/client/progress/measurements`
- `POST /api/client/progress/measurements`
- `PUT /api/client/progress/measurements/{id}`
- `POST /api/client/progress/photos`
- `GET /api/client/progress/photos`
- `GET /api/client/progress/summary`

### Client Sleep
- `POST /api/client/sleep/logs`
- `GET /api/client/sleep/logs`
- `GET /api/client/sleep/summary`

Sleep log fields include `date`, optional `bedtime` and `wake_time`, required `duration_minutes` and `quality` (1-5), optional `interruptions`, and optional `notes`. A client can update the log for an existing date by posting that date again.

---

## Coach Routes (auth:sanctum + coach)

### Coach Auth (Coach)
- `POST /api/coach/logout`
- `GET /api/coach/me`
- `PUT /api/coach/profile`

### Coach Monitoring
- `GET /api/coach/monitoring/clients/{clientId}/overview`
- `GET /api/coach/monitoring/clients/{clientId}/workout-adherence`
- `GET /api/coach/monitoring/clients/{clientId}/meal-adherence`
- `GET /api/coach/monitoring/clients/{clientId}/measurements`
- `GET /api/coach/monitoring/clients/{clientId}/photos`
- `GET /api/coach/monitoring/clients/{clientId}/logs/sleep`
- `GET /api/coach/monitoring/clients/{clientId}/logs/{type}`
- `POST /api/coach/monitoring/clients/{clientId}/notes`

### Coach Plans
- `GET /api/coach/plans/clients`
- `GET /api/coach/plans/clients/{clientId}/workouts`
- `GET /api/coach/plans/clients/{clientId}/meals`
- `POST /api/coach/plans/workouts`
- `PUT /api/coach/plans/workouts/{id}`
- `POST /api/coach/plans/meals`
- `PUT /api/coach/plans/meals/{id}`
- `POST /api/coach/plans/{type}/{id}/activate`
- `DELETE /api/coach/plans/{type}/{id}`
- `GET /api/coach/plans/library/exercises`
- `GET /api/coach/plans/library/foods`

---

## Admin Routes (auth:sanctum + admin)

### Admin Auth (Admin)
- `POST /api/admin/logout`
- `GET /api/admin/me`

### Payments (Admin)
- `GET /api/admin/payments/pending`
- `POST /api/admin/payments/{id}/approve`
- `POST /api/admin/payments/{id}/reject`
- `GET /api/admin/payments/history`
- `POST /api/admin/payments/manual-subscription`
- `GET /api/admin/payments/stats`

### Users (Admin)
- `GET /api/admin/users`
- `GET /api/admin/users/{id}`
- `PUT /api/admin/users/{id}`
- `DELETE /api/admin/users/{id}`
- `POST /api/admin/users/{id}/restore`
- `POST /api/admin/users/{id}/subscription`

### Coaches (Admin)
- `GET /api/admin/coaches`
- `POST /api/admin/coaches`
- `GET /api/admin/coaches/{id}`
- `PUT /api/admin/coaches/{id}`
- `DELETE /api/admin/coaches/{id}`
- `POST /api/admin/coaches/{id}/suspend`

### Content (Admin)
- `GET /api/admin/content`
- `POST /api/admin/content`
- `PUT /api/admin/content/{id}`
- `DELETE /api/admin/content/{id}`
- `POST /api/admin/content/bulk-upload`

### Reports (Admin)
- `GET /api/admin/reports/users`
- `GET /api/admin/reports/revenue`
- `GET /api/admin/reports/coaches`
- `GET /api/admin/reports/export/{type}`
