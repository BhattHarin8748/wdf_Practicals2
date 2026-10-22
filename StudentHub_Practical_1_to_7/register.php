<?php

declare(strict_types=1);

session_start();
require __DIR__ . '/db.php';

function cleanInput(string $value): string
{
    return trim(strip_tags($value));
}

function oldValue(array $data, string $key): string
{
    return isset($data[$key]) ? htmlspecialchars((string) $data[$key], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
}

function showError(string $message): void
{
    echo '<p role="alert" style="color:#b5412b; margin-bottom:1rem;">' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
}

$allowedCourses = ['B.Tech Computer Engineering', 'B.Tech Information Technology', 'BCA', 'MCA'];
$allowedYears = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
$allowedGenders = ['Female', 'Male', 'Other'];
$submittedData = [
    'fullName' => '',
    'username' => '',
    'email' => '',
    'mobile' => '',
    'course' => '',
    'year' => '',
    'gender' => '',
];
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedData = [
        'fullName' => cleanInput($_POST['fullName'] ?? ''),
        'username' => cleanInput($_POST['username'] ?? ''),
        'email' => strtolower(trim($_POST['email'] ?? '')),
        'mobile' => cleanInput($_POST['mobile'] ?? ''),
        'course' => cleanInput($_POST['course'] ?? ''),
        'year' => cleanInput($_POST['year'] ?? ''),
        'gender' => cleanInput($_POST['gender'] ?? ''),
    ];

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $termsAccepted = isset($_POST['terms']) && $_POST['terms'] === 'on';

    if (!preg_match('/^(?=.{2,50}$)[A-Za-z][A-Za-z \'’.-]*[A-Za-z]$/', $submittedData['fullName'])) {
        $errors[] = 'Enter a valid full name using 2 to 50 letters, spaces, apostrophes or hyphens.';
    }

    if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $submittedData['username'])) {
        $errors[] = 'Use 3 to 20 letters, numbers or underscores for the username.';
    }

    if (!filter_var($submittedData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if (!preg_match('/^(?:\+91[ -]?)?[6-9]\d{9}$/', $submittedData['mobile'])) {
        $errors[] = 'Enter a valid 10-digit mobile number, optionally prefixed with +91.';
    }

    if (!in_array($submittedData['course'], $allowedCourses, true)) {
        $errors[] = 'Select a valid course.';
    }

    if (!in_array($submittedData['year'], $allowedYears, true)) {
        $errors[] = 'Select a valid year.';
    }

    if (!in_array($submittedData['gender'], $allowedGenders, true)) {
        $errors[] = 'Select a valid gender.';
    }

    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[^A-Za-z\d]/', $password)) {
        $errors[] = 'Use at least 8 characters with a letter, number and symbol.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$termsAccepted) {
        $errors[] = 'You must accept the terms and conditions.';
    }

    if ($errors === []) {
        try {
            $db = getDbConnection();
            $duplicateCheck = $db->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
            $duplicateCheck->bind_param('ss', $submittedData['email'], $submittedData['username']);
            $duplicateCheck->execute();
            $duplicateCheck->store_result();

            if ($duplicateCheck->num_rows > 0) {
                $errors[] = 'An account with this email or username already exists.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $db->prepare('INSERT INTO users (full_name, username, email, mobile, course, year, gender, password_hash, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
                $insertStmt->bind_param('ssssssss', $submittedData['fullName'], $submittedData['username'], $submittedData['email'], $submittedData['mobile'], $submittedData['course'], $submittedData['year'], $submittedData['gender'], $passwordHash);
                $insertStmt->execute();
                $successMessage = 'Registration successful! Your StudentHub account has been created.';
            }
        } catch (Throwable $exception) {
            error_log('StudentHub registration failed: ' . $exception->getMessage());
            $errors[] = 'Registration could not be completed. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | StudentHub</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <header class="site-header">
    <div class="container nav-wrap">
      <a class="brand" href="index.html">Student<span>Hub</span></a>
      <nav id="mainNav">
        <a href="index.html">Home</a>
        <a href="about.html">About</a>
        <a href="events.html">Events</a>
        <a href="faq.html">FAQ</a>
        <a href="contact.html">Contact</a>
        <a href="login.html">Login</a>
        <a class="nav-cta active" href="register.html">Register</a>
      </nav>
    </div>
  </header>

  <main class="page">
    <div class="container form-container">
      <div class="form-card">
        <p class="eyebrow">CREATE ACCOUNT</p>
        <h1>Student Registration</h1>
        <p class="muted">Create your StudentHub account securely.</p>

        <?php if ($successMessage !== ''): ?>
          <div class="success-box" role="status" tabindex="-1"><?= htmlspecialchars($successMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
          <div class="error-box" role="alert" style="background:#fff5f1; border:1px solid #d99a85; color:#7e3420; padding:0.9rem 1rem; border-radius:8px; margin-bottom:1rem;">
            <strong>Please review the following:</strong>
            <ul style="margin:0.6rem 0 0; padding-left:1.25rem;">
              <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form id="registerForm" method="post" action="register.php" novalidate>
          <div class="form-grid">
            <label for="fullName">Full Name
              <input id="fullName" name="fullName" type="text" value="<?= oldValue($submittedData, 'fullName') ?>" placeholder="Enter full name" autocomplete="name" minlength="2" maxlength="50" required>
            </label>
            <label for="username">Username
              <input id="username" name="username" type="text" value="<?= oldValue($submittedData, 'username') ?>" placeholder="Choose a username" autocomplete="username" minlength="3" maxlength="20" required>
            </label>
            <label for="email">Email
              <input id="email" name="email" type="email" value="<?= oldValue($submittedData, 'email') ?>" placeholder="student@example.com" autocomplete="email" maxlength="254" required>
            </label>
            <label for="mobile">Mobile Number
              <input id="mobile" name="mobile" type="tel" value="<?= oldValue($submittedData, 'mobile') ?>" placeholder="10-digit mobile number" autocomplete="tel" inputmode="tel" maxlength="14" required>
            </label>
            <label for="course">Course
              <select id="course" name="course" required>
                <option value="">Select course</option>
                <option value="B.Tech Computer Engineering"<?= $submittedData['course'] === 'B.Tech Computer Engineering' ? ' selected' : '' ?>>B.Tech Computer Engineering</option>
                <option value="B.Tech Information Technology"<?= $submittedData['course'] === 'B.Tech Information Technology' ? ' selected' : '' ?>>B.Tech Information Technology</option>
                <option value="BCA"<?= $submittedData['course'] === 'BCA' ? ' selected' : '' ?>>BCA</option>
                <option value="MCA"<?= $submittedData['course'] === 'MCA' ? ' selected' : '' ?>>MCA</option>
              </select>
            </label>
            <label for="year">Year
              <select id="year" name="year" required>
                <option value="">Select year</option>
                <option value="1st Year"<?= $submittedData['year'] === '1st Year' ? ' selected' : '' ?>>1st Year</option>
                <option value="2nd Year"<?= $submittedData['year'] === '2nd Year' ? ' selected' : '' ?>>2nd Year</option>
                <option value="3rd Year"<?= $submittedData['year'] === '3rd Year' ? ' selected' : '' ?>>3rd Year</option>
                <option value="4th Year"<?= $submittedData['year'] === '4th Year' ? ' selected' : '' ?>>4th Year</option>
              </select>
            </label>
            <label for="gender">Gender
              <select id="gender" name="gender" required>
                <option value="">Select gender</option>
                <option value="Female"<?= $submittedData['gender'] === 'Female' ? ' selected' : '' ?>>Female</option>
                <option value="Male"<?= $submittedData['gender'] === 'Male' ? ' selected' : '' ?>>Male</option>
                <option value="Other"<?= $submittedData['gender'] === 'Other' ? ' selected' : '' ?>>Other</option>
              </select>
            </label>
            <label for="password">Password
              <input id="password" name="password" type="password" placeholder="Minimum 8 characters" autocomplete="new-password" minlength="8" required>
            </label>
            <label for="confirmPassword">Confirm Password
              <input id="confirmPassword" name="confirmPassword" type="password" placeholder="Re-enter password" autocomplete="new-password" required>
            </label>
          </div>

          <label class="terms" for="terms">
            <input id="terms" name="terms" type="checkbox" value="on"> I accept the StudentHub terms and conditions.
          </label>

          <button class="btn primary full" type="submit">Create Account</button>
          <p class="form-note">Already registered? <a href="login.html">Login here</a>.</p>
        </form>
      </div>
    </div>
  </main>

  <footer class="site-footer">
    <div class="container">
      <strong>StudentHub</strong>
      <p>Student portal project • Practical 9</p>
    </div>
  </footer>

  <script src="assets/js/validation.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
