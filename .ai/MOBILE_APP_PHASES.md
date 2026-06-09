# FitCRM Staff Mobile App — Phase Tracker

> Companion to [`MOBILE_APP_PLAN.md`](./MOBILE_APP_PLAN.md). This is the **living checklist** — tick items as they ship.
> Each phase has two halves: **Backend** (`/api/v1` staff API on `project-fit`) and **App** (native Flutter in `/Applications/MAMP/htdocs/fitcrm-app`).
> Status legend: `[ ]` todo · `[~]` in progress · `[x]` done.

---

## Listings sprint (shipped 2026-06-08)
A cross-phase "browse everything first" pass. Read-only listing endpoints + Flutter list screens for the Core 6, ahead of detail/action flows.

**Backend** — all under `auth:sanctum` + `studio.context`, host-scoped, paginated `{ data, links, meta }` via JSON Resources. Curl-verified end-to-end.
- [x] `GET /bookings` (status/source/payment/search filters) — `BookingResource`
- [x] `GET /class-sessions` (range today|week|month|all + status/plan/instructor/location) — `ClassSessionResource`
- [x] `GET /clients` (search/status/source/tag) — `ClientResource`
- [x] `GET /membership-plans` (status) — `MembershipPlanResource`
- [x] `GET /class-passes` (status) — `ClassPassResource`
- [x] `GET /payments/transactions` (status/type) — `TransactionResource`

**App** — `fitcrm-app`, mirrors dashboard pattern (riverpod `FutureProvider.autoDispose`, skeleton/error/empty/pull-to-refresh). Shared `widgets/list_states.dart`. `flutter analyze` clean.
- [x] Schedule tab → class-sessions list (week)
- [x] Clients tab → clients list
- [x] More tab → Bookings, Membership Plans, Class Passes, Payments list screens

> Still TODO for these resources: query-param filter UI, pagination/infinite-scroll (screens fetch first page only), and per-item detail screens + actions (tracked in their original phases below).

---

## Phase 0 — Foundation
**Goal:** Log in, pick studio, land on an empty themed shell.

**Backend**
- [x] Sanctum staff auth: `POST /api/v1/auth/login` → token + user + studios list
- [x] `GET /api/v1/auth/me`, `POST /api/v1/auth/logout`
- [x] `GET /api/v1/auth/studios`, `POST /api/v1/auth/switch-studio/{host}`
- [x] Host-scoping middleware for token requests (`ResolveStudioContext`, `X-Studio-Id` header, alias `studio.context`)
- [x] `UserResource` / `StudioResource` for mobile responses
- [x] Verified end-to-end with curl (login good/bad, me, studios, switch valid/invalid, logout, 401s)

**App**
- [x] Scaffold `fitcrm-app` Flutter project (pubspec: riverpod, dio, shared_preferences, flutter_svg, url_launcher, intl)
- [x] `core/api_config.dart` (per-platform host + `--dart-define` overrides; dev port 8001)
- [x] `core/theme.dart` (FitCRM brand #023E8A + Averta) + fonts
- [x] `core/responsive.dart` (phone vs tablet breakpoint)
- [x] `dioProvider` + bearer-token + `X-Studio-Id` interceptor + `AuthStore` (shared_preferences)
- [x] Models (`AppUser`, `Studio`) + `AuthRepository` + `AuthController` (AsyncNotifier)
- [x] Sign-in screen + choose-studio screen
- [x] Responsive shell (bottom nav on phone, nav rail on tablet) with placeholder tabs + profile/switch/logout menu
- [x] Root gate: loading → splash; no user → sign-in; no studio → choose-studio; ready → shell
- [x] `flutter analyze` clean + smoke test passing

---

## Phase 1 — Daily ops (highest value)
**Goal:** Run the day: see classes, check people in, take walk-ins, look up clients.

**Backend**
- [x] `GET /dashboard` (metrics, quick stats, today's classes, upcoming summary, alerts) — reuses ReportingService; curl-verified
- [ ] `GET /schedule`, `GET /class-sessions/{session}`
- [ ] `POST /schedule/check-in/{booking}`, `POST /schedule/mark-complete/{session}`
- [ ] `GET /walk-in/sessions`, `POST /walk-in/class/{session}`, `POST /walk-in/service/{slot}`
- [ ] `GET /walk-in/class/{session}/availability`, `GET /walk-in/payment-methods/{client}`
- [ ] `GET /clients` (+ leads/members/at-risk filters), `GET /clients/{id}`
- [ ] `GET /clients/search`, `POST /clients/quick-add`
- [ ] `POST /clients/{id}/note`, `PUT /clients/{id}/tags`, convert-to-client/member

**App**
- [x] Dashboard screen (stat cards, upcoming strip, today's classes, alerts) + skeleton loaders + pull-to-refresh; verified on simulator with live data
- [ ] Schedule screen (calendar/list) + session detail
- [ ] Check-in flow
- [ ] Walk-in booking flow (class/service, quick-add + search client, payment method)
- [ ] Clients list (filters) + search
- [ ] Client detail (info, notes, tags, convert actions)

---

## Phase 2 — Bookings & scheduling management
**Backend**
- [ ] `GET /bookings` (+ upcoming/cancelled/no-shows), `GET /bookings/{booking}`
- [ ] `POST /bookings/{booking}/cancel`, `/reactivate`, `/resend-intake`
- [ ] `GET /waitlist`, `PATCH /waitlist/{entry}/status|offer|cancel`
- [ ] Class sessions manage: publish/unpublish/cancel, duplicate, reassign-instructor
- [ ] `GET/POST /class-plans` (+ toggle-active)

**App**
- [ ] Bookings list + filters + detail
- [ ] Cancel / reactivate / resend intake actions
- [ ] Waitlist screen + actions
- [ ] Class session management actions
- [ ] Class plans list/create

---

## Phase 3 — Money & access
**Backend**
- [ ] `GET/POST /membership-plans` (+ toggle-status)
- [ ] `GET/POST /class-passes` (+ toggle, duplicate, sell, purchases)
- [ ] `GET /membership-checkin/{plan}` (membership check-in)
- [ ] `GET /payments/transactions`, `GET /payments/transactions/{transaction}`
- [ ] `POST /payments/transactions/{transaction}/confirm|cancel`

**App**
- [ ] Membership plans list/create
- [ ] Class passes list + sell flow
- [ ] Membership check-in screen
- [ ] Payments/transactions list + detail + confirm/cancel

---

## Phase 4 — People & operations
**Backend**
- [ ] `GET/POST /instructors`, `GET /instructors/{id}` (+ notes, certifications, toggle-status)
- [ ] `GET/POST /events` (+ publish/cancel, add clients, attendee check-in)
- [ ] Rentals: items, fulfillment, inventory adjust
- [ ] Space rentals: list, confirm/start/complete/cancel, availability
- [ ] `GET /questionnaires/{q}/responses`, `GET .../responses/{response}`

**App**
- [ ] Instructors list + detail
- [ ] Events list + detail + attendee check-in
- [ ] Rentals + fulfillment screens
- [ ] Space rentals screens
- [ ] Questionnaire responses viewer

---

## Phase 5 — Growth & settings
**Backend**
- [ ] `GET/POST /segments` (+ add/remove client, refresh, preview)
- [ ] `GET/POST /offers` (+ duplicate, toggle, validate-code)
- [ ] Settings: studio profile, locations, rooms, team users
- [ ] Reports/analytics aggregates
- [ ] Push notification registration + triggers

**App**
- [ ] Segments screens
- [ ] Offers screens
- [ ] Settings screens (profile, locations, rooms, team)
- [ ] Reports/analytics dashboards
- [ ] Push notifications

---

## Cross-cutting (apply every phase)
- [ ] Honor RBAC `permission:` middleware on every endpoint
- [ ] Host-scope every query by `host_id`
- [ ] Skeleton loaders on every async area (per CLAUDE.md)
- [ ] Consistent JSON Resource responses (`{ message, data }` + paginated `{ data, links, meta }`)
- [ ] Responsive layouts (phone stacks / tablet master-detail) per screen
- [ ] Error handling: friendly messages from Dio exceptions
- [ ] Verify each phase against running app (`php artisan serve --port=8001`)

---

## Decisions (resolved 2026-06-07)
- [x] Branding: **reuse fithq-app palette (#023E8A + Averta font)**
- [x] Multi-studio switching: **yes, in v1**
- [x] Phase 1 = **dashboard + schedule + check-in + walk-in + clients** (confirmed)
- [x] App: project `fitcrm_app`, org `com.fitcrm` → package `com.fitcrm.fitcrm_app`, location `/Applications/MAMP/htdocs/fitcrm-app`