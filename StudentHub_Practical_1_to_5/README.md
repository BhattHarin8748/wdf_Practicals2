# StudentHub — Practical 1 to 5

StudentHub is a responsive student portal frontend created for the semester-long Web Development practicals.

## Practicals covered

- **Practical 1:** Project initiation, requirement analysis, sitemap, wireframe, GitHub setup
- **Practical 2:** Semantic HTML5 pages and accessibility-ready structure
- **Practical 3:** Responsive UI using CSS Grid, Flexbox and media queries
- **Practical 4:** JavaScript DOM manipulation, events, FAQ, modal, mobile menu
- **Practical 5:** Registration form validation, regular expressions, password strength and friendly errors

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

## Folder structure

```text
StudentHub/
├── index.html
├── about.html
├── register.html
├── login.html
├── dashboard.html
├── events.html
├── profile.html
├── faq.html
├── contact.html
├── feedback.html
├── admin.html
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   ├── js/validation.js
│   └── images/
├── practical-1/design/
├── practical-2/
├── practical-3/
├── practical-4/
└── practical-5/
```

## How to run

1. Download/clone the repository.
2. Open the folder in VS Code.
3. Open `index.html` in a browser, or use the **Live Server** extension.
4. Test navigation, FAQ, event modal, mobile menu and registration validation.

## Git commit plan

```text
Practical 1 - Project setup, sitemap, wireframe and README
Practical 2 - Added semantic HTML5 pages
Practical 3 - Added responsive CSS Grid/Flexbox design
Practical 4 - Added JavaScript interactions
Practical 5 - Added registration validation
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
