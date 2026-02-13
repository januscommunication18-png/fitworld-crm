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

### ADR-010: Multi-Studio User Support

- **Status:** Accepted
- **Context:** Users (instructors, staff) may work at multiple studios. Need to support many-to-many relationship between users and hosts while maintaining backward compatibility.
- **Decision:** Introduce `host_user` pivot table with role, permissions, and metadata. Keep legacy `users.host_id` for backward compatibility but use pivot for authoritative multi-studio data.
- **Reasoning:**
  - Instructors commonly teach at multiple studios
  - Staff may manage multiple locations
  - Single user account with multiple studio memberships is better UX than separate accounts
  - Pivot table allows per-studio roles and permissions
- **Consequences:**
  - `User::hosts()` returns belongsToMany relationship
  - `User::currentHost()` returns session-based or primary host
  - `User::host()` (legacy) still works for backward compatibility
  - All permission checks must be context-aware (which studio?)
  - Studio switching UI required in navigation

### ADR-011: Subdomain-Based Onboarding

- **Status:** Accepted
- **Context:** When inviting team members or instructors, the setup experience should be branded with the inviting studio's identity.
- **Decision:** Serve invite acceptance pages on studio subdomains (`{studio}.domain.com/setup/invite/{token}`). Main app login remains on primary domain.
- **Reasoning:**
  - Clear studio context during onboarding
  - Professional, branded experience
  - Prevents confusion about which studio is inviting
  - Token validation includes subdomain verification for security
- **Consequences:**
  - Subdomain routing middleware required (`ResolveSubdomainHost`)
  - Separate Blade layout for subdomain pages (`layouts/subdomain.blade.php`)
  - Invite emails must generate subdomain URLs
  - After setup, redirect to main app (not subdomain)
  - Error pages for wrong subdomain, expired token, etc.

### ADR-012: Client Lifecycle Model (Rename Students → Clients)

- **Status:** Accepted
- **Context:** Need a unified client management system that supports different lifecycle stages (Lead, Client, Member, At-Risk) without creating separate tables for each type.
- **Decision:** Single `clients` table (renamed from `students`) with status-based lifecycle. Clients move through stages: Lead → Client → Member → At-Risk. Status is a field, not a separate entity.
- **Reasoning:**
  - Simpler data model than separate tables per type
  - Single client record maintains full history
  - Status changes are logged, not data migrations
  - Allows flexible filtering and reporting across all stages
  - "At-Risk" is computed from rules, not manually assigned
- **Consequences:**
  - All "Student" references renamed to "Client" in UI and code
  - `clients` table has `status` enum: lead, client, member, at_risk
  - Lead source metadata stored in dedicated columns
  - Membership status tracked separately from client status
  - At-Risk flagging requires background job or query-time calculation

### ADR-013: Client Custom Fields System

- **Status:** Accepted
- **Context:** Each studio has unique data requirements for their clients (emergency contacts, injuries, fitness goals, etc.). Need extensible field system.
- **Decision:** Schema-driven custom fields with:
  - `client_field_sections` table for grouping fields
  - `client_field_definitions` table for field schema (type, label, validation)
  - `client_field_values` table for storing actual values per client
- **Reasoning:**
  - Flexible: studios define their own fields
  - Type-safe: field definitions include type and validation
  - Non-destructive: hiding a field preserves historical data
  - Performant: values stored in dedicated table, not JSON blob
- **Consequences:**
  - Add/Edit Client forms dynamically render custom fields
  - Field types supported: text, textarea, number, date, dropdown, checkbox, yes_no
  - Default system fields cannot be deleted, only hidden
  - Drag-and-drop ordering in settings UI

### ADR-014: Member Portal with Passwordless Auth

- **Status:** Accepted
- **Context:** Studio clients (members) need self-service access to view bookings, membership status, etc. Must be simple and secure without password management.
- **Decision:** Passwordless email OTP authentication for member portal:
  - Member enters email → system checks eligibility (active membership/class pack)
  - If eligible, send 6-digit OTP to email
  - Member enters OTP → verified → access portal
- **Reasoning:**
  - No password to remember = higher engagement
  - Email verification proves identity
  - Eligibility check prevents unauthorized access
  - Simple UX for non-technical users
- **Consequences:**
  - Portal URL: `{subdomain}.domain.com/members`
  - OTP expires in 10 minutes, one-time use
  - Rate limiting: max 5 attempts per email per hour
  - Must check for active membership/class pack before granting access
  - Portal features are "Coming Soon" initially

### ADR-015: Lead Magnet (Web Forms)

- **Status:** Accepted
- **Context:** Studios need to capture leads from their website without building custom integrations. Need simple embeddable forms.
- **Decision:** Built-in form builder (Phase 1 - basic):
  - Create simple forms with predefined field types
  - Hosted URL: `{subdomain}.domain.com/forms/{form-slug}`
  - Form submissions create Lead records with source metadata
  - Capture UTM parameters automatically
- **Reasoning:**
  - Lower barrier to lead capture than API integration
  - Branded experience on studio subdomain
  - Source tracking enables marketing attribution
  - Phase 1 keeps it simple (no conditional logic)
- **Consequences:**
  - `lead_forms` table for form definitions
  - `lead_form_fields` table for form field configuration
  - Form submissions route to `LeadFormController`
  - Auto-create Lead (Client with status=lead)
  - Store UTM params in lead record

### ADR-016: Questionnaire Builder Architecture

- **Status:** Accepted
- **Context:** Studios need customizable onboarding questionnaires for client intake before classes/services.
- **Decision:** Build a schema-driven questionnaire system following the Client Custom Fields pattern with: questionnaires → versions → steps → blocks → questions hierarchy. Separate response storage from question definitions.
- **Reasoning:**
  - Versioning preserves historical responses when questionnaires are edited
  - Block/section grouping allows logical question organization
  - Separation of concerns: definition vs responses
  - Follows existing custom fields pattern for consistency
- **Consequences:**
  - 7 new database tables for questionnaire system
  - Vue-powered builder UI for drag-and-drop
  - Token-based public response pages
  - Version management adds complexity but ensures data integrity

### ADR-017: Questionnaire Versioning Strategy

- **Status:** Accepted
- **Context:** Questionnaires will be edited over time, but client responses must remain valid and viewable with original questions.
- **Decision:** Implement explicit versioning: each edit to an active questionnaire creates a new draft version. Publishing makes the draft active. Responses always link to `questionnaire_version_id`, not just `questionnaire_id`.
- **Reasoning:**
  - Prevents data corruption when questions change
  - Allows viewing old responses with their original context
  - Clear audit trail of changes
  - Studios can review response trends across versions
- **Consequences:**
  - Every response stores `questionnaire_version_id`
  - Admin must "publish" to activate changes
  - Builder always works on draft version
  - Old versions are archived, not deleted

### ADR-018: Questionnaire Attachment Model

- **Status:** Accepted
- **Context:** Questionnaires need to be optionally required for class plans, service plans, and membership plans with configurable timing.
- **Decision:** Use polymorphic `questionnaire_attachments` table with `attachable_type` + `attachable_id` pattern. Attachment includes configuration: required flag, collection timing, and applicability scope.
- **Reasoning:**
  - Single table handles all attachment types
  - Configuration per attachment allows flexibility
  - Booking system can query attachments to determine intake requirements
  - Supports future attachment to other entities
- **Consequences:**
  - Plan edit pages need questionnaire attachment UI
  - Booking creation checks for required questionnaires
  - Email automation triggers based on `collection_timing`

### ADR-019: Questionnaire Response Token System

- **Status:** Accepted
- **Context:** Clients need to complete questionnaires via email link without requiring login.
- **Decision:** Generate unique, non-guessable tokens per response invitation. Token grants access to specific questionnaire for specific client. Tokens expire on completion or after 30 days.
- **Reasoning:**
  - No login required improves completion rates
  - Token-per-response prevents sharing/reuse
  - Expiration adds security
  - Works on mobile without app installation
- **Consequences:**
  - Public routes for `/q/{token}` without auth middleware
  - Token validation in controller
  - Token stored in `questionnaire_responses.token`
  - Must rate-limit response creation to prevent abuse

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

### FEAT-005: Subdomain-Based Account Setup for Invites

- **Status:** Completed
- **Priority:** P1 (high)
- **Description:** Studio-branded onboarding flow for team member and instructor invites via subdomain URLs
- **User Story:** As an invited team member/instructor, I want to complete my account setup on a branded page so I know which studio invited me and feel confident joining.
- **Affected Areas:**
  - Routes: `routes/web.php` (subdomain group)
  - Controllers: `SubdomainSetupController`, `Host/InvitationController`, `Host/AuthController`
  - Middleware: `ResolveSubdomainHost`, `SetCurrentHost`
  - Views: `layouts/subdomain.blade.php`, `subdomain/invite-setup.blade.php`, error pages
  - Models: `User` (multi-host methods), `TeamInvitation`, `Host`
  - Mail: `TeamInvitationMail`
  - Migration: `create_host_user_table`
- **Dependencies:** Team invitation system, Host model with subdomain field

#### Core Flow

1. **Admin sends invite** → `TeamInvitationMail` generates subdomain URL
2. **Invitee clicks link** → `{studio}.domain.com/setup/invite/{token}`
3. **Middleware resolves studio** → `ResolveSubdomainHost` finds Host by subdomain
4. **Setup page renders** → Branded layout with studio logo/name
5. **Form submission** → Creates user (or verifies existing), adds `host_user` record
6. **Redirect** → Main app dashboard (auto-logged in)

#### URL Structure

| Route | URL Pattern | Controller Method |
|-------|-------------|-------------------|
| Show invite | `{subdomain}.{domain}/setup/invite/{token}` | `SubdomainSetupController@showInvite` |
| Accept invite | `{subdomain}.{domain}/setup/invite/{token}` (POST) | `SubdomainSetupController@acceptInvite` |

#### Validation Rules

| Check | Error Response |
|-------|----------------|
| Token doesn't exist | `subdomain/invalid.blade.php` |
| Token already used | `subdomain/invalid.blade.php` |
| Token revoked | `subdomain/invalid.blade.php` |
| Token expired | `subdomain/expired.blade.php` |
| Wrong subdomain | `subdomain/wrong-studio.blade.php` (with correct link) |
| Already a member | `subdomain/already-member.blade.php` |
| Studio not found | `subdomain/studio-not-found.blade.php` |

#### Setup Form Fields

**New Users:**
- First name (required, no digits)
- Last name (required, no digits)
- Password (min 8 chars, with strength indicator)
- Password confirmation

**Existing Users:**
- Password verification only
- Shows "You already have an account" message

#### Post-Setup Actions

1. Create user if new (email auto-verified since they received invite)
2. Add `host_user` pivot record with role/permissions from invitation
3. Set `is_primary = true` if first studio membership
4. Mark invitation as accepted (`accepted_at`, status = accepted)
5. Log user in via `Auth::login()`
6. Set current host in session
7. Redirect to main app dashboard

#### Multi-Studio Support

- **Single studio:** Login → direct to dashboard
- **Multiple studios:** Login → studio selector page
- **Switching:** Sidebar footer shows "Switch Studio" button
- **Session:** Current host stored in `session('current_host_id')`

#### Email Template

- **Subject:** "You're invited to join {studio_name}"
- **Content:** Inviter name, studio name, role, accept button
- **CTA URL:** `{scheme}://{subdomain}.{domain}/setup/invite/{token}`
- **Expiry note:** Link expires in 7 days

#### Files Implemented

| File | Purpose |
|------|---------|
| `app/Http/Controllers/SubdomainSetupController.php` | Handles invite display and acceptance |
| `app/Http/Middleware/ResolveSubdomainHost.php` | Resolves subdomain to Host model |
| `app/Http/Middleware/SetCurrentHost.php` | Sets current host context for auth users |
| `app/Mail/TeamInvitationMail.php` | Generates invite email with subdomain URL |
| `resources/views/layouts/subdomain.blade.php` | Branded layout for subdomain pages |
| `resources/views/subdomain/invite-setup.blade.php` | Invite acceptance form |
| `resources/views/subdomain/wrong-studio.blade.php` | Token belongs to different studio |
| `resources/views/subdomain/invalid.blade.php` | Invalid/used/revoked token |
| `resources/views/subdomain/expired.blade.php` | Expired invitation |
| `resources/views/subdomain/already-member.blade.php` | User already member of studio |
| `resources/views/subdomain/studio-not-found.blade.php` | Subdomain doesn't match any studio |
| `resources/views/auth/select-studio.blade.php` | Multi-studio selection page |
| `database/migrations/2026_02_11_120001_create_host_user_table.php` | Pivot table for user-host memberships |

#### Coming Soon

| Feature | Description |
|---------|-------------|
| Studio logo in invite email | Email template currently uses basic markdown; add studio logo image |
| "Request new invite" on expired page | Allow user to request admin sends a new invite when token expires |
| "Contact studio" link on error pages | Add studio contact info or link on error pages |
| Accept terms checkbox | Optional terms acceptance checkbox on setup form |
| Instructor-specific fields | Phone and profile photo fields for instructor role invites |
| Login prefill after setup | Redirect to login with email/studio prefilled (current flow auto-logs in, which is better UX) |

### FEAT-006: Clients Module (Rename Students → Clients)

- **Status:** Planned
- **Priority:** P1 (high)
- **Description:** Complete client management system with lifecycle stages (Lead, Client, Member, At-Risk), custom fields, tags, lead magnet forms, and member portal.
- **User Story:** As a studio owner, I want to manage all my clients in one place with lifecycle tracking so that I can nurture leads, retain members, and identify at-risk clients.
- **Affected Areas:**
  - Navigation: Sidebar rename "Students" → "Clients"
  - Routes: `/clients/*` (replace `/students/*`)
  - Controllers: `ClientController`, `LeadController`, `TagController`, `LeadFormController`, `MemberPortalController`
  - Models: `Client` (rename Student), `ClientTag`, `Tag`, `LeadForm`, `LeadFormField`, `ClientFieldSection`, `ClientFieldDefinition`, `ClientFieldValue`
  - Views: `resources/views/host/clients/*`, `resources/views/member-portal/*`, `resources/views/lead-forms/*`
  - Settings: New "Members / Client Settings" section
- **Dependencies:** Multi-studio support (ADR-010), Subdomain routing (ADR-011)

---

#### 6.1 Navigation Structure

```
Clients (sidebar)
├── All Clients
├── Leads
├── Members
├── At-Risk
├── Tags
└── Lead Magnet (Coming Soon badge)
```

---

#### 6.2 Client Data Model

**Client Lifecycle (Single Record, Status-Driven)**

| Status | Description | Source |
|--------|-------------|--------|
| `lead` | Captured prospect, not yet converted | Website, marketing, FitNearYou, Lead Magnet |
| `client` | Known person with profile data | Manual entry, converted lead |
| `member` | Client with active membership/subscription | Membership purchase |
| `at_risk` | Flagged for inactivity or payment issues | Rule-based (configurable) |

**Note:** Status is mutable. A client can move: lead → client → member → at_risk → member (if re-engaged).

---

#### 6.3 Features - Client Listing (All Clients)

**List Columns**
- Name (first + last)
- Email
- Phone (optional)
- Status badges: Lead / Member / At-Risk
- Last Visit (date)
- Next Booking (date)
- Source (FitNearYou, Website, Manual, Lead Magnet)
- Tags (pill badges)
- Created Date

**Filters**
- Status: Lead / Member / At-Risk / All
- Source: FitNearYou, Website, Manual, Lead Magnet
- Tags (multi-select)
- Date added (range)
- Location (optional, for multi-location)

**Actions**
- View Profile
- Edit
- Add Booking (shortcut)
- Add Note
- Tag (add/remove)
- Archive (soft delete)

---

#### 6.4 Features - Add/Edit Client

**Default System Fields (Always Present)**
| Field | Required | Hideable |
|-------|----------|----------|
| First Name | Yes | No |
| Last Name | Yes | No |
| Email | Yes (unique per studio) | No |
| Phone | No | Yes |
| Address | No | Yes |
| Notes | No | Yes |
| Tags | No | No |
| Source | No | Yes |

**Custom Fields**
- Rendered below default fields
- Grouped by sections
- Controlled via Settings → Client Custom Fields

---

#### 6.5 Features - Leads

**Definition:** Clients with `status = lead` OR `source IN (marketing, website, lead_magnet, fitnearyou)`

**Lead Listing View**
- Same columns as All Clients, filtered to leads
- Lead-specific metadata visible: source, UTM params, referral info

**Lead Actions**
- Convert to Client (manual button)
- Convert to Member (if purchasing membership)
- Add Note
- Add Booking

**Lead Metadata Fields**
| Field | Description |
|-------|-------------|
| `lead_source` | Enum: marketing, website, lead_magnet, fitnearyou, manual |
| `source_url` | URL where lead was captured |
| `utm_source` | UTM source parameter |
| `utm_medium` | UTM medium parameter |
| `utm_campaign` | UTM campaign parameter |
| `referral_id` | FitNearYou referral tracking ID |

---

#### 6.6 Features - Members

**Definition:** Clients with active membership subscription (`membership_status = active`)

**Members Listing View**
- Same columns as All Clients + membership-specific columns:
  - Membership Plan Name
  - Membership Status: active / paused / cancelled
  - Renewal Date
  - Payment Status (if available)

**Member Conversion**
- Automatic: When client purchases active membership
- Manual: Admin can flag as member (optional)

---

#### 6.7 Features - At-Risk

**Definition:** Clients flagged based on inactivity rules (studio-configurable)

**Default Rules (MVP)**
| Rule | Default | Configurable |
|------|---------|--------------|
| No visit in last X days | 14 days | Yes |
| No upcoming bookings | — | Yes |
| Payment failed | — | Future |

**At-Risk Listing View**
- Same columns as All Clients + reason column
- Reason tag: "Inactive 14 days", "No upcoming bookings"

**At-Risk Actions**
- Send re-engagement email (future)
- Add booking
- Add note
- Clear at-risk flag (manual override)

---

#### 6.8 Features - Tags

**Tag Management (Settings → Tags)**
- Create tag (name, color)
- Edit tag
- Delete tag (with confirmation)
- View usage count

**Tag Assignment**
- Assign tags from client profile
- Assign tags from client listing (bulk action)
- Filter by tags in all client views

---

#### 6.9 Lead Magnet (Phase 1 - Coming Soon)

**Goal:** Simple web forms to capture leads into FitCRM

**Form Builder (Phase 1 - MVP)**
- Form name
- Form slug (auto-generated, editable)
- Available fields:
  - First Name (text)
  - Last Name (text)
  - Email (required)
  - Phone (text)
  - Preferred class type (dropdown)
  - Message (textarea)
- Hidden fields (auto-captured):
  - Source name
  - Source URL
  - UTM parameters

**Form Output**
- Hosted URL: `{subdomain}.domain.com/forms/{form-slug}`
- Future: Embed code (iframe/script)

**Form Submission Flow**
1. User fills form → POST to `/forms/{slug}/submit`
2. Validate fields
3. Create Client record with `status = lead`, `lead_source = lead_magnet`
4. Store UTM params
5. Optional: auto-assign tag
6. Redirect to thank-you page

---

#### 6.10 Settings - Members / Client Settings

**New Settings Section Location:** Settings → Members / Client Settings

**Subsections:**

**1. Member Portal**
- Enable Member Portal: Yes/No (default No)
- Portal URL (read-only): `{subdomain}.domain.com/members`

**2. Client Custom Fields**
- Section management (add/edit/delete/reorder)
- Field management per section:
  - Add field (type, label, required, help text)
  - Edit field
  - Delete field (with warning about data loss)
  - Reorder fields (drag-and-drop)
- Default field visibility toggle (hide/show)

**3. At-Risk Rules**
- Inactivity threshold (days): Default 14
- Enable/disable specific rules

---

#### 6.11 Member Portal

**URL:** `{subdomain}.domain.com/members`

**Authentication Flow (Passwordless OTP)**
1. Member enters email address
2. System checks eligibility:
   - Has active membership? OR
   - Has active class pack? OR
   - Has any subscription entitlement?
3. If eligible:
   - Generate 6-digit OTP
   - Send to email
   - Show OTP entry screen
4. Member enters OTP
5. If valid:
   - Create session
   - Redirect to portal dashboard
6. If not eligible:
   - Show message: "No active membership found for this email. Please contact {studio_name}."

**Security Requirements**
| Rule | Value |
|------|-------|
| OTP expiry | 10 minutes |
| Max attempts | 5 per code |
| Rate limit | 5 requests per email per hour |
| Code reuse | One-time only |

**Portal Branding**
- Studio logo
- Studio name
- Studio contact info / support link

**Portal Features (Coming Soon)**
- Upcoming bookings list
- Membership status
- Book a class link
- Profile view/edit (limited)

---

#### 6.12 Client Custom Fields

**Field Set Structure**
```
Section Header (e.g., "Health & Goals")
├── Field 1 (Short Text)
├── Field 2 (Dropdown)
└── Field 3 (Yes/No)

Section Header (e.g., "Emergency Contact")
├── Field 4 (Short Text)
└── Field 5 (Short Text)
```

**Supported Field Types (Phase 1)**
| Type | Input | Storage |
|------|-------|---------|
| `text` | Single-line text | VARCHAR |
| `textarea` | Multi-line text | TEXT |
| `number` | Numeric input | DECIMAL |
| `date` | Date picker | DATE |
| `dropdown` | Single select | VARCHAR (stores key) |
| `checkbox` | Multi select | JSON array |
| `yes_no` | Toggle | BOOLEAN |

**Field Properties**
| Property | Required | Description |
|----------|----------|-------------|
| Label | Yes | Display name |
| Key/Slug | Auto | Unique identifier (auto-generated) |
| Type | Yes | Field type |
| Required | No | Validation rule |
| Help Text | No | Shown below field |
| Default Value | No | Pre-filled value |
| Options | For dropdown/checkbox | Array of choices |
| Visible on Add | Yes | Show on Add Client form |
| Visible on Edit | Yes | Show on Edit Client form |

**Default Fields Behavior**
- Cannot be deleted
- Can be hidden (toggle visibility)
- Always stored in client record
- Hiding doesn't delete historical values

**Storage Model**
- Values stored per client, per studio
- Changing field definition preserves historical values
- Disabled/hidden fields retain data but don't display

---

#### 6.13 Client Profile Page

**Sections:**
1. **Header:** Name, photo, status badges, quick actions
2. **Overview:** Contact info, next booking, membership summary
3. **Custom Fields:** Rendered from schema
4. **Notes & Tags:** Activity log, tags
5. **Bookings History:** Past classes
6. **Membership:** Plan details, renewal, payment history (if member)

---

#### 6.14 Permissions

| Action | Owner | Admin | Staff | Instructor |
|--------|-------|-------|-------|------------|
| View all clients | ✓ | ✓ | ✓ (if permitted) | ✗ |
| View roster clients | ✓ | ✓ | ✓ | ✓ |
| Add/Edit clients | ✓ | ✓ | ✓ (if permitted) | ✗ |
| Delete/Archive clients | ✓ | ✓ | ✗ | ✗ |
| Manage tags | ✓ | ✓ | ✗ | ✗ |
| Manage custom fields | ✓ | ✓ | ✗ | ✗ |
| Access settings | ✓ | ✓ | ✗ | ✗ |

---

#### 6.15 Acceptance Criteria (MVP)

- [ ] "Students" renamed to "Clients" everywhere in UI
- [ ] Navigation: All Clients, Leads, Members, At-Risk, Tags views exist
- [ ] Leads can be captured with source metadata
- [ ] At-Risk computed from configurable inactivity rule
- [ ] Tags CRUD and assignment works
- [ ] Settings → Members / Client Settings section exists
- [ ] Member Portal toggle in settings
- [ ] Passwordless email OTP login for member portal
- [ ] Portal shows "Coming Soon" after login
- [ ] Custom field builder supports sections + fields
- [ ] Add Client screen shows default + custom fields
- [ ] Default fields can be hidden but not removed
- [ ] Lead Magnet shows "Coming Soon" with description

---

#### 6.16 Coming Soon / Phase 2

| Feature | Description |
|---------|-------------|
| Lead Magnet form builder | Full form creation and hosted forms |
| Member Portal dashboard | Actual booking/membership views |
| At-Risk email automation | Auto-send re-engagement emails |
| Lead Magnet embed code | iframe/script embed for external sites |
| Conditional form logic | Show/hide fields based on answers |
| Custom field import/export | Bulk data management |
| Client merge | Merge duplicate client records |
| Client export | CSV/Excel export |

### FEAT-007: Questionnaire Builder

- **Status:** Completed
- **Priority:** P1 (high)
- **Description:** Complete questionnaire builder system for client onboarding/intake with single-page and multi-step wizard support, section blocks, and mobile-friendly form completion.
- **User Story:** As a studio owner, I want to build and send onboarding questionnaires to better understand clients before their first visit so instructors can deliver a safer and more personalized experience.
- **Affected Areas:**
  - Navigation: Classes & Services → Questionnaires (new menu item)
  - Routes: `/questionnaires/*`, `/q/{token}` (public response page)
  - Controllers: `QuestionnaireController`, `QuestionnaireBuilderController`, `QuestionnaireResponseController`
  - Models: `Questionnaire`, `QuestionnaireVersion`, `QuestionnaireStep`, `QuestionnaireBlock`, `QuestionnaireQuestion`, `QuestionnaireResponse`, `QuestionnaireAnswer`
  - Views: `resources/views/host/questionnaires/*`, `resources/views/questionnaire/*` (public)
  - Vue: `resources/js/apps/questionnaire-builder.js`, `resources/js/apps/questionnaire-response.js`
- **Dependencies:** Email system (SendGrid/Mailgun), Client management (FEAT-006)

---

#### 7.1 Navigation Structure

```
Classes & Services (sidebar section)
├── Catalog
├── Class Plans
├── Service Plans
├── Memberships
├── Questionnaires      ← NEW
└── Service Slots
```

---

#### 7.2 Questionnaire Types

**A) Single Form Questionnaire**
- All questions displayed on one page
- Best for quick onboarding (5–12 questions)
- Submit once at end
- No progress indicator needed

**B) Multi-Page Questionnaire (Wizard)**
- Broken into steps (Step 1, Step 2…)
- Each step contains one or more section blocks
- Progress indicator (e.g., "Step 2 of 5")
- Next/Back navigation
- Auto-save progress after each step
- Resume link valid until final submit

Admin chooses type per questionnaire during creation.

---

#### 7.3 Questionnaire Structure

```
Questionnaire
├── Version (for edit tracking)
│   ├── Step 1 (wizard only, or implicit single step)
│   │   ├── Block A (Section Header + Description)
│   │   │   ├── Question 1
│   │   │   ├── Question 2
│   │   │   └── Question 3
│   │   └── Block B
│   │       ├── Question 4
│   │       └── Question 5
│   └── Step 2 (wizard only)
│       └── Block C
│           └── Question 6
```

**Section Block Properties:**
| Field | Required | Description |
|-------|----------|-------------|
| Block Title | Yes | Header displayed above questions |
| Block Description | No | Explanatory text below title |
| Display Style | No | `plain` (default) or `card` (styled container) |
| Visibility | No | `public` (client sees) or `internal` (admin/instructor only) |

---

#### 7.4 Question Types (MVP)

| Type | Input Control | Storage |
|------|---------------|---------|
| `short_text` | Single-line text input | VARCHAR |
| `long_text` | Multi-line textarea | TEXT |
| `email` | Email input with validation | VARCHAR |
| `phone` | Phone input | VARCHAR |
| `yes_no` | Toggle or radio (Yes/No) | BOOLEAN |
| `single_select` | Radio buttons | VARCHAR (option key) |
| `multi_select` | Checkboxes | JSON array |
| `dropdown` | Select dropdown | VARCHAR (option key) |
| `date` | Date picker (Flatpickr) | DATE |
| `number` | Number input | DECIMAL |
| `acknowledgement` | Single checkbox (e.g., "I confirm…") | BOOLEAN |

**Phase 2 (Future):**
| Type | Description |
|------|-------------|
| `rating` | 1–5 star rating |
| `file_upload` | Medical notes, waivers (sensitive) |

---

#### 7.5 Question Properties

| Property | Required | Description |
|----------|----------|-------------|
| Question Label | Yes | The question text displayed to client |
| Question Type | Yes | One of the MVP types above |
| Required | No | Toggle (default: No) |
| Help Text | No | Explanatory text below question |
| Placeholder | No | Placeholder text for text inputs |
| Options | Conditional | For dropdown/single_select/multi_select |
| Min/Max Length | No | Validation for text inputs |
| Min/Max Value | No | Validation for number inputs |
| Visibility | No | `client` (default) or `instructor_only` |
| Tags | No | For reporting: `injury`, `goals`, `experience` |
| Sensitive | No | Mark as sensitive (restricted visibility) |

---

#### 7.6 Wizard Mode Requirements

**Step Builder (Admin):**
- Add Step button
- Step title (required)
- Step description (optional)
- Add one or more blocks into a step
- Reorder steps via drag-and-drop

**Wizard UX (Client Side):**
- Progress indicator: "Step X of Y"
- Next button disabled until required questions complete
- Back button always available
- Auto-save responses after each step
- Resume link valid until final submit
- Mobile-optimized: sticky Next/Submit button

---

#### 7.7 Attachment Rules

Questionnaires can be attached to:
- Class Plans
- Service Plans
- Membership Plans

**Attachment Configuration:**
| Setting | Options | Default |
|---------|---------|---------|
| Required? | Yes / No | Yes |
| When to collect | `before_booking` (blocking), `after_booking` (intake pending), `before_first_session` | `before_first_session` |
| Applies to | `first_time_only`, `every_booking` | `first_time_only` |

**How Attachment Works:**
1. Class/Service/Membership plan has `questionnaire_id` + attachment settings
2. On booking creation, system checks if questionnaire required
3. If `before_booking`: client must complete before booking confirmed
4. If `after_booking`: booking created, client gets email to complete
5. If `before_first_session`: reminder sent before first session

---

#### 7.8 Sending Options

**A) Email (MVP)**

Delivery methods:
- **Automatic triggers:**
  - After booking created (if `after_booking` mode)
  - After membership purchase
  - Before first session (X hours prior)
- **Manual send:**
  - From Client profile → "Send Questionnaire"
  - From Booking details → "Send Questionnaire"

Email contains:
- Studio name/logo
- CTA button: "Complete Your Intake"
- Secure link: `{subdomain}.domain.com/q/{token}`
- Estimated completion time (optional)

**B) SMS (Coming Soon)**
- Placeholder UI in send options
- Architecture supports `channel = email | sms`
- Future Twilio integration

---

#### 7.9 Client Experience (Mobile-First)

**Layout Requirements:**
- Single column layout
- Large tap targets (min 44px)
- Sticky "Next/Submit" button (wizard mode)
- Minimal scrolling per step

**UX Requirements:**
- Clear intro text with studio branding
- Estimated time to complete (optional)
- Save + resume for wizard mode
- Inline validation messages
- Submit confirmation: "Thanks — your responses were sent to {studio_name}."

**Accessibility:**
- Proper `<label for="">` on all inputs
- Keyboard navigation support
- High contrast support
- ARIA attributes where needed

---

#### 7.10 Versioning System (Critical)

**Why:** Questionnaires change over time but old responses must remain valid.

**Rules:**
1. Questionnaire has versions: v1, v2, v3…
2. Editing an **Active** questionnaire creates a **new draft version**
3. Publishing draft version makes it the new active version
4. Past responses always reference `questionnaire_id + version_id`
5. Old responses remain viewable with their original questions

**Version States:**
| State | Description |
|-------|-------------|
| `draft` | Being edited, not sent to clients |
| `active` | Currently in use, sent to new clients |
| `archived` | No longer in use, responses preserved |

---

#### 7.11 Response Data & Privacy

**Response Linked To:**
- Studio (`host_id`)
- Client (`client_id`)
- Booking (`booking_id` - optional but recommended)
- Questionnaire Version (`questionnaire_version_id`)

**Who Can View Responses:**
| Role | Access |
|------|--------|
| Owner | All responses |
| Admin | All responses |
| Staff | View (configurable per studio) |
| Instructor | Only responses for their assigned sessions |
| Client | Their own submitted responses (optional toggle) |

**Sensitive Questions:**
- Questions marked `sensitive = true`
- Visible only to Owner/Admin + assigned instructor
- Hidden from Staff (even with view permission)

---

#### 7.12 Admin Reporting (MVP)

**Completion Status per Booking:**
- Badge: "Intake Pending" / "Intake Complete"
- Completion timestamp
- Link to view response
- Resend questionnaire button

**Locations:**
- Booking detail page
- Client profile → Responses tab
- Questionnaire detail → Responses list

---

#### 7.13 Screens Required

**Admin Screens:**
| Screen | Route | Description |
|--------|-------|-------------|
| Questionnaires List | `/questionnaires` | All questionnaires with status badges |
| Create Questionnaire | `/questionnaires/create` | Type selection + basic info |
| Questionnaire Builder | `/questionnaires/{id}/builder` | Vue-powered drag-drop builder |
| Preview Mode | `/questionnaires/{id}/preview` | Mobile/desktop toggle preview |
| Responses List | `/questionnaires/{id}/responses` | All submissions for this questionnaire |
| View Response | `/questionnaires/{id}/responses/{response_id}` | Individual response detail |

**Attachment Screens (within existing pages):**
- Class Plan edit → Questionnaire attachment section
- Service Plan edit → Questionnaire attachment section
- Membership Plan edit → Questionnaire attachment section
- Booking detail → Intake status + view response + resend

**Client Profile Integration:**
- Responses history tab
- Send questionnaire action

**Client-Facing Screens:**
| Screen | Route | Description |
|--------|-------|-------------|
| Questionnaire Page | `/q/{token}` | Single page or wizard form |
| Resume Page | `/q/{token}/resume` | Continue wizard from last step |
| Confirmation | `/q/{token}/complete` | Thank you message |

---

#### 7.14 Builder Features (Admin)

**Core Capabilities:**
- Create questionnaire (starts as Draft)
- Choose type: Single Page / Wizard
- Add/Edit/Delete Steps (wizard mode)
- Add/Edit/Delete Blocks within steps
- Add/Edit/Delete Questions within blocks
- Reorder all elements via drag-and-drop
- Preview in mobile and desktop views
- Publish / Unpublish
- Duplicate questionnaire
- Archive questionnaire

**Builder UI (Vue Component):**
- Left panel: Steps/Blocks list
- Center panel: Active block with questions
- Right panel: Question properties editor
- Bottom toolbar: Preview, Save Draft, Publish

---

#### 7.15 Database Tables

```
questionnaires
├── id, host_id FK, name, description, type (single|wizard)
├── status (draft|active|archived), estimated_minutes (nullable)
├── intro_text (nullable), thank_you_message
├── allow_save_resume (boolean, default true for wizard)
├── created_by FK (users), timestamps

questionnaire_versions
├── id, questionnaire_id FK, version_number
├── status (draft|active|archived), published_at (nullable)
├── created_by FK (users), timestamps

questionnaire_steps (wizard mode only)
├── id, questionnaire_version_id FK, title, description (nullable)
├── sort_order, timestamps

questionnaire_blocks
├── id, questionnaire_version_id FK, step_id FK (nullable for single)
├── title, description (nullable)
├── display_style (plain|card), visibility (public|internal)
├── sort_order, timestamps

questionnaire_questions
├── id, questionnaire_block_id FK, question_key (slug)
├── question_label, question_type (enum)
├── options (JSON), is_required (boolean)
├── help_text, placeholder, default_value
├── validation_rules (JSON: min, max, etc.)
├── visibility (client|instructor_only), is_sensitive (boolean)
├── tags (JSON), sort_order, timestamps

questionnaire_attachments
├── id, questionnaire_id FK, attachable_type, attachable_id
├── is_required (boolean), collection_timing (enum)
├── applies_to (first_time_only|every_booking)
├── timestamps

questionnaire_responses
├── id, questionnaire_version_id FK, host_id FK
├── client_id FK, booking_id FK (nullable)
├── token (unique), status (in_progress|completed)
├── current_step (integer, for wizard resume)
├── started_at, completed_at, timestamps
├── ip_address, user_agent

questionnaire_answers
├── id, questionnaire_response_id FK, question_id FK
├── answer (TEXT), timestamps
```

---

#### 7.16 API Endpoints

**Builder API (Admin, Sanctum auth):**
| Method | Endpoint | Purpose |
|--------|----------|---------|
| `GET` | `/api/v1/questionnaires` | List all questionnaires |
| `POST` | `/api/v1/questionnaires` | Create questionnaire |
| `GET` | `/api/v1/questionnaires/{id}` | Get questionnaire with structure |
| `PUT` | `/api/v1/questionnaires/{id}` | Update questionnaire metadata |
| `DELETE` | `/api/v1/questionnaires/{id}` | Delete questionnaire |
| `POST` | `/api/v1/questionnaires/{id}/publish` | Publish draft version |
| `POST` | `/api/v1/questionnaires/{id}/duplicate` | Clone questionnaire |
| `POST` | `/api/v1/questionnaires/{id}/steps` | Add step (wizard) |
| `PUT` | `/api/v1/questionnaires/{id}/steps/{stepId}` | Update step |
| `DELETE` | `/api/v1/questionnaires/{id}/steps/{stepId}` | Delete step |
| `POST` | `/api/v1/questionnaires/{id}/blocks` | Add block |
| `PUT` | `/api/v1/questionnaires/{id}/blocks/{blockId}` | Update block |
| `DELETE` | `/api/v1/questionnaires/{id}/blocks/{blockId}` | Delete block |
| `POST` | `/api/v1/questionnaires/{id}/questions` | Add question |
| `PUT` | `/api/v1/questionnaires/{id}/questions/{qId}` | Update question |
| `DELETE` | `/api/v1/questionnaires/{id}/questions/{qId}` | Delete question |
| `PUT` | `/api/v1/questionnaires/{id}/reorder` | Reorder steps/blocks/questions |

**Response API (Public, token-based):**
| Method | Endpoint | Purpose |
|--------|----------|---------|
| `GET` | `/api/v1/q/{token}` | Get questionnaire structure for client |
| `POST` | `/api/v1/q/{token}/save` | Save progress (wizard) |
| `POST` | `/api/v1/q/{token}/submit` | Final submission |

**Send API (Admin):**
| Method | Endpoint | Purpose |
|--------|----------|---------|
| `POST` | `/api/v1/questionnaires/{id}/send` | Send to client(s) |
| `POST` | `/api/v1/responses/{id}/resend` | Resend incomplete |

---

#### 7.17 Permissions

| Action | Owner | Admin | Staff | Instructor |
|--------|-------|-------|-------|------------|
| View questionnaires | ✓ | ✓ | ✓ (list only) | ✗ |
| Create/Edit questionnaires | ✓ | ✓ | ✗ | ✗ |
| Delete questionnaires | ✓ | ✓ | ✗ | ✗ |
| Send questionnaires | ✓ | ✓ | ✓ (if permitted) | ✗ |
| View all responses | ✓ | ✓ | ✓ (if permitted) | ✗ |
| View own session responses | ✓ | ✓ | ✓ | ✓ |
| Attach to plans | ✓ | ✓ | ✗ | ✗ |

---

#### 7.18 MVP Acceptance Criteria

- [ ] Admin can create questionnaires in draft mode
- [ ] Supports single page + wizard types
- [ ] Supports section blocks with title/description + multiple questions
- [ ] All 11 MVP question types functional
- [ ] Drag-and-drop reordering works
- [ ] Preview mode shows mobile/desktop toggle
- [ ] Can attach questionnaire to class plan, service plan, membership plan
- [ ] Client receives email link and completes form on mobile
- [ ] Wizard mode supports save/resume
- [ ] Responses stored and viewable in booking + client profile
- [ ] Edits create new version; past responses preserved
- [ ] Completion status badge shown on bookings
- [ ] SMS shown as "Coming soon" option (non-functional)

---

#### 7.19 Implementation Phases

**Phase 1: Core Builder (MVP)** ✅ COMPLETED
1. ✅ Database migrations (8 tables)
2. ✅ Models with relationships (8 models)
3. Navigation update (sidebar)
4. Questionnaire CRUD (list, create, edit, delete)
5. Basic builder UI (Blade + Vue hybrid)
6. Single page form type
7. All 11 question types
8. Preview mode

**Phase 2: Wizard & Responses** ✅ COMPLETED
1. ✅ Wizard step management (admin)
2. ✅ Client-facing response page (single page and wizard views)
3. ✅ Auto-save for wizard (JavaScript step-by-step saving)
4. ✅ Response storage (answers saved to questionnaire_answers table)
5. ✅ Manual sending (generate link from questionnaire show page)
6. ✅ Admin response viewing (list and detail views)

**Phase 3: Attachments & Automation** ✅ COMPLETED
> Note: Automatic email triggers deferred until email system is implemented.
1. ✅ Attachment to plans UI (Class Plans, Service Plans, Membership Plans)
2. ⏸️ Booking integration (intake status) — DEFERRED until booking system enhancement
3. ⏸️ Automatic email triggers — DEFERRED until email system is implemented
4. ✅ Client profile responses tab (questionnaires tab with send functionality)
5. ✅ Resend functionality (regenerate link)

**Phase 4: Polish & Advanced** ✅ COMPLETED
1. ✅ Versioning system (version history UI on questionnaire show page)
2. ✅ Duplicate questionnaire (duplicate method in controller)
3. ✅ Archive functionality (unpublish/archive in controller)
4. ✅ Sensitive question privacy (reveal/hide toggle in response detail view)
5. ⏸️ Reporting enhancements — DEFERRED to future iteration
6. ✅ SMS placeholder UI (coming soon message in send modal)

---

#### 7.20 Coming Soon / Future

| Feature | Description |
|---------|-------------|
| SMS delivery | Send questionnaire via SMS |
| File upload questions | Medical notes, waivers |
| Rating questions | 1-5 star ratings |
| Conditional logic | Show/hide questions based on answers |
| Branching | Different paths based on responses |
| Response export | CSV/Excel export |
| Templates library | Pre-built questionnaire templates |
| Embed code | Embed on external website |

### FEAT-008: Walk-In Booking Experience (Owner/Staff)

- **Status:** Planned
- **Priority:** P0 (critical)
- **Description:** Complete walk-in booking system for owner/staff to book clients in-person in under 60 seconds, with payment handling, membership/pack support, and intake questionnaire integration.
- **User Story:** As a studio owner/staff, I want to quickly book walk-in clients so that I can serve customers efficiently without losing revenue or tracking accuracy.
- **Modules Involved:** Schedule, Bookings, Clients, Payments, Memberships, Questionnaires
- **Affected Areas:**
  - Routes: `/api/walk-in/*`, booking routes
  - Controllers: `WalkInBookingController`, `BookingController`, `PaymentController`
  - Models: `Booking` (update), `CustomerMembership` (new), `ClassPack` (new), `ClassPackPurchase` (new), `Payment` (new), `AuditLog` (new)
  - Views: Walk-in modal components, schedule integration
  - Services: `BookingService`, `PaymentService`, `MembershipService`, `StripeService`, `AuditService`
- **Dependencies:** Existing Booking model, ClassSession model, ServiceSlot model, Client model, MembershipPlan model, Questionnaire system

---

#### 8.1 Walk-In Principles

| Principle | Description |
|-----------|-------------|
| **Fast** | Minimal steps, quick-add client, default values prefilled |
| **Flexible** | Allow overrides (capacity, payment, intake) with audit logs |
| **Accurate** | Updates roster, capacity, payments, memberships/credits correctly |
| **Optional Communications** | Do not force customer emails for walk-ins unless desired |

---

#### 8.2 Entry Points

Walk-in booking can be started from:

| Entry Point | Location | Context |
|-------------|----------|---------|
| Schedule → Today View | Class row | "Walk-In Booking" button |
| Schedule → Today View | Service slot row | "Walk-In Booking" button |
| Schedule → Calendar | Session details popup | "Add Walk-In" button |
| Global Quick Action | Top navbar | "+ Walk-In" button |
| Client Profile | Client page | "Book for Client" button |

---

#### 8.3 Walk-In Booking Modes

The walk-in flow supports 3 booking types:

| Mode | Description |
|------|-------------|
| **Book a Class Session** | Add client to a class roster |
| **Book a Service Slot** | Book a 1-on-1 service appointment |
| **Sell a Membership** | Sell membership + optionally book immediately |

Each mode follows the shared step pattern for speed.

---

#### 8.4 Shared Walk-In Booking Flow

```
┌─────────────────┐
│ Step 1: Client  │ ← Search or Quick Add
└────────┬────────┘
         ▼
┌─────────────────┐
│ Step 2: Payment │ ← Membership > Pack > Manual > Stripe > Comp
└────────┬────────┘
         ▼
┌─────────────────┐
│ Step 3: Intake  │ ← Send Link / Mark Complete / Skip
└────────┬────────┘
         ▼
┌─────────────────┐
│ Step 4: Confirm │ ← Review + Submit
└─────────────────┘
```

---

#### 8.5 Step 1 — Identify the Client

**Client Selector (Always First)**

- Search existing clients by: name, email, phone
- Display recent clients for quick selection

**Quick Add (Inline)**

| Field | Required | Notes |
|-------|----------|-------|
| First Name | Yes | |
| Last Name | Yes | |
| Phone | No | Recommended for walk-ins |
| Email | No | Optional |
| Send email confirmations | Toggle | Default OFF for walk-in |

**Rules:**
- If same email exists → attach booking to existing client
- If no email provided → allow "phone-only" record

---

#### 8.6 Step 2 — Payment Method Selection

**Payment Methods (Priority Order in UI)**

| Method | Description | When Available |
|--------|-------------|----------------|
| Membership | Use active membership credits | If client has active eligible membership |
| Pack | Use class pack credits | If client has eligible pack with credits |
| Manual | Cash/Venmo/Zelle/PayPal/etc. | Always |
| Stripe | Card payment | If Stripe enabled |
| Comp/Free | No charge | Owner/Admin only |

**Walk-In Defaults:**
- Default method: Manual payment (Cash) — configurable per studio

**Manual Payment Capture (if selected)**

| Field | Required |
|-------|----------|
| Payment method type | Yes (cash/venmo/zelle/paypal/cash app/other) |
| Amount | Yes |
| Notes | No |
| Paid status | Yes (default: Paid) |

**Rules:**
- If membership selected → verify eligible for this class/service
- If pack selected → verify credits available
- If unpaid allowed → booking status shows "Payment Pending"

---

#### 8.7 Step 3 — Intake/Questionnaire (Optional)

**When Shown:**
- If selected class/service/membership has required questionnaire attached

**Intake Status Display:**
- Required / Optional / Not required

**Options:**

| Option | Description |
|--------|-------------|
| Send intake link by email | Sends questionnaire to client email (if email exists) |
| Mark as completed (staff override) | Owner/admin marks as done (audit logged) |
| Skip for now | Proceeds without intake (if not required) |

**Rules:**
- If "Required before booking" is enabled and this is walk-in, allow owner override with audit reason

---

#### 8.8 Step 4 — Confirmation

**Final Confirmation Screen Shows:**
- What is being booked/sold
- Client name
- Payment method
- Intake status
- Total amount

**Buttons:**
- Confirm & Save
- Cancel

**After Success:**
- Show success toast
- Optionally print/share details (future)

---

#### 8.9 Class Session Walk-In (Detailed)

**Pre-filled from Schedule Context:**
- Class Plan name
- Date/time
- Location/room
- Primary/backup instructor
- Capacity + spots left

**Booking Rules:**

| Rule | Behavior |
|------|----------|
| Duplicate check | If client already booked → show "Already booked" |
| Capacity available | Normal booking proceeds |
| Capacity full | Option A: Add to waitlist (future) |
| | Option B: Owner override "Add anyway" (requires reason + audit log) |

**Attendance Options:**
- "Mark as checked-in now" toggle (default OFF)

**Booking Record Output:**

| Field | Value |
|-------|-------|
| booking_type | class |
| class_session_id | Selected session |
| client_id | Selected/created client |
| payment_source | membership/pack/manual/stripe/comp |
| created_by_user_id | Current user |
| booking_source | internal_walkin |
| status | confirmed |
| intake_status | pending/completed/waived |

---

#### 8.10 Service Slot Walk-In (Detailed)

**Slot Selection:**
- Must be: Available, Not in past
- If no slot pre-selected, show "Find next available" search:
  - Choose service plan
  - Choose instructor (optional)
  - Pick time from available slots

**Rules:**
- Slot cannot be double-booked
- If slot is booked → show alternative slots
- Buffer times enforced automatically

**Booking Output:**

| Field | Value |
|-------|-------|
| booking_type | service |
| service_slot_id | Selected slot |
| service_plan_id | From slot |
| client_id | Selected/created client |
| payment_source | As selected |
| created_by_user_id | Current user |
| booking_source | internal_walkin |

---

#### 8.11 Membership Sales (Detailed)

**Entry Points:**
- Client Profile → "Sell Membership"
- Walk-In → "Sell Membership"
- Booking flow upsell: "Sell membership instead"

**Membership Purchase Flow:**

```
Step 1: Select client
Step 2: Select membership plan
Step 3: Select payment method
  ├── A) Stripe subscription (recurring)
  │     └── Requires: user email, Stripe enabled
  │     └── Creates subscription, status: active/past_due
  └── B) Manual membership (cash/check)
        └── Admin sets: start date, cycle, next renewal
        └── Status: active (manual)
Step 4: Confirm purchase
```

**Membership Rules:**
- Store eligibility scope (all classes/selected plans)
- If credit-based → grant credits for current period immediately

**Book Immediately After Purchase:**
After successful membership creation, show:
- "Book their first class now"
- "Book a service now"
- "Done"

---

#### 8.12 Receipts & Communication

**Default Communications Behavior:**
- Studio setting: "Send confirmation emails for internal bookings" (default OFF)

**If email exists and setting ON:**
- Send booking confirmation
- Send receipt (if paid)

**Manual Payments Receipt:**
- Provide "Mark paid + send receipt" option
- If no email, store receipt in client profile only

---

#### 8.13 Audit Logging

**Actions to Log:**

| Action | Details |
|--------|---------|
| booking.created | With booking_source |
| booking.cancelled | Who cancelled |
| booking.checked_in | Timestamp |
| booking.capacity_overridden | Reason required |
| booking.intake_waived | Reason required |
| payment.processed | Method, amount |
| payment.refunded | Amount, reason |
| membership.created | Method (stripe/manual) |
| membership.cancelled | Reason |
| pack.purchased | Pack details |

**Audit Log Fields:**
- actor_user_id
- action_type
- entity_type, entity_id
- before_data, after_data (JSON)
- reason
- ip_address
- timestamp

---

#### 8.14 Database Schema (New Tables)

```
customer_memberships
├── id, host_id FK, client_id FK, membership_plan_id FK
├── stripe_subscription_id (nullable), stripe_customer_id (nullable)
├── status (enum: active, paused, cancelled, expired)
├── payment_method (enum: stripe, manual)
├── credits_remaining (nullable), credits_per_period (nullable)
├── current_period_start, current_period_end
├── started_at, cancelled_at, expires_at
├── timestamps
Indexes: [host_id, client_id], [status]

class_packs
├── id, host_id FK, name, class_count, price
├── expires_after_days (nullable)
├── eligible_class_plan_ids (JSON, nullable)
├── stripe_product_id (nullable)
├── status (enum: active, archived)
├── timestamps
Index: [host_id, status]

class_pack_purchases
├── id, host_id FK, client_id FK, class_pack_id FK
├── classes_remaining, purchased_at, expires_at
├── payment_id FK (nullable)
├── timestamps
Index: [host_id, client_id]

payments
├── id, host_id FK, client_id FK
├── booking_id FK (nullable), payable_type, payable_id (polymorphic)
├── amount, currency (default USD)
├── payment_method (enum: stripe, membership, pack, manual, cash, comp)
├── manual_method (nullable: cash, venmo, zelle, paypal, cash_app, other)
├── status (enum: pending, completed, failed, refunded)
├── stripe_payment_intent_id (nullable), stripe_charge_id (nullable)
├── notes, processed_by_user_id FK
├── timestamps
Indexes: [host_id, client_id], [booking_id], [status]

audit_logs
├── id, host_id FK, user_id FK
├── action (string)
├── auditable_type, auditable_id (polymorphic)
├── before_data (JSON), after_data (JSON)
├── reason (nullable), ip_address
├── timestamps
Indexes: [host_id, auditable_type, auditable_id], [action]
```

**Booking Table Updates:**

| New Column | Type | Description |
|------------|------|-------------|
| booking_source | enum | online, internal_walkin, api |
| intake_status | enum | pending, completed, waived, not_required |
| intake_waived_by | FK users | Who waived intake |
| intake_waived_reason | string | Reason for waiving |
| capacity_override | boolean | Was capacity overridden |
| capacity_override_reason | string | Reason for override |
| created_by_user_id | FK users | Staff who created booking |

**Client Table Update:**

| New Column | Type | Description |
|------------|------|-------------|
| stripe_customer_id | string | Stripe customer ID |

---

#### 8.15 Service Layer

```
app/Services/
├── BookingService.php
│   ├── createWalkInBooking()
│   ├── createOnlineBooking()
│   ├── cancelBooking()
│   ├── checkIn()
│   ├── validateCapacity()
│   └── overrideCapacity()
│
├── PaymentService.php
│   ├── processManualPayment()
│   ├── processStripePayment()
│   ├── processMembershipPayment()
│   ├── processPackPayment()
│   ├── processCompPayment()
│   ├── refund()
│   └── getAvailablePaymentMethods()
│
├── MembershipService.php
│   ├── createManualMembership()
│   ├── createStripeMembership()
│   ├── pauseMembership()
│   ├── cancelMembership()
│   ├── checkEligibility()
│   └── deductCredit()
│
├── ClassPackService.php
│   ├── purchasePack()
│   ├── checkEligibility()
│   ├── deductCredit()
│   └── getAvailablePacks()
│
├── StripeService.php
│   ├── createCustomer()
│   ├── createPaymentIntent()
│   ├── confirmPayment()
│   ├── createSubscription()
│   ├── cancelSubscription()
│   └── refundCharge()
│
└── AuditService.php
    ├── log()
    ├── logCapacityOverride()
    ├── logIntakeWaive()
    └── logPaymentAction()
```

---

#### 8.16 API Endpoints

**Walk-In API (Admin, Sanctum auth):**

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/v1/walk-in/class` | Book class walk-in |
| POST | `/api/v1/walk-in/service` | Book service walk-in |
| POST | `/api/v1/walk-in/membership` | Sell membership |
| GET | `/api/v1/walk-in/availability` | Check session availability |
| GET | `/api/v1/walk-in/payment-methods/{client}` | Get available payment methods for client |

**Client Quick Add API:**

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/v1/clients/quick-add` | Create client inline |
| GET | `/api/v1/clients/search` | Search clients |

---

#### 8.17 UI Components

```
resources/views/components/walk-in/
├── modal.blade.php              → Main walk-in modal wrapper
├── client-selector.blade.php    → Search + Quick Add
├── session-card.blade.php       → Selected class/service display
├── payment-selector.blade.php   → Payment method options
├── intake-status.blade.php      → Questionnaire handling
├── confirmation.blade.php       → Final summary
└── success.blade.php            → Success message
```

---

#### 8.18 Permissions

| Action | Owner | Admin | Staff | Instructor |
|--------|-------|-------|-------|------------|
| Walk-in booking | ✓ | ✓ | ✓ (if permitted) | ✗ |
| Override capacity | ✓ | ✓ | ✗ | ✗ |
| Waive intake | ✓ | ✓ | ✗ | ✗ |
| Comp/Free payment | ✓ | ✓ | ✗ | ✗ |
| Sell membership | ✓ | ✓ | ✓ (if permitted) | ✗ |
| Process refunds | ✓ | ✓ | ✗ | ✗ |

---

#### 8.19 Reporting Requirements

Walk-in bookings must be labeled: `booking_source = internal_walkin`

**Reports Should Show:**
- Online vs walk-in booking counts
- Manual vs Stripe revenue breakdown
- Membership conversion rate from walk-ins (future)

---

#### 8.20 Implementation Phases

**Phase 1: Database Foundation** ✅ COMPLETED
- [x] Create migrations: customer_memberships, class_packs, class_pack_purchases, payments, audit_logs
- [x] Update bookings table with new columns
- [x] Update clients table with stripe_customer_id
- [x] Create models with relationships

**Phase 2: Service Layer** ✅ COMPLETED
- [x] BookingService with walk-in logic
- [x] PaymentService for all payment methods
- [x] MembershipService for eligibility + credits
- [x] ClassPackService for pack handling
- [x] AuditService for logging

**Phase 3: Walk-In API** ✅ COMPLETED
- [x] API controllers for walk-in booking
- [x] Client quick-add endpoint
- [x] Payment method availability endpoint
- [x] Form request validation

**Phase 4: Walk-In UI** ✅ COMPLETED
- [x] Walk-in modal component
- [x] Client selector with search + quick add
- [x] Payment method selector
- [x] Intake status component
- [x] Confirmation screen

**Phase 5: Class Session Walk-In** ✅ COMPLETED
- [x] Schedule integration (class-sessions index + show pages)
- [x] Walk-in button on published, future sessions
- [x] Added isPast()/isFuture() methods to ClassSession model
- [x] Walk-in modal integrated in dashboard layout
- [x] walk-in.js bundled via layout.js

**Phase 6: Service Slot Walk-In** ✅ COMPLETED
- [x] Walk-in button on available service slots
- [x] Updated walk-in.js to handle service slot data format
- [x] Session display shows duration for services
- [ ] "Find next available" search (future enhancement)
- [ ] Buffer time handling (future enhancement)

**Phase 7: Membership Sales**
- [ ] Membership plan selection
- [ ] Manual membership creation
- [ ] Stripe subscription creation (future)
- [ ] "Book first class" flow

**Phase 8: Intake Integration**
- [ ] Link questionnaires to walk-in flow
- [ ] Send link / waive options
- [ ] Audit logging for waivers

**Phase 9: Audit & Reporting**
- [ ] Complete audit logging
- [ ] Booking source reporting
- [ ] Revenue breakdown reports

---

#### 8.21 MVP Acceptance Criteria

- [ ] Owner/staff can book a class from Today view in <60 seconds
- [ ] Owner/staff can book a service slot in <60 seconds
- [ ] Owner/staff can sell a membership (manual payment)
- [ ] Payment method selection supports membership/pack/manual/comp
- [ ] Capacity override with audit logging
- [ ] Intake required flow supports send link / waive (owner only)
- [ ] Walk-in bookings tracked separately in reporting
- [ ] Quick-add client works with phone-only record

---

#### 8.22 Coming Soon / Future

| Feature | Description |
|---------|-------------|
| Stripe payments | Full Stripe integration for walk-ins |
| Stripe subscriptions | Recurring membership via Stripe |
| Waitlist | Add to waitlist when class is full |
| Print receipt | Print/share receipt after booking |
| SMS confirmation | Send booking confirmation via SMS |
| Webhook handlers | Stripe webhook processing |
| Refund processing | In-app refund workflow |

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

- host_user                — pivot table for multi-studio user memberships
  Columns: id, user_id FK, host_id FK, role (owner/admin/staff/instructor),
           permissions (json, nullable), instructor_id FK (nullable),
           is_primary (boolean, default false), joined_at (timestamp)
  Constraints: unique [user_id, host_id], indexes on both FKs
  Note: Migration includes data transfer from legacy users.host_id to pivot

- team_invitations         — invitation records for team/instructor invites
  Columns: id, host_id FK, email, role, permissions (json), token (unique),
           invited_by FK (users), status (pending/accepted/expired/revoked),
           expires_at, accepted_at, accepted_by_user_id FK (nullable)

- clients                  — unified client records (renamed from students)
  Columns: id, host_id FK, first_name, last_name, email, phone, address (json),
           status (enum: lead, client, member, at_risk), membership_status (enum: none, active, paused, cancelled),
           lead_source (enum: manual, marketing, website, lead_magnet, fitnearyou, referral),
           source_url, utm_source, utm_medium, utm_campaign, referral_id,
           last_visit_at, next_booking_at, membership_id FK (nullable), membership_expires_at,
           notes (text), archived_at (soft delete), timestamps
  Indexes: [host_id, status], [host_id, email], [host_id, membership_status]

- tags                     — tag definitions per host
  Columns: id, host_id FK, name, slug, color, usage_count (default 0), timestamps
  Constraints: unique [host_id, slug]

- client_tag               — pivot table for client-tag relationships
  Columns: id, client_id FK, tag_id FK, timestamps
  Constraints: unique [client_id, tag_id]

- client_field_sections    — custom field section headers per host
  Columns: id, host_id FK, name, sort_order, is_active (default true), timestamps

- client_field_definitions — custom field schema per host
  Columns: id, host_id FK, section_id FK (nullable), field_key (slug), field_label,
           field_type (enum: text, textarea, number, date, dropdown, checkbox, yes_no),
           options (json, for dropdown/checkbox), is_required (default false),
           help_text, default_value, show_on_add (default true), show_on_edit (default true),
           sort_order, is_active (default true), timestamps
  Constraints: unique [host_id, field_key]

- client_field_values      — custom field values per client
  Columns: id, client_id FK, field_definition_id FK, value (text), timestamps
  Constraints: unique [client_id, field_definition_id]

- lead_forms               — lead magnet form definitions per host
  Columns: id, host_id FK, name, slug, description, thank_you_message,
           redirect_url (nullable), auto_tag_id FK (nullable),
           is_active (default true), submissions_count (default 0), timestamps
  Constraints: unique [host_id, slug]

- lead_form_fields         — fields configured for each lead form
  Columns: id, lead_form_id FK, field_type (enum: text, email, phone, dropdown, textarea),
           field_label, field_key, is_required (default false), options (json),
           sort_order, timestamps

- member_portal_sessions   — portal login sessions
  Columns: id, host_id FK, client_id FK, email, otp_code (hashed),
           otp_expires_at, attempts (default 0), verified_at (nullable),
           ip_address, user_agent, created_at
  Indexes: [host_id, email], [otp_expires_at]

- client_notes             — notes/activity log per client
  Columns: id, client_id FK, user_id FK (author), note_type (enum: note, call, email, booking, system),
           content (text), timestamps

Tables (planned):
- class_sessions           — individual class sessions (date/time instances)
- bookings                 — class bookings (scoped by host_id)
- memberships              — membership plans (scoped by host_id)
- client_memberships       — client membership subscriptions
- payments                 — payment records (scoped by host_id)
- attendance               — attendance logs (scoped by host_id)
- reminders                — scheduled reminders (scoped by host_id)
- intro_offers             — trial/intro offer configs (scoped by host_id)

- questionnaires           — questionnaire definitions (scoped by host_id)
  Columns: id, host_id FK, name, description (nullable), type (enum: single, wizard),
           status (enum: draft, active, archived), estimated_minutes (nullable),
           intro_text (nullable), thank_you_message (nullable),
           allow_save_resume (boolean, default true), created_by FK (users), timestamps
  Indexes: [host_id, status]

- questionnaire_versions   — version tracking for edits
  Columns: id, questionnaire_id FK, version_number (integer),
           status (enum: draft, active, archived), published_at (nullable),
           created_by FK (users), timestamps
  Constraints: unique [questionnaire_id, version_number]

- questionnaire_steps      — wizard steps (wizard mode only)
  Columns: id, questionnaire_version_id FK, title, description (nullable),
           sort_order (integer), timestamps
  Indexes: [questionnaire_version_id, sort_order]

- questionnaire_blocks     — section blocks grouping questions
  Columns: id, questionnaire_version_id FK, step_id FK (nullable, for wizard mode),
           title, description (nullable), display_style (enum: plain, card),
           visibility (enum: public, internal), sort_order (integer), timestamps
  Indexes: [questionnaire_version_id, sort_order], [step_id]

- questionnaire_questions  — individual question definitions
  Columns: id, questionnaire_block_id FK, question_key (slug),
           question_label (text), question_type (enum: short_text, long_text, email,
           phone, yes_no, single_select, multi_select, dropdown, date, number, acknowledgement),
           options (JSON, for select types), is_required (boolean, default false),
           help_text (nullable), placeholder (nullable), default_value (nullable),
           validation_rules (JSON: min, max, pattern), visibility (enum: client, instructor_only),
           is_sensitive (boolean, default false), tags (JSON), sort_order (integer), timestamps
  Indexes: [questionnaire_block_id, sort_order]

- questionnaire_attachments — links questionnaires to plans (polymorphic)
  Columns: id, questionnaire_id FK, attachable_type (string), attachable_id (bigint),
           is_required (boolean, default true),
           collection_timing (enum: before_booking, after_booking, before_first_session),
           applies_to (enum: first_time_only, every_booking), timestamps
  Indexes: [attachable_type, attachable_id], [questionnaire_id]

- questionnaire_responses  — client response sessions
  Columns: id, questionnaire_version_id FK, host_id FK, client_id FK,
           booking_id FK (nullable), token (string, unique),
           status (enum: pending, in_progress, completed),
           current_step (integer, nullable, for wizard resume),
           started_at (nullable), completed_at (nullable),
           ip_address (nullable), user_agent (nullable), timestamps
  Indexes: [host_id, client_id], [token], [questionnaire_version_id]

- questionnaire_answers    — individual question answers
  Columns: id, questionnaire_response_id FK, question_id FK,
           answer (TEXT), timestamps
  Constraints: unique [questionnaire_response_id, question_id]
```

> Every table except `hosts` and `studio_types` must have a `host_id` foreign key.

### Eloquent Models (Implemented)

| Model | Table | Key Relationships | Notes |
|-------|-------|-------------------|-------|
| `Host` | `hosts` | hasMany users, instructors, studioClasses; belongsToMany users (via host_user) | Tenant root. Casts json columns as arrays. |
| `User` | `users` | belongsTo Host (legacy), belongsToMany hosts (via host_user) | Multi-studio support. `currentHost()`, `getPrimaryHost()`, `hasMultipleHosts()`, `setCurrentHost()`. Context-aware role/permission methods. |
| `Instructor` | `instructors` | belongsTo Host, belongsTo User (nullable), hasMany StudioClass | `user_id` null until instructor creates an account. |
| `StudioClass` | `classes` | belongsTo Host, belongsTo Instructor (nullable) | Named `StudioClass` to avoid PHP reserved word `class`. Uses `$table = 'classes'`. |
| `StudioType` | `studio_types` | — | Admin lookup. `scopeActive` query scope. |
| `TeamInvitation` | `team_invitations` | belongsTo Host, belongsTo User (inviter), belongsTo User (accepter) | Status: pending/accepted/expired/revoked. Methods: `isExpired()`, `isPending()`, `markAsAccepted()`, `revoke()`, `regenerate()`. |
| `Client` | `clients` | belongsTo Host, belongsToMany Tag, hasMany ClientFieldValue, hasMany ClientNote, belongsTo Membership (nullable) | Renamed from Student. Scopes: `leads()`, `members()`, `atRisk()`, `active()`. Status enum. |
| `Tag` | `tags` | belongsTo Host, belongsToMany Client | Simple tag model with color support. |
| `ClientFieldSection` | `client_field_sections` | belongsTo Host, hasMany ClientFieldDefinition | Groups custom fields. Sortable. |
| `ClientFieldDefinition` | `client_field_definitions` | belongsTo Host, belongsTo ClientFieldSection, hasMany ClientFieldValue | Field schema with type, validation, options. |
| `ClientFieldValue` | `client_field_values` | belongsTo Client, belongsTo ClientFieldDefinition | Stores actual custom field values. |
| `LeadForm` | `lead_forms` | belongsTo Host, hasMany LeadFormField, belongsTo Tag (auto-tag) | Form definition for lead capture. |
| `LeadFormField` | `lead_form_fields` | belongsTo LeadForm | Field config for lead form. |
| `MemberPortalSession` | `member_portal_sessions` | belongsTo Host, belongsTo Client | OTP-based auth session for member portal. |
| `ClientNote` | `client_notes` | belongsTo Client, belongsTo User (author) | Activity log and notes. |
| `Questionnaire` | `questionnaires` | belongsTo Host, hasMany QuestionnaireVersion, belongsTo User (creator) | Parent questionnaire definition. Scopes: `active()`, `draft()`. |
| `QuestionnaireVersion` | `questionnaire_versions` | belongsTo Questionnaire, hasMany QuestionnaireStep, hasMany QuestionnaireBlock | Version tracking. Methods: `publish()`, `archive()`. |
| `QuestionnaireStep` | `questionnaire_steps` | belongsTo QuestionnaireVersion, hasMany QuestionnaireBlock | Wizard steps only. Sortable. |
| `QuestionnaireBlock` | `questionnaire_blocks` | belongsTo QuestionnaireVersion, belongsTo QuestionnaireStep (nullable), hasMany QuestionnaireQuestion | Section grouping. Visibility: public/internal. |
| `QuestionnaireQuestion` | `questionnaire_questions` | belongsTo QuestionnaireBlock, hasMany QuestionnaireAnswer | Question definition with type, options, validation. |
| `QuestionnaireAttachment` | `questionnaire_attachments` | belongsTo Questionnaire, morphTo attachable | Polymorphic link to Class/Service/Membership plans. |
| `QuestionnaireResponse` | `questionnaire_responses` | belongsTo QuestionnaireVersion, belongsTo Host, belongsTo Client, belongsTo Booking (nullable), hasMany QuestionnaireAnswer | Client response session. Token-based access. |
| `QuestionnaireAnswer` | `questionnaire_answers` | belongsTo QuestionnaireResponse, belongsTo QuestionnaireQuestion | Individual answer storage. |

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
| `questionnaire-builder.js` | `/questionnaires/{id}/builder` | Drag-drop questionnaire builder | Planned |
| `questionnaire-response.js` | `/q/{token}` | Client-facing form (single/wizard) | Planned |

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
| Q5 | Tenant scoping middleware? | All queries need `host_id` filtering — global scope vs middleware | Resolved — `SetCurrentHost` middleware sets context; queries use `User::currentHost()` |
| Q6 | Signup API: save per-step or all at end? | Current frontend stores all data client-side | Resolved — per-step save (ADR-008) |

---

## 8. Decision Log

Log every decision with date and reasoning. Most recent first.

| Date | Decision | Context | Outcome |
|---|---|---|---|
| 2026-02-13 | Questionnaire Builder architecture (ADR-016) | Studios need customizable client intake forms | Schema-driven system: questionnaires → versions → steps → blocks → questions; follows Custom Fields pattern |
| 2026-02-13 | Questionnaire versioning strategy (ADR-017) | Responses must stay valid when questionnaires are edited | Explicit versions; edits create draft; responses link to version_id; old versions archived not deleted |
| 2026-02-13 | Questionnaire attachment model (ADR-018) | Questionnaires attach to class/service/membership plans | Polymorphic `questionnaire_attachments` table with timing + scope config |
| 2026-02-13 | Response token system (ADR-019) | Clients complete forms via email link without login | Unique tokens per response; public routes; 30-day expiry; rate limiting |
| 2026-02-13 | Questionnaire Attachment UI implementation | Admins need to link questionnaires to plans | Added morphMany to ClassPlan/ServicePlan/MembershipPlan; created reusable partial `_questionnaire-attachments.blade.php`; added SyncsQuestionnaireAttachments trait |
| 2026-02-13 | Questionnaire Builder all phases completed | Full questionnaire system implementation | Phases 1-4 completed: database/models, builder UI, client response pages, wizard auto-save, admin response viewing, version history, client profile tab, SMS placeholder |
| 2026-02-13 | Questionnaire types: single vs wizard | Need both quick forms and multi-step intake | Single page (all questions) or Wizard (steps with progress); admin chooses per questionnaire |
| 2026-02-13 | Block-based question grouping | Questions need logical sections with headers | Blocks contain questions; blocks have title, description, visibility; supports card styling |
| 2026-02-13 | 11 MVP question types | Need comprehensive input types for intake forms | short_text, long_text, email, phone, yes_no, single_select, multi_select, dropdown, date, number, acknowledgement |
| 2026-02-13 | Wizard auto-save and resume | Long forms shouldn't lose progress | Auto-save after each step; resume link in email; `current_step` tracks position |
| 2026-02-13 | Mobile-first client experience | Most clients complete on phone | Single column, large tap targets, sticky submit button, minimal scrolling per step |
| 2026-02-13 | Sensitive question privacy | Some health/injury questions need restricted access | `is_sensitive` flag; visible only to owner/admin + assigned instructor; hidden from staff |
| 2026-02-13 | SMS delivery as "Coming Soon" | SMS is valuable but not MVP | Placeholder UI; architecture supports `channel = email | sms`; Twilio integration later |
| 2026-02-11 | Clients Module replaces Students (ADR-012) | Need unified client management with lifecycle stages | Single `clients` table with status-driven lifecycle: lead → client → member → at_risk |
| 2026-02-11 | Client Custom Fields system (ADR-013) | Studios need flexible extra fields for clients | Schema-driven: sections, definitions, values tables; supports 7 field types; default fields hideable not deletable |
| 2026-02-11 | Member Portal with passwordless OTP (ADR-014) | Members need self-service access without password management | Email OTP auth; 6-digit code; 10min expiry; eligibility check (active membership/class pack) |
| 2026-02-11 | Lead Magnet web forms (ADR-015) | Studios need simple lead capture forms | Phase 1: basic forms on subdomain URLs; auto-create leads with UTM tracking; embed code in Phase 2 |
| 2026-02-11 | At-Risk computed from rules | At-Risk status should be automatic, not manual | Rule-based: no visit in X days (default 14), no upcoming bookings; configurable per studio |
| 2026-02-11 | Tags as separate entity | Need flexible client segmentation | `tags` + `client_tag` pivot; tags are studio-scoped; filter by tags in all views |
| 2026-02-11 | Lead source tracking | Need marketing attribution for leads | `lead_source` enum + UTM fields + referral_id; supports FitNearYou, website, marketing, lead_magnet sources |
| 2026-02-11 | Member Portal URL structure | Members need branded portal access | Portal at `{subdomain}.domain.com/members`; leverages existing subdomain routing |
| 2026-02-11 | Settings → Members / Client Settings | Need central location for client-related settings | New settings section with: Member Portal toggle, Custom Fields builder, At-Risk rules config |
| 2026-02-11 | My Profile page for all users | Team members need to view/edit their own profile regardless of role | `/settings/profile` always accessible; shows personal info, photo upload, password change, role/permissions (read-only); linked instructor profile if applicable |
| 2026-02-11 | Role-based permission enforcement | Permissions page showed same content for all roles; need to enforce permissions across the app | Created `CheckPermission` middleware; updated sidebars with `@if($user->hasPermission())` checks; grouped routes by permission; owner sees everything, other roles restricted |
| 2026-02-11 | CheckPermission middleware for routes | Need to protect routes based on user permissions | Middleware accepts multiple permissions (any match grants access); owner bypasses all checks; redirects to dashboard with error on denial |
| 2026-02-11 | Settings index smart redirect | `/settings` should redirect to first accessible page based on permissions | `SettingsController@index` checks permissions in priority order and redirects accordingly |
| 2026-02-11 | Advanced settings owner-only | Data export, audit logs, danger zone should be owner-only | Controller methods check `isOwner()` and redirect with error if not owner |
| 2026-02-11 | Subdomain-based invite onboarding (ADR-011) | Invitees need branded studio context during setup | Setup pages served on `{studio}.domain.com/setup/invite/{token}`; branded layout with logo/name; error pages for edge cases |
| 2026-02-11 | Multi-studio user support via pivot table (ADR-010) | Users may belong to multiple studios with different roles | `host_user` pivot table with role, permissions, is_primary; legacy `users.host_id` kept for backward compatibility |
| 2026-02-11 | Session-based current host context | Need to track which studio user is currently working in | `session('current_host_id')` set on login/switch; `SetCurrentHost` middleware shares with views |
| 2026-02-11 | Studio selector for multi-studio users | Users with multiple memberships need to choose studio | `select-studio.blade.php` shown after login if `hasMultipleHosts() > 1`; sidebar "Switch Studio" button |
| 2026-02-11 | Auto-login after invite acceptance | Better UX than requiring separate login step | `Auth::login()` called after successful setup; redirects directly to dashboard |
| 2026-02-11 | Token validation includes subdomain check | Security: prevent token use on wrong studio subdomain | `SubdomainSetupController` compares token's `host_id` with resolved subdomain host; redirects with correct URL if mismatch |
| 2026-02-11 | Existing user detection during invite setup | Same email shouldn't create duplicate accounts | If user exists, show password-only form; add membership without creating new account |
| 2026-02-11 | ResolveSubdomainHost middleware | Need to resolve subdomain to Host model for subdomain routes | Middleware extracts subdomain, queries Host, sets on request attributes; returns 404 if not found |
| 2026-02-10 | Instructor Employment & Availability | Instructors need employment details, workload limits, and availability windows | Added 10 new fields to instructors table; multi-step modal UI; soft warnings during class scheduling |
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
