# FitCRM Staff Mobile App — Plan

> Native Flutter app for studio **staff/owners** to run their studio from a phone or iPad.
> Mirrors the architecture of the existing `fithq-app` (consumer app), adapted to FitCRM's B2B CRM domain.
> **No WebView** — real native screens talking to a new `/api/v1` mobile API on `project-fit`.

---

## 1. Goal & Audience

- **Who:** Studio staff and owners (the people who use the FitCRM web dashboard today).
- **What:** Manage daily studio operations on mobile — see today's classes, check in clients, take walk-in bookings, look up clients, manage bookings/memberships/payments, etc.
- **Why native (not WebView):** the smooth "real app" feel, fast navigation, push notifications later, and offline-friendly caching.
- **Form factors:** iPhone/Android **and** iPad/tablet — both native, with responsive layouts (phone = bottom-nav stacks; tablet = nav rail + master-detail).

---

## 2. Architecture (reuse fithq-app's exact patterns)

| Concern | Choice (same as fithq-app) |
|---|---|
| State management | **Riverpod** (`flutter_riverpod ^3.x`) — AsyncNotifier, FutureProvider.family/autoDispose |
| Networking | **Dio** singleton via `dioProvider`, baseURL = `{origin}/api/v1`, bearer token interceptor |
| Local storage | **shared_preferences** — auth token, cached user, current studio id |
| Structure | feature-first: `core/`, `models/`, `data/` (repositories), `features/` (screens+widgets) |
| Config | `core/api_config.dart` (per-platform host detection, `--dart-define` overrides) |
| Theme | `core/theme.dart` — FitCRM brand colors + fonts (Material 3, light) |
| Navigation | Manual `Navigator` + a root gate; responsive shell (bottom nav vs nav rail) |
| Auth | **Laravel Sanctum** bearer tokens |

**Project location:** new sibling folder `/Applications/MAMP/htdocs/fitcrm-app` (parallel to `fithq-app`).

**Proposed `lib/` layout:**
```
lib/
  main.dart                      # ProviderScope + root gate (auth → studio pick → shell)
  src/
    core/  api_config.dart, theme.dart, responsive.dart
    models/  app_user.dart, studio.dart, client.dart, class_session.dart,
             booking.dart, membership.dart, payment.dart, instructor.dart, ...
    data/    auth_repository.dart, dashboard_repository.dart, schedule_repository.dart,
             clients_repository.dart, bookings_repository.dart, walkin_repository.dart, ...
    features/
      auth/        sign_in, choose_studio
      shell/       responsive_scaffold (bottom nav / nav rail)
      dashboard/   today's classes, upcoming bookings, alerts
      schedule/    calendar + session detail + check-in
      walkin/      book class/service, quick-add/search client
      clients/     list (leads/members/at-risk), detail, notes, tags
      bookings/    list, detail, cancel/reactivate, waitlist
      memberships/ plans, passes, sell
      payments/    transactions list + detail
      instructors/ list + detail
      more/        events, rentals, questionnaires, settings, profile
```

---

## 3. Backend work — the API gap (required)

Today `project-fit/routes/api.php` only covers signup, address, questionnaire-builder, walk-in, and fitnearyou-sync. **Most CRM features are web routes (Blade/Vue), so a mobile API must be added.**

Approach: add a **`/api/v1` staff API** that reuses existing `Host\*` controllers' service/query logic (don't duplicate business rules). Key pieces:

- **Auth (Sanctum):** `POST /auth/login` (email/password → token + user + studios list), `GET /auth/me`, `POST /auth/logout`, `GET /auth/studios`, `POST /auth/switch-studio/{host}`.
- **Tenant scoping:** resolve `host_id` from the authenticated user's current studio (carried in token/state), reusing the existing `ResolveTenant`/host-scoping logic. Every endpoint stays host-scoped.
- **Per feature**, expose read + key write endpoints, e.g.:
  - Dashboard: `GET /dashboard` (today's classes, upcoming bookings, alerts).
  - Schedule: `GET /schedule`, `GET /class-sessions/{s}`, `POST /schedule/check-in/{booking}`, `POST /schedule/mark-complete/{session}`.
  - Walk-in: `GET /walk-in/sessions`, `POST /walk-in/class/{session}`, `POST /walk-in/service/{slot}`, `POST /clients/quick-add`, `GET /clients/search`.
  - Clients: `GET /clients`, `GET /clients/{id}`, `POST /clients/{id}/note`, `PUT /clients/{id}/tags`, convert endpoints.
  - Bookings/waitlist, memberships/passes, payments, instructors, events, etc. — added per phase.
- **Permissions:** honor existing RBAC (`permission:` middleware) so staff only see what their role allows.
- **Responses:** JSON Resources with `{ message, data }` + paginated `{ data, links, meta }` (same conventions as `project-fitHQ`).

---

## 4. Phased delivery

**Phase 0 — Foundation**
Flutter scaffold, theme, api_config, Dio+token store, responsive shell. Backend: Sanctum login, `/auth/me`, studios list + switch-studio. Deliverable: log in, pick studio, see an empty themed shell.

**Phase 1 — Daily ops (highest value)**
Dashboard (today's classes, upcoming bookings, alerts), Schedule view + session detail, **Check-in**, **Walk-in booking** (class/service, quick-add & search client), Clients list/search/detail + notes + tags.

**Phase 2 — Bookings & scheduling management**
Bookings (upcoming/cancelled/no-shows) + detail + cancel/reactivate, Waitlist, Class sessions manage (publish/cancel/duplicate/reassign), Class plans.

**Phase 3 — Money & access**
Membership plans, Class passes (+ sell), Membership check-in, Payments/transactions list + detail.

**Phase 4 — People & operations**
Instructors, Events (+ attendee check-in), Rentals & space rentals, Questionnaire responses.

**Phase 5 — Growth & settings**
Segments, Offers, Settings (studio profile, locations, rooms, team), Reports/analytics, push notifications.

---

## 5. iPad/tablet handling

Single codebase, responsive: `core/responsive.dart` detects width.
- **Phone:** bottom navigation + full-screen pushes.
- **Tablet/iPad:** left **navigation rail** + **master-detail** (e.g. client list on the left, detail on the right). Same Riverpod providers feed both.

---

## 6. Open questions / decisions to confirm

1. **Branding:** FitCRM brand colors + fonts for `theme.dart` (fithq-app uses #023E8A + Averta). Reuse or different palette?
2. **Multi-studio:** confirm staff-with-multiple-studios switching is needed in v1 (backend supports `host_user` pivot).
3. **Phase 1 must-haves:** confirm the daily-ops set above is the right first slice.
4. **Package ids / app name:** e.g. `com.fitcrm.staff`, display name "FitCRM".

---

## 7. Next step

On approval: scaffold `fitcrm-app` (Phase 0) + add the Sanctum staff-auth endpoints to `project-fit`, then iterate phase by phase. Each phase = backend endpoints + matching Flutter screens, verified against the running app (`php artisan serve --port=8001`).
