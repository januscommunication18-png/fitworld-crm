# FitCRM & FitNearYou - AI Planning Document

> **This is the living planning document for all AI-assisted development.**
> Every recommendation, architectural decision, approach, and requirement is logged here.
> Use this file as the base template to check flow, requirements, and decisions before implementing anything.
>
> **This file is checked into GitHub but must NOT be deployed to production or dev branches.**

---

## How to Use This Document

1. **Before starting any feature** — check this document for prior decisions that affect your work
2. **After making a decision** — log it here with date, context, and reasoning
3. **When planning a feature** — add the plan here before writing code
4. **When changing an approach** — update the relevant section and note why it changed

---

## Table of Contents

1. [Architecture Decisions](#1-architecture-decisions)
2. [Feature Plans](#2-feature-plans)
3. [Database Design](#3-database-design)
4. [API Design](#4-api-design)
5. [Frontend Approach](#5-frontend-approach)
6. [Third-Party Integrations](#6-third-party-integrations)
7. [Open Questions](#7-open-questions)
8. [Decision Log](#8-decision-log)

---

## 1. Architecture Decisions

### ADR-001: Single Database Tenancy for Host Platform

- **Status:** Accepted
- **Context:** Host businesses need isolated data but share the same application
- **Decision:** Single database with `host_id` foreign key on all tenant-scoped tables
- **Reasoning:** Simpler to manage than multi-database. Good enough for 1-3 location studios with 1-15 instructors. Can migrate to multi-database later if needed.
- **Consequences:** Every query on tenant tables must include `host_id` scope. Global scopes or middleware handle this automatically.

### ADR-002: Vue.js Hybrid (Not SPA)

- **Status:** Accepted
- **Context:** Need interactive UI components without losing Laravel's SSR benefits
- **Decision:** Mount Vue into specific `<div id="app">` areas within Blade templates. Each page gets its own Vue entrypoint.
- **Reasoning:** Keeps SEO, keeps Laravel routing, avoids complexity of full SPA + API tokens. Easy to adopt incrementally.
- **Consequences:** No Vue Router. No Vuex/Pinia for global state across pages. Each page is self-contained.

### ADR-003: Single Codebase for Customer + Host

- **Status:** Accepted
- **Context:** Two products (fitnearyou.com + fitcrm.net) need to share models, services, and UI components
- **Decision:** Single Laravel codebase. Separated by route files, controller namespaces, and view directories.
- **Reasoning:** Shared booking logic, shared user models, shared design system. Two repos would mean duplicating a lot of code.
- **Consequences:** Need clear directory structure. Route files must be separate. Middleware must distinguish customer vs host requests.

### ADR-004: FlyonUI as Component Library

- **Status:** Accepted
- **Context:** Need a Tailwind-based component library for rapid UI development
- **Decision:** Use FlyonUI for all UI components
- **Reasoning:** Built on Tailwind CSS, includes JS components (modals, dropdowns, etc.), clean design, actively maintained.
- **Consequences:** All developers must reference `CSS_README.md` and `.ai/INSTRUCTIONS.md` for approved component patterns.

### ADR-005: No CDN in Production

- **Status:** Accepted
- **Context:** CDN links create external dependencies and potential downtime risks
- **Decision:** All libraries must be installed via npm and served locally
- **Reasoning:** Performance (no external DNS lookups), reliability (no CDN outages), security (no supply chain risk from CDN).
- **Consequences:** Must install all libraries via npm. Must configure Vite to bundle them.

### ADR-006: Skeleton Loading Pattern

- **Status:** Accepted
- **Context:** Pages with async data need loading states
- **Decision:** Use skeleton loading (animate-pulse placeholders) instead of spinners
- **Reasoning:** Better perceived performance. Layout doesn't shift when data loads. Professional feel.
- **Consequences:** Every Vue component that fetches data must have a `loading` state with skeleton markup.

### ADR-009: Dropdowns with Search

- **Status:** Accepted
- **Context:** Select inputs with many options (timezones, countries, currencies) need better UX
- **Decision:** Use FlyonUI "Select with Search" pattern for all dropdowns with more than 5-6 options
- **Reasoning:** Better user experience for long lists. Allows quick filtering. Professional feel.
- **Reference:** See `test-components.html` for implementation pattern using `advance-select` component
- **Consequences:** All select elements with many options should use the searchable dropdown pattern from FlyonUI.

### ADR-007: Manual Auth (Not Laravel Breeze)

- **Status:** Accepted
- **Context:** Need login/logout for host dashboard. Breeze scaffolds its own views/controllers that conflict with FlyonUI layout.
- **Decision:** Manual `AuthController` with `showLogin()`, `login()`, `logout()` methods. Custom login Blade page using FlyonUI components.
- **Reasoning:** Breeze would overwrite the existing layout and introduce Tailwind-only components. Manual auth is simpler and keeps FlyonUI consistency.
- **Consequences:** No password reset or registration via Breeze. Signup handles registration. Password reset to be built manually later.

### ADR-008: Progressive Per-Step Signup Save

- **Status:** Accepted
- **Context:** Signup wizard has 9 steps. Need to decide whether to save all data at the end or progressively per-step.
- **Decision:** Progressive per-step save. Account (User + Host) created at Step 2. Steps 3-8 update the Host record via authenticated API calls. Step 9 marks onboarding complete.
- **Reasoning:** Prevents data loss if user abandons mid-flow. Enables resume-from-where-you-left-off. Each step validates independently.
- **Consequences:** API endpoints need Sanctum auth for Steps 3-9. Step 2 is public. Re-save pattern (delete + recreate) used for instructors/classes to handle users going back.

---

## 2. Feature Plans

### Template for New Features

```
### FEAT-XXX: Feature Name

- **Status:** Planned / In Progress / Completed / Cancelled
- **Priority:** P0 (critical) / P1 (high) / P2 (medium) / P3 (low)
- **Description:** What this feature does
- **User Story:** As a [customer/host], I want to [action] so that [benefit]
- **Affected Areas:** Which modules, routes, views, components
- **Dependencies:** What must be built first
- **Approach:** How we plan to implement it
- **Open Questions:** Unresolved decisions
- **Notes:** Additional context
```

### FEAT-001: Signup / Onboarding Wizard (9 Steps)

- **Status:** Completed (frontend + backend)
- **Priority:** P0 (critical)
- **Description:** 9-step signup wizard for studio owners at `/signup`
- **User Story:** As a studio owner, I want to create my account and configure my studio in a guided flow so that I can start using FitCRM quickly.
- **Affected Areas:** `routes/web.php`, `routes/api.php`, `SignupController` (web + API), `signup.blade.php`, 12 Vue components under `resources/js/components/signup/`, 5 Form Request classes
- **Dependencies:** Laravel scaffold, Vite + Vue build pipeline, Sanctum
- **Approach:**
  - Standalone Blade page (no dashboard layout) loads `signup.js` Vue entrypoint
  - `SignupWizard.vue` orchestrates all 9 steps via `currentStep` ref and dynamic `<component :is="...">`
  - Each step component uses `reactive()` local state, emits `update` + `next`/`prev` to parent
  - `ProgressBar.vue` shows progress for steps 2-8
  - `PasswordStrength.vue` provides real-time password validation with 4 rules + colored meter
  - Steps: Welcome → Account → Email Verify → Studio Basics → Location → Instructors → Class Setup → Payments → Go Live
  - **Backend:** 9 API endpoints under `/api/v1/signup/` with progressive per-step saving (ADR-008)
  - **Frontend-Backend wiring:** `SignupWizard.vue` calls API on each `nextStep()`, displays server errors under fields, shows loading states, uses Notyf toasts for feedback
  - **Subdomain check:** Step 4 has debounced availability check with green/red indicator
  - **Completion:** Step 9 calls `/signup/complete` which sets `is_live = true`, dispatches `HostOnboardingCompleted` event, then redirects to dashboard
- **Notes:** Step 3 (Email Verification) is non-blocking — users can continue setup without verifying email

### FEAT-002: Dashboard Layout

- **Status:** Completed (static layout)
- **Priority:** P0 (critical)
- **Description:** Master dashboard layout with navbar, collapsible sidebar, footer, search modal, alerts drawer, app modal, breadcrumbs
- **User Story:** As a studio owner, I want a professional dashboard interface so that I can navigate all CRM features efficiently.
- **Affected Areas:** `resources/views/layouts/dashboard.blade.php`, 7 Blade components in `resources/views/components/`
- **Dependencies:** Laravel scaffold, Vite build, FlyonUI JS
- **Approach:**
  - FlyonUI `overlay [--is-layout-affect:true]` sidebar that pushes content when open
  - `overlay-minified:` prefix classes for icon-only collapsed mode
  - Dropdown submenus use `overlay-minified:[--trigger:hover]` to show on hover when sidebar is collapsed
  - Navbar uses FlyonUI `navbar` with profile dropdown
  - Search modal (`overlay modal`), alerts drawer (`overlay drawer drawer-end`), app modal (`overlay modal`)
  - Content wrapper uses `sm:overlay-layout-open:ps-64 overlay-layout-open-minified:ps-17` for responsive padding
  - Active sidebar items determined by `request()->is()` Blade directive
- **Notes:** All components are static HTML/Blade. JavaScript interactivity handled by FlyonUI's built-in JS.

### FEAT-003: Authentication System

- **Status:** Completed
- **Priority:** P0 (critical)
- **Description:** Manual login/logout for host dashboard with route protection
- **User Story:** As a studio owner, I want to log in securely so that only I can access my dashboard.
- **Affected Areas:** `AuthController`, `login.blade.php`, `routes/web.php`, `navbar.blade.php`, `sidebar.blade.php`
- **Approach:**
  - Manual `AuthController` with `showLogin()`, `login()`, `logout()` (ADR-007)
  - Standalone login page (not dashboard layout), centered card, FlyonUI styled
  - `guest` middleware on login/signup routes, `auth` middleware on all dashboard routes
  - Navbar shows `Auth::user()->full_name` and `Auth::user()->email`, POST logout form with CSRF
  - Sidebar submenu items use `request()->is()` for active state detection

### FEAT-004: Dashboard Navigation Pages (10 pages)

- **Status:** Completed (shell pages with skeletons)
- **Priority:** P1 (high)
- **Description:** All sidebar navigation links resolve to real Blade pages with proper layout, breadcrumbs, and skeleton loading
- **User Story:** As a studio owner, I want to navigate to all sections of my dashboard so I can manage my studio.
- **Affected Areas:** 6 controllers, 10 Blade views, `routes/web.php`
- **Approach:**
  - Each page extends `layouts.dashboard` with breadcrumbs, skeleton loading, and empty states
  - Pages: Class Schedule, Appointments, Calendar, Students, Instructors, Transactions, Memberships, Class Packs, Reports, Settings
  - Controllers: `ScheduleController`, `StudentController`, `InstructorController`, `PaymentController`, `ReportController`, `SettingsController`
  - Settings page displays real `Auth::user()->host` data
  - Dashboard page shows dynamic counts (classes, instructors), personalized welcome, onboarding prompt

<!-- Add feature plans below this line -->

---

## 3. Database Design

### Customer Database (`customer_db`)

```
Tables (planned):
- users                    — customer accounts
- bookings                 — class/appointment bookings
- reviews                  — studio reviews
- favorites                — saved studios
- search_history           — search analytics
```

### Host Database (`fitcrm_db`)

```
Tables (implemented):
- hosts                    — host business accounts (tenant table)
  Columns: id, studio_name, subdomain (unique), studio_types (json), city, timezone,
           address, rooms (default 1), default_capacity (default 20),
           room_capacities (json), amenities (json), stripe_account_id,
           is_live (default false), onboarding_completed_at, timestamps

- users (modified)         — added host_id FK, first_name, last_name, role, is_instructor
  Roles: owner, admin, instructor, staff (default: owner)
  Note: original `name` column dropped in favor of first_name + last_name

- studio_types             — admin lookup table for studio categories
  Columns: id, name, slug (unique), is_active (default true), sort_order (default 0)

- instructors              — instructor records per host (scoped by host_id)
  Columns: id, host_id FK, user_id FK (nullable), name, email, invite_status (default pending)

- classes                  — class definitions per host (scoped by host_id)
  Columns: id, host_id FK, instructor_id FK (nullable), name, type, duration_minutes,
           capacity, price (8,2 nullable), is_active (default true)

Tables (planned):
- class_sessions           — individual class sessions (date/time instances)
- bookings                 — class bookings (scoped by host_id)
- students                 — student records (scoped by host_id)
- leads                    — prospect/lead records (scoped by host_id)
- memberships              — membership plans (scoped by host_id)
- payments                 — payment records (scoped by host_id)
- attendance               — attendance logs (scoped by host_id)
- reminders                — scheduled reminders (scoped by host_id)
- intro_offers             — trial/intro offer configs (scoped by host_id)
```

> Every table except `hosts` and `studio_types` must have a `host_id` foreign key.

### Eloquent Models (Implemented)

| Model | Table | Key Relationships | Notes |
|-------|-------|-------------------|-------|
| `Host` | `hosts` | hasMany users, instructors, studioClasses | Tenant root. Casts json columns as arrays. |
| `User` | `users` | belongsTo Host | Modified default model. `getFullNameAttribute` accessor. Implements `MustVerifyEmail`. |
| `Instructor` | `instructors` | belongsTo Host, belongsTo User (nullable), hasMany StudioClass | `user_id` null until instructor creates an account. |
| `StudioClass` | `classes` | belongsTo Host, belongsTo Instructor (nullable) | Named `StudioClass` to avoid PHP reserved word `class`. Uses `$table = 'classes'`. |
| `StudioType` | `studio_types` | — | Admin lookup. `scopeActive` query scope. |

---

## 4. API Design

### API Conventions

- RESTful endpoints under `/api/v1/`
- JSON responses with consistent envelope: `{ data: {}, meta: {}, errors: [] }`
- Laravel API resources for response formatting
- Sanctum for API authentication (if needed for Vue components)

### Signup API Endpoints (Implemented)

| Method | Endpoint | Auth | Purpose | Status |
|--------|----------|------|---------|--------|
| `POST` | `/api/v1/signup/register` | Public | Create User + Host (Step 2) | Implemented |
| `POST` | `/api/v1/signup/verify-email` | Sanctum | Resend verification email (Step 3) | Implemented |
| `GET`  | `/api/v1/signup/subdomain-check` | Public | Check subdomain availability (Step 4) | Implemented |
| `POST` | `/api/v1/signup/studio` | Sanctum | Save studio basics (Step 4) | Implemented |
| `POST` | `/api/v1/signup/location` | Sanctum | Save location & space (Step 5) | Implemented |
| `POST` | `/api/v1/signup/instructors` | Sanctum | Save instructor setup (Step 6) | Implemented |
| `POST` | `/api/v1/signup/classes` | Sanctum | Save first class (Step 7) | Implemented |
| `POST` | `/api/v1/signup/payments` | Sanctum | Save payment preferences (Step 8) | Implemented |
| `POST` | `/api/v1/signup/complete` | Sanctum | Mark onboarding complete (Step 9) | Implemented |

<!-- Add API endpoint plans below -->

---

## 5. Frontend Approach

### Vue Entrypoints

| Entrypoint | Page | Purpose | Status |
|---|---|---|---|
| `signup.js` | `/signup` | 9-step onboarding wizard | Implemented |
| `booking.js` | Public booking page | Class selection + booking flow | Planned |
| `schedule.js` | Host dashboard | Class schedule management | Planned |
| `crm.js` | Host dashboard | Student/lead management | Planned |
| `feedback.js` | Customer | Reviews and feedback | Planned |

> Each entrypoint is registered in `vite.config.js` under `laravel({ input: [...] })`.
> Each entrypoint creates its own Vue app via `createApp()` and mounts into a page-specific `<div id="xxx-app">`.

### Signup Vue Components (Implemented)

| Component | Purpose |
|---|---|
| `SignupWizard.vue` | Orchestrator — step state, formData reactive object, fade transitions |
| `ProgressBar.vue` | Step progress bar (visible on steps 2-8) |
| `PasswordStrength.vue` | 4-rule validator + colored strength meter |
| `Step1Welcome.vue` | Headline + "Get Started" CTA |
| `Step2Account.vue` | First/last name, email, password, studio owner checkbox |
| `Step3EmailVerification.vue` | Non-blocking verify banner with resend cooldown |
| `Step4StudioBasics.vue` | Studio name, types, city, timezone, subdomain |
| `Step5LocationSpace.vue` | Address, rooms, capacity, amenities |
| `Step6InstructorSetup.vue` | Self-add checkbox + dynamic instructor list |
| `Step7ClassSetup.vue` | Class form or "skip" toggle |
| `Step8Payments.vue` | Stripe connect card or "skip" checkbox |
| `Step9GoLive.vue` | Summary card + celebration + dashboard CTA |

### Shared JS Utilities (Implemented)

| File | Purpose |
|---|---|
| `resources/js/utils/api.js` | Axios client with baseURL `/api/v1`, CSRF interceptor, 401→login redirect, `setAuthToken()` export |
| `resources/js/utils/toast.js` | Notyf wrapper with success/error/warning/info methods, top-right position |
| `resources/js/utils/debounce.js` | Standard debounce utility function |

### Shared Vue Components (Planned)

| Component | Used By | Purpose |
|---|---|---|
| `AvatarGroup.vue` | Both | Display instructor/student avatars |
| `SkeletonLoader.vue` | Both | Reusable skeleton loading states |
| `DatePicker.vue` | Both | Flatpickr wrapper component |

<!-- Add more shared components as they are planned -->

---

## 6. Third-Party Integrations

| Service | Purpose | Status | Package |
|---|---|---|---|
| Stripe | Payment processing (Stripe Connect) | Package installed, UI placeholder | `stripe/stripe-php` (Composer) |
| Notyf | Toast notifications | Installed + documented | `notyf` (npm) + FlyonUI vendor CSS |
| FullCalendar | Calendar widget | Installed | `@fullcalendar/*` (npm) |
| Flatpickr | Date/time picker | Installed | `flatpickr` (npm) + FlyonUI vendor CSS |
| Laravel Sanctum | API authentication | Installed + published | `laravel/sanctum` (Composer) |
| Laravel Octane | High-performance server | Installed (RoadRunner) | `laravel/octane` (Composer) |
| SendGrid / Mailgun | Transactional email | Planned | — |
| Twilio | SMS reminders | Planned | — |
| Smarty Streets | Address autocomplete | Planned (Steps 4-5 of signup) | — |

<!-- Add integration decisions and API key management notes below -->

---

## 7. Open Questions

<!-- Add unresolved questions here. Move to Decision Log once resolved. -->

| # | Question | Context | Status |
|---|---|---|---|
| Q1 | Stripe Connect or direct Stripe? | Marketplace commission model may need Connect | Open |
| Q2 | Swoole or RoadRunner for Octane? | RoadRunner installed as default, benchmark later | Resolved (RoadRunner for now) |
| Q3 | Email provider? | SendGrid vs Mailgun vs Amazon SES | Open |
| Q4 | Studio types: DB-managed or hardcoded? | Step 4 currently hardcodes list in Vue; `studio_types` migration exists | Open — need admin seeder |
| Q5 | Tenant scoping middleware? | All queries need `host_id` filtering — global scope vs middleware | Open |
| Q6 | Signup API: save per-step or all at end? | Current frontend stores all data client-side | Resolved — per-step save (ADR-008) |

---

## 8. Decision Log

Log every decision with date and reasoning. Most recent first.

| Date | Decision | Context | Outcome |
|---|---|---|---|
| 2026-02-06 | Dropdowns with search (ADR-009) | Select inputs with many options need searchable UI | Use FlyonUI `advance-select` pattern for all long dropdowns (timezone, country, currency, etc.) |
| 2026-02-05 | Progressive per-step signup save (ADR-008) | Need to decide save strategy for 9-step wizard | Account at Step 2, updates per-step, prevents data loss |
| 2026-02-05 | Manual auth instead of Breeze (ADR-007) | Breeze scaffolding conflicts with FlyonUI layout | Custom login page + AuthController keeps UI consistency |
| 2026-02-05 | Sanctum session + token auth for signup | Same-domain API calls need auth after Step 2 | Cookie-based session auth + Bearer token for API |
| 2026-02-05 | Re-save pattern for instructors/classes | Users may go back and change data during signup | Delete existing + recreate on each save |
| 2026-02-05 | Shell pages with skeletons for dashboard | Need all sidebar links to resolve to real pages | 10 Blade pages with FlyonUI skeletons, ready for Vue mounts |
| 2026-02-05 | ApiResponse trait for JSON envelope | Need consistent API response format | `{ data, meta, errors }` envelope via reusable trait |
| 2026-02-05 | HostOnboardingCompleted event | Need to trigger actions when onboarding finishes | Laravel event dispatched when `complete()` is called |
| 2026-02-05 | Name class model `StudioClass` | PHP reserves `class` keyword; model uses `$table = 'classes'` | Avoids language conflict |
| 2026-02-05 | Non-blocking email verification (Step 3) | Users shouldn't be stuck waiting for email during onboarding | Soft banner, can continue setup |
| 2026-02-05 | Per-page Vue entrypoints (not SPA router) | Each Blade page mounts its own Vue app via `createApp()` | Consistent with ADR-002 |
| 2026-02-05 | FlyonUI layout-affect sidebar pattern | Sidebar pushes content using `[--is-layout-affect:true]` + `overlay-minified:` prefix | Works with FlyonUI's built-in JS |
| 2026-02-05 | Vite multi-input (app.css + layout.js + signup.js) | Each entrypoint bundled separately, loaded per-page via `@vite()` | Smaller per-page bundles |
| 2026-02-05 | Notyf for toast notifications | FlyonUI provides vendor CSS theming for Notyf | Integrated via npm + FlyonUI vendor CSS |
| 2026-02-05 | RoadRunner for Octane (default) | `php artisan octane:install` chose RoadRunner | Can switch to Swoole later |
| 2026-02-05 | Use FlyonUI as component library | Needed Tailwind-based components | Accepted (ADR-004) |
| 2026-02-05 | Vue hybrid, not SPA | Need SEO + simplicity | Accepted (ADR-002) |
| 2026-02-05 | Single database tenancy | Multi-tenant host platform | Accepted (ADR-001) |
| 2026-02-05 | Single codebase for both products | Shared logic between marketplace + CRM | Accepted (ADR-003) |
| 2026-02-05 | No CDN in production | Reliability + security | Accepted (ADR-005) |
| 2026-02-05 | Skeleton loading over spinners | Better UX | Accepted (ADR-006) |

<!-- Add new decisions at the top of this table -->
