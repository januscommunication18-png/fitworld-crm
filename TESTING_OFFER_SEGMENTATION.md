# Offer & Segmentation Module - Testing Guide

## Prerequisites

1. **Run migrations** (if not already done):
   ```bash
   php artisan migrate
   ```

2. **Seed test data** (optional but recommended):
   ```bash
   php artisan db:seed --class=OfferSegmentTestSeeder
   ```

3. **Login** to the dashboard as a host owner

---

## Part 1: Segments Module Testing

### 1.1 View Segments List
**URL:** `/segments`

**Test Steps:**
- [ ] Navigate to Marketing > Segments in sidebar
- [ ] Verify the page loads without errors
- [ ] Check that segment cards display correctly (if test data was seeded)
- [ ] Verify stats show: Total Segments, Active, Total Members, Avg. Revenue

**Expected Results:**
- Page displays grid of segment cards
- Each card shows: name, type badge, member count, description
- Filter tabs work (All, Static, Dynamic, Smart)

---

### 1.2 Create Static Segment
**URL:** `/segments/create`

**Test Steps:**
- [ ] Click "Create Segment" button
- [ ] Fill in:
  - Name: "Test VIP Clients"
  - Description: "Manually selected VIP clients"
  - Color: Pick any color
  - Type: Static
- [ ] Click "Create Segment"
- [ ] Verify redirect to segment show page

**Expected Results:**
- Segment created successfully
- Flash message appears
- Redirected to `/segments/{id}`

---

### 1.3 Create Dynamic Segment with Rules
**URL:** `/segments/create`

**Test Steps:**
- [ ] Click "Create Segment" button
- [ ] Fill in:
  - Name: "Active Members"
  - Description: "Members who visited recently"
  - Type: Dynamic
- [ ] Add Rule Group 1:
  - Field: "Status"
  - Operator: "Equals"
  - Value: "member"
- [ ] Click "Add Rule" (same group - AND logic)
  - Field: "Created At"
  - Operator: "Is within last"
  - Value: "30" days
- [ ] Click "Preview Matching Clients" button
- [ ] Verify preview shows matching clients count
- [ ] Click "Create Segment"

**Expected Results:**
- Rules builder UI works correctly
- Preview shows count of matching clients
- Segment saves with rules

---

### 1.4 Create Smart Segment (Score-based)
**URL:** `/segments/create`

**Test Steps:**
- [ ] Click "Create Segment" button
- [ ] Fill in:
  - Name: "Gold Members"
  - Type: Smart
- [ ] Set score range:
  - Tier: Gold
  - Min Score: 500
  - Max Score: 749
- [ ] Click "Create Segment"

**Expected Results:**
- Smart segment created
- Shows tier badge on card

---

### 1.5 View Segment Details
**URL:** `/segments/{id}`

**Test Steps:**
- [ ] Click on any segment card to view details
- [ ] Verify segment info displays:
  - Name, description, type
  - Member count
  - Rules (for dynamic segments)
  - Analytics cards
- [ ] Check members table shows clients in segment
- [ ] For dynamic segments, click "Refresh" button

**Expected Results:**
- All segment details display correctly
- Members list paginated if many clients
- Refresh updates member count

---

### 1.6 Add/Remove Clients from Static Segment
**URL:** `/segments/{id}` (static segment)

**Test Steps:**
- [ ] Go to a static segment's show page
- [ ] Click "Add Client" button
- [ ] Search for a client by name
- [ ] Select and add the client
- [ ] Verify client appears in members list
- [ ] Click remove (X) button next to a client
- [ ] Confirm removal
- [ ] Verify client removed from list

**Expected Results:**
- Clients can be manually added to static segments
- Clients can be removed
- Member count updates

---

### 1.7 Edit Segment
**URL:** `/segments/{id}/edit`

**Test Steps:**
- [ ] Click "Edit" button on segment show page
- [ ] Change the segment name
- [ ] Change the color
- [ ] For dynamic segment: modify a rule
- [ ] Save changes

**Expected Results:**
- Edit form pre-filled with current values
- Changes saved successfully
- Redirected back to show page

---

### 1.8 Delete Segment
**URL:** `/segments/{id}`

**Test Steps:**
- [ ] Go to segment show page
- [ ] Click "Delete" button (in dropdown menu)
- [ ] Confirm deletion in modal
- [ ] Verify redirect to segments index

**Expected Results:**
- Confirmation modal appears
- Segment deleted
- Redirected to `/segments`
- Flash message confirms deletion

---

## Part 2: Offers Module Testing

### 2.1 View Offers List
**URL:** `/offers`

**Test Steps:**
- [ ] Navigate to Marketing > Offers in sidebar
- [ ] Verify the page loads without errors
- [ ] Check stats cards: Total Offers, Active, Redemptions, Discounts Given, Revenue
- [ ] Verify offers table displays correctly
- [ ] Test status filter tabs (All, Active, Draft, Paused, Expired)

**Expected Results:**
- Page displays offers table
- Each row shows: name, code, discount, target, duration, redemptions, status
- Dropdown actions menu works

---

### 2.2 Create Percentage Discount Offer
**URL:** `/offers/create`

**Test Steps:**
- [ ] Click "Create Offer" button
- [ ] Fill in Basic Info:
  - Name: "Summer Special 15% Off"
  - Promo Code: "SUMMER15"
  - Description: "15% off all classes"
- [ ] Set Duration:
  - Start Date: Today
  - End Date: 30 days from now
- [ ] Set Discount:
  - Applies To: Classes
  - Discount Type: Percentage
  - Discount Value: 15
- [ ] Set Target:
  - Target Audience: All Members
- [ ] Set Usage Limits:
  - Total Usage Limit: 100
  - Per Member Limit: 2
- [ ] Set Options:
  - Requires Code: Yes
  - Show on Invoice: Yes
- [ ] Click "Create Offer"

**Expected Results:**
- Offer created successfully
- Redirected to offer show page
- All details saved correctly

---

### 2.3 Create Fixed Amount Discount
**URL:** `/offers/create`

**Test Steps:**
- [ ] Click "Create Offer" button
- [ ] Fill in:
  - Name: "$10 Off First Service"
  - Code: "SAVE10"
  - Discount Type: Fixed Amount
  - Discount Value: 10
  - Applies To: Services
  - Target: New Members
- [ ] Click "Create Offer"

**Expected Results:**
- Offer created with fixed amount discount
- Shows "$10 off" in discount column

---

### 2.4 Create Segment-Targeted Offer
**URL:** `/offers/create`

**Test Steps:**
- [ ] Click "Create Offer" button
- [ ] Fill in basic details
- [ ] Set Target Audience: "Specific Segment"
- [ ] Select segment from dropdown (e.g., "VIP Members")
- [ ] Complete other fields
- [ ] Click "Create Offer"

**Expected Results:**
- Offer linked to segment
- Shows segment name in Target column on index
- Only segment members eligible

---

### 2.5 Create Auto-Apply Offer
**URL:** `/offers/create`

**Test Steps:**
- [ ] Click "Create Offer" button
- [ ] Fill in:
  - Name: "Welcome Discount"
  - Auto Apply: Yes (toggle on)
  - Requires Code: No
  - Target: New Members
- [ ] Click "Create Offer"

**Expected Results:**
- Offer set to auto-apply
- No promo code required
- Will automatically apply at checkout

---

### 2.6 View Offer Details
**URL:** `/offers/{id}`

**Test Steps:**
- [ ] Click on an offer name to view details
- [ ] Verify all info displays:
  - Name, status badge, code
  - Analytics cards (Redemptions, Total Discounts, Revenue, New Members, Avg Discount)
  - Recent redemptions table
  - Offer details sidebar
  - Channel breakdown
  - Options (auto-apply, requires code, etc.)
- [ ] Check metadata (created date, created by)

**Expected Results:**
- All offer details display correctly
- Analytics show accurate data
- Redemptions table populated (if any)

---

### 2.7 Toggle Offer Status
**URL:** `/offers/{id}` or `/offers`

**Test Steps:**
- [ ] Find an active offer
- [ ] Click "Pause" button
- [ ] Verify status changes to "Paused"
- [ ] Click "Activate" button
- [ ] Verify status changes back to "Active"

**Expected Results:**
- Status toggles correctly
- Flash message confirms change
- Badge updates on page

---

### 2.8 Duplicate Offer
**URL:** `/offers`

**Test Steps:**
- [ ] Find an offer in the list
- [ ] Click dropdown menu > "Duplicate"
- [ ] Verify redirected to new offer
- [ ] Check that name has "(Copy)" suffix
- [ ] Verify all settings copied

**Expected Results:**
- New offer created as copy
- Name modified to indicate copy
- Status set to "draft"
- All other settings preserved

---

### 2.9 Edit Offer
**URL:** `/offers/{id}/edit`

**Test Steps:**
- [ ] Click "Edit" button on offer show page
- [ ] Modify offer name
- [ ] Change discount value
- [ ] Update usage limits
- [ ] Save changes

**Expected Results:**
- Edit form pre-filled with current values
- Changes saved successfully
- Redirected back to show page

---

### 2.10 Delete Offer
**URL:** `/offers/{id}`

**Test Steps:**
- [ ] Go to offer show page
- [ ] Click "Delete" option (dropdown or button)
- [ ] Confirm deletion
- [ ] Verify redirect to offers index

**Expected Results:**
- Confirmation required
- Offer soft-deleted
- Redirected to `/offers`
- Flash message confirms deletion

---

## Part 3: Integration Tests

### 3.1 Segment → Offer Integration
**Test Steps:**
- [ ] Create a new segment "Test Segment"
- [ ] Add at least one client to it
- [ ] Create an offer targeting this segment
- [ ] Verify offer shows segment name in target column
- [ ] View offer details and confirm segment link works

**Expected Results:**
- Offer correctly linked to segment
- Only segment members eligible for offer

---

### 3.2 Validate Promo Code
**Test Steps:**
- [ ] Create an offer with code "TESTCODE"
- [ ] Try creating another offer with same code
- [ ] Verify validation error appears

**Expected Results:**
- Duplicate codes rejected
- Error message displayed

---

### 3.3 Usage Limits
**Test Steps:**
- [ ] Create offer with total_usage_limit = 5
- [ ] View offer details
- [ ] Verify usage progress bar shows 0/5

**Expected Results:**
- Usage limits display correctly
- Progress indicator shows usage vs limit

---

## Part 4: Navigation & UI Tests

### 4.1 Sidebar Navigation
**Test Steps:**
- [ ] Verify "Marketing" section in sidebar
- [ ] Click to expand Marketing submenu
- [ ] Verify "Segments" link works
- [ ] Verify "Offers" link works
- [ ] Check "Campaigns" and "Referrals" show "Soon" badge

**Expected Results:**
- Marketing section visible in sidebar
- Submenu expands/collapses
- Links navigate correctly

---

### 4.2 Breadcrumbs
**Test Steps:**
- [ ] Navigate to /segments
- [ ] Verify breadcrumb: Dashboard > Marketing > Segments
- [ ] Click on a segment
- [ ] Verify breadcrumb updates
- [ ] Repeat for offers

**Expected Results:**
- Breadcrumbs display correctly
- Clicking breadcrumb links navigates properly

---

### 4.3 Empty States
**Test Steps:**
- [ ] Delete all segments (or test with fresh DB)
- [ ] Go to /segments
- [ ] Verify empty state displays
- [ ] Repeat for /offers

**Expected Results:**
- Friendly empty state message
- "Create First" button visible
- Proper icons displayed

---

### 4.4 Responsive Design
**Test Steps:**
- [ ] Open /segments on mobile viewport
- [ ] Verify cards stack properly
- [ ] Test /offers table on mobile
- [ ] Verify horizontal scroll or card layout

**Expected Results:**
- Pages are mobile-friendly
- Tables scroll horizontally or transform to cards
- Buttons remain accessible

---

## Part 5: Error Handling

### 5.1 Validation Errors
**Test Steps:**
- [ ] Try creating segment without name
- [ ] Try creating offer without required fields
- [ ] Verify error messages display

**Expected Results:**
- Form validation prevents submission
- Clear error messages shown
- Fields highlighted in red

---

### 5.2 404 Handling
**Test Steps:**
- [ ] Go to /segments/99999 (non-existent)
- [ ] Go to /offers/99999 (non-existent)

**Expected Results:**
- 404 page displayed
- No server error

---

### 5.3 Authorization
**Test Steps:**
- [ ] Try accessing another host's segment URL
- [ ] Try accessing another host's offer URL

**Expected Results:**
- 403 or 404 returned
- Cannot access other hosts' data

---

## Quick Test Checklist

### Segments (/segments)
- [ ] List view loads
- [ ] Create static segment
- [ ] Create dynamic segment with rules
- [ ] Create smart segment
- [ ] View segment details
- [ ] Add client to static segment
- [ ] Remove client from segment
- [ ] Refresh dynamic segment
- [ ] Edit segment
- [ ] Delete segment

### Offers (/offers)
- [ ] List view loads
- [ ] Create percentage discount
- [ ] Create fixed amount discount
- [ ] Create segment-targeted offer
- [ ] Create auto-apply offer
- [ ] View offer details
- [ ] Pause/Activate offer
- [ ] Duplicate offer
- [ ] Edit offer
- [ ] Delete offer

### Integration
- [ ] Segment linked to offer
- [ ] Sidebar navigation works
- [ ] Breadcrumbs correct
- [ ] Empty states display
- [ ] Validation errors show
- [ ] Authorization enforced

---

## Test Data Summary (from Seeder)

If you ran the seeder, you have:

**Segments:**
| Name | Type | Description |
|------|------|-------------|
| VIP Members | Static | Hand-picked VIPs |
| High Spenders | Dynamic | $500+ total spend |
| Inactive Members | Dynamic | No visit in 30+ days |
| New Clients | Dynamic | Joined last 14 days |
| Gold Tier Members | Smart | Score 500-749 |
| Frequent Visitors | Dynamic | 5+ visits/30 days |

**Offers:**
| Code | Name | Status | Type |
|------|------|--------|------|
| SUMMER20 | Summer Sale 20% Off | Active | 20% off classes |
| VIP25 | VIP $25 Credit | Active | $25 off (VIP only) |
| COMEBACK30 | We Miss You | Active | 30% off (inactive) |
| WELCOME | First Class Free | Active | 100% off |
| ANNUAL15 | Annual Membership 15% Off | Active | 15% off memberships |
| BLACKFRI50 | Black Friday Special | Draft | 50% off |
| SPRING10 | Spring Promo | Paused | 10% off |
| NEWYEAR25 | New Year Sale | Expired | 25% off |

---

## Troubleshooting

### "Table not found" error
```bash
php artisan migrate
```

### "Class not found" error
```bash
composer dump-autoload
```

### Routes not working
```bash
php artisan route:clear
php artisan route:list --name=segments
php artisan route:list --name=offers
```

### Views not updating
```bash
php artisan view:clear
```
