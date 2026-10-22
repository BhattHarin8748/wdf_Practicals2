# StudentHub — Practical 1 to 7

StudentHub is a responsive student portal frontend created for the semester-long Web Development practicals.

## Practicals covered

- **Practical 1:** Project initiation, requirement analysis, sitemap, wireframe, GitHub setup
- **Practical 2:** Semantic HTML5 pages and accessibility-ready structure
- **Practical 3:** Responsive UI using CSS Grid, Flexbox and media queries
- **Practical 4:** JavaScript DOM manipulation, events, FAQ, modal, mobile menu
- **Practical 5:** Registration form validation, regular expressions, password strength and friendly errors
- **Practical 6:** Fetch API, JSON rendering, search, filtering, sorting, pagination and cached offline fallback
- **Practical 7:** PHP POST handling, server-side validation, CSRF protection and protected JSON storage
- **Practical 9:** Secure registration with MySQLi, prepared statements, duplicate email/username checks and password hashing

## Main pages

1. Home
2. About
3. Register
4. Login
5. Dashboard
6. Events
7. Profile
8. FAQ
9. Contact
10. Feedback
11. Admin
12. Practical 6 Data Browser
13. Practical 7 Server Forms

## Folder structure

```text
StudentHub/
├── index.html
├── about.html
├── register.html
├── register.php
├── db.php
├── studenthub_users.sql
├── login.html
├── dashboard.html
├── events.html
├── practical-6.html
├── practical-7.php
├── profile.html
├── faq.html
├── contact.html
├── feedback.html
├── admin.html
├── assets/
│   ├── css/style.css
│   ├── css/practical6.css
│   ├── js/main.js
│   ├── js/practical6.js
│   ├── js/validation.js
│   ├── data/events.json
│   ├── data/students.json
│   ├── data/faqs.json
│   └── images/
├── storage/
│   ├── .htaccess
│   ├── registrations.json
│   └── contact-messages.json
├── practical-1/design/
├── practical-2/
├── practical-3/
├── practical-4/
└── practical-5/
```

## How to run

1. Download/clone the repository.
2. Open the folder in VS Code.
3. Practicals 1–6 can run on a local HTTP server such as **Live Server**. Fetch requests to local JSON files do not work from a `file://` URL.
4. To run Practical 7, install **XAMPP**, copy the project folder into `C:\xampp\htdocs`, start Apache, then open `http://localhost/StudentHub_Practical_1_to_5/practical-7.php`. Live Server and Python's static server do not execute PHP.
5. Open `index.html` over HTTP and select **Browse JSON data**, or navigate to `practical-6.html`. Test all three tabs, search, filters, sorting, pagination and empty results.
6. For the Practical 6 offline fallback, load a collection once, stop the server and reload it. Clear local storage to test the error and retry states.
7. On the PHP page, submit a valid registration and contact message; then test invalid email, weak/mismatched password, missing terms, short message and a modified CSRF token. Confirm only valid submissions are appended to their JSON files and registration records contain `password_hash`, not the password.

| Test | Input/action | Expected result |
| --- | --- | --- |
| Valid registration | Complete all fields, use a strong matching password and accept terms | Success message; registration JSON gains a record with a password hash |
| Invalid registration | Use an invalid email or mobile, weak/mismatched password, invalid course/year, or omit terms | Error summary; no record is added; safe field values remain populated |
| Valid contact | Valid name/email and a 10–5000 character message | Success message; contact JSON gains a record |
| Invalid contact | Invalid email or message shorter than 10 characters | Error summary; no record is added |
| CSRF protection | Remove or alter the hidden CSRF token | Request is rejected; no record is added |
| Output encoding | Submit markup such as `<script>` in a text field | Markup is stripped on input and escaped on output; no script executes |

## Practical 7 implementation

`practical-7.php` accepts both forms through `method="post"`, validates and normalizes inputs server-side, escapes messages on output, hashes passwords, and embeds a session CSRF token in each form. `flock(LOCK_EX)` protects each JSON read-modify-write operation. The `storage/.htaccess` file disables directory listing and blocks direct downloads of the generated records.

The starter JSON files are empty arrays. Submissions append to `storage/registrations.json` and `storage/contact-messages.json`; keep these files private and clear test submissions before sharing the project.

## Practical 6 implementation

The Events, Students and FAQs tabs fetch `assets/data/events.json`, `assets/data/students.json` and `assets/data/faqs.json`. Each sample dataset contains 15 records. The separate `assets/js/practical6.js` module renders text safely with DOM APIs, searches with `filter()` and `some()`, sorts a copied array with `sort()`, and paginates with `slice()`.

Successful responses are cached in `localStorage` per collection. If a request fails, the last valid cached response is rendered with an offline status; without a cache, a retryable error is shown. A page size of six keeps pagination visible for the supplied sample data.

## Git commit plan

```text
Practical 1 - Project setup, sitemap, wireframe and README
Practical 2 - Added semantic HTML5 pages
Practical 3 - Added responsive CSS Grid/Flexbox design
Practical 4 - Added JavaScript interactions
Practical 5 - Added registration validation
Practical 6 - Added fetched JSON data browser with search, filters, sorting and pagination
Practical 7 - Added PHP form processing, server validation, CSRF and JSON storage
```

## Key viva answers

### What is a URL?

URL means Uniform Resource Locator. Its common parts are protocol, domain, port (optional), path, query string and fragment.

Example:
`https://example.com/events.html?id=10#upcoming`

### How is HTML processed?

The browser downloads HTML, parses it into a DOM tree, loads linked CSS and JavaScript, builds the render tree/layout and paints the page on screen.

### How is navigation managed?

StudentHub uses relative links such as `href="events.html"`. Every page shares a consistent navigation menu, so users can move between modules without a backend.

### How are GitHub commits maintained?

A commit is made after each practical with a clear message describing the completed work. The repository history therefore shows the project progression from Practical 1 through Practical 5.
