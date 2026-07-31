# Software Requirements Specification & Website Planning Document
## Campus Improvement & Student Voice Platform
### (Adaptation of IT 305 – Act 5, Set B: Web-Based Survey Management System)

**Prepared as:** Full project blueprint — planning only, no code
**Base Assignment:** Development of a Web-Based Survey Management System for a Multipurpose Cooperative (BulSU IT 305, Set B)
**Theme Adaptation:** Members → Students | Cooperative Staff → Administrators | Cooperative → School/Campus
**Required Technologies:** HTML, CSS, JavaScript, PHP, MySQL only

> **Mapping note (read first):** Every requirement in the original assignment PDF is preserved below with identical functionality. Only labels changed: "Members" are now "Students," "Cooperative Staff" are now "Administrators," and the "Cooperative Account Number" login credential becomes a "Student ID Number." Nothing from the original System Requirements, Database Requirements, Functional/Non-Functional Requirements, Suggested Pages, or Submission Requirements has been removed, simplified, or altered in substance.

---

## SECTION 1 — Project Overview

### 1.1 What the website is
The Campus Improvement & Student Voice Platform is a web-based survey management system built for a school. It lets students submit structured feedback about campus life — classrooms, Wi‑Fi, library, laboratories, cafeteria, safety, wellness, events, academic support, organizations, and facilities — and lets administrators design surveys, track participation, and automatically turn raw responses into statistics, highlighted concerns, and printable reports.

Functionally, this is the exact same system the assignment describes for a multipurpose cooperative: a member-facing survey portal plus a staff-facing survey/reporting console. The only thing that changed is who the "members" and "staff" are and what the surveys are about.

### 1.2 What problem it solves
Schools usually collect feedback informally (paper forms, verbal complaints, scattered emails), so nothing gets counted, compared, or prioritized. This platform solves that by:
- Centralizing every survey response in one database.
- Automatically computing percentages, averages, and distributions instead of manual tallying.
- Surfacing the surveys/questions with the worst scores so administrators know where to act first.
- Producing a report administrators can print and bring to a meeting or budget discussion.

### 1.3 Who will use it
Two user roles, matching the assignment's two modules exactly:
- **Students** (equivalent to "Members") — log in, view active surveys, answer once, submit, view confirmation, optionally update their profile.
- **Administrators** (equivalent to "Cooperative Staff") — log in securely, create/edit/deactivate surveys, add questions of multiple types, schedule surveys, monitor respondents, view auto-computed results with charts, generate printable reports, and optionally export PDF/Excel.

### 1.4 Why the school needs it
Administrators cannot act on problems they cannot measure. A platform like this turns "students keep complaining about the Wi‑Fi" into "82% of 400 respondents report slow internet, concentrated in Building C" — a fact that can be attached to a budget request.

### 1.5 How it improves campus decision making
By replacing anecdote with data: participation rates show engagement, rating distributions show severity, and trend charts (if surveys repeat each term) show whether previous interventions worked.

### 1.6 How it fulfills every requirement in the assignment
Every subsequent section of this document maps one-to-one onto a section of the official PDF (Member Module → Student Module, Staff Module → Administrator Module, Database Requirements, Functional Requirements, Non-Functional Requirements, Suggested Pages, Submission Requirements). Section 21 contains the full audit/checklist confirming this.

---

## SECTION 2 — Website Flow (Complete User Journeys)

### 2.1 Student Journey
```
Landing Page
   │
   ▼
Login Page  ──(invalid credentials)──▶ Login Page + Error Message
   │ (valid credentials, first login)
   ▼
Forced "Change Password" Page ──▶ Student Dashboard
   │ (valid credentials, not first login)
   ▼
Student Dashboard
   │
   ├──▶ Available Surveys List
   │        │
   │        ├──▶ Survey Closed / No Available Survey (empty state)
   │        │
   │        └──▶ Survey Form Page
   │                 │
   │                 ├──(already answered)──▶ Redirect to Dashboard + "Already Answered" notice
   │                 │
   │                 ├──(validation fails)──▶ Survey Form Page + inline errors
   │                 │
   │                 └──(submit success)──▶ Submission Confirmation Page
   │                                              │
   │                                              ▼
   │                                        Student Dashboard (survey now removed from active list)
   │
   ├──▶ Profile Page ──▶ Update Profile ──▶ Success Message ──▶ Profile Page
   │
   ├──▶ Change Password Page ──▶ Success Message ──▶ Student Dashboard
   │
   └──▶ Logout ──▶ Session Destroyed ──▶ Login Page
```

### 2.2 Administrator Journey
```
Landing Page
   │
   ▼
Login Page ──(invalid)──▶ Login Page + Error
   │ (valid)
   ▼
Administrator Dashboard
   │
   ├──▶ Survey Management
   │        ├──▶ Create Survey ──▶ Add Questions ──▶ Set Schedule ──▶ Publish ──▶ Survey Management (list updated)
   │        ├──▶ Edit Survey ──▶ Save Changes ──▶ Survey Management
   │        └──▶ Deactivate Survey ──▶ Confirmation Modal ──▶ Survey Management (status = inactive)
   │
   ├──▶ Question Management ──▶ Add/Edit/Delete Question ──▶ Question Management
   │
   ├──▶ Student Management ──▶ View/Search/Filter Students ──▶ Student Detail (response history)
   │
   ├──▶ Results Dashboard ──▶ Select Survey ──▶ Charts + Tables + Highlighted Concerns
   │
   ├──▶ Reports ──▶ Select Survey/Range ──▶ Generate Report ──▶ Print / Export PDF / Export Excel
   │
   ├──▶ User Management ──▶ Manage Administrator Accounts / Roles
   │
   └──▶ Logout ──▶ Session Destroyed ──▶ Login Page
```

### 2.3 Navigation rules that apply everywhere
- Every button that leaves data unsaved shows a confirmation modal before navigating away.
- Session timeout redirects any logged-in user to a **Session Expired** page, never straight to Login, so the reason is clear.
- Role-based guards: a Student who manually types an Administrator URL is redirected to an **Unauthorized** page, not the login page.

---

## SECTION 3 — Complete Sitemap

```
CAMPUS IMPROVEMENT & STUDENT VOICE PLATFORM
│
├── PUBLIC PAGES
│   ├── / (Landing Page)
│   ├── /login (Shared Login, role-detected)
│   ├── /forgot-password (optional, if implemented)
│   ├── /maintenance (system-wide maintenance notice)
│   └── /404 (Page Not Found)
│
├── STUDENT PAGES  (auth required, role = student)
│   ├── /student/dashboard
│   ├── /student/surveys (Available Surveys list)
│   ├── /student/surveys/no-available (empty state)
│   ├── /student/surveys/:id (Survey Form)
│   ├── /student/surveys/:id/closed (Survey Closed state)
│   ├── /student/surveys/:id/confirmation (Submission Confirmation)
│   ├── /student/profile
│   ├── /student/change-password
│   └── /student/logout
│
├── ADMINISTRATOR PAGES  (auth required, role = admin)
│   ├── /admin/dashboard
│   ├── /admin/surveys (Survey Management list)
│   ├── /admin/surveys/create
│   ├── /admin/surveys/:id/edit
│   ├── /admin/surveys/:id/questions (Question Management)
│   ├── /admin/students (Student Management)
│   ├── /admin/students/:id (Student Detail / Response History)
│   ├── /admin/results (Results Dashboard)
│   ├── /admin/results/:surveyId
│   ├── /admin/reports
│   ├── /admin/reports/:id/print
│   ├── /admin/users (User/Administrator Management)
│   └── /admin/logout
│
└── SYSTEM / HIDDEN PAGES  (shared across roles)
    ├── /401 (Unauthorized)
    ├── /session-expired
    ├── /error (Generic Error Page)
    ├── /success (Generic Success wrapper, used by modals/toasts contextually)
    └── /survey-closed (global fallback if a direct link is hit after closing)
```

---

## SECTION 4 — Complete Information Architecture (Page-Level Contracts)

For brevity of structure but completeness of content, each page below lists: **Purpose, Target User, Features, Buttons, Navigation, Inputs, Outputs, Data Displayed, Actions, DB Tables Used, Post-Click Behavior.**

### 4.1 Login Page
- **Purpose:** Authenticate Students and Administrators through one shared form.
- **Target User:** Both roles.
- **Features:** Username field (Student ID Number for students, Admin username for admins), password field, "Login" button, error banner.
- **Buttons:** Login, (optional) Forgot Password.
- **Navigation:** On success → role-specific Dashboard; on first-login student → Change Password page first.
- **Inputs:** Username/ID, password.
- **Outputs:** Session token/cookie, redirect.
- **Data Displayed:** Error messages only (invalid credentials, account locked, etc.).
- **Actions Available:** Submit login, toggle password visibility.
- **DB Tables Used:** Students, Administrators, Login History (optional).
- **Post-Click:** Validates credentials server-side via PHP; on failure increments a failed-attempt counter (supports Section 17 security); on success starts a PHP session and logs entry to Login History.

### 4.2 Student Dashboard
- **Purpose:** Home base after login; students see what needs their attention.
- **Features:** Welcome banner, count of available surveys, quick links, notifications.
- **Buttons:** "View Available Surveys," "My Profile," "Change Password," "Logout."
- **Data Displayed:** Number of pending surveys, last submission date, any admin announcements.
- **DB Tables Used:** Students, Surveys, Responses.
- **Post-Click:** Each button routes to the corresponding page listed in the sitemap.

### 4.3 Available Surveys (Student)
- **Purpose:** List every survey the student has not yet answered and that is currently open.
- **Features:** Card/list of surveys with title, short description, deadline countdown.
- **Buttons:** "Answer Survey" per card.
- **Data Displayed:** Survey title, category (Wi‑Fi, Cafeteria, etc.), closing date.
- **Empty State:** "No Available Survey" page/section when the list is empty.
- **DB Tables Used:** Surveys, Responses (to exclude already-answered), Students.
- **Post-Click:** Opens Survey Form for the selected survey ID; server re-checks eligibility (open + not yet answered) before rendering, to prevent stale links.

### 4.4 Survey Form
- **Purpose:** Render all questions for one survey and collect answers.
- **Features:** Dynamically rendered questions by type (Multiple Choice, Yes/No, Rating Scale, Short Answer), progress indicator, client-side validation via JavaScript.
- **Buttons:** "Submit," "Cancel/Back."
- **Inputs:** Radio buttons (MC/Yes-No), star or numeric scale (Rating), text area (Short Answer).
- **Outputs:** Validation error highlights; on submit, a POST to PHP handler.
- **DB Tables Used:** Survey Questions, Survey Choices, Responses.
- **Post-Click Submit:** PHP validates required fields, checks for a prior response by this student+survey (duplicate prevention), inserts rows into Responses, then redirects to Submission Confirmation. If a duplicate is detected, redirects to Dashboard with a notice instead of inserting.

### 4.5 Submission Confirmation
- **Purpose:** Confirm the response was recorded.
- **Features:** Success icon, summary text ("Thank you — your response to 'Wi-Fi Performance' was recorded.").
- **Buttons:** "Back to Dashboard."
- **DB Tables Used:** Responses (read-only confirmation of the just-inserted row).

### 4.6 Student Profile / Change Password
- **Purpose:** Let a student update optional profile fields and mandatory password changes.
- **Buttons:** "Save Changes," "Cancel."
- **Inputs:** Name/contact fields (profile), current password + new password + confirm (change password).
- **DB Tables Used:** Students.
- **Post-Click:** PHP re-hashes and stores new password (Section 17); shows Success State inline; forced on first login per assignment requirement A.2.

### 4.7 Administrator Dashboard
- **Purpose:** Command center summarizing platform activity.
- **Data Displayed:** Active surveys count, total respondents today, participation rate, most-answered survey, flagged concerns (e.g., any result crossing a concern threshold).
- **Buttons:** Links into each admin module.
- **DB Tables Used:** Surveys, Responses, Survey Results, Students.

### 4.8 Survey Management
- **Purpose:** List, create, edit, deactivate surveys.
- **Buttons:** "Create Survey," "Edit," "Deactivate," "Manage Questions."
- **DB Tables Used:** Surveys.
- **Post-Click:** Create/Edit opens a form (title, description, category, open date, close date); Deactivate opens a confirmation modal, then sets `status = inactive`.

### 4.9 Question Management
- **Purpose:** Add/edit/delete questions and choices within a survey.
- **Buttons:** "Add Question," "Edit," "Delete," "Reorder."
- **Inputs:** Question text, question type selector, choice list (for MC), scale range (for Rating).
- **DB Tables Used:** Survey Questions, Survey Choices.

### 4.10 Student Management (Admin)
- **Purpose:** View and manage the student list (create accounts, reset passwords, view response history).
- **Buttons:** "Add Student," "Reset Password," "View Responses."
- **DB Tables Used:** Students, Responses.

### 4.11 Results Dashboard
- **Purpose:** Auto-computed statistics per survey.
- **Features:** Charts (pie/bar/line), summary tables, highlighted major concerns (e.g., "82% report slow internet").
- **DB Tables Used:** Survey Results, Responses, Survey Questions.

### 4.12 Reports
- **Purpose:** Generate printable/exportable reports.
- **Buttons:** "Generate," "Print," "Export PDF," "Export Excel" (optional).
- **DB Tables Used:** Survey Results, Surveys, Responses.

### 4.13 User Management (Admin accounts)
- **Purpose:** Manage administrator accounts and roles.
- **DB Tables Used:** Administrators.

### 4.14 Hidden/System Pages
- **404:** Unknown route, generic "page not found" with link home.
- **Unauthorized (401):** Wrong-role access attempt.
- **Session Expired:** Timed-out session, prompts re-login.
- **Maintenance:** Whole-site notice, no data access.
- **Survey Closed / No Available Survey:** Empty/blocked states described above.

---

## SECTION 5 — Complete Website Structure

Every logged-in page shares this shell:
- **Header:** Logo/site name, role badge, notification bell, profile menu, logout.
- **Sidebar (role-specific):** Student sidebar = Dashboard, Surveys, Profile, Change Password. Admin sidebar = Dashboard, Surveys, Questions, Students, Results, Reports, Users.
- **Top Navigation / Breadcrumbs:** Shows current location (e.g., Dashboard ▸ Survey Management ▸ Edit Survey).
- **Content Area:** Page-specific cards, tables, forms, charts.
- **Footer:** School name, version, support contact.

Reusable components used throughout: Dashboard Cards, Data Tables (sortable/paginated), Charts (chart.js-style via JS), Forms with inline validation, Toast Notifications, Modals (confirm deactivate, confirm logout, confirm delete), Breadcrumbs, Search bar (Student Management, Survey Management), Pagination controls, Empty-state illustrations, Loading spinners.

---

## SECTION 6 — Page-by-Page Breakdown (Layout & States)

To avoid repeating Section 4 verbatim, this section adds the states/behaviors not yet covered per page category.

**All form pages (Login, Survey Form, Profile, Change Password, Create/Edit Survey, Question Management):**
- *Validation:* Required-field checks in JavaScript before submit; authoritative re-validation in PHP.
- *Loading State:* Submit button shows a spinner and disables itself to prevent double-submit.
- *Success State:* Green toast + redirect or inline confirmation.
- *Error State:* Red inline messages under each invalid field; a summary banner at top if more than one error.
- *Empty State (where relevant, e.g., Question Management with zero questions):* "No questions yet — add your first question" prompt.
- *Responsive Behavior:* Single-column stacked fields on mobile; two-column on tablet/desktop.

**All list/table pages (Survey Management, Student Management, Available Surveys, Reports list):**
- *Empty State:* Illustration + short text + primary call-to-action button.
- *Loading State:* Skeleton rows while data fetches.
- *Pagination:* 10–25 rows per page with next/prev controls.
- *Search/Filter:* Text search plus dropdown filters (status, category, date range).

**All dashboards (Student, Administrator):**
- *Icons:* Each card uses a representative icon (Wi-Fi icon for network surveys, shield icon for safety, etc.).
- *Responsive Behavior:* Cards reflow from a 4-column grid (desktop) to 2-column (tablet) to 1-column (mobile).

---

## SECTION 7 — Student Module (Full Detail)

**Login:** Student logs in using Student ID Number as username and, on first use, a system-assigned default password identical to the ID number (mirrors the cooperative's "Account Number as username & default password" rule exactly). PHP checks the `students` table, verifies the password hash, and starts a session storing `student_id` and `role = student`.

**Forced Password Change:** A `must_change_password` boolean flag on the Students table is checked right after login; if true, the student is routed to Change Password before anything else, satisfying "change password after first login."

**Dashboard:** Shows counts pulled live from Surveys/Responses (open surveys not yet answered by this student).

**Available Surveys:** Query = surveys where `status = active` AND `now() BETWEEN open_date AND close_date` AND no matching row exists in Responses for this student+survey. This enforces "view active surveys" and lays groundwork for "answer once."

**Survey Form:** Renders questions/choices from Survey Questions/Survey Choices for that survey ID. Client-side JS validates required answers exist before allowing submit.

**Submit Survey:** PHP re-checks eligibility server-side (never trust the client) immediately before inserting, using a database transaction plus a unique constraint on `(student_id, survey_id)` in Responses — this is the actual mechanism that prevents duplicate responses even if two tabs are submitted at once.

**Confirmation:** Reads back the just-created response(s) to show a friendly confirmation summary.

**Completed surveys disappear:** Because the Available Surveys query excludes any survey with an existing Responses row for that student, a completed survey naturally drops off the list without needing a separate flag.

**Active surveys appear / expired surveys behave:** The same query's date range (`open_date`/`close_date`) automatically adds a survey the moment it opens and removes it the moment it closes; if a student navigates directly to an expired survey's URL, the server detects `now() > close_date` and renders the "Survey Closed" state instead of the form.

**Profile:** Optional fields (contact info, etc.) editable at will; changes saved to Students table, success message shown inline — this satisfies the Submission Requirement screenshot "Update Personal Information Page" and "Successful Update Confirmation."

**Change Password:** Standard current/new/confirm flow; new password is hashed (never stored in plaintext) before saving.

**Logout:** Destroys the PHP session and clears the session cookie, then redirects to Login.

---

## SECTION 8 — Administrator Module (Full Detail)

**Dashboard:** Aggregates live counts from Surveys, Responses, Survey Results.

**Survey Management:** Create (insert into Surveys with `open_date`, `close_date`, `status`), Edit (update same row), Deactivate (set `status = inactive`, which immediately removes it from students' Available Surveys query — no separate cron job needed).

**Question Management:** Add/edit/delete rows in Survey Questions; for Multiple Choice and Yes/No types, also manage rows in Survey Choices; question type is stored as an enum (`multiple_choice`, `yes_no`, `rating`, `short_answer`).

**Student Management:** Search/filter the Students table; view a given student's full response history by joining Responses back to that student.

**Results Dashboard:** For a selected survey, aggregates Responses into percentages/averages and stores/refreshes a snapshot in Survey Results (so heavy computation isn't repeated on every page view); flags any answer option whose share crosses a configurable "concern" threshold (e.g., ≥50% negative) for the highlighted-concerns feature.

**Reports:** Pulls from Survey Results plus metadata from Surveys to assemble a formatted, printable page (browser print via `window.print()` styled with a print stylesheet — no extra library needed) and, optionally, exports to PDF/Excel using PHP libraries.

**User Management:** CRUD over the Administrators table, including role assignment if multiple admin sub-roles are wanted later.

**Logout:** Same session-destroy behavior as the student side.

---

## SECTION 9 — Survey Lifecycle

```
Administrator creates survey (Surveys row inserted, status = draft)
        │
        ▼
Adds questions (Survey Questions / Survey Choices rows inserted)
        │
        ▼
Sets schedule (open_date, close_date set on the Surveys row)
        │
        ▼
Publishes survey (status changes draft → active)
        │
        ▼
Students answer (Responses rows inserted, one per student per question,
                 enforced unique per student+survey)
        │
        ▼
Survey closes (close_date passes OR admin manually deactivates;
               status changes active → closed)
        │
        ▼
Results computed (aggregation query populates/refreshes Survey Results)
        │
        ▼
Reports generated (Reports module reads Survey Results + Surveys)
        │
        ▼
Survey archived (status changes closed → archived; still viewable in
                 Reports/History but no longer editable)
```

---

## SECTION 10 — Survey Types

| Type | Validation | Storage | Result Computation |
|---|---|---|---|
| Multiple Choice | Exactly one choice required | `Responses.choice_id` references `Survey Choices` | Count per choice ÷ total responses = percentage |
| Yes / No | Exactly one of Yes/No required | Stored as a Multiple Choice with two fixed choices, OR a dedicated boolean field | Percentage Yes vs. No |
| Rating Scale | Numeric value within defined range (e.g., 1–5) required | `Responses.rating_value` (integer) | Average rating; distribution histogram per rating value |
| Short Answer (optional) | Free text, optional max length, optional required flag | `Responses.text_answer` | Not statistically aggregated automatically; listed/searchable in Reports as qualitative feedback |

---

## SECTION 11 — Database Planning

### 11.1 Tables (mirrors the assignment's required list exactly, renamed)

**Students** *(was Members)*
- `student_id` (PK, e.g., Student ID Number)
- `full_name`
- `password_hash`
- `must_change_password` (bool)
- `contact_info` (optional profile fields)
- `created_at`

**Administrators** *(was Staff/Admin)*
- `admin_id` (PK)
- `full_name`
- `username`
- `password_hash`
- `role` (e.g., super_admin, moderator — optional expansion)
- `created_at`

**Surveys**
- `survey_id` (PK)
- `title`
- `description`
- `category` (Wi-Fi, Cafeteria, Safety, etc.)
- `open_date`
- `close_date`
- `status` (draft, active, closed, archived)
- `created_by` (FK → Administrators.admin_id)

**Survey Questions**
- `question_id` (PK)
- `survey_id` (FK → Surveys.survey_id)
- `question_text`
- `question_type` (multiple_choice, yes_no, rating, short_answer)
- `is_required` (bool)
- `order_index`

**Survey Choices**
- `choice_id` (PK)
- `question_id` (FK → Survey Questions.question_id)
- `choice_text`

**Responses**
- `response_id` (PK)
- `student_id` (FK → Students.student_id)
- `survey_id` (FK → Surveys.survey_id)
- `question_id` (FK → Survey Questions.question_id)
- `choice_id` (FK → Survey Choices.choice_id, nullable)
- `rating_value` (nullable)
- `text_answer` (nullable)
- `submitted_at`
- **Constraint:** unique index on `(student_id, question_id)` — the actual enforcement mechanism for "answer once."

**Survey Results**
- `result_id` (PK)
- `survey_id` (FK)
- `question_id` (FK)
- `computed_metric` (e.g., percentage, average)
- `computed_value`
- `last_refreshed_at`

**Login History** *(optional, as in the original PDF)*
- `log_id` (PK)
- `user_id`
- `role` (student/admin)
- `login_time`
- `ip_address` (optional)

### 11.2 Relationships
- Surveys 1—∞ Survey Questions
- Survey Questions 1—∞ Survey Choices
- Students 1—∞ Responses
- Surveys 1—∞ Responses
- Survey Questions 1—∞ Responses
- Surveys 1—∞ Survey Results

### 11.3 Normalization & Constraints
Tables are normalized to 3NF: choice text lives only in Survey Choices (not repeated per response), question metadata lives only in Survey Questions, and Responses stores only foreign keys plus the actual answer value. Foreign keys enforce referential integrity; the unique constraint on Responses enforces the one-response-per-question rule; indexes are placed on `survey_id`, `student_id`, and `question_id` for fast lookups on the Dashboard and Results queries.

### 11.4 Why every table exists
Each table exists because a specific assignment requirement needs it: Students/Administrators for the two login roles; Surveys for scheduling/status; Survey Questions/Choices for flexible question types; Responses for the actual submitted data and duplicate prevention; Survey Results so heavy statistics aren't recomputed on every dashboard load; Login History to optionally satisfy the security/audit non-functional requirement.

---

## SECTION 12 — System Logic

- **Login:** PHP compares submitted username (Student ID / admin username) against the stored record, verifies the password using `password_verify()` against a `password_hash()`-generated hash, and starts a session on success.
- **Authentication:** Session-based; a `$_SESSION['user_id']` and `$_SESSION['role']` gate every protected page via an include/check at the top of each PHP file.
- **Sessions:** PHP native sessions with a configured idle timeout (e.g., 20 minutes); on timeout, next request redirects to Session Expired.
- **Passwords:** Hashed with `password_hash()` (bcrypt); never stored or logged in plaintext; changing password re-hashes and overwrites.
- **Duplicate submissions blocked:** Server-side re-check plus a database unique constraint on `(student_id, question_id)` in Responses, wrapped in a transaction so a race condition (double-click, two tabs) cannot slip through.
- **Survey schedules:** `open_date`/`close_date` compared against server time (`NOW()`) on every relevant query — this single mechanism drives "view active surveys," "completed surveys disappear," and "expired surveys behave" all at once.
- **Reports computed:** A scheduled or on-demand aggregation query (`COUNT`, `AVG`, `GROUP BY`) reads Responses and writes/refreshes Survey Results, which Reports then formats for print/export.

---

## SECTION 13 — Dashboard Design

| Widget | Exists Because |
|---|---|
| Statistics cards (active surveys, respondents, participation %) | Gives an at-a-glance status without opening a report |
| Charts | Visual pattern recognition is faster than reading a table |
| Recent Surveys | Lets an admin jump straight back into what they were just editing |
| Participation Rate | Directly required by "monitor survey participation" |
| Most Answered Survey | Highlights which topic students care about most |
| Pending Surveys (student side) | Tells a student exactly what action is owed |
| Recent Activity | Simple audit trail of the last few submissions/edits |
| Announcements | Lets admins push context (e.g., "Wi-Fi survey closes Friday") |
| Notifications | Real-time nudge for new surveys or results ready |
| Recommendations | Auto-generated text like "Upgrade campus network" tied to a flagged concern |

---

## SECTION 14 — Charts

| Chart | Belongs On |
|---|---|
| Pie Chart | Results Dashboard — share of each Multiple Choice/Yes-No option |
| Bar Chart | Results Dashboard — side-by-side comparison across survey categories |
| Line Chart | Reports — trend across repeated survey runs over time |
| Rating Distribution | Results Dashboard — histogram of 1–5 rating counts |
| Participation Rate | Administrator Dashboard — gauge/bar of respondents vs. total students |
| Survey Completion | Survey Management — progress bar per survey |
| Most Common Responses | Results Dashboard — ranked list beside the chart |
| Trend Analysis | Reports — multi-survey comparison over the term/year |

---

## SECTION 15 — Reports

- **Printable Reports:** HTML page with a dedicated print stylesheet (`@media print`), triggered by `window.print()`.
- **PDF Reports:** Generated server-side in PHP (e.g., via a PHP PDF library) from the same report data — optional, as in the original PDF.
- **Excel Reports (optional):** Server-side export of the results table to `.xlsx`/`.csv`.
- **Summary Reports:** One page per survey — headline stats only.
- **Detailed Reports:** Full breakdown per question, including Short Answer text responses listed verbatim.
- **Participation Reports:** Respondent counts/rates across all surveys in a date range.
- **Survey Reports:** Everything about one specific survey, ready to print.
- **Recommendation Reports:** Auto-generated suggestion text attached to any flagged concern (mirrors the "82% slow internet → upgrade network" example).

---

## SECTION 16 — User Interface

- **Typography:** One clean sans-serif family; clear size hierarchy (page titles > section headers > body text).
- **Spacing:** Consistent 8px-based spacing scale for padding/margins across cards, forms, and tables.
- **Cards:** Rounded corners, soft shadow, consistent internal padding for dashboard widgets.
- **Buttons:** Primary (filled, brand color), secondary (outlined), destructive (red, for deactivate/delete), all with a hover and a disabled state.
- **Forms:** Labeled fields, inline validation messages, clear required-field indicators.
- **Colors:** A primary school-brand color, a neutral gray scale for text/backgrounds, and semantic colors (green = success, red = error, amber = warning) used consistently.
- **Icons:** Simple line icons per module (surveys, students, reports, settings).
- **Animations/Hover Effects:** Subtle (150–200ms) transitions on buttons/cards; no distracting motion.
- **Loading Animations:** Skeleton screens for tables, spinner for form submits.
- **Empty States:** Friendly illustration + one-line explanation + a clear next action.
- **Error/Success Messages:** Toast notifications for transient feedback; inline banners for form-level errors.

---

## SECTION 17 — Security

- **Authentication:** Session-based login for both roles, verified on every protected request.
- **Authorization:** Role checks (`student` vs `admin`) gate every route; mismatched access → Unauthorized page.
- **Password Encryption:** `password_hash()`/`password_verify()` (bcrypt); plaintext never stored.
- **Session Timeout:** Idle sessions expire automatically; expired sessions are cleared server-side.
- **SQL Injection Prevention:** All queries use PHP PDO/MySQLi **prepared statements** with bound parameters — never string-concatenated SQL.
- **XSS Prevention:** All user-supplied output is escaped (`htmlspecialchars()`) before being echoed into HTML.
- **Input Validation:** Client-side (JavaScript, fast feedback) plus authoritative server-side (PHP) validation on every form.
- **Duplicate Prevention:** Unique database constraint plus a server-side pre-check, as detailed in Section 12.
- **Secure Logout:** Full session destruction (`session_unset()` + `session_destroy()`) and cookie clearing.
- **Role-Based Access:** Every admin-only page/action re-checks `$_SESSION['role'] === 'admin'` before executing.

---

## SECTION 18 — Responsive Design

- **Desktop (≥1024px):** Full sidebar visible, multi-column dashboards, side-by-side charts.
- **Tablet (768–1023px):** Sidebar collapses to an icon rail or hamburger toggle; dashboard cards reflow to 2 columns; tables become horizontally scrollable if needed.
- **Mobile (<768px):** Sidebar becomes a slide-in drawer; all forms and tables stack to a single column; charts resize to full width; sticky bottom action buttons on long forms (e.g., Survey Form) for easy reach.

---

## SECTION 19 — Development Roadmap

| Phase | Focus | Why This Order |
|---|---|---|
| 1. Planning | Finalize this document, wireframes, sitemap | Nothing should be built before scope is locked |
| 2. Database | Create schema, tables, constraints, seed test data | Every other layer depends on a stable schema |
| 3. Authentication | Login, sessions, password hashing, role guards | Nothing else can be tested securely without this |
| 4. Student Module | Dashboard, surveys list, survey form, submission, profile | Core value delivery — the data collection side |
| 5. Administrator Module | Survey/question management, student management | Needed to actually create the surveys students answer |
| 6. Survey Engine | Scheduling logic, duplicate prevention, close/open automation | Ties Student and Administrator modules together |
| 7. Reports | Results Dashboard, charts, printable/exportable reports | Depends on real response data existing first |
| 8. Testing | Full functional + edge-case testing (Section 20) | Must happen once all features exist |
| 9. Deployment | Final server setup, submission packaging | Last step before hand-in |

---

## SECTION 20 — Testing Plan

- **Unit Testing:** Individual PHP functions (password hashing, duplicate-check query, percentage calculation) tested with known inputs/expected outputs.
- **System Testing:** Full flows — login → answer survey → confirmation; login → create survey → publish → student sees it.
- **User Acceptance Testing:** A few real students/staff try the platform and confirm it matches expectations.
- **Test Cases (examples):**
  - Student logs in with correct Student ID and default password → succeeds, forced to Change Password.
  - Student attempts to answer the same survey twice → second attempt blocked with a clear message.
  - Admin sets a survey's `close_date` in the past → survey correctly shows as closed to students.
  - Admin deactivates a survey mid-response → students immediately stop seeing it as available.
  - Rating Scale question submitted with an out-of-range value → rejected by both JS and PHP validation.
- **Expected Results:** Documented alongside each test case before running it, so pass/fail is unambiguous.
- **Edge Cases:** Simultaneous double-submit (two browser tabs), survey with zero questions, survey with zero respondents (Results Dashboard must show an empty state, not an error), extremely long Short Answer text.
- **Validation Tests:** Every form field checked for required/format/range rules on both client and server.

---

## SECTION 21 — Submission Checklist & Self-Audit

### 21.1 Submission Checklist (from the assignment's requirements)
- [ ] Complete Source Code
- [ ] HTML
- [ ] CSS
- [ ] JavaScript
- [ ] PHP
- [ ] MySQL
- [ ] Login System (Student + Administrator)
- [ ] Student Module
- [ ] Administrator Module
- [ ] Survey Management
- [ ] Question Management
- [ ] Student (Member) Management
- [ ] Dashboard (both roles)
- [ ] Survey Scheduling
- [ ] One Response Per Student
- [ ] Response Confirmation
- [ ] Automatic Result Computation
- [ ] Graphs
- [ ] Tables
- [ ] Reports
- [ ] Printable Reports
- [ ] Optional PDF Export
- [ ] Optional Excel Export
- [ ] Responsive Design
- [ ] Secure Authentication
- [ ] Organized Database
- [ ] Video Presentation
- [ ] MySQL Database (.sql)
- [ ] Login Screenshot
- [ ] Dashboard Screenshot
- [ ] Profile Update Screenshot
- [ ] Successful Update Screenshot
- [ ] Member/Student Contribution Documentation

### 21.2 Full Requirement-by-Requirement Self-Audit Against the Uploaded PDF

| PDF Requirement | Covered In This Document | Status |
|---|---|---|
| Objective: web-based survey system, cooperative members/staff | Section 1 (reframed as Students/Administrators) | ✅ Covered |
| Required technologies: HTML/CSS/JS/PHP/MySQL only | Stated up front; no other framework used anywhere in this document | ✅ Covered |
| A1. Log in with Account Number as username & default password | Section 7 (Student ID Number plays this role) | ✅ Covered |
| A2. Change password after first login | Section 7, Section 12 (`must_change_password` flag) | ✅ Covered |
| A3. View active surveys | Section 7 (Available Surveys query) | ✅ Covered |
| A4. Answer surveys only once | Section 7, Section 12 (unique constraint + server re-check) | ✅ Covered |
| A5. Submit survey responses | Section 7 (Survey Form → Submit) | ✅ Covered |
| A6. View confirmation after submission | Section 4.5, Section 7 | ✅ Covered |
| A7. Update profile (optional) | Section 4.6, Section 7 | ✅ Covered |
| B1. Staff securely log in | Section 8, Section 17 | ✅ Covered |
| B2. Create new surveys | Section 8, Section 4.8 | ✅ Covered |
| B3. Edit/deactivate surveys | Section 8, Section 9 | ✅ Covered |
| B4. Question types: MC, Yes/No, Rating, Short Answer (optional) | Section 10 | ✅ Covered |
| B5. Set survey open/close dates | Section 8, Section 9, Section 12 | ✅ Covered |
| B6. View list of respondents | Section 4.10, Section 8 | ✅ Covered |
| B7. Monitor survey participation | Section 13 (Participation Rate widget) | ✅ Covered |
| B8. Automatically compute survey results | Section 12, Section 11 (Survey Results table) | ✅ Covered |
| B9. Display statistics with tables and charts | Section 4.11, Section 14 | ✅ Covered |
| B10. Generate printable reports | Section 15 | ✅ Covered |
| B11. Export to PDF/Excel (optional) | Section 15 | ✅ Covered |
| C. Database tables: Members, Staff/Admin, Surveys, Survey Questions, Survey Choices, Responses, Survey Results, Login History (optional) | Section 11 (all 8 tables present, renamed only) | ✅ Covered |
| D. Functional: authenticate, manage accounts, manage surveys, record responses, prevent duplicates, auto-calc results, graphical summaries, printable reports | Sections 7, 8, 12, 14, 15 | ✅ Covered |
| E. Non-functional: user-friendly, responsive, data accuracy, security, organized DB, efficient processing | Sections 16, 17, 18, 11 | ✅ Covered |
| F. Suggested Member pages: Login, Dashboard, Available Surveys, Survey Form, Submission Confirmation, Profile, Change Password | Section 3, Section 4 (all present under Student) | ✅ Covered |
| F. Suggested Staff pages: Login, Dashboard, Survey Management, Question Management, Member Management, Results Dashboard, Reports, User Management, Logout | Section 3, Section 4 (all present under Administrator) | ✅ Covered |
| Submission: complete source code | Section 21.1 checklist | ✅ Covered (planning stage only, as instructed) |
| Submission: video presentation | Section 21.1 checklist | ✅ Covered |
| Submission: member contribution documentation | Section 21.1 checklist | ✅ Covered |
| Submission: MySQL .sql file | Section 21.1 checklist | ✅ Covered |
| Submission: screenshots (login, dashboard, update profile, successful update) | Section 21.1 checklist | ✅ Covered |
| Evaluation criteria awareness (Functionality 40%, UI 20%, DB 15%, Code Org 15%, Docs/video 10%) | Reflected in roadmap priority order (Section 19) and this audit | ✅ Covered |

### 21.3 Gaps
No gaps identified. Every functional requirement, non-functional requirement, database table, suggested page, and submission requirement from the uploaded PDF is represented in this plan under the Campus Improvement & Student Voice theme, with identical underlying functionality to the original Multipurpose Cooperative assignment.

---

**End of Document.** This blueprint is planning-only, as requested — no code has been written. It is ready to hand to a development team to begin Phase 2 (Database) of the roadmap in Section 19.
