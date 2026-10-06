<?php
declare(strict_types=1);

session_start();

const STORAGE_DIRECTORY = __DIR__ . '/storage';

function escape_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function clean_text(string $value): string
{
    $value = strip_tags(trim($value));
    return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
}

function posted_string(string $key): string
{
    return isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : '';
}

function save_record(string $fileName, array $record): void
{
    if (!is_dir(STORAGE_DIRECTORY) && !mkdir(STORAGE_DIRECTORY, 0700, true) && !is_dir(STORAGE_DIRECTORY)) {
        throw new RuntimeException('Storage directory could not be created.');
    }

    $path = STORAGE_DIRECTORY . '/' . $fileName;
    $handle = fopen($path, 'c+');
    if ($handle === false) {
        throw new RuntimeException('Storage file could not be opened.');
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException('Storage file could not be locked.');
        }

        $contents = stream_get_contents($handle);
        $records = $contents === false || trim($contents) === '' ? [] : json_decode($contents);
        if (!is_array($records) || (json_last_error() !== JSON_ERROR_NONE && trim($contents) !== '')) {
            throw new RuntimeException('Storage file contains invalid JSON.');
        }

        $records[] = $record;
        $json = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false || !rewind($handle) || !ftruncate($handle, 0) || fwrite($handle, $json . PHP_EOL) === false || !fflush($handle)) {
            throw new RuntimeException('Record could not be written.');
        }
        flock($handle, LOCK_UN);
    } finally {
        fclose($handle);
    }
}

function redirect_after_post(string $anchor): void
{
    header('Location: practical-7.php#' . $anchor, true, 303);
    exit;
}

$course_options = [
    'B.Tech Computer Engineering',
    'B.Tech Information Technology',
    'BCA',
    'MCA'
];
$year_options = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_type = posted_string('form_type');
    $anchor = $form_type === 'contact' ? 'contact' : 'registration';
    $errors = [];
    $old = [];
    $csrf_token = posted_string('csrf_token');
    $session_token = isset($_SESSION['csrf_token']) && is_string($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';

    if (!in_array($form_type, ['registration', 'contact'], true)) {
        $errors[] = 'Choose a valid form before submitting.';
    }
    if ($session_token === '' || !hash_equals($session_token, $csrf_token)) {
        $errors[] = 'Your form session expired. Please try again.';
    }

    if ($form_type === 'registration') {
        $name = clean_text(posted_string('name'));
        $email = strtolower(trim(posted_string('email')));
        $mobile = trim(posted_string('mobile'));
        $course = clean_text(posted_string('course'));
        $year = clean_text(posted_string('year'));
        $password = posted_string('password');
        $confirm_password = posted_string('confirm_password');
        $old = ['name' => $name, 'email' => $email, 'mobile' => $mobile, 'course' => $course, 'year' => $year];

        if (!preg_match("/^[\\p{L}][\\p{L} .'-]{0,78}[\\p{L}]$/u", $name)) {
            $errors[] = 'Enter a name using 2 to 80 letters, spaces, apostrophes or hyphens.';
        }
        if (strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Enter a valid email address.';
        }
        if (!preg_match('/^(?:\\+91[ -]?)?[6-9]\\d{9}$/', $mobile)) {
            $errors[] = 'Enter a valid 10-digit mobile number, optionally prefixed with +91.';
        }
        if (!in_array($course, $course_options, true)) {
            $errors[] = 'Select a course from the list.';
        }
        if (!in_array($year, $year_options, true)) {
            $errors[] = 'Select a year from the list.';
        }
        if (strlen($password) < 8 || strlen($password) > 128 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\\d/', $password) || !preg_match('/[^A-Za-z\\d]/', $password)) {
            $errors[] = 'Use 8 to 128 characters with at least one letter, number and symbol.';
        }
        if ($password !== $confirm_password) {
            $errors[] = 'The password confirmation does not match.';
        }
        if (posted_string('terms') !== 'accepted') {
            $errors[] = 'Accept the terms to submit your registration.';
        }

        if ($errors === []) {
            try {
                save_record('registrations.json', [
                    'id' => bin2hex(random_bytes(8)),
                    'name' => $name,
                    'email' => $email,
                    'mobile' => $mobile,
                    'course' => $course,
                    'year' => $year,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'created_at' => gmdate('c')
                ]);
                $_SESSION['flash'] = 'Registration received. Your details were saved securely.';
            } catch (Throwable $error) {
                error_log('StudentHub registration storage error: ' . $error->getMessage());
                $errors[] = 'Your registration could not be saved. Please try again.';
            }
        }
    } elseif ($form_type === 'contact') {
        $name = clean_text(posted_string('name'));
        $email = strtolower(trim(posted_string('email')));
        $message = clean_text(posted_string('message'));
        $old = ['name' => $name, 'email' => $email, 'message' => $message];

        if (!preg_match("/^[\\p{L}][\\p{L} .'-]{0,78}[\\p{L}]$/u", $name)) {
            $errors[] = 'Enter a name using 2 to 80 letters, spaces, apostrophes or hyphens.';
        }
        if (strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Enter a valid email address.';
        }
        if (strlen($message) < 10 || strlen($message) > 5000) {
            $errors[] = 'Enter a message between 10 and 5000 characters.';
        }

        if ($errors === []) {
            try {
                save_record('contact-messages.json', [
                    'id' => bin2hex(random_bytes(8)),
                    'name' => $name,
                    'email' => $email,
                    'message' => $message,
                    'created_at' => gmdate('c')
                ]);
                $_SESSION['flash'] = 'Message received. Thank you for contacting StudentHub.';
            } catch (Throwable $error) {
                error_log('StudentHub contact storage error: ' . $error->getMessage());
                $errors[] = 'Your message could not be saved. Please try again.';
            }
        }
    }

    if ($errors !== []) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_old'] = $old;
    } else {
        unset($_SESSION['form_errors'], $_SESSION['form_old']);
    }
    $_SESSION['active_form'] = $anchor;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    redirect_after_post($errors === [] ? $anchor : 'formFeedback');
}

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$flash = isset($_SESSION['flash']) && is_string($_SESSION['flash']) ? $_SESSION['flash'] : '';
$errors = isset($_SESSION['form_errors']) && is_array($_SESSION['form_errors']) ? $_SESSION['form_errors'] : [];
$old = isset($_SESSION['form_old']) && is_array($_SESSION['form_old']) ? $_SESSION['form_old'] : [];
$active_form = isset($_SESSION['active_form']) && is_string($_SESSION['active_form']) ? $_SESSION['active_form'] : 'registration';
$registration_old = $active_form === 'registration' ? $old : [];
$contact_old = $active_form === 'contact' ? $old : [];
unset($_SESSION['flash'], $_SESSION['form_errors'], $_SESSION['form_old'], $_SESSION['active_form']);

function old_value(array $old, string $key): string
{
    return isset($old[$key]) && is_string($old[$key]) ? escape_html($old[$key]) : '';
}

function selected_option(array $old, string $key, string $option): string
{
    return isset($old[$key]) && $old[$key] === $option ? ' selected' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Submit registration and contact forms with server-side validation and JSON storage.">
  <title>Practical 7 Server Forms | StudentHub</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .server-forms{padding:48px 0 72px}.server-intro{max-width:760px;margin-bottom:28px}.server-intro h1{margin:0 0 8px}.server-intro>p:last-child{color:#64748b}.server-form-grid{align-items:start}.server-form{display:grid;gap:14px}.server-form .form-grid{align-items:start}.server-form textarea{width:100%;resize:vertical}.server-status{padding:14px 16px;border:1px solid #79b9a4;border-radius:8px;background:#edfaf5;color:#145944}.server-errors{padding:14px 16px;border:1px solid #d99a85;border-radius:8px;background:#fff5f1;color:#7e3420}.server-errors ul{margin:8px 0 0;padding-left:20px}.server-form .muted{margin:0}.server-form [aria-invalid="true"]{border-color:#b5412b}.dark-theme .server-intro>p:last-child{color:#a9b8c9}.dark-theme .server-status{background:#15372f;color:#a7f3d0;border-color:#28745f}.dark-theme .server-errors{background:#38231f;color:#ffd1c2;border-color:#75483c}
  </style>
</head>
<body>
<header class="site-header"><div class="container nav-wrap"><a class="brand" href="index.html">Student<span>Hub</span></a><nav id="mainNav" aria-label="Main navigation"><a href="index.html">Home</a><a href="events.html">Events</a><a href="practical-6.html">Data Browser</a><a class="active" href="practical-7.php" aria-current="page">Server Forms</a></nav></div></header>
<main class="page server-forms"><div class="container">
  <div class="server-intro"><p class="eyebrow">PRACTICAL 7 · PHP POST</p><h1>Server-processed forms</h1><p>Registration and contact submissions are validated on the server and saved to protected JSON files.</p></div>
  <?php if ($flash !== ''): ?><p class="server-status" role="status"><?= escape_html($flash) ?></p><?php endif; ?>
    <?php if ($errors !== []): ?><div class="server-errors" id="formFeedback" role="alert"><strong>Please review the following:</strong><ul><?php foreach ($errors as $error): ?><li><?= escape_html((string) $error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <div class="card-grid two server-form-grid">
    <section class="form-card" id="registration" aria-labelledby="registrationHeading"><p class="eyebrow">NEW STUDENT</p><h2 id="registrationHeading">Registration</h2>
      <form class="server-form" action="practical-7.php#registration" method="post" accept-charset="UTF-8">
        <input type="hidden" name="form_type" value="registration"><input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
        <div class="form-grid">
          <label for="registrationName">Full name<input id="registrationName" name="name" type="text" value="<?= old_value($registration_old, 'name') ?>" minlength="2" maxlength="80" autocomplete="name" required></label>
          <label for="registrationEmail">Email<input id="registrationEmail" name="email" type="email" value="<?= old_value($registration_old, 'email') ?>" maxlength="254" autocomplete="email" required></label>
          <label for="registrationMobile">Mobile number<input id="registrationMobile" name="mobile" type="tel" value="<?= old_value($registration_old, 'mobile') ?>" inputmode="tel" maxlength="14" autocomplete="tel" required></label>
          <label for="registrationCourse">Course<select id="registrationCourse" name="course" required><option value="">Select course</option><?php foreach ($course_options as $option): ?><option value="<?= escape_html($option) ?>"<?= selected_option($registration_old, 'course', $option) ?>><?= escape_html($option) ?></option><?php endforeach; ?></select></label>
          <label for="registrationYear">Year<select id="registrationYear" name="year" required><option value="">Select year</option><?php foreach ($year_options as $option): ?><option value="<?= escape_html($option) ?>"<?= selected_option($registration_old, 'year', $option) ?>><?= escape_html($option) ?></option><?php endforeach; ?></select></label>
          <label for="registrationPassword">Password<input id="registrationPassword" name="password" type="password" minlength="8" maxlength="128" autocomplete="new-password" required></label>
          <label for="registrationConfirm">Confirm password<input id="registrationConfirm" name="confirm_password" type="password" minlength="8" maxlength="128" autocomplete="new-password" required></label>
        </div>
        <label class="terms" for="registrationTerms"><input id="registrationTerms" name="terms" type="checkbox" value="accepted" required> I accept the StudentHub terms.</label>
        <p class="muted">Passwords are stored as one-way hashes, never as plain text.</p>
        <button class="btn primary full" type="submit">Submit registration</button>
      </form>
    </section>
    <section class="form-card" id="contact" aria-labelledby="contactHeading"><p class="eyebrow">GET IN TOUCH</p><h2 id="contactHeading">Contact StudentHub</h2>
      <form class="server-form" action="practical-7.php#contact" method="post" accept-charset="UTF-8">
        <input type="hidden" name="form_type" value="contact"><input type="hidden" name="csrf_token" value="<?= escape_html($csrf_token) ?>">
        <label for="contactName">Name<input id="contactName" name="name" type="text" value="<?= old_value($contact_old, 'name') ?>" minlength="2" maxlength="80" autocomplete="name" required></label>
        <label for="contactEmail">Email<input id="contactEmail" name="email" type="email" value="<?= old_value($contact_old, 'email') ?>" maxlength="254" autocomplete="email" required></label>
        <label for="contactMessage">Message<textarea id="contactMessage" name="message" rows="6" minlength="10" maxlength="5000" required><?= old_value($contact_old, 'message') ?></textarea></label>
        <button class="btn primary" type="submit">Send message</button>
      </form>
    </section>
  </div>
</div></main>
<footer class="site-footer"><div class="container"><strong>StudentHub</strong><p>Student portal project · Practical 7</p></div></footer>
</body>
</html>
