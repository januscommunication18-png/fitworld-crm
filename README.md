# FitNearYou.com & FitCRM.net

A marketplace platform connecting fitness customers with studios, paired with a simple studio management CRM built for small Yoga & Pilates businesses.

---

## Overview

**FitNearYou** is the demand engine — a marketplace where customers discover and book classes at nearby studios.

**FitCRM** is the studio engine — a lightweight SaaS for studio owners to manage scheduling, payments, students, and more.

Together they form a closed loop: discovery to booking to long-term retention.

---

## How They Work Together

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

---

## Platform Roles

### Customer — "I'm looking to book classes and/or appointments"

- Discover nearby Yoga & Pilates studios
- Compare classes, instructors, pricing, and reviews
- Book drop-ins, trials, class packs, or memberships
- Find beginner-friendly and specialty classes easily

### Host Business — "I'm looking to run my business"

Supported studio types:
- Yoga Studio
- Pilates Studio
- Fitness Studio
- CrossFit Studio

Each host business gets their own subdomain: `businessname.fitcrm.app`

---

## Key Features

### For Customers (fitnearyou.com)

- Discover nearby Yoga & Pilates studios
- Compare classes, instructors, pricing, and reviews
- Book drop-ins, trials, class packs, or memberships
- Find beginner-friendly and specialty classes

### For Studios (FitCRM — separate login)

- Get discovered by high-intent local users
- Fill empty class slots
- Promote trials, intro offers, and new instructors
- No marketing expertise required

### FitCRM Core Modules

| Module | Description |
|---|---|
| Class Scheduling & Capacity | Manage class timetables and spot limits |
| Payments, Packs & Memberships | Accept payments, sell class packs and recurring memberships |
| Student & Lead Management | Track students, prospects, and communication |
| Automated Reminders & Follow-ups | Email/SMS reminders for bookings and retention |
| Attendance & Revenue Insights | Dashboards for attendance trends and revenue |
| Intro Offers & Retention Tools | Trial offers, win-back campaigns, and loyalty features |

No bloated gym features. No enterprise complexity. Just what a small Yoga or Pilates studio actually needs.

---

## Target Customer

- Independent Yoga studios
- Pilates (Mat + Reformer) studios
- 1-3 locations
- 1-15 instructors
- Owner-operators who teach classes themselves

---

## Revenue Model

### FitNearYou
- Commission per booking
- Featured listings
- City-based promotions

### FitCRM
- Monthly SaaS pricing (Simple / Growth / Studio+)
- Discounted or free tier for FitNearYou partners

---

## Tech Stack

- **UI Framework:** [FlyonUI](https://flyonui.com/) (CSS component library)
- **CSS:** [Tailwind CSS](https://tailwindcss.com/) (via CDN browser build)
- **Package Manager:** npm

---

## Getting Started

### Prerequisites

- Node.js and npm installed
- A local web server (MAMP, or Python `http.server`, etc.)

### Installation

```bash
cd fitcrm
npm install
```

### Running Locally

Start a local server from the project directory:

```bash
# Using Python
python3 -m http.server 9000

# Then open in browser
open http://localhost:9000/test-components.html
```

---

## Project Structure

```
fitcrm/
  test-components.html   # UI component test page
  package.json           # npm dependencies (flyonui)
  node_modules/          # installed packages
  README.md              # this file
```

---

## Architecture (Planned)

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
