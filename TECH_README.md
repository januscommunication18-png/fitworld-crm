# FitNearYou & FitCRM - Technology Stack & Architecture

> **Read this file before starting any development work.**
> This is the single source of truth for all technology decisions, architecture patterns, and development conventions.

---

> **IMPORTANT - No CDN links in production pages.**
> Do NOT use CDN links (`cdn.jsdelivr.net`, `unpkg.com`, etc.) in any project page.
> All libraries must be **downloaded locally** via npm and served from the project's own files.

> **IMPORTANT - Every `<label>` must have a `for` attribute.**
> All `<label>` elements must include a `for="inputId"` attribute that matches the `id` of the associated form input.
> The only exception is wrapping labels (e.g. checkbox option cards) where the `<label>` directly wraps the `<input>`.

---

## 1. Architecture Overview

```
fitnearyou.com                         fitcrm.net
(Customer Marketplace)                 (Host SaaS Platform)
┌─────────────────────┐                ┌──────────────────────────────┐
│  Single Database     │                │  Single Database Tenancy     │
│  (customer_db)       │                │  (fitcrm_db)                 │
│                      │                │                              │
│  Laravel 12          │                │  Laravel 12                  │
│  Vue.js (Hybrid)     │                │  Vue.js (Hybrid)             │
│  Tailwind + FlyonUI  │                │  Tailwind + FlyonUI          │
│  Laravel Octane      │                │  Laravel Octane              │
└─────────────────────┘                │                              │
                                       │  hostname.fitcrm.app         │
                                       │  (per-host subdomains)       │
                                       └──────────────────────────────┘
```

---

## 2. Databases

Two separate databases. Customer and Host data are fully isolated.

| Database | Domain | Purpose |
|---|---|---|
| `customer_db` | fitnearyou.com | Customer marketplace — discovery, bookings, reviews |
| `fitcrm_db` | fitcrm.net | Host SaaS — scheduling, payments, students, CRM |

### Host Database - Single Database Tenancy

All host businesses share one database (`fitcrm_db`) with tenant isolation via `host_id` foreign keys.

| Feature | Details |
|---|---|
| Tenancy Model | Single database, all hosts in one DB |
| Tenant Isolation | `host_id` foreign key on every tenant-scoped table |
| Mode | Automatic & manual mode |
| Architecture | Event-based architecture |
| Subdomains | Every host gets `hostname.fitcrm.app` |

**Automatic mode** — tenant is resolved automatically from the subdomain (e.g. `yogabliss.fitcrm.app` resolves to `host_id = 42`).

**Manual mode** — tenant can be set explicitly in code when needed (e.g. admin panels, cross-tenant operations, background jobs).

**Event-based architecture** — domain events are dispatched for key actions (booking created, payment received, class cancelled, etc.) to decouple business logic and enable async processing.

---

## 3. Framework & Backend

| Technology | Version / Details |
|---|---|
| **Framework** | Laravel 12 |
| **Database** | MySQL / MariaDB |
| **Performance** | Laravel Octane |
| **Routing** | Laravel router (Blade-first, server-rendered) |
| **API** | Laravel API routes where needed for Vue components |

### Laravel Octane

Laravel Octane is used for high-performance request handling. Octane serves the application using a long-running process (Swoole or RoadRunner), eliminating framework boot overhead on every request.

---

## 4. Frontend Architecture - Vue.js Hybrid (Blade + Vue)

**This is NOT a Vue SPA.** Vue is used as a hybrid enhancement layer inside Laravel Blade pages.

### How It Works

```
Browser Request
     │
     ▼
Laravel Router → Controller → Blade View (full HTML page)
                                    │
                                    ▼
                              <div id="app"></div>
                                    │
                                    ▼
                         Vue mounts into that div only
                         (compiled from a per-page entrypoint)
```

1. **Laravel serves the page** — full Blade template with layout, SEO meta, nav, footer
2. **Only that page loads a compiled Vue bundle** — no global Vue app
3. **Vue mounts into a `<div id="app"></div>`** — scoped to one area of the page
4. **Minimal Vue footprint** — only the interactive parts use Vue
5. **Laravel routing + SEO stays intact** — server-rendered HTML, no hash/history routing issues
6. **Easy incremental adoption** — add Vue to one page at a time, no big rewrite

### Entrypoint Structure

Each Vue "page" is a **separate entrypoint**. No single monolithic Vue app.

```
resources/
  js/
    apps/
      feedback.js        ← Vue entrypoint for feedback page
      crm.js             ← Vue entrypoint for CRM dashboard
      booking.js         ← Vue entrypoint for booking widget
      schedule.js        ← Vue entrypoint for class schedule
      ...
```

Each Blade page includes **only its own** compiled file:

```html
<!-- resources/views/feedback.blade.php -->
@extends('layouts.app')

@section('content')
  <h1>Feedback</h1>
  <div id="app"></div>
@endsection

@push('scripts')
  @vite('resources/js/apps/feedback.js')
@endpush
```

```js
// resources/js/apps/feedback.js
import { createApp } from 'vue'
import FeedbackForm from '../components/FeedbackForm.vue'

createApp(FeedbackForm).mount('#app')
```

### Why Hybrid (Not SPA)

| Concern | Hybrid (Blade + Vue) | Full SPA |
|---|---|---|
| SEO | Server-rendered HTML | Requires SSR setup |
| Initial load | Fast (HTML ready) | Slow (JS must load first) |
| Routing | Laravel handles it | Vue Router needed |
| Complexity | Low | High |
| Incremental adoption | Easy, page by page | All or nothing |
| Auth/middleware | Laravel native | API tokens needed |

---

## 5. CSS & Component Library

| Technology | Details |
|---|---|
| **CSS Framework** | Tailwind CSS |
| **Component Library** | [FlyonUI](https://flyonui.com/docs/getting-started/introduction/) |
| **Icons** | Tabler Icons (via webfont, local) |

### FlyonUI

FlyonUI is the primary component library for all UI development. It provides pre-built Tailwind CSS components (alerts, modals, dropdowns, cards, forms, tables, etc.).

**Documentation:** https://flyonui.com/docs/getting-started/introduction/

See `CSS_README.md` for the full component guide with code snippets and class references for every component used in this project.

---

## 6. Content Loading - Skeleton Loading

All pages use **skeleton loading** for content that loads asynchronously. No blank screens, no spinners — show content placeholders that match the final layout shape.

```html
<!-- Skeleton placeholder (shown while Vue/API loads) -->
<div class="animate-pulse">
  <div class="h-4 bg-base-300 rounded w-3/4 mb-2"></div>
  <div class="h-4 bg-base-300 rounded w-1/2 mb-2"></div>
  <div class="h-32 bg-base-300 rounded w-full"></div>
</div>

<!-- Real content (Vue replaces skeleton once data loads) -->
```

**Rules:**
- Every page/component that fetches data must show a skeleton first
- Skeletons must match the shape of the real content (not a generic spinner)
- Use `animate-pulse` with `bg-base-300` for skeleton elements
- Vue components should have a `loading` state that shows the skeleton

---

## 7. Codebase Structure

**Single codebase** serving both the customer portal (fitnearyou.com) and the host SaaS product (fitcrm.net).

```
fitcrm/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Customer/          ← fitnearyou.com controllers
│   │   │   └── Host/              ← fitcrm.net controllers
│   │   └── Middleware/
│   │       └── ResolveTenant.php  ← subdomain → host_id resolution
│   ├── Models/
│   ├── Events/                    ← domain events (event-based architecture)
│   ├── Listeners/                 ← event handlers
│   └── Services/
├── resources/
│   ├── views/
│   │   ├── customer/              ← Blade views for fitnearyou.com
│   │   └── host/                  ← Blade views for fitcrm.net
│   ├── js/
│   │   ├── apps/                  ← Vue entrypoints (one per page)
│   │   │   ├── feedback.js
│   │   │   ├── crm.js
│   │   │   ├── booking.js
│   │   │   └── schedule.js
│   │   └── components/            ← Shared Vue components
│   └── css/
│       └── app.css                ← Tailwind + FlyonUI imports
├── routes/
│   ├── customer.php               ← fitnearyou.com routes
│   └── host.php                   ← fitcrm.net routes
├── config/
│   └── tenancy.php                ← tenant resolution config
├── database/
│   └── migrations/
├── CSS_README.md                  ← Component guide (read before building UI)
├── TECH_README.md                 ← This file
├── README.md                      ← Project overview
└── package.json
```

### Why Single Codebase

- Shared models, services, and business logic between customer and host
- Shared Vue components (e.g. booking widget used on both sides)
- Shared Tailwind/FlyonUI design system
- One deployment pipeline
- Domain routing separates customer vs host at the route level

---

## 8. Subdomain Routing

| URL Pattern | Resolves To |
|---|---|
| `fitnearyou.com/*` | Customer marketplace routes |
| `fitcrm.net/*` | Host admin / marketing pages |
| `{hostname}.fitcrm.app/*` | Specific host's tenant (booking page, schedule, etc.) |

The `ResolveTenant` middleware reads the subdomain, looks up the host, and sets the tenant context for the request. All subsequent queries are automatically scoped to that `host_id`.

---

## 9. Development Quick Reference

| What | Command / Location |
|---|---|
| Install dependencies | `composer install && npm install` |
| Run dev server | `php artisan octane:start` |
| Build frontend | `npm run build` |
| Dev frontend (hot reload) | `npm run dev` |
| Component guide | `CSS_README.md` |
| FlyonUI docs | https://flyonui.com/docs/getting-started/introduction/ |
| Add a new Vue page | Create `resources/js/apps/{name}.js` + Blade view with `@vite()` |
| Add a new component | See `CSS_README.md` for approved components |

---

## 10. Key Rules

1. **No CDN links** — download everything locally via npm
2. **Every `<label>` needs a `for` attribute** — matching the input's `id`
3. **Skeleton loading** — every async content area must show a skeleton first
4. **Vue is hybrid only** — mount into Blade pages, not a SPA
5. **One entrypoint per Vue page** — no monolithic Vue app
6. **FlyonUI components** — use the approved components from `CSS_README.md`
7. **Event-based architecture** — dispatch domain events for key business actions
8. **Tenant scoping** — every host query must be scoped to `host_id`
9. **Read `CSS_README.md` before building any UI** — ensures consistent components
10. **Read this file before starting any development** — ensures correct architecture
