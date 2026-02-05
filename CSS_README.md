# FitCRM - CSS Component Guide

> **Library:** FlyonUI (Tailwind CSS)
> Always read this file before creating any page to ensure consistent components across the project.

---

> **IMPORTANT — No CDN links in production pages.**
> Do NOT use CDN links (`cdn.jsdelivr.net`, `unpkg.com`, etc.) in any project page.
> All libraries (FlyonUI, Tailwind CSS, FullCalendar, Flatpickr, Tabler Icons, etc.) must be **downloaded locally** and served from the project's own files.
> CDN links are only acceptable in `test-components.html` for quick prototyping.

> **IMPORTANT — Every `<label>` must have a `for` attribute.**
> All `<label>` elements must include a `for="inputId"` attribute that matches the `id` of the associated form input.
> The only exception is wrapping labels (e.g. checkbox option cards) where the `<label>` directly wraps the `<input>`.

---

## 1. Select Dropdown (FlyonUI Advance Select)

### A. Multi-Select with Search

Use this variant when users need to **select multiple options** with a **search filter**.

```html
<div class="max-w-sm">
  <label class="label-text" for="multi-select-members">Select Members</label>
  <select
    id="multi-select-members"
    multiple
    data-select='{
      "hasSearch": true,
      "isSearchDirectMatch": false,
      "searchPlaceholder": "Search options...",
      "placeholder": "Select options...",
      "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
      "toggleClasses": "advance-select-toggle select-disabled:pointer-events-none select-disabled:opacity-40",
      "dropdownClasses": "advance-select-menu max-h-48 -ms-1 overflow-y-auto pt-0",
      "optionClasses": "advance-select-option selected:select-active",
      "optionTemplate": "<div class=\"flex items-center\"> <div class=\"size-8 me-2\" data-icon></div><div><div class=\"text-sm font-semibold text-base-content\" data-title></div> <div class=\"text-xs text-base-content/80\" data-description></div></div><div class=\"flex justify-between items-center flex-1\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block \"></span></div> </div>",
      "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content absolute top-1/2 end-3 -translate-y-1/2 \"></span>"
    }'
    class="hidden"
    aria-label="Advance select"
  >
    <option value="">Choose</option>
    <option value="1" data-select-option='{"icon": "<img class=\"rounded-full\" src=\"AVATAR_URL\" alt=\"Name\" />"}'>
      Option 1
    </option>
    <!-- Add more options as needed -->
  </select>
</div>
```

### B. Single Select (No Search, No Multi-Select)

Use this variant for a **simple single-select** dropdown.

```html
<div class="max-w-sm">
  <label class="label-text" for="single-select-member">Select Member</label>
  <select
    id="single-select-member"
    data-select='{
      "placeholder": "Select option...",
      "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
      "toggleClasses": "advance-select-toggle select-disabled:pointer-events-none select-disabled:opacity-40",
      "dropdownClasses": "advance-select-menu max-h-48 -ms-1 overflow-y-auto pt-0",
      "optionClasses": "advance-select-option selected:select-active",
      "optionTemplate": "<div class=\"flex items-center\"> <div class=\"size-8 me-2\" data-icon></div><div><div class=\"text-sm font-semibold text-base-content\" data-title></div> <div class=\"text-xs text-base-content/80\" data-description></div></div><div class=\"flex justify-between items-center flex-1\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block \"></span></div> </div>",
      "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content absolute top-1/2 end-3 -translate-y-1/2 \"></span>"
    }'
    class="hidden"
    aria-label="Advance select"
  >
    <option value="">Choose</option>
    <option value="1" data-select-option='{"icon": "<img class=\"rounded-full\" src=\"AVATAR_URL\" alt=\"Name\" />"}'>
      Option 1
    </option>
    <!-- Add more options as needed -->
  </select>
</div>
```

**Key differences:**
- **Multi-select:** has `multiple` attribute, `"hasSearch": true`, `"isSearchDirectMatch": false`, `"searchPlaceholder": "..."`
- **Single select:** no `multiple` attribute, no search-related properties in `data-select`

---

## 2. Alerts (FlyonUI Alert Component)

Alerts use `role="alert"` for accessibility. Three style variants are available, each with semantic color options.

### A. Default (Solid Background)

Full-color background alerts for high-visibility messages.

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

Lighter background for less intrusive alerts. Add `alert-soft` class.

```html
<div class="alert alert-soft alert-warning flex items-center gap-4" role="alert">
  <span class="icon-[tabler--alert-triangle] shrink-0 size-6"></span>
  <p><span class="text-lg font-semibold">Warning alert:</span> Your message here.</p>
</div>

<div class="alert alert-soft alert-success flex items-center gap-4" role="alert">
  <span class="icon-[tabler--circle-check] shrink-0 size-6"></span>
  <p><span class="text-lg font-semibold">Success alert:</span> Your message here.</p>
</div>
```

### C. Outline (Border Only)

Border-only style for minimal alerts. Add `alert-outline` class.

```html
<div class="alert alert-outline alert-warning flex items-center gap-4" role="alert">
  <span class="icon-[tabler--alert-triangle] shrink-0 size-6"></span>
  <p><span class="text-lg font-semibold">Warning alert:</span> Your message here.</p>
</div>

<div class="alert alert-outline alert-success flex items-center gap-4" role="alert">
  <span class="icon-[tabler--circle-check] shrink-0 size-6"></span>
  <p><span class="text-lg font-semibold">Success alert:</span> Your message here.</p>
</div>
```

**Key differences:**
- **Default:** `alert alert-{color}` — solid background
- **Soft:** `alert alert-soft alert-{color}` — muted/lighter background
- **Outline:** `alert alert-outline alert-{color}` — border only, no fill
- **Colors:** `alert-warning` (yellow/amber), `alert-success` (green)
- **Icons:** `icon-[tabler--alert-triangle]` for warnings, `icon-[tabler--circle-check]` for success

---

## 4. Avatar Group — Person / Profile Display (Pull-Up Animation with Tooltips)

> **Standard component for displaying people/profiles** — Use this component whenever showing instructors, students, staff, or any person/profile across FitCRM and FitNearYou (e.g. class attendees, studio instructors, student lists).

Overlapping avatar groups with hover pull-up animation and name tooltips. Two variants: plain group and group with a "+N more" counter.

### A. Avatar Group (Pull-Up)

Avatars overlap with `-space-x-5` and lift on hover via `pull-up`.

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
  <!-- Repeat for each avatar -->
</div>
```

### B. Avatar Group with Counter

Same as above, but the last item is a placeholder showing the overflow count.

```html
<div class="avatar-group pull-up -space-x-5">
  <!-- Regular avatars... -->

  <!-- Counter (last item) -->
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
</div>
```

**Key classes:**

| Class | Purpose |
|---|---|
| `avatar-group` | Groups avatars together |
| `pull-up` | Hover animation — avatar lifts up |
| `-space-x-5` | Negative spacing for overlap effect |
| `avatar` | Individual avatar wrapper |
| `avatar-placeholder` | Used for the counter (no image) |
| `w-13` | Avatar size (3.25rem) |
| `bg-neutral text-neutral-content` | Counter background/text color |
| `tooltip` / `tooltip-toggle` / `tooltip-content` / `tooltip-body` | Tooltip on hover |
| `tooltip-shown:opacity-100 tooltip-shown:visible` | Makes tooltip visible on hover |
| `cursor-default` | No pointer cursor on counter |

**Key differences:**
- **Plain group:** all items are `avatar` with `<img>`
- **With counter:** last item uses `avatar avatar-placeholder` with a `<span>+N</span>` instead of an image

---

## 3. Animated Stats & Text (Intersection Observer + Motion Presets)

FlyonUI provides scroll-triggered animations using `intersect:motion-*` utility classes. Elements animate when they scroll into view.

### A. Stats Cards with Slide-In Animation

Stat cards that slide in from the left with staggered delays and a spring-bounce easing.

```html
<div class="flex flex-wrap gap-6">
  <!-- Card 1 — no delay -->
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

  <!-- Card 2 — 400ms delay -->
  <div class="stats intersect:motion-preset-slide-left intersect:motion-delay-[400ms] intersect:motion-ease-spring-bouncier max-sm:w-full">
    <div class="stat">
      <div class="avatar avatar-placeholder">
        <div class="bg-warning/20 text-warning size-10 rounded-full">
          <span class="icon-[tabler--cash] size-6"></span>
        </div>
      </div>
      <div class="stat-value mb-1">Revenue</div>
      <div class="stat-title">$45,000 of $100,000</div>
      <div class="progress bg-warning/10 h-2" role="progressbar" aria-label="Revenue Progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar progress-warning w-2/5"></div>
      </div>
    </div>
  </div>

  <!-- Card 3 — 800ms delay -->
  <div class="stats intersect:motion-preset-slide-left intersect:motion-delay-[800ms] intersect:motion-ease-spring-bouncier max-sm:w-full">
    <div class="stat">
      <div class="avatar avatar-placeholder">
        <div class="bg-error/20 text-error size-10 rounded-full">
          <span class="icon-[tabler--credit-card] size-6"></span>
        </div>
      </div>
      <div class="stat-value mb-1">Invoice</div>
      <div class="stat-title">$18,200 of $25,000</div>
      <div class="progress bg-error/10 h-2" role="progressbar" aria-label="Invoice Progressbar" aria-valuenow="73" aria-valuemin="0" aria-valuemax="100">
        <div class="progress-bar progress-error w-[73%]"></div>
      </div>
    </div>
  </div>
</div>
```

### B. Text with Scroll Animation

Heading uses blur-in from left, paragraph uses focus-in effect.

```html
<div>
  <h2 class="intersect:motion-preset-blur-left intersect:motion-delay-[1000ms] mb-4 text-3xl font-bold">
    Track Your Stats
  </h2>

  <p class="intersect:motion-preset-focus intersect:motion-delay-[1400ms]">
    Your paragraph text here...
  </p>
</div>
```

**Animation classes reference:**

| Class | Effect |
|---|---|
| `intersect:motion-preset-slide-left` | Slides element in from the left on scroll |
| `intersect:motion-preset-blur-left` | Blurs in from the left on scroll |
| `intersect:motion-preset-focus` | Fades/focuses in on scroll |
| `intersect:motion-ease-spring-bouncier` | Spring easing with bounce |
| `intersect:motion-delay-[400ms]` | Custom delay before animation starts |

**Stat card structure:**
- `stats` — wrapper container
- `stat` — individual stat block
- `stat-value` — large value text
- `stat-title` — smaller description text
- `avatar avatar-placeholder` — icon circle container
- `progress` / `progress-bar` — progress indicator

**Color tokens used:**
- `bg-success/20`, `text-success`, `progress-success` — green (orders, completed)
- `bg-warning/20`, `text-warning`, `progress-warning` — amber (revenue, pending)
- `bg-error/20`, `text-error`, `progress-error` — red (invoices, urgent)

**Icons used:**
- `icon-[tabler--package]` — orders/packages
- `icon-[tabler--cash]` — revenue/money
- `icon-[tabler--credit-card]` — invoices/payments

---

## 5. Badges with Icons (FlyonUI Badge Component)

Small icon badges in semantic colors. Use for status indicators, tags, labels, and compact icon markers.

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

**Badge color variants:**

| Class | Color |
|---|---|
| `badge` | Default / neutral |
| `badge-primary` | Primary brand color |
| `badge-secondary` | Secondary color |
| `badge-accent` | Accent color |
| `badge-info` | Blue / informational |
| `badge-success` | Green / success |
| `badge-warning` | Amber / warning |
| `badge-error` | Red / error |

**Structure:**
- `badge` — outer container, `size-6 p-0` for compact icon-only badges
- `icon-[tabler--*]` — Tabler icon inside the badge

**Icons used:**
- `icon-[tabler--user]` — person/profile
- `icon-[tabler--star]` — favorites/ratings
- `icon-[tabler--sun]` — day/light mode
- `icon-[tabler--moon]` — night/dark mode
- `icon-[tabler--folder]` — categories/files
- `icon-[tabler--check]` — confirmed/complete
- `icon-[tabler--cloud]` — cloud/sync
- `icon-[tabler--clock]` — time/schedule

---

## 6. Breadcrumbs (FlyonUI Breadcrumb Component)

Navigation breadcrumbs with icons and a collapsible "more" indicator. Supports RTL via `rtl:rotate-180` on separators.

```html
<div class="breadcrumbs">
  <ol>
    <li>
      <a href="#"> <span class="icon-[tabler--folder] size-5"></span>Home</a>
    </li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li>
      <a href="#" aria-label="More Pages"><span class="icon-[tabler--dots]"></span></a>
    </li>
    <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
    <li aria-current="page">
      <span class="icon-[tabler--file] me-1 size-5"></span>
      Breadcrumb
    </li>
  </ol>
</div>
```

**Structure:**
- `breadcrumbs` — outer wrapper
- `<ol>` — ordered list of crumb items
- `<li>` — each breadcrumb step (link or current page)
- `breadcrumbs-separator` — separator item with chevron icon
- `aria-current="page"` — marks the current/active page (no link)
- `aria-label="More Pages"` — accessible label for the collapsed "..." link

**Key classes:**

| Class | Purpose |
|---|---|
| `breadcrumbs` | Wrapper container |
| `breadcrumbs-separator` | Separator between items |
| `rtl:rotate-180` | Flips chevron for RTL layouts |
| `aria-current="page"` | Marks the active page |

**Icons used:**
- `icon-[tabler--folder]` — home/root level
- `icon-[tabler--chevron-right]` — separator arrow
- `icon-[tabler--dots]` — collapsed pages indicator
- `icon-[tabler--file]` — current page

---

## 7. Buttons (FlyonUI Button Component)

Standard buttons in all semantic color variants.

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

**Color variants:**

| Class | Color |
|---|---|
| `btn` | Default / neutral |
| `btn-primary` | Primary brand color |
| `btn-secondary` | Secondary color |
| `btn-accent` | Accent color |
| `btn-info` | Blue / informational |
| `btn-success` | Green / success |
| `btn-warning` | Amber / warning |
| `btn-error` | Red / error |

**Usage:** Apply `btn` as the base class, then add a color modifier. Works on `<button>`, `<a>`, and `<input type="submit">` elements.

---

## 8. Calendar (FullCalendar + FlyonUI Modal)

Interactive monthly/weekly/daily calendar with event creation via a FlyonUI modal. Uses [FullCalendar](https://fullcalendar.io/) for the calendar engine and FlyonUI overlay/modal for the event form.

### Required CDN Dependencies

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
```

### HTML Structure

```html
<!-- Calendar container -->
<div class="card flex not-prose p-4 w-full">
  <div id="calendar-custom"></div>
</div>

<!-- Hidden trigger button for modal -->
<button type="button" class="btn hidden" id="modalTrigger"
  aria-haspopup="dialog" aria-expanded="false"
  aria-controls="calendar-event-modal"
  data-overlay="#calendar-event-modal"></button>

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

### Event Color Classes

| Class | Color | Use Case |
|---|---|---|
| `fc-event-primary` | Primary | Conferences, multi-day events |
| `fc-event-secondary` | Secondary | Recurring sessions |
| `fc-event-success` | Green | Confirmed, completed |
| `fc-event-info` | Blue | Informational, all-day |
| `fc-event-warning` | Amber | Payments, reminders |
| `fc-event-error` | Red | Meetings, urgent |
| `fc-event-disabled` | Muted | Blocked/unavailable date ranges (display: 'background') |

### Calendar Config Highlights

| Option | Value | Purpose |
|---|---|---|
| `initialView` | `'dayGridMonth'` | Default to month view |
| `editable` | `true` | Drag & drop events |
| `selectable` | `true` | Click to create events |
| `dayMaxEvents` | `2` | Show "+more" after 2 events per day |
| `headerToolbar.right` | `dayGridMonth,timeGridWeek,timeGridDay,listMonth` | View switcher |
| `eventResizableFromStart` | `true` | Resize events from either end |

### Modal Classes (FlyonUI Overlay)

| Class | Purpose |
|---|---|
| `overlay modal` | Base modal container |
| `overlay-open:opacity-100` | Fade-in on open |
| `overlay-open:duration-300` | 300ms transition |
| `modal-dialog` | Dialog wrapper |
| `modal-content` | Content area |
| `modal-header` / `modal-body` / `modal-footer` | Layout sections |
| `data-overlay="#id"` | Toggle/close the modal |
| `HSOverlay.close('#id')` | Programmatic close via JS |

### Key Interactions

- **Click a date** — opens modal to create a new event
- **Click an event** — opens modal to edit the event title
- **Drag an event** — moves it to another date
- **Blocked dates** — background events prevent new event creation

---

## 9. Cards (FlyonUI Card Component)

Content cards with title, subtitle, body text, and action links.

```html
<div class="card sm:max-w-sm">
  <div class="card-body">
    <h5 class="card-title mb-0">Welcome to Our Platform</h5>
    <div class="text-base-content/50 mb-6">Your journey starts here</div>
    <p class="mb-4">Explore a range of features and services designed to enhance your experience. Join us and make the most out of what we have to offer.</p>
    <div class="card-actions">
      <a href="#" class="link link-primary no-underline">Get Started</a>
      <a href="#" class="link link-primary no-underline">Learn More</a>
    </div>
  </div>
</div>
```

**Key classes:**

| Class | Purpose |
|---|---|
| `card` | Outer card container (rounded, shadow) |
| `sm:max-w-sm` | Constrains width on small+ screens |
| `card-body` | Padded content area |
| `card-title` | Bold heading text |
| `text-base-content/50` | Muted subtitle (50% opacity) |
| `card-actions` | Action links/buttons container |
| `link link-primary` | Primary-colored link |
| `no-underline` | Removes default link underline |

**Structure:**
- `card` > `card-body` — main layout
- `card-title` — heading
- `text-base-content/50` — subtitle/description
- `<p>` — body text
- `card-actions` — footer with links or buttons

---

## 10. Chat Bubbles (FlyonUI Chat Component)

Chat message bubbles with avatars, timestamps, and delivery status. Two directions: receiver (left-aligned) and sender (right-aligned).

```html
<!-- Receiver (left) -->
<div class="chat chat-receiver">
  <div class="chat-avatar avatar">
    <div class="size-10 rounded-full">
      <img src="AVATAR_URL" alt="avatar" />
    </div>
  </div>
  <div class="chat-header text-base-content">
    User Name
    <time class="text-base-content/50">12:45</time>
  </div>
  <div class="chat-bubble">Message text here</div>
  <div class="chat-footer text-base-content/50">
    <div>Delivered</div>
  </div>
</div>

<!-- Sender (right) -->
<div class="chat chat-sender">
  <div class="chat-avatar avatar">
    <div class="size-10 rounded-full">
      <img src="AVATAR_URL" alt="avatar" />
    </div>
  </div>
  <div class="chat-header text-base-content">
    User Name
    <time class="text-base-content/50">12:46</time>
  </div>
  <div class="chat-bubble">Message text here</div>
  <div class="chat-footer text-base-content/50">
    Seen
    <span class="icon-[tabler--checks] text-success align-bottom"></span>
  </div>
</div>
```

**Key classes:**

| Class | Purpose |
|---|---|
| `chat` | Base chat wrapper |
| `chat-receiver` | Left-aligned (incoming message) |
| `chat-sender` | Right-aligned (outgoing message) |
| `chat-avatar` | Avatar container |
| `chat-header` | Name + timestamp row |
| `chat-bubble` | Message content bubble |
| `chat-footer` | Delivery status below the bubble |
| `text-base-content/50` | Muted text for time/status |
| `icon-[tabler--checks] text-success` | Double-check "seen" indicator |

**Key differences:**
- **Receiver:** `chat-receiver` — aligns left with bubble on the right of avatar
- **Sender:** `chat-sender` — aligns right with bubble on the left of avatar

---

## 11. Checkboxes — Custom Option Cards (FlyonUI Checkbox Component)

Checkbox inputs styled as selectable option cards with a title, price, and description. Useful for plan selection, feature toggles, and multi-choice forms.

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
  <label class="custom-option flex flex-row items-start gap-3 sm:w-1/2">
    <input type="checkbox" class="checkbox checkbox-primary mt-2" required />
    <span class="label-text w-full text-start">
      <span class="flex justify-between mb-1">
        <span class="text-base font-medium">Premium</span>
        <span class="text-base-content/50 text-base">$ 5.00</span>
      </span>
      <span class="text-base-content/80">Get 5 projects with 5 team members.</span>
    </span>
  </label>
</div>
```

**Key classes:**

| Class | Purpose |
|---|---|
| `custom-option` | Card-style wrapper with border highlight on check |
| `checkbox` | Base checkbox input |
| `checkbox-primary` | Primary color when checked |
| `label-text` | Text content area beside the checkbox |
| `text-base-content/50` | Muted price/secondary text |
| `text-base-content/80` | Slightly muted description text |
| `sm:w-1/2` | Half-width on small+ screens (side-by-side) |
| `sm:flex-nowrap` | Prevents wrapping on small+ screens |

**Structure:**
- Outer `div` with `flex flex-wrap` / `sm:flex-nowrap` — responsive row layout
- `<label class="custom-option">` — clickable card that wraps checkbox + text
- `checkbox checkbox-primary` — the actual input
- `label-text` > title row (`flex justify-between`) + description

---

## 12. Dropdown with Nested Collapse (FlyonUI Dropdown Component)

Dropdown menu with a header, action items, a collapsible nested submenu, and auto-close behavior.

```html
<div class="dropdown relative inline-flex [--auto-close:inside]">
  <button id="dropdown-collapse" type="button" class="dropdown-toggle btn btn-primary" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
    Actions
    <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
  </button>
  <div class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-collapse">
    <div class="dropdown-header">Quick Actions</div>
    <div><a class="dropdown-item" href="#">Send Newsletter</a></div>
    <div><a class="dropdown-item" href="#">View Purchases</a></div>
    <div>
      <button id="nested-collapse-2" class="collapse-toggle dropdown-item justify-between" aria-expanded="false" aria-controls="nested-collapse-content" data-collapse="#nested-collapse-content">
        More Options
        <span class="icon-[tabler--chevron-down] collapse-open:rotate-180 size-4"></span>
      </button>
      <div class="collapse hidden w-full overflow-hidden transition-[height] duration-300" aria-labelledby="nested-collapse-2" id="nested-collapse-content">
        <ul class="py-3 ps-3">
          <li><a class="dropdown-item" href="#">Download Documents</a></li>
          <li><a class="dropdown-item" href="#">Manage Team Account</a></li>
        </ul>
      </div>
    </div>
    <div><a class="dropdown-item" href="#">Logout</a></div>
  </div>
</div>
```

**Key classes:**

| Class | Purpose |
|---|---|
| `dropdown` | Base dropdown wrapper |
| `[--auto-close:inside]` | Keeps dropdown open when clicking inside |
| `dropdown-toggle` | Button that opens/closes the menu |
| `dropdown-open:rotate-180` | Rotates chevron icon when open |
| `dropdown-menu` | The menu panel |
| `dropdown-open:opacity-100` | Fade-in on open |
| `dropdown-header` | Non-clickable section header |
| `dropdown-item` | Clickable menu item |
| `collapse-toggle` | Button that toggles nested submenu |
| `collapse` | Collapsible content container |
| `collapse-open:rotate-180` | Rotates chevron in nested toggle |
| `data-collapse="#id"` | Links toggle to collapsible content |

**Structure:**
- `dropdown` > `dropdown-toggle` (button) + `dropdown-menu` (panel)
- `dropdown-header` — section title inside menu
- `dropdown-item` — individual actions
- Nested: `collapse-toggle` + `collapse` for expandable submenu
- `[--auto-close:inside]` — prevents closing when interacting with nested content

---

## 13. Date & Time Pickers (Flatpickr + FlyonUI Input)

Lightweight date/time picker using [Flatpickr](https://flatpickr.js.org/) with FlyonUI's `input` class for consistent styling. Three variants: date only, date+time, and time only.

### Required CDN Dependencies

```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
```

### A. Date Picker (Human Friendly)

Displays "February 5, 2026" format to user, stores `2026-02-05` as value.

```html
<label class="label-text" for="flatpickr-human-friendly">Select Date</label>
<input type="text" class="input max-w-sm" placeholder="Month DD, YYYY" id="flatpickr-human-friendly" />
```

```js
flatpickr('#flatpickr-human-friendly', {
  altInput: true,
  altFormat: 'F j, Y',
  dateFormat: 'Y-m-d'
})
```

### B. Date & Time Picker

Combined date and time selection in `YYYY-MM-DD HH:MM` format.

```html
<label class="label-text" for="flatpickr-date-time">Select Date & Time</label>
<input type="text" class="input max-w-sm" placeholder="YYYY-MM-DD HH:MM" id="flatpickr-date-time" />
```

```js
flatpickr('#flatpickr-date-time', {
  enableTime: true,
  dateFormat: 'Y-m-d H:i'
})
```

### C. Time Only Picker

Time-only selection with no calendar, `HH:MM` format.

```html
<label class="label-text" for="flatpickr-time">Select Time</label>
<input type="text" class="input max-w-sm" placeholder="HH:MM" id="flatpickr-time" />
```

```js
flatpickr('#flatpickr-time', {
  enableTime: true,
  noCalendar: true,
  dateFormat: 'H:i'
})
```

**Flatpickr config reference:**

| Option | Value | Purpose |
|---|---|---|
| `altInput` | `true` | Shows human-readable format, stores machine format |
| `altFormat` | `'F j, Y'` | Display format (e.g. "February 5, 2026") |
| `dateFormat` | `'Y-m-d'` | Stored value format |
| `enableTime` | `true` | Enables time selection |
| `noCalendar` | `true` | Hides calendar, time-only mode |

**Key classes:**
- `input` — FlyonUI styled text input
- `max-w-sm` — constrains width

---

## 17. Pagination (FlyonUI Pagination Component)

Page navigation with previous/next buttons, numbered pages, active state, and a "more pages" tooltip with hover icon swap.

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
    <!-- More pages tooltip -->
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

**Key classes:**

| Class | Purpose |
|---|---|
| `btn btn-soft btn-square` | Square soft-styled page button |
| `aria-[current='page']:text-bg-soft-primary` | Highlights the active page |
| `aria-current="page"` | Marks the currently active page |
| `max-sm:btn-square` | Prev/Next become icon-only on small screens |
| `sm:hidden` / `hidden sm:inline` | Toggle icon vs text by screen size |
| `group-hover:hidden` / `group-hover:block` | Swaps dots to chevrons on hover |
| `rtl:rotate-180` | Flips arrows for RTL |
| `tooltip` / `tooltip-toggle` / `tooltip-content` | "Next 7 pages" tooltip on hover |

**Icons used:**
- `icon-[tabler--chevron-left]` — previous arrow
- `icon-[tabler--chevron-right]` — next arrow
- `icon-[tabler--dots]` — ellipsis (default state)
- `icon-[tabler--chevrons-right]` — double arrow (hover state)

---

## 16. Menu with Badges (FlyonUI Menu Component)

Horizontal/vertical menu with icons and badge indicators. Use for navigation bars, sidebars, and action menus.

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

**Key classes:**

| Class | Purpose |
|---|---|
| `menu` | Base menu (vertical by default) |
| `lg:menu-horizontal` | Switches to horizontal on large screens |
| `badge badge-sm badge-primary` | Small primary text badge (e.g. "1K+") |
| `badge badge-sm badge-warning` | Small warning text badge (e.g. "NEW") |
| `badge badge-success size-3 p-0` | Tiny dot indicator (no text, green) |

**Structure:**
- `<ul class="menu">` > `<li>` > `<a>` with icon + label + badge
- Icons and badges are inline inside the `<a>` tag
- Dot badge: use `size-3 p-0` with no text content for a status dot

**Icons used:**
- `icon-[tabler--mail]` — inbox/messages
- `icon-[tabler--info-circle]` — updates/info

---

## 15. Number Input (FlyonUI Input Number Component)

Numeric input with increment/decrement buttons. Uses `data-input-number` attributes for JS behavior.

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

**Key classes & attributes:**

| Class / Attribute | Purpose |
|---|---|
| `data-input-number` | Wrapper — enables FlyonUI JS behavior |
| `data-input-number-input` | The text input for the number value |
| `data-input-number-decrement` | Minus button |
| `data-input-number-increment` | Plus button |
| `input` | FlyonUI styled input wrapper |
| `btn btn-primary btn-soft` | Soft primary buttons for +/- |
| `size-5.5 min-h-0 rounded-sm p-0` | Compact square button sizing |
| `icon-[tabler--minus]` / `icon-[tabler--plus]` | +/- icons |
| `label-text` | Label styling |

---

## 14. Footer — Static (FlyonUI Footer Component)

Single-row static footer with copyright and navigation links. Use at the bottom of dashboard pages.

```html
<footer class="footer bg-base-200/60 items-center rounded-t-box px-6 py-4 shadow-base-300/20 shadow-sm">
  <aside class="grid-flow-col items-center">
    <p>&copy;2026 <a class="link link-hover font-medium" href="#">FitCRM</a></p>
  </aside>
  <nav class="text-base-content grid-flow-col gap-4 md:place-self-center md:justify-self-end">
    <a class="link link-hover" href="#">License</a>
    <a class="link link-hover" href="#">Help</a>
    <a class="link link-hover" href="#">Contact</a>
    <a class="link link-hover" href="#">Policy</a>
  </nav>
</footer>
```

**Key classes:**

| Class | Purpose |
|---|---|
| `footer` | Footer layout (auto grid) |
| `bg-base-200/60` | Semi-transparent background |
| `items-center` | Vertically center both columns |
| `rounded-t-box` | Rounded top corners matching theme border radius |
| `px-6 py-4` | Compact horizontal/vertical padding |
| `shadow-base-300/20 shadow-sm` | Subtle top shadow |
| `grid-flow-col` | Inline flow for both aside and nav |
| `link link-hover` | Link that shows underline on hover |
| `md:place-self-center md:justify-self-end` | Right-aligns nav links on medium+ screens |

**Structure:**
- `footer` — single-row layout with two columns
- `<aside>` — left side: copyright text with brand link
- `<nav>` — right side: horizontal navigation links
- On mobile the columns stack; on `md+` they sit side-by-side

---

## 18. Toast Notifications (Notyf + FlyonUI Vendor CSS)

Lightweight toast notifications using [Notyf](https://github.com/caroso1222/notyf) with FlyonUI's vendor CSS for consistent theming. Toasts appear as brief, non-blocking messages for success, error, and custom notification types.

### Required Dependencies

```bash
npm install notyf
```

### CSS Setup (Tailwind v4 / Vite)

Add to `resources/css/app.css`:

```css
@import "flyonui/src/vendor/notyf.css";
```

### A. Default Toast (Success)

Built-in success notification with no custom config needed.

```html
<button class="btn btn-success" id="notyf-default-example">Show Default Toast</button>
```

```js
const notyfDefault = new Notyf()
document.getElementById('notyf-default-example').addEventListener('click', function () {
  notyfDefault.success('This is a default success notification!')
})
```

### B. Custom Toast (Primary)

Custom notification type using FlyonUI color variables and Tabler icons.

```html
<button class="btn btn-primary" id="notyf-custom-example">Show Primary Toast</button>
```

```js
const notyfCustom = new Notyf({
  duration: 3000,
  position: { x: 'right', y: 'top' },
  types: [
    {
      type: 'primary',
      background: 'var(--color-primary)',
      icon: { className: 'icon-[tabler--circle-check] !text-primary', tagName: 'i' },
      color: 'white'
    }
  ]
})

document.getElementById('notyf-custom-example').addEventListener('click', function () {
  notyfCustom.open({
    type: 'primary',
    message: 'This is a primary notification!',
    duration: 3000,
    ripple: true,
    dismissible: true
  })
})
```

### C. All Semantic Variants

Define all color variants using FlyonUI CSS variables.

```js
const notyf = new Notyf({
  duration: 3000,
  position: { x: 'right', y: 'top' },
  types: [
    {
      type: 'success',
      background: 'var(--color-success)',
      icon: { className: 'icon-[tabler--circle-check] !text-success', tagName: 'i' },
      color: 'white'
    },
    {
      type: 'error',
      background: 'var(--color-error)',
      icon: { className: 'icon-[tabler--circle-x] !text-error', tagName: 'i' },
      color: 'white'
    },
    {
      type: 'warning',
      background: 'var(--color-warning)',
      icon: { className: 'icon-[tabler--alert-triangle] !text-warning', tagName: 'i' },
      color: 'white'
    },
    {
      type: 'info',
      background: 'var(--color-info)',
      icon: { className: 'icon-[tabler--info-circle] !text-info', tagName: 'i' },
      color: 'white'
    },
    {
      type: 'secondary',
      background: 'var(--color-secondary)',
      icon: { className: 'icon-[tabler--bell] !text-secondary', tagName: 'i' },
      color: 'white'
    }
  ]
})

// Usage
notyf.open({ type: 'success', message: 'Success! Operation completed.', ripple: true, dismissible: true })
notyf.open({ type: 'error', message: 'Error! Something went wrong.', ripple: true, dismissible: true })
notyf.open({ type: 'warning', message: 'Warning! Please check your input.', ripple: true, dismissible: true })
notyf.open({ type: 'info', message: 'Info: New updates available.', ripple: true, dismissible: true })
notyf.open({ type: 'secondary', message: 'You have a new notification.', ripple: true, dismissible: true })
```

**Notyf config reference:**

| Option | Value | Purpose |
|---|---|---|
| `duration` | `3000` | Auto-dismiss after 3 seconds |
| `position.x` | `'right'` | Horizontal position (`left`, `center`, `right`) |
| `position.y` | `'top'` | Vertical position (`top`, `center`, `bottom`) |
| `ripple` | `true` | Ripple animation on appear |
| `dismissible` | `true` | User can click to dismiss |

**Type definition properties:**

| Property | Purpose |
|---|---|
| `type` | Identifier string (matches `notyf.open({ type })`) |
| `background` | Background color — use `var(--color-*)` for FlyonUI theme colors |
| `icon.className` | Icon class — use `icon-[tabler--*]` + `!text-{color}` for themed icon |
| `icon.tagName` | HTML tag for icon element (use `'i'`) |
| `color` | Text color inside the toast |

**Color variables for FlyonUI themes:**
- `var(--color-primary)` — primary brand color
- `var(--color-secondary)` — secondary color
- `var(--color-success)` — green / success
- `var(--color-error)` — red / error
- `var(--color-warning)` — amber / warning
- `var(--color-info)` — blue / informational

**Icons used:**
- `icon-[tabler--circle-check]` — success
- `icon-[tabler--circle-x]` — error
- `icon-[tabler--alert-triangle]` — warning
- `icon-[tabler--info-circle]` — info
- `icon-[tabler--bell]` — notifications/secondary

---

## 19. Radio — Custom Option Cards (FlyonUI Radio Component)

Radio inputs styled as selectable option cards with an icon, title, and description. Uses the same `custom-option` wrapper as checkboxes (section 11) but with `radio` inputs for single-select behavior. Useful for plan selection, tier selection, and mutually exclusive choices.

```html
<div class="flex w-full items-start gap-3 flex-wrap sm:flex-nowrap">
  <label class="custom-option text-center flex sm:w-1/2 flex-col items-center gap-3">
    <span class="icon-[tabler--rocket] size-10"></span>
    <span class="flex flex-col label-text">
      <span class="text-base font-medium mb-1">Starter</span>
      <span class="text-base-content/80">Perfect for solo instructors just getting started.</span>
    </span>
    <input type="radio" name="radio-plan" class="radio radio-primary" />
  </label>
  <label class="custom-option text-center flex sm:w-1/2 flex-col items-center gap-3">
    <span class="icon-[tabler--user] size-10"></span>
    <span class="flex flex-col label-text">
      <span class="text-base font-medium mb-1">Personal</span>
      <span class="text-base-content/80">Great for small studios with a few instructors.</span>
    </span>
    <input type="radio" name="radio-plan" class="radio radio-primary" checked />
  </label>
  <label class="custom-option text-center flex sm:w-1/2 flex-col items-center gap-3">
    <span class="icon-[tabler--crown] size-10"></span>
    <span class="flex flex-col label-text">
      <span class="text-base font-medium mb-1">Enterprise</span>
      <span class="text-base-content/80">For multi-location studios with full teams.</span>
    </span>
    <input type="radio" name="radio-plan" class="radio radio-primary" />
  </label>
</div>
```

**Key classes:**

| Class | Purpose |
|---|---|
| `custom-option` | Card-style wrapper with border highlight on selection |
| `radio` | Base radio input |
| `radio-primary` | Primary color when selected |
| `label-text` | Text content area |
| `text-base-content/80` | Slightly muted description text |
| `sm:w-1/2` | Equal-width columns on small+ screens |
| `sm:flex-nowrap` | Side-by-side layout on small+ screens |
| `text-center` | Centers icon, text, and radio vertically |
| `flex-col` | Vertical card layout (icon → text → radio) |

**Structure:**
- Outer `div` with `flex flex-wrap` / `sm:flex-nowrap` — responsive row layout
- `<label class="custom-option">` — clickable card that wraps icon + text + radio
- `icon-[tabler--*] size-10` — large icon at the top of the card
- `label-text` > title (`text-base font-medium`) + description (`text-base-content/80`)
- `radio radio-primary` — the actual radio input at the bottom
- All `<input type="radio">` must share the same `name` attribute for single-select behavior

**Key differences from Checkboxes (section 11):**
- **Checkboxes:** `checkbox checkbox-primary`, allows multiple selections, horizontal layout with checkbox on the left
- **Radio cards:** `radio radio-primary`, single selection only (same `name` group), vertical card layout with icon on top

**Icons used:**
- `icon-[tabler--rocket]` — starter/launch
- `icon-[tabler--user]` — personal/individual
- `icon-[tabler--crown]` — enterprise/premium

---

## 20. Action Dropdown — Hover (FlyonUI Dropdown Component)

Hover-triggered dropdown for row-level actions in datatables, cards, and listings. Opens on hover without requiring a click. Use this as the standard action button for all table rows and list items.

### A. Dots Trigger (Table Row Actions)

Compact icon-only trigger for use in datatable rows and card action slots.

```html
<div class="dropdown relative inline-flex [--trigger:hover] [--placement:bottom-end]">
  <button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Actions">
    <span class="icon-[tabler--dots] size-4"></span>
  </button>
  <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu" aria-orientation="vertical">
    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--eye] size-4 me-2"></span>View</a></li>
    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--edit] size-4 me-2"></span>Edit</a></li>
    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--trash] size-4 me-2 text-error"></span><span class="text-error">Delete</span></a></li>
  </ul>
</div>
```

### B. Button Trigger (Standalone)

Standard button trigger with label and chevron for use outside tables.

```html
<div class="dropdown relative inline-flex [--trigger:hover]">
  <button type="button" class="dropdown-toggle btn btn-primary" aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
    Actions
    <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
  </button>
  <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-orientation="vertical">
    <li><a class="dropdown-item" href="#">My Profile</a></li>
    <li><a class="dropdown-item" href="#">Settings</a></li>
    <li><a class="dropdown-item" href="#">Billing</a></li>
    <li><a class="dropdown-item" href="#">FAQs</a></li>
  </ul>
</div>
```

**Key classes:**

| Class | Purpose |
|---|---|
| `dropdown` | Base dropdown wrapper |
| `[--trigger:hover]` | Opens on hover (no click needed) |
| `[--placement:bottom-end]` | Aligns dropdown to the right edge of the trigger |
| `dropdown-toggle` | Button that opens/closes the menu |
| `dropdown-open:rotate-180` | Rotates chevron icon when open |
| `dropdown-menu` | The menu panel |
| `dropdown-open:opacity-100` | Fade-in on open |
| `dropdown-item` | Clickable menu item |
| `btn-ghost btn-xs btn-square` | Compact icon-only trigger for table rows |
| `min-w-40` | Minimum width for compact menus |
| `min-w-60` | Minimum width for full menus |

**Structure:**
- `dropdown [--trigger:hover]` > `dropdown-toggle` (button) + `dropdown-menu` (`<ul>`)
- Each menu item: `<li>` > `<a class="dropdown-item">` with optional icon + label
- Destructive actions use `text-error` on both icon and label
- For table rows: use `btn-ghost btn-xs btn-square` trigger with `icon-[tabler--dots]`
- For standalone: use `btn btn-primary` trigger with label + `icon-[tabler--chevron-down]`
- Add `[--placement:bottom-end]` when the dropdown is in the last column of a table to prevent overflow

**Icons used:**
- `icon-[tabler--dots]` — action trigger (three dots)
- `icon-[tabler--eye]` — view details
- `icon-[tabler--edit]` — edit item
- `icon-[tabler--trash]` — delete/remove (use with `text-error`)

---

## 21. Combo-box Search (FlyonUI Combo-box Component)

Inline search input with grouped, filterable dropdown results. Used in the toolbar for global search. FlyonUI's JS handles filtering, keyboard navigation, and open/close behavior automatically via `data-combo-box` attributes.

```html
<div class="w-72">
  <div class="relative"
    data-combo-box='{
      "groupingType": "default",
      "preventSelection": true,
      "isOpenOnFocus": true,
      "groupingTitleTemplate": "<div class=\"block text-xs text-base-content/50 m-3 mb-1\"></div>"
    }'>
    <div class="relative">
      <input class="input input-sm ps-8" type="text" placeholder="Search students, classes, bookings..."
        role="combobox" aria-expanded="false" value="" data-combo-box-input="" />
      <span class="icon-[tabler--search] text-base-content/50 absolute start-3 top-1/2 size-4 shrink-0 -translate-y-1/2"></span>
    </div>
    <div class="bg-base-100 rounded-box p-2 shadow-base-300/20 shadow-lg" style="display: none" data-combo-box-output="">
      <div data-combo-box-output-items-wrapper="" class="space-y-0.5">
        <!-- Group: Recent Pages -->
        <div data-combo-box-output-item='{"group": {"name": "recent", "title": "Recent Pages"}}' tabindex="0">
          <a class="dropdown-item combo-box-selected:dropdown-active" href="#">
            <span class="icon-[tabler--layout-dashboard] text-base-content/80 size-5 shrink-0"></span>
            <span class="text-base-content" data-combo-box-search-text="Dashboard Overview" data-combo-box-value="">Dashboard Overview</span>
            <span class="text-base-content/50 ms-auto hidden text-xs sm:inline" data-combo-box-search-text="Dashboard" data-combo-box-value="">Dashboard</span>
          </a>
        </div>
        <!-- Group: Students -->
        <div data-combo-box-output-item='{"group": {"name": "students", "title": "Students"}}' tabindex="1">
          <a class="dropdown-item combo-box-selected:dropdown-active" href="#">
            <div class="avatar avatar-placeholder"><div class="bg-primary text-primary-content size-6 rounded-full text-xs font-bold">AL</div></div>
            <span class="text-base-content" data-combo-box-search-text="Amy Lopez" data-combo-box-value="">Amy Lopez</span>
            <span class="ms-auto text-xs text-success" data-combo-box-search-text="Active" data-combo-box-value="">Active</span>
          </a>
        </div>
        <!-- Group: Classes -->
        <div data-combo-box-output-item='{"group": {"name": "classes", "title": "Classes"}}' tabindex="2">
          <a class="dropdown-item combo-box-selected:dropdown-active" href="#">
            <span class="icon-[tabler--calendar-event] text-base-content/80 size-5 shrink-0"></span>
            <span class="text-base-content" data-combo-box-search-text="Morning Vinyasa Yoga" data-combo-box-value="">Morning Vinyasa</span>
            <span class="text-base-content/50 ms-auto hidden text-xs sm:inline" data-combo-box-search-text="6:00 AM" data-combo-box-value="">6:00 AM</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
```

**Key attributes:**

| Attribute | Purpose |
|---|---|
| `data-combo-box` | Initializes the combo-box with config (JSON) |
| `"groupingType": "default"` | Groups items under title headers |
| `"preventSelection": true` | Keeps input text as-is (doesn't replace with selection) |
| `"isOpenOnFocus": true` | Shows dropdown when input is focused |
| `"groupingTitleTemplate"` | HTML template for group headers |
| `data-combo-box-input` | Marks the `<input>` as the search field |
| `data-combo-box-output` | Marks the dropdown container |
| `data-combo-box-output-items-wrapper` | Wraps all result items |
| `data-combo-box-output-item` | Individual result item with group assignment |
| `data-combo-box-search-text` | Text that is searched/filtered against |
| `data-combo-box-value` | Value returned on selection |
| `combo-box-selected:dropdown-active` | Highlights the currently focused item |

**Key classes:**

| Class | Purpose |
|---|---|
| `input input-sm ps-8` | Small input with left padding for search icon |
| `rounded-box` | Theme-aware border radius on dropdown panel |
| `shadow-base-300/20 shadow-lg` | Elevated dropdown shadow |
| `dropdown-item` | Standard dropdown item styling |
| `combo-box-selected:dropdown-active` | Active/focused state via FlyonUI variant |
| `avatar avatar-placeholder` | Avatar for people results |

**Structure:**
- Outer `div` with `data-combo-box` config — FlyonUI auto-initializes
- `<input>` with `data-combo-box-input` + search icon absolutely positioned
- Output panel: `data-combo-box-output` with `style="display: none"` (FlyonUI toggles visibility)
- Items grouped by `{"group": {"name": "...", "title": "..."}}` — FlyonUI renders group headers
- Each item: `<a class="dropdown-item">` with icon/avatar + label + right-aligned meta text
- Requires FlyonUI JS (`flyonui.js`) to be loaded for filtering and keyboard navigation
