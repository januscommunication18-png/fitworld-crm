# FitCRM & FitNearYou - AI Development Instructions

> **This file is the single source of truth for AI assistants working on this project.**
> It combines all project READMEs into one document. Read this entire file before writing any code.
>
> **This file is checked into GitHub but must NOT be deployed to production or dev branches.**

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Technology Stack & Architecture](#2-technology-stack--architecture)
3. [CSS Component Guide](#3-css-component-guide)
4. [Key Rules (Always Follow)](#4-key-rules-always-follow)

---

# 1. Project Overview

## FitNearYou.com & FitCRM.net

A marketplace platform connecting fitness customers with studios, paired with a simple studio management CRM built for small Yoga & Pilates businesses.

### Overview

**FitNearYou** is the demand engine — a marketplace where customers discover and book classes at nearby studios.

**FitCRM** is the studio engine — a lightweight SaaS for studio owners to manage scheduling, payments, students, and more.

Together they form a closed loop: discovery to booking to long-term retention.

### How They Work Together

```
FitNearYou (Marketplace)          FitCRM (Studio Software)
─────────────────────────         ──────────────────────────
Customer discovers studio   --->  Booking flows into FitCRM
Marketplace user converts   --->  Becomes long-term member
First visit tracked         --->  Lifetime value measured
```

Studios listed on FitNearYou can:
- Accept bookings directly into FitCRM
- Convert marketplace users into long-term members
- Track lifetime value from first discovery through repeat client

### Platform Roles

#### Customer — "I'm looking to book classes and/or appointments"

- Discover nearby Yoga & Pilates studios
- Compare classes, instructors, pricing, and reviews
- Book drop-ins, trials, class packs, or memberships
- Find beginner-friendly and specialty classes easily

#### Host Business — "I'm looking to run my business"

Supported studio types:
- Yoga Studio
- Pilates Studio
- Fitness Studio
- CrossFit Studio

Each host business gets their own subdomain: `businessname.fitcrm.app`

### Key Features

#### For Customers (fitnearyou.com)

- Discover nearby Yoga & Pilates studios
- Compare classes, instructors, pricing, and reviews
- Book drop-ins, trials, class packs, or memberships
- Find beginner-friendly and specialty classes

#### For Studios (FitCRM — separate login)

- Get discovered by high-intent local users
- Fill empty class slots
- Promote trials, intro offers, and new instructors
- No marketing expertise required

#### FitCRM Core Modules

| Module | Description |
|---|---|
| Class Scheduling & Capacity | Manage class timetables and spot limits |
| Payments, Packs & Memberships | Accept payments, sell class packs and recurring memberships |
| Student & Lead Management | Track students, prospects, and communication |
| Automated Reminders & Follow-ups | Email/SMS reminders for bookings and retention |
| Attendance & Revenue Insights | Dashboards for attendance trends and revenue |
| Intro Offers & Retention Tools | Trial offers, win-back campaigns, and loyalty features |

No bloated gym features. No enterprise complexity. Just what a small Yoga or Pilates studio actually needs.

### Target Customer

- Independent Yoga studios
- Pilates (Mat + Reformer) studios
- 1-3 locations
- 1-15 instructors
- Owner-operators who teach classes themselves

### Revenue Model

#### FitNearYou
- Commission per booking
- Featured listings
- City-based promotions

#### FitCRM
- Monthly SaaS pricing (Simple / Growth / Studio+)
- Discounted or free tier for FitNearYou partners

### Architecture

```
fitnearyou.com                    fitcrm.net
(Customer Marketplace)            (Studio SaaS)
┌──────────────────┐              ┌──────────────────────────┐
│  Search & Browse  │              │  businessname.fitcrm.app │
│  Compare Studios  │───booking──>│  Class Scheduling        │
│  Book Classes     │              │  Payments & Memberships  │
│  Reviews & Ratings│              │  Student Management      │
│  Intro Offers     │              │  Reminders & Follow-ups  │
└──────────────────┘              │  Attendance & Revenue    │
                                  └──────────────────────────┘
```

Separate login systems for customers and host businesses. Each host gets a unique subdomain for their booking page.

---

# 2. Technology Stack & Architecture

## 2.1 Architecture Overview

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

## 2.2 Databases

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

## 2.3 Framework & Backend

| Technology | Version / Details |
|---|---|
| **Framework** | Laravel 12 |
| **Database** | MySQL / MariaDB |
| **Performance** | Laravel Octane |
| **Routing** | Laravel router (Blade-first, server-rendered) |
| **API** | Laravel API routes where needed for Vue components |

### Laravel Octane

Laravel Octane is used for high-performance request handling. Octane serves the application using a long-running process (Swoole or RoadRunner), eliminating framework boot overhead on every request.

## 2.4 Frontend Architecture - Vue.js Hybrid (Blade + Vue)

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

## 2.5 CSS & Component Library

| Technology | Details |
|---|---|
| **CSS Framework** | Tailwind CSS |
| **Component Library** | [FlyonUI](https://flyonui.com/docs/getting-started/introduction/) |
| **Icons** | Tabler Icons (via webfont, local) |
| **Theme File** | `resources/css/fitcrm-theme.css` |

FlyonUI is the primary component library for all UI development. See Section 3 for the full component guide.

### Theme Customization

All brand customization is controlled via a single CSS file: `resources/css/fitcrm-theme.css`

**File Structure:**
- **Section 1** - Brand Settings (EDIT THESE) - Colors, fonts, border radius, spacing
- **Section 2** - FlyonUI Mapping (DO NOT EDIT) - Maps fitcrm-* variables to FlyonUI
- **Section 3** - Dark Mode (optional, commented out)
- **Section 4** - Custom Utilities (optional)

**How to Customize:**

1. Open `resources/css/fitcrm-theme.css`
2. Edit values in **Section 1 only**
3. Save file (changes apply automatically in dev mode via Vite)

**Color Format (OKLCH):**

```
oklch(lightness% chroma hue)
- lightness: 0-100% (0=black, 100=white)
- chroma: 0-0.4 (0=gray, higher=more saturated)
- hue: 0-360 (0=red, 120=green, 240=blue)
```

Use https://oklch.com/ to pick colors visually.

**Example Colors:**
```css
Purple:  oklch(55% 0.25 275)
Blue:    oklch(60% 0.20 250)
Green:   oklch(70% 0.20 145)
Red:     oklch(60% 0.22 25)
Orange:  oklch(75% 0.16 50)
```

**Available Variables:**
| Variable | Purpose |
|---|---|
| `--fitcrm-brand-primary` | Main brand color (buttons, links) |
| `--fitcrm-brand-secondary` | Secondary actions |
| `--fitcrm-brand-accent` | Highlights, badges |
| `--fitcrm-brand-neutral` | Dark/muted elements |
| `--fitcrm-success/warning/error/info` | Feedback colors |
| `--fitcrm-base-100/200/300` | Backgrounds, cards, borders |
| `--fitcrm-base-content` | Text color |
| `--fitcrm-font-family-sans` | Main font family |
| `--fitcrm-radius-selector/field/box` | Border radius sizes |
| `--fitcrm-depth` | 0=flat, 1=shadows enabled |

## 2.6 Content Loading - Skeleton Loading

All pages use **skeleton loading** for content that loads asynchronously. No blank screens, no spinners — show content placeholders that match the final layout shape.

```html
<!-- Skeleton placeholder (shown while Vue/API loads) -->
<div class="animate-pulse">
  <div class="h-4 bg-base-300 rounded w-3/4 mb-2"></div>
  <div class="h-4 bg-base-300 rounded w-1/2 mb-2"></div>
  <div class="h-32 bg-base-300 rounded w-full"></div>
</div>
```

**Rules:**
- Every page/component that fetches data must show a skeleton first
- Skeletons must match the shape of the real content (not a generic spinner)
- Use `animate-pulse` with `bg-base-300` for skeleton elements
- Vue components should have a `loading` state that shows the skeleton

## 2.7 Codebase Structure

**Single codebase** serving both the customer portal (fitnearyou.com) and the host SaaS product (fitcrm.net).

```
fitcrm/
├── .ai/
│   ├── INSTRUCTIONS.md            ← This file (AI instructions, not deployed)
│   └── PLANNING.md                ← AI planning & decisions log (not deployed)
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
├── CLAUDE.md                      ← Auto-read by Claude Code every session
├── CSS_README.md                  ← Component guide
├── TECH_README.md                 ← Technology stack
├── README.md                      ← Project overview
└── package.json
```

## 2.8 Subdomain Routing

| URL Pattern | Resolves To |
|---|---|
| `fitnearyou.com/*` | Customer marketplace routes |
| `fitcrm.net/*` | Host admin / marketing pages |
| `{hostname}.fitcrm.app/*` | Specific host's tenant (booking page, schedule, etc.) |

The `ResolveTenant` middleware reads the subdomain, looks up the host, and sets the tenant context for the request. All subsequent queries are automatically scoped to that `host_id`.

---

# 3. CSS Component Guide

> **Library:** FlyonUI (Tailwind CSS)
> Always read this section before creating any page to ensure consistent components across the project.
> **Documentation:** https://flyonui.com/docs/getting-started/introduction/

## 3.1 Select Dropdown (FlyonUI Advance Select)

> **IMPORTANT:** All form dropdowns should use the advance-select with search functionality. This is the standard pattern for the project.

### A. Single Select with Search (Default for all dropdowns)

Use this for **all form dropdowns**. Always include search for better UX.

```html
<div>
    <label class="label-text" for="instructor_id">Instructor</label>
    <select id="instructor_id" name="instructor_id" class="hidden" required
        data-select='{
            "hasSearch": true,
            "searchPlaceholder": "Search instructors...",
            "placeholder": "Select an instructor...",
            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
            "toggleClasses": "advance-select-toggle",
            "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
            "optionClasses": "advance-select-option selected:select-active",
            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
        }'>
        <option value="">Select an instructor...</option>
        @foreach($instructors as $instructor)
        <option value="{{ $instructor->id }}" {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>
            {{ $instructor->name }}
        </option>
        @endforeach
    </select>
    @error('instructor_id')
        <p class="text-error text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
```

### B. Single Select without Search (Small lists only)

Only use for dropdowns with **5 or fewer options** (like status selects).

```html
<select id="status" name="status" class="hidden"
    data-select='{
        "placeholder": "Select status...",
        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
        "toggleClasses": "advance-select-toggle",
        "dropdownClasses": "advance-select-menu",
        "optionClasses": "advance-select-option selected:select-active",
        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
    }'>
    <option value="">All Statuses</option>
    <option value="draft">Draft</option>
    <option value="published">Published</option>
</select>
```

### C. Multi-Select with Search

Use when users need to **select multiple options**.

```html
<select id="multi-select-members" name="members[]" multiple class="hidden"
    data-select='{
        "hasSearch": true,
        "isSearchDirectMatch": false,
        "searchPlaceholder": "Search members...",
        "placeholder": "Select members...",
        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
        "toggleClasses": "advance-select-toggle",
        "dropdownClasses": "advance-select-menu max-h-72 overflow-y-auto",
        "optionClasses": "advance-select-option selected:select-active",
        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
    }'>
    <option value="">Choose</option>
    <option value="1">Option 1</option>
    <option value="2">Option 2</option>
</select>
```

**Key points:**
- **Always use `class="hidden"`** on the select element
- **Always include search** for lists with more than 5 options
- Use `max-h-72 overflow-y-auto` in `dropdownClasses` for longer lists
- The `data-select` JSON must be properly escaped
- Works with standard form submission and Laravel validation

## 3.2 Alerts (FlyonUI Alert Component)

Alerts use `role="alert"` for accessibility. Three style variants available.

### A. Default (Solid Background)

```html
<div class="alert alert-warning flex items-center gap-4" role="alert">
  <span class="icon-[tabler--alert-triangle] shrink-0 size-6"></span>
  <p><span class="text-lg font-semibold">Warning alert:</span> Your message here.</p>
</div>

<div class="alert alert-success flex items-center gap-4" role="alert">
  <span class="icon-[tabler--circle-check] shrink-0 size-6"></span>
  <p><span class="text-lg font-semibold">Success alert:</span> Your message here.</p>
</div>
```

### B. Soft (Muted Background)

Add `alert-soft` class.

```html
<div class="alert alert-soft alert-warning flex items-center gap-4" role="alert">
  <span class="icon-[tabler--alert-triangle] shrink-0 size-6"></span>
  <p><span class="text-lg font-semibold">Warning alert:</span> Your message here.</p>
</div>
```

### C. Outline (Border Only)

Add `alert-outline` class.

```html
<div class="alert alert-outline alert-warning flex items-center gap-4" role="alert">
  <span class="icon-[tabler--alert-triangle] shrink-0 size-6"></span>
  <p><span class="text-lg font-semibold">Warning alert:</span> Your message here.</p>
</div>
```

**Key differences:**
- **Default:** `alert alert-{color}` — solid background
- **Soft:** `alert alert-soft alert-{color}` — muted background
- **Outline:** `alert alert-outline alert-{color}` — border only
- **Colors:** `alert-warning`, `alert-success`
- **Icons:** `icon-[tabler--alert-triangle]` for warnings, `icon-[tabler--circle-check]` for success

## 3.3 Animated Stats & Text (Intersection Observer + Motion Presets)

Scroll-triggered animations using `intersect:motion-*` utility classes.

### A. Stats Cards with Slide-In Animation

```html
<div class="stats intersect:motion-preset-slide-left intersect:motion-ease-spring-bouncier max-sm:w-full">
  <div class="stat">
    <div class="avatar avatar-placeholder">
      <div class="bg-success/20 text-success size-10 rounded-full">
        <span class="icon-[tabler--package] size-6"></span>
      </div>
    </div>
    <div class="stat-value mb-1">Order</div>
    <div class="stat-title">7,500 of 10,000 orders</div>
    <div class="progress bg-success/10 h-2" role="progressbar" aria-label="Order Progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
      <div class="progress-bar progress-success w-3/4"></div>
    </div>
  </div>
</div>
```

### B. Text with Scroll Animation

```html
<h2 class="intersect:motion-preset-blur-left intersect:motion-delay-[1000ms] mb-4 text-3xl font-bold">
  Track Your Stats
</h2>
<p class="intersect:motion-preset-focus intersect:motion-delay-[1400ms]">
  Your paragraph text here...
</p>
```

**Animation classes:** `intersect:motion-preset-slide-left`, `intersect:motion-preset-blur-left`, `intersect:motion-preset-focus`, `intersect:motion-ease-spring-bouncier`, `intersect:motion-delay-[400ms]`

## 3.4 Avatar Group — Person / Profile Display

> **Standard component for displaying people/profiles** — Use whenever showing instructors, students, staff, or any person/profile.

### A. Avatar Group (Pull-Up)

```html
<div class="avatar-group pull-up -space-x-5">
  <div class="tooltip">
    <div class="tooltip-toggle avatar">
      <div class="w-13">
        <img src="AVATAR_URL" alt="avatar" />
      </div>
    </div>
    <span class="tooltip-content tooltip-shown:opacity-100 tooltip-shown:visible" role="tooltip">
      <span class="tooltip-body">User Name</span>
    </span>
  </div>
</div>
```

### B. Avatar Group with Counter

```html
<div class="tooltip cursor-default">
  <div class="tooltip-toggle avatar avatar-placeholder">
    <div class="bg-neutral text-neutral-content w-13">
      <span>+9</span>
    </div>
  </div>
  <span class="tooltip-content tooltip-shown:opacity-100 tooltip-shown:visible" role="tooltip">
    <span class="tooltip-body">9 more</span>
  </span>
</div>
```

## 3.5 Badges with Icons

```html
<span class="badge size-6 p-0"> <span class="icon-[tabler--user]"></span></span>
<span class="badge badge-primary size-6 p-0"> <span class="icon-[tabler--star]"></span></span>
<span class="badge badge-secondary size-6 p-0"> <span class="icon-[tabler--sun]"></span></span>
<span class="badge badge-accent size-6 p-0"> <span class="icon-[tabler--moon]"></span></span>
<span class="badge badge-info size-6 p-0"> <span class="icon-[tabler--folder]"></span></span>
<span class="badge badge-success size-6 p-0"> <span class="icon-[tabler--check]"></span></span>
<span class="badge badge-warning size-6 p-0"> <span class="icon-[tabler--cloud]"></span></span>
<span class="badge badge-error size-6 p-0"> <span class="icon-[tabler--clock]"></span></span>
```

## 3.6 Breadcrumbs

```html
<div class="breadcrumbs">
  <ol>
    <li><a href="#"> <span class="icon-[tabler--folder] size-5"></span>Home</a></li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li><a href="#" aria-label="More Pages"><span class="icon-[tabler--dots]"></span></a></li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page"><span class="icon-[tabler--file] me-1 size-5"></span>Breadcrumb</li>
  </ol>
</div>
```

## 3.7 Buttons

```html
<button class="btn">Default</button>
<button class="btn btn-primary">Primary</button>
<button class="btn btn-secondary">Secondary</button>
<button class="btn btn-accent">Accent</button>
<button class="btn btn-info">Info</button>
<button class="btn btn-success">Success</button>
<button class="btn btn-warning">Warning</button>
<button class="btn btn-error">Error</button>
```

## 3.8 Calendar (FullCalendar + FlyonUI Modal)

**Required local packages:** FullCalendar v6.1.15, FlyonUI

```html
<div class="card flex not-prose p-4 w-full">
  <div id="calendar-custom"></div>
</div>

<!-- Event Modal -->
<div id="calendar-event-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 hidden" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="modalTitle">Event</h3>
        <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#calendar-event-modal">
          <span class="icon-[tabler--x] size-4"></span>
        </button>
      </div>
      <form id="eventForm">
        <div class="modal-body pt-0">
          <div class="mb-4">
            <label class="label-text" for="eventTitle"> Add event title below </label>
            <input type="text" id="eventTitle" class="input" placeholder="Event title" required />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-soft btn-secondary" data-overlay="#calendar-event-modal">Close</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
```

**Event colors:** `fc-event-primary`, `fc-event-secondary`, `fc-event-success`, `fc-event-info`, `fc-event-warning`, `fc-event-error`, `fc-event-disabled`

## 3.9 Cards

```html
<div class="card sm:max-w-sm">
  <div class="card-body">
    <h5 class="card-title mb-0">Welcome to Our Platform</h5>
    <div class="text-base-content/50 mb-6">Your journey starts here</div>
    <p class="mb-4">Body text here.</p>
    <div class="card-actions">
      <a href="#" class="link link-primary no-underline">Get Started</a>
      <a href="#" class="link link-primary no-underline">Learn More</a>
    </div>
  </div>
</div>
```

## 3.10 Chat Bubbles

```html
<!-- Receiver (left) -->
<div class="chat chat-receiver">
  <div class="chat-avatar avatar"><div class="size-10 rounded-full"><img src="AVATAR_URL" alt="avatar" /></div></div>
  <div class="chat-header text-base-content">User Name <time class="text-base-content/50">12:45</time></div>
  <div class="chat-bubble">Message text here</div>
  <div class="chat-footer text-base-content/50"><div>Delivered</div></div>
</div>

<!-- Sender (right) -->
<div class="chat chat-sender">
  <div class="chat-avatar avatar"><div class="size-10 rounded-full"><img src="AVATAR_URL" alt="avatar" /></div></div>
  <div class="chat-header text-base-content">User Name <time class="text-base-content/50">12:46</time></div>
  <div class="chat-bubble">Message text here</div>
  <div class="chat-footer text-base-content/50">Seen <span class="icon-[tabler--checks] text-success align-bottom"></span></div>
</div>
```

## 3.11 Checkboxes — Custom Option Cards

```html
<div class="flex w-full flex-wrap items-start gap-3 sm:flex-nowrap">
  <label class="custom-option flex flex-row items-start gap-3 sm:w-1/2">
    <input type="checkbox" class="checkbox checkbox-primary mt-2" checked required />
    <span class="label-text w-full text-start">
      <span class="flex justify-between mb-1">
        <span class="text-base font-medium">Basic</span>
        <span class="text-base-content/50 text-base">Free</span>
      </span>
      <span class="text-base-content/80">Get 1 project with 1 teams members.</span>
    </span>
  </label>
</div>
```

## 3.12 Dropdown with Nested Collapse

```html
<div class="dropdown relative inline-flex [--auto-close:inside]">
  <button id="dropdown-collapse" type="button" class="dropdown-toggle btn btn-primary" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
    Actions
    <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
  </button>
  <div class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-collapse">
    <div class="dropdown-header">Quick Actions</div>
    <div><a class="dropdown-item" href="#">Send Newsletter</a></div>
    <div>
      <button id="nested-collapse-2" class="collapse-toggle dropdown-item justify-between" aria-expanded="false" aria-controls="nested-collapse-content" data-collapse="#nested-collapse-content">
        More Options
        <span class="icon-[tabler--chevron-down] collapse-open:rotate-180 size-4"></span>
      </button>
      <div class="collapse hidden w-full overflow-hidden transition-[height] duration-300" aria-labelledby="nested-collapse-2" id="nested-collapse-content">
        <ul class="py-3 ps-3">
          <li><a class="dropdown-item" href="#">Download Documents</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>
```

## 3.13 Action Dropdown (Three-Dot Menu)

> **Standard pattern for row actions in tables/lists.** Use HTML5 `<details>/<summary>` pattern for reliable behavior.

```html
<details class="dropdown dropdown-bottom dropdown-end">
    <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
        <span class="icon-[tabler--dots-vertical] size-4"></span>
    </summary>
    <ul class="dropdown-content menu bg-base-100 rounded-box w-40 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
        <li>
            <form action="/route" method="POST" class="m-0">
                @csrf
                <button type="submit" class="w-full text-left flex items-center gap-2">
                    <span class="icon-[tabler--copy] size-4"></span> Duplicate
                </button>
            </form>
        </li>
        <li>
            <a href="/edit">
                <span class="icon-[tabler--edit] size-4"></span> Edit
            </a>
        </li>
        <li>
            <form action="/delete" method="POST" class="m-0" onsubmit="return confirm('Delete?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full text-left flex items-center gap-2 text-error">
                    <span class="icon-[tabler--trash] size-4"></span> Delete
                </button>
            </form>
        </li>
    </ul>
</details>
```

**Key points:**
- Use `<details>` and `<summary>` instead of `<div>` with JS-based toggle
- `summary` needs `list-none cursor-pointer` to hide default marker
- `dropdown-content menu` classes for the menu, with inline `style` for z-index
- Forms inside `<li>` with `class="m-0"` to remove default margin
- Buttons inside forms use `w-full text-left flex items-center gap-2`
- Destructive actions use `text-error` class

## 3.14 Date & Time Pickers (Flatpickr)

> **IMPORTANT:** Always use Flatpickr for date and time inputs instead of native HTML date/time inputs. This provides a consistent, user-friendly experience.

**Required local packages:** Flatpickr

### A. Date Picker (Human Friendly) - Default for all date fields

Use this pattern for **all date inputs**. Shows human-readable format but submits Y-m-d format.

```html
<label class="label-text" for="session_date">Date</label>
<input type="text" id="session_date" name="session_date"
    value="{{ old('session_date', $date) }}"
    class="input w-full"
    placeholder="Select date..."
    required>
```

```js
flatpickr('#session_date', {
    altInput: true,
    altFormat: 'F j, Y',
    dateFormat: 'Y-m-d',
    minDate: 'today'  // optional: prevent past dates
});
```

### B. Time Only Picker - Default for all time fields

Use this pattern for **all time inputs**. 24-hour format with 5-minute increments.

```html
<label class="label-text" for="session_time">Start Time</label>
<input type="text" id="session_time" name="session_time"
    value="{{ old('session_time', '09:00') }}"
    class="input w-full"
    placeholder="HH:MM"
    required>
```

```js
flatpickr('#session_time', {
    enableTime: true,
    noCalendar: true,
    dateFormat: 'H:i',
    time_24hr: true,
    minuteIncrement: 5
});
```

### C. Date & Time Picker (Combined)

Use when both date and time are needed in a single field.

```html
<label class="label-text" for="datetime">Select Date & Time</label>
<input type="text" id="datetime" name="datetime" class="input w-full" placeholder="Select date & time...">
```

```js
flatpickr('#datetime', {
    enableTime: true,
    dateFormat: 'Y-m-d H:i',
    altInput: true,
    altFormat: 'F j, Y at H:i'
});
```

**Key points:**
- Always use `type="text"` instead of `type="date"` or `type="time"`
- Use `altInput: true` with `altFormat` for human-readable display
- `dateFormat` determines the actual submitted value
- Add `minDate: 'today'` to prevent selecting past dates
- Use `onChange` callback to trigger updates in dependent fields

## 3.15 Footer

```html
<footer class="footer bg-base-200/60 p-10">
  <form class="gap-6">
    <div class="flex items-center gap-2 text-xl font-bold text-base-content">
      <span>Brand Name</span>
    </div>
    <p class="text-base-content text-sm">Your tagline here.</p>
    <fieldset>
      <label class="label-text" for="subscribeLetter"> Subscribe to newsletter </label>
      <div class="flex w-full flex-wrap gap-1 sm:flex-nowrap">
        <input class="input input-sm" id="subscribeLetter" placeholder="johndoe@gmail.com" />
        <button class="btn btn-sm btn-primary" type="submit">Subscribe</button>
      </div>
    </fieldset>
  </form>
  <nav class="text-base-content">
    <h6 class="footer-title">Services</h6>
    <a href="#" class="link link-hover">Link 1</a>
  </nav>
</footer>
```

## 3.16 Number Input

```html
<div class="max-w-sm" data-input-number>
  <label class="label-text" for="number-input-label">Quantity:</label>
  <div class="input">
    <input type="text" value="1" aria-label="Label input number" data-input-number-input id="number-input-label" />
    <span class="my-auto flex gap-3">
      <button type="button" class="btn btn-primary btn-soft size-5.5 min-h-0 rounded-sm p-0" aria-label="Decrement button" data-input-number-decrement>
        <span class="icon-[tabler--minus] size-3.5 shrink-0"></span>
      </button>
      <button type="button" class="btn btn-primary btn-soft size-5.5 min-h-0 rounded-sm p-0" aria-label="Increment button" data-input-number-increment>
        <span class="icon-[tabler--plus] size-3.5 shrink-0"></span>
      </button>
    </span>
  </div>
</div>
```

## 3.17 Menu with Badges

```html
<ul class="menu lg:menu-horizontal">
  <li>
    <a href="#">
      <span class="icon-[tabler--mail] size-5"></span>
      Inbox
      <span class="badge badge-sm badge-primary">1K+</span>
    </a>
  </li>
  <li>
    <a href="#">
      <span class="icon-[tabler--info-circle] size-5"></span>
      Updates
      <span class="badge badge-sm badge-warning">NEW</span>
    </a>
  </li>
  <li>
    <a href="#">
      Status
      <span class="badge badge-success size-3 p-0"></span>
    </a>
  </li>
</ul>
```

## 3.18 Pagination

```html
<nav class="flex items-center gap-x-1">
  <button type="button" class="btn btn-soft max-sm:btn-square">
    <span class="icon-[tabler--chevron-left] size-5 rtl:rotate-180 sm:hidden"></span>
    <span class="hidden sm:inline">Previous</span>
  </button>
  <div class="flex items-center gap-x-1">
    <button type="button" class="btn btn-soft btn-square aria-[current='page']:text-bg-soft-primary">1</button>
    <button type="button" class="btn btn-soft btn-square aria-[current='page']:text-bg-soft-primary" aria-current="page">2</button>
    <button type="button" class="btn btn-soft btn-square aria-[current='page']:text-bg-soft-primary">3</button>
    <div class="tooltip inline-block">
      <button type="button" class="tooltip-toggle btn btn-soft btn-square group" aria-label="More Pages">
        <span class="icon-[tabler--dots] size-5 group-hover:hidden"></span>
        <span class="icon-[tabler--chevrons-right] rtl:rotate-180 hidden size-5 shrink-0 group-hover:block"></span>
        <span class="tooltip-content tooltip-shown:opacity-100 tooltip-shown:visible" role="tooltip">
          <span class="tooltip-body">Next 7 pages</span>
        </span>
      </button>
    </div>
    <button type="button" class="btn btn-soft btn-square aria-[current='page']:text-bg-soft-primary">10</button>
  </div>
  <button type="button" class="btn btn-soft max-sm:btn-square">
    <span class="hidden sm:inline">Next</span>
    <span class="icon-[tabler--chevron-right] size-5 rtl:rotate-180 sm:hidden"></span>
  </button>
</nav>
```

## 3.19 Modal / Popup

FlyonUI modals use the overlay system with `data-overlay` attribute for triggering.

### A. Middle Center (Default)

```html
<!-- Trigger Button -->
<button type="button" class="btn btn-primary" aria-haspopup="dialog" aria-expanded="false" aria-controls="my-modal" data-overlay="#my-modal">
  Open Modal
</button>

<!-- Modal -->
<div id="my-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Dialog Title</h3>
        <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-overlay="#my-modal">
          <span class="icon-[tabler--x] size-4"></span>
        </button>
      </div>
      <div class="modal-body">
        Modal content goes here.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-soft btn-secondary" data-overlay="#my-modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
```

### B. Position Variants

Use position classes on the outer modal div:

- **Middle center:** `modal-middle` (default, centered)
- **Middle start:** `modal-middle-start` (left side)
- **Middle end:** `modal-middle-end` (right side)

```html
<!-- Middle Start -->
<div id="modal-start" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle-start hidden" ...>

<!-- Middle End -->
<div id="modal-end" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle-end hidden" ...>
```

### C. Modal Sizing

Add max-width classes to `modal-dialog`:

```html
<div class="modal-dialog max-w-sm">  <!-- Small -->
<div class="modal-dialog max-w-md">  <!-- Medium -->
<div class="modal-dialog max-w-lg">  <!-- Large -->
<div class="modal-dialog max-w-xl">  <!-- Extra Large -->
```

### D. JavaScript Control

```js
// Open modal programmatically
HSOverlay.open('#my-modal');

// Close modal programmatically
HSOverlay.close('#my-modal');
```

**Key points:**
- Trigger uses `data-overlay="#modal-id"` attribute
- Modal outer div needs: `overlay modal overlay-open:opacity-100 overlay-open:duration-300 hidden`
- Close buttons also use `data-overlay="#modal-id"`
- Position classes: `modal-middle`, `modal-middle-start`, `modal-middle-end`

---

# 4. Key Rules (Always Follow)

1. **No CDN links** — download everything locally via npm. No `cdn.jsdelivr.net`, `unpkg.com`, etc.
2. **Every `<label>` needs a `for` attribute** — matching the input's `id`. Exception: wrapping labels.
3. **Skeleton loading** — every async content area must show a skeleton first
4. **Vue is hybrid only** — mount into Blade pages, not a SPA
5. **One entrypoint per Vue page** — no monolithic Vue app
6. **FlyonUI components** — use only the approved components from this guide
7. **Event-based architecture** — dispatch domain events for key business actions
8. **Tenant scoping** — every host query must be scoped to `host_id`
9. **Single codebase** — customer and host share one Laravel project, separated by routes/controllers/views
10. **Check `.ai/PLANNING.md`** — before implementing any feature, check the planning doc for prior decisions
