# MASTER ANTIGRAVITY PROMPT — CampusVoice

## ROLE

You are a senior software architect, senior full-stack engineer, database architect, UI/UX designer, and PHP application developer working as one unified build team.

Your task is to build a complete, production-ready web application called **CampusVoice** — a Campus Improvement & Student Voice Platform. This document is the single source of truth. It contains every page, workflow, database table, business rule, and design requirement needed to build the application in full, in one pass.

**Rules of engagement:**
- Do not simplify any feature.
- Do not skip any page, table, or workflow listed below.
- Do not substitute functionality with something "close enough."
- Do not invent features that conflict with what's described here.
- If something is marked optional, you may build it, but everything not marked optional is mandatory.
- Before you finish, self-check your output against the "Final Build Checklist" at the end of this document.

---

## 1. PROJECT OVERVIEW

**Name:** CampusVoice
**Tagline:** "Every Student Voice Shapes a Better Campus"

CampusVoice is a web-based survey management system for a school. Students log in to answer campus-improvement surveys (about Wi-Fi, cafeteria, library, labs, safety, wellness, events, academics, organizations, facilities, etc.). Administrators create and schedule those surveys, manage the question bank, monitor participation, and turn raw responses into automatically computed statistics, flagged concerns, and printable/exportable reports.

There are exactly **two authenticated roles**: **Student** and **Administrator**. No other roles exist.

---

## 2. REQUIRED TECHNOLOGY STACK

Build using **only**:
- HTML5
- CSS3
- Vanilla JavaScript (no frameworks)
- PHP (procedural or lightly OOP, no framework)
- MySQL (via PDO or MySQLi with prepared statements)

**Explicitly forbidden:** Laravel, Symfony, React, Vue, Angular, Node.js, Firebase, Supabase, MongoDB, any JS build tooling (Webpack/Vite/npm packages beyond a CDN chart library if needed).

The project must run unmodified on **XAMPP or Laragon** (Apache + PHP + MySQL). Include a `database/campusvoice.sql` file that creates the full schema and seeds at least a handful of demo rows (a few students, a few admins, a couple of surveys with questions and sample responses) so the app is testable immediately after import.

---

## 3. FOLDER STRUCTURE

Generate a modular structure along these lines (adjust naming only if a cleaner convention is used consistently):

```
/campusvoice
  /config          → db.php, constants.php, session.php
  /includes         → header.php, sidebar_student.php, sidebar_admin.php, footer.php, auth_check.php
  /auth             → login.php, logout.php, change_password.php
  /student           → dashboard.php, surveys.php, survey_form.php, confirmation.php, profile.php
  /admin             → dashboard.php, surveys/, questions/, students/, results/, reports/, users/
  /components        → reusable PHP partials (buttons, cards, modals, tables, charts, forms)
  /assets
    /css             → base.css, variables.css, components.css, responsive.css, print.css
    /js              → validation.js, charts.js, ui.js (modals/toasts/etc.)
    /img
  /database          → campusvoice.sql (schema + seed data)
  /utils             → helper functions (formatting, stats calculations, report generation)
  /system-pages       → 404.php, 401.php, session-expired.php, maintenance.php, error.php
```

Keep frontend rendering (HTML/CSS/JS) cleanly separated from backend logic (DB access, validation, business rules). Use PHP includes for shared UI (header, sidebar, footer) rather than duplicating markup on every page. Centralize DB access through one `config/db.php` connection file.

---

## 4. USER ROLES & PERMISSIONS

### 4.1 Student
Can: log in, be forced to change password on first login, view active/open surveys not yet answered, answer a survey exactly once, submit responses, see a confirmation, update their own profile, change their password anytime, log out.
Cannot: access any `/admin` route, view other students' responses, edit surveys or questions.

### 4.2 Administrator
Can: log in securely, view a dashboard, create/edit/deactivate/schedule surveys, create/edit/delete/reorder questions and choices, manage student accounts (add, reset password, view response history), view respondents and participation, view analytics/charts, generate and print reports, export PDF/Excel (optional), manage other administrator accounts, log out.
Cannot: answer surveys as a student.

Every admin-only page or action must re-verify `$_SESSION['role'] === 'admin'` server-side before executing — never trust a hidden nav link as the only protection.

---

## 5. DATABASE SCHEMA (MySQL)

Build exactly these tables, normalized to 3NF, with full constraints:

**students**
- `student_id` PK
- `student_number` (unique — used as login username, formerly "Cooperative Account Number")
- `full_name`
- `email`
- `password_hash`
- `must_change_password` (bool, default true)
- `status` (active/inactive)
- `created_at`

**administrators**
- `admin_id` PK
- `username` (unique)
- `full_name`
- `email`
- `password_hash`
- `role` (e.g. super_admin/admin, for the optional admin-management screen)
- `status`
- `created_at`

**surveys**
- `survey_id` PK
- `title`
- `description`
- `category` (Wi-Fi, Cafeteria, Safety, Library, etc.)
- `open_date`
- `close_date`
- `status` (draft, active, closed, archived)
- `created_by` FK → administrators.admin_id
- `created_at`

**survey_questions**
- `question_id` PK
- `survey_id` FK → surveys.survey_id
- `question_text`
- `question_type` (multiple_choice, yes_no, rating, short_answer)
- `is_required` (bool)
- `order_index`

**survey_choices**
- `choice_id` PK
- `question_id` FK → survey_questions.question_id
- `choice_text`

**responses**
- `response_id` PK
- `student_id` FK → students.student_id
- `survey_id` FK → surveys.survey_id
- `question_id` FK → survey_questions.question_id
- `choice_id` FK → survey_choices.choice_id (nullable — used for MC/Yes-No)
- `rating_value` (nullable int — used for Rating Scale)
- `text_answer` (nullable text — used for Short Answer)
- `submitted_at`
- **UNIQUE constraint on `(student_id, question_id)`** — this is the actual mechanism that enforces "one response per student per question." Combine with a server-side pre-check inside a transaction to prevent race conditions from double-clicks or two open tabs.

**survey_results**
- `result_id` PK
- `survey_id` FK
- `question_id` FK
- `computed_metric` (e.g. "percentage", "average")
- `computed_value`
- `last_refreshed_at`

**login_history** *(optional but recommended)*
- `log_id` PK
- `user_id`
- `role` (student/admin)
- `login_time`
- `ip_address`

**Relationships:** surveys 1—∞ survey_questions; survey_questions 1—∞ survey_choices; students 1—∞ responses; surveys 1—∞ responses; survey_questions 1—∞ responses; surveys 1—∞ survey_results.

Add indexes on `survey_id`, `student_id`, and `question_id` wherever they appear as foreign keys, since Dashboard and Results queries filter on these constantly. Use `ON DELETE CASCADE` for child records that make no sense orphaned (e.g. deleting a survey cascades to its questions and choices) but **not** for `responses`, which should be preserved or explicitly archived rather than silently cascaded away.

---

## 6. AUTHENTICATION & SESSIONS

- One shared login form; role is detected from which table the identifier matches (student number vs admin username) — do not build two separate login pages.
- Passwords hashed with `password_hash()` (bcrypt) and verified with `password_verify()`. Never store or log plaintext.
- On successful login, start a PHP session storing `$_SESSION['user_id']` and `$_SESSION['role']`, and optionally log the event to `login_history`.
- Every protected page includes an `auth_check.php` guard at the top that verifies session + role before rendering anything.
- Students with `must_change_password = true` are redirected to the Change Password page immediately after login, before they can reach the dashboard.
- Idle sessions time out (e.g., 20 minutes) and route the user to a **Session Expired** page — not directly back to login — so the reason is clear.
- A Student who manually navigates to an admin URL is redirected to an **Unauthorized (401)** page, not the login page.
- Logout fully destroys the session (`session_unset()` + `session_destroy()`) and clears the session cookie.

---

## 7. SURVEY ENGINE & LIFECYCLE

```
Admin creates survey (draft)
   → Admin adds questions + choices
   → Admin sets open_date / close_date
   → Admin publishes (status → active)
   → Students see it in Available Surveys once open_date has passed
   → Student answers, submits
   → Server validates + blocks duplicates
   → Response rows inserted
   → survey_results recomputed (aggregation query: COUNT/AVG/GROUP BY)
   → Analytics + charts reflect new data
   → Reports can be generated/printed/exported
   → Survey auto-closes once close_date passes, or admin deactivates manually anytime
   → Archived surveys remain viewable in Reports but not answerable
```

**Status logic must be date-driven, not just a manual toggle:** every query that lists "available surveys" compares `open_date`/`close_date` against `NOW()` in addition to checking `status`. This single mechanism should simultaneously handle: hiding future surveys, showing currently active ones, and treating expired ones as closed — even if nobody manually changed the status field.

---

## 8. QUESTION TYPES

Support exactly four types, each with its own validation, storage column usage, rendering, and result-computation logic:

| Type | Input UI | Stored In | Result Computation |
|---|---|---|---|
| Multiple Choice | radio buttons from `survey_choices` | `choice_id` | % share per choice (pie/bar) |
| Yes / No | two radio buttons | `choice_id` | % yes vs % no |
| Rating Scale | 1–5 stars or numeric scale | `rating_value` | average + distribution histogram |
| Short Answer | text area | `text_answer` | listed verbatim in detailed reports (no auto-aggregation) |

Every question type is both client-side validated (JS, fast feedback) and server-side re-validated (PHP, authoritative) — never trust the client alone.

---

## 9. BUSINESS RULES (must all hold)

- A student can answer each survey question only once, enforced by both a UI flow and a DB unique constraint.
- Closed or inactive surveys cannot be answered, even via a direct/stale link — re-check eligibility server-side on render, not just at submit time.
- Future surveys (open_date in the future) stay hidden from students until that date arrives.
- Once a student completes a survey, it disappears from their Available Surveys list.
- Admins can deactivate any survey at any time, immediately removing it from students' available lists.
- Rating values outside the valid range are rejected by both JS and PHP.
- Every destructive or unsaved-data action (delete question, deactivate survey, navigate away mid-form) shows a confirmation modal first.

---

## 10. FULL SITEMAP

```
PUBLIC
  /                              Landing Page
  /login                         Shared login (role auto-detected)
  /maintenance                   Site-wide maintenance notice
  /404                           Not found

STUDENT (auth required, role=student)
  /student/dashboard
  /student/surveys                        Available Surveys
  /student/surveys/no-available            Empty state
  /student/surveys/:id                     Survey Form
  /student/surveys/:id/closed              Survey Closed state
  /student/surveys/:id/confirmation        Submission Confirmation
  /student/profile
  /student/change-password
  /student/logout

ADMINISTRATOR (auth required, role=admin)
  /admin/dashboard
  /admin/surveys                           Survey Management (list)
  /admin/surveys/create
  /admin/surveys/:id/edit
  /admin/surveys/:id/questions             Question Management
  /admin/students                          Student Management
  /admin/students/:id                      Student detail / response history
  /admin/results                           Results Dashboard
  /admin/results/:surveyId
  /admin/reports
  /admin/reports/:id/print
  /admin/users                             Administrator account management
  /admin/logout

SYSTEM (shared)
  /401           Unauthorized
  /session-expired
  /error         Generic error page
  /success       Generic success wrapper (used contextually by modals/toasts)
  /survey-closed Global fallback for stale links to a now-closed survey
```

---

## 11. PAGE-LEVEL SPECIFICATIONS

Build every page below with the described purpose, contents, and behavior.

### Student side
- **Landing Page** — public marketing/intro page, explains the platform, links to Login.
- **Login** — one username field (student number or admin username), password field, error banner on failure, "Login" button, optional password-visibility toggle.
- **Student Dashboard** — welcome banner, count of pending surveys, last submission date, admin announcements, quick links to Surveys/Profile/Change Password/Logout.
- **Available Surveys** — cards/list of open, not-yet-answered surveys (title, category, closing-date countdown), "Answer Survey" button per card; shows the "No Available Survey" empty state when the list is empty.
- **Survey Form** — dynamically renders every question of the selected survey by type, with a progress indicator and inline client-side validation; "Submit" and "Cancel/Back" buttons. On submit: server validates required fields, re-checks for a prior response (duplicate prevention), inserts response rows, redirects to Confirmation. If a duplicate is detected, redirect to Dashboard with a notice instead of inserting anything.
- **Submission Confirmation** — success state confirming the specific survey title was recorded, with a "Back to Dashboard" button.
- **Survey Closed** — shown if a student reaches a survey via a stale/direct link after it has closed.
- **Profile / Edit Profile** — editable contact fields, "Save Changes"/"Cancel" buttons, inline success state on save.
- **Change Password** — current password + new password + confirm fields; forced immediately after first login; re-hashes and stores on save.
- **Notifications** — simple list of announcements/system messages relevant to the student.
- **Help Center** — basic FAQ/contact info page.
- **Logout** — destroys session, redirects to Login.

### Administrator side
- **Login** — same shared form as students, routes to Admin Dashboard on success.
- **Administrator Dashboard** — stat cards (total students, active surveys, closed surveys, participation rate, response rate), recent surveys list, charts, recent activity feed, flagged concerns, auto-generated recommendations, quick-action links.
- **Survey Management** — list of all surveys with status; "Create Survey," "Edit," "Deactivate," "Manage Questions" actions. Create/Edit opens a form for title, description, category, open_date, close_date. Deactivate opens a confirmation modal, then sets status to inactive.
- **Create Survey / Edit Survey** — full form as above, saving to `surveys`.
- **Question Management** — add/edit/delete/reorder questions within a survey; question text, type selector, choice list (for MC), scale range (for Rating).
- **Student Management** — searchable/filterable student list; "Add Student," "Reset Password," "View Responses" (drills into Student Detail).
- **Student Detail** — one student's full response history across surveys.
- **Results Dashboard** — survey selector, then charts + summary tables + a highlighted-concerns panel (e.g. "82% report slow internet").
- **Reports** — select a survey or date range, generate a report, then Print / Export PDF (optional) / Export Excel (optional).
- **Administrator Management (Users)** — manage other admin accounts and roles.
- **Settings, Profile, Change Password, Notifications** — same pattern as the student equivalents, scoped to the admin account.
- **Logout** — destroys session, redirects to Login.

### System / hidden pages (shared)
- **404** — friendly "page not found," link home.
- **Unauthorized (401)** — shown when a role tries to access a route it doesn't own.
- **Session Expired** — shown after idle timeout, prompts re-login.
- **Maintenance** — whole-site notice, no data access.
- **Survey Closed / No Available Surveys / Already Answered** — the specific empty/blocked states described above.
- **Success / Error / Loading** — generic wrapper states used contextually by toasts, modals, and form submissions.
- **Empty Search / No Results** — used by any searchable admin table (Student Management, Reports).

---

## 12. SHARED PAGE SHELL

Every authenticated page shares:
- **Header** — logo/site name, role badge, notification bell, profile menu, logout.
- **Sidebar** (role-specific) — Student: Dashboard, Surveys, Profile, Change Password. Admin: Dashboard, Surveys, Questions, Students, Results, Reports, Users.
- **Breadcrumbs** — e.g. "Dashboard ▸ Survey Management ▸ Edit Survey."
- **Content area** — page-specific cards/tables/forms/charts.
- **Footer** — school name, version, support contact.

---

## 13. DASHBOARDS

**Student Dashboard shows:** pending surveys, completed surveys, announcements, quick actions, notifications.

**Administrator Dashboard shows:** total students, active surveys, closed surveys, participation rate, response rate, recent surveys, charts, recent activity, flagged concerns, auto-generated recommendations (e.g. a concern crossing a threshold produces a suggestion like "Upgrade campus network"), quick actions.

---

## 14. ANALYTICS & CHARTS

Compute and visualize: participation rate, response distribution, average ratings, category comparison, survey trends over time, most common concerns, completion rates.

| Chart | Where |
|---|---|
| Pie chart | Results Dashboard — share of each MC/Yes-No option |
| Bar chart | Results Dashboard — comparison across survey categories |
| Line chart | Reports — trend across repeated survey runs over time |
| Rating distribution histogram | Results Dashboard — 1–5 rating counts |
| Participation rate gauge/bar | Administrator Dashboard |
| Survey completion progress bar | Survey Management, per survey |

Charts can be built with lightweight vanilla-JS canvas/SVG rendering or a single CDN-loaded charting library (e.g. Chart.js) — pick one and use it consistently. Results are computed from `responses` via aggregation queries (`COUNT`, `AVG`, `GROUP BY`) and cached into `survey_results` so dashboards aren't recomputing heavy stats on every page load.

---

## 15. REPORTING

Generate: participation reports, survey reports, summary reports (headline stats per survey), detailed reports (full per-question breakdown, including verbatim Short Answer text), recommendation reports (auto-generated suggestion text tied to flagged concerns).

- **Printable reports:** dedicated `print.css` with `@media print` rules, triggered by `window.print()`.
- **PDF export (optional):** server-side PHP PDF generation from the same report data.
- **Excel export (optional):** server-side `.xlsx`/`.csv` export of the results table.

---

## 16. UI / UX DESIGN SYSTEM

Design a premium SaaS interface, inspired by Notion, Linear, Stripe Dashboard, Google Workspace, and Figma — clean, spacious, confident, not generic Bootstrap-default.

- **Typography:** one clean sans-serif family, clear size hierarchy (page titles > section headers > body).
- **Spacing:** consistent 8px-based scale across cards, forms, tables.
- **Cards:** rounded corners, soft shadows, consistent internal padding.
- **Buttons:** primary (filled brand color), secondary (outlined), destructive (red, for deactivate/delete) — each with hover and disabled states.
- **Forms:** labeled fields, inline validation, clear required-field markers.
- **Icons:** simple line icons per module (surveys, students, reports, settings).
- **Motion:** subtle 150–200ms transitions on buttons/cards only — no distracting animation.
- **Loading states:** skeleton screens for tables, spinners for form submits.
- **Empty states:** short illustration or icon + one-line explanation + a clear next action.
- **Feedback:** toast notifications for transient feedback, inline banners for form-level errors.
- **Glassmorphism:** only where subtle — never sacrifice legibility for style.
- **Responsive breakpoints:**
  - Desktop (≥1024px): full sidebar, multi-column dashboards, side-by-side charts.
  - Tablet (768–1023px): sidebar collapses to icon rail/hamburger, dashboard cards reflow to 2 columns, tables scroll horizontally if needed.
  - Mobile (<768px): sidebar becomes a slide-in drawer, forms/tables stack to one column, charts resize full-width, sticky bottom action buttons on long forms like the Survey Form.

### Branding
- **App name:** CampusVoice
- **Tagline:** Every Student Voice Shapes a Better Campus
- **Primary:** Professional Blue
- **Secondary:** Campus Green
- **Background:** White
- **Text:** Dark Gray
- **Accent:** Purple
- **Success:** Green
- **Warning:** Yellow
- **Error:** Red

Define these as CSS custom properties in one `variables.css` file and reference them everywhere — no hardcoded hex values scattered through the codebase.

---

## 17. REUSABLE COMPONENTS

Build as PHP includes/partials + shared CSS classes, not copy-pasted per page: buttons, inputs, dropdowns, cards, charts, tables, pagination, sidebar, header, breadcrumbs, search bar, filters, date picker, rating control, progress bar, toast notifications, confirmation modals, loading skeletons, empty states, statistic cards, profile cards, survey cards.

---

## 18. SECURITY REQUIREMENTS

- All SQL via **prepared statements** (PDO or MySQLi with bound parameters) — never string-concatenated queries.
- Passwords hashed with `password_hash()` / verified with `password_verify()`; plaintext never stored or logged.
- All user-supplied output escaped with `htmlspecialchars()` before being echoed into HTML (XSS prevention).
- CSRF tokens on all state-changing forms.
- Both client-side (JS) and authoritative server-side (PHP) input validation on every form.
- Duplicate prevention via unique DB constraint + server-side pre-check inside a transaction.
- Secure logout: full session destruction + cookie clearing.
- Role checks re-verified server-side on every protected page/action, never inferred from the UI alone.

---

## 19. CODE QUALITY

- Clean, readable, consistently named PHP, HTML, CSS, and JS.
- Reusable PHP includes for shared UI; no duplicated markup across pages.
- Configuration (DB credentials, constants) isolated in `/config`, never hardcoded inline in page files.
- Business logic (validation, stats computation, report generation) separated from presentation markup.
- Comments where logic isn't self-explanatory (e.g. the duplicate-prevention transaction, the date-driven status logic).

---

## 20. FINAL BUILD CHECKLIST (self-verify before considering the build complete)

- [ ] Full MySQL schema in `database/campusvoice.sql`, with seed data, all 8 tables, PKs/FKs/indexes/unique constraints in place
- [ ] Shared login with role auto-detection; bcrypt hashing; forced first-login password change
- [ ] Session-based auth with role guards on every protected page; Session Expired + Unauthorized pages wired up
- [ ] Every Student page listed in Section 11 built and linked correctly
- [ ] Every Administrator page listed in Section 11 built and linked correctly
- [ ] Every system/hidden page (404, 401, session-expired, maintenance, survey-closed, empty states) built
- [ ] All four question types implemented end-to-end: render, client validation, server validation, storage, result computation
- [ ] One-response-per-question enforced via unique constraint + server re-check + transaction
- [ ] Date-driven survey status logic (open/close/active/archived) working off `NOW()` comparisons
- [ ] Student and Administrator dashboards showing all specified widgets
- [ ] Results Dashboard with pie/bar/line/rating-distribution charts wired to real computed data
- [ ] Reports: summary, detailed, participation, recommendation — printable via `@media print`; PDF/Excel export if implemented
- [ ] Full reusable component library (buttons, cards, tables, modals, toasts, skeletons, empty states, etc.)
- [ ] Branding/colors applied consistently via CSS variables across every page
- [ ] Fully responsive at desktop/tablet/mobile breakpoints
- [ ] Security checklist in Section 18 fully implemented, not partially
- [ ] No forbidden frameworks/technologies used anywhere in the codebase

Build the entire application now, following this document exactly, end to end.
