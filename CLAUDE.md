# Claude Code Instructions for FitCRM

## Required Reading - Do This First

Before writing ANY code, you MUST read these files in order:

1. **`.ai/INSTRUCTIONS.md`** — Full project instructions (product overview, tech stack, CSS components). This is the combined reference for everything.
2. **`.ai/PLANNING.md`** — Architecture decisions, feature plans, and decision log. Check for prior decisions before implementing anything.

## Quick Rules

- **No CDN links** — all libraries must be installed locally via npm
- **Every `<label>` must have a `for` attribute** — matching the input's `id`
- **Skeleton loading** — every async content area must show a skeleton placeholder
- **Vue is hybrid only** — mount into Blade `<div id="app">`, NOT a SPA
- **One Vue entrypoint per page** — `resources/js/apps/{name}.js`
- **FlyonUI components only** — check `.ai/INSTRUCTIONS.md` Section 3 for approved patterns
- **Tenant scoping** — every host database query must be scoped to `host_id`
- **Event-based architecture** — dispatch domain events for key business actions

## Tech Stack

- Laravel 12, MySQL/MariaDB, Laravel Octane
- Vue.js (hybrid, inside Blade pages)
- Tailwind CSS + FlyonUI
- Tabler Icons (local webfont)
- FullCalendar, Flatpickr (local installs)

## After Making Decisions

Log all architecture decisions, approach changes, and feature plans in `.ai/PLANNING.md`.
