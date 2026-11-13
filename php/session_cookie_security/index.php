
<?php

// ================================================
// 1. SESSION SECURITY CONFIGURATION
// ================================================

$isSecure = (
    !empty($_SERVER['HTTPS']) &&
    $_SERVER['HTTPS'] !== 'off'
);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();


// ================================================
// 2. HELPER FUNCTION
// ================================================

function escape($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


// ================================================
// 3. CSRF TOKEN
// ================================================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$messageType = '';


// ================================================
// 4. LOGIN AND LOGOUT PROCESSING
// ================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $submittedToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {

        $message = 'Security check failed. Request rejected.';
        $messageType = 'error';

    } elseif (isset($_POST['login'])) {

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username !== '' && $password !== '') {

            // Save the old session ID for comparison
            $oldSessionId = session_id();

            // Regenerate session ID after login
            session_regenerate_id(true);

            $newSessionId = session_id();

            $_SESSION['username'] = $username;
            $_SESSION['logged_in'] = true;

            // Store fingerprints instead of displaying real IDs
            $_SESSION['old_fingerprint'] =
                substr(hash('sha256', $oldSessionId), 0, 12);

            $_SESSION['new_fingerprint'] =
                substr(hash('sha256', $newSessionId), 0, 12);

            $_SESSION['session_changed'] =
                ($oldSessionId !== $newSessionId);

            $message = 'Login successful! Session ID regenerated.';
            $messageType = 'success';

        } else {

            $message = 'Please enter both username and password.';
            $messageType = 'error';
        }

    } elseif (isset($_POST['logout'])) {

        // Clear session data
        $_SESSION = [];

        // Generate a new session ID
        session_regenerate_id(true);

        // Create a new CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $message = 'You have logged out successfully.';
        $messageType = 'success';
    }
}


// ================================================
// 5. COOKIE SECURITY COMPARISON
// ================================================

// Protected cookie
setcookie('demo_protected', 'Protected Cookie', [
    'expires' => time() + 3600,
    'path' => '/',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Unprotected cookie
// HttpOnly is disabled for testing purposes
setcookie('demo_unprotected', 'Unprotected Cookie', [
    'expires' => time() + 3600,
    'path' => '/',
    'secure' => $isSecure,
    'httponly' => false,
    'samesite' => 'Strict'
]);


// ================================================
// 6. INSPECT ACTUAL SET-COOKIE HEADERS
// ================================================

$cookieHeaders = headers_list();
$cookieDetails = [];

foreach ($cookieHeaders as $header) {

    if (stripos($header, 'Set-Cookie:') === 0) {

        $cookieParts = explode(';', $header);

        // Read cookie name without exposing its value
        $firstPart = trim(substr($cookieParts[0], 11));
        $cookieName = explode('=', $firstPart)[0];

        $httpOnly = false;
        $secureFlag = false;
        $sameSite = 'Not Set';

        foreach ($cookieParts as $part) {

            $part = trim($part);

            if (strcasecmp($part, 'HttpOnly') === 0) {
                $httpOnly = true;
            }

            if (strcasecmp($part, 'Secure') === 0) {
                $secureFlag = true;
            }

            if (stripos($part, 'SameSite=') === 0) {
                $sameSite = substr($part, 9);
            }
        }

        $cookieDetails[] = [
            'name' => $cookieName,
            'httponly' => $httpOnly,
            'secure' => $secureFlag,
            'samesite' => $sameSite
        ];
    }
}


// ================================================
// 7. SESSION INFORMATION
// ================================================

$isLoggedIn = !empty($_SESSION['logged_in']);

$username = $_SESSION['username'] ?? 'Guest';

$oldFingerprint = $_SESSION['old_fingerprint'] ?? 'Not tested';

$newFingerprint = $_SESSION['new_fingerprint'] ?? 'Not tested';

$sessionChanged = $_SESSION['session_changed'] ?? false;

$sessionStatus = $isLoggedIn ? 'Authenticated' : 'Not Logged In';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Session & Cookie Security</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #0b1120;
            color: #e2e8f0;
            min-height: 100vh;
            padding: 30px 15px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
        }

        header h1 {
            color: #38bdf8;
            font-size: 30px;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        header p {
            color: #94a3b8;
            font-size: 14px;
        }

        .status-bar {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            background: #172033;
            border: 1px solid #263449;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 22px;
        }

        .status-item {
            font-size: 13px;
            color: #94a3b8;
        }

        .status-item strong {
            display: block;
            color: #38bdf8;
            margin-top: 6px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .card {
            background: #172033;
            border: 1px solid #263449;
            padding: 23px;
            border-radius: 14px;
            margin-bottom: 2px;
        }

        .card h2 {
            color: #38bdf8;
            font-size: 17px;
            margin-bottom: 17px;
        }

        .card p {
            color: #cbd5e1;
            font-size: 13px;
            line-height: 1.8;
        }

        .full {
            grid-column: 1 / -1;
        }

        input {
            width: 100%;
            padding: 13px;
            margin-bottom: 13px;
            background: #0b1120;
            color: white;
            border: 1px solid #334155;
            border-radius: 8px;
            outline: none;
        }

        input:focus {
            border-color: #38bdf8;
        }

        button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #0284c7;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }

        button:hover {
            background: #0369a1;
        }

        .logout {
            background: #dc2626;
        }

        .logout:hover {
            background: #b91c1c;
        }

        .message {
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success {
            background: #064e3b;
            color: #6ee7b7;
            border: 1px solid #059669;
        }

        .error {
            background: #7f1d1d;
            color: #fecaca;
            border: 1px solid #dc2626;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #334155;
            font-size: 13px;
        }

        .info-row span:first-child {
            color: #cbd5e1;
        }

        .info-row span:last-child {
            color: #38bdf8;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .tag {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: bold;
            margin: 5px 0 12px;
        }

        .safe {
            background: #064e3b;
            color: #6ee7b7;
        }

        .unsafe {
            background: #7f1d1d;
            color: #fecaca;
        }

        .test-box {
            background: #0b1120;
            border: 1px solid #334155;
            border-radius: 9px;
            padding: 16px;
            margin-top: 15px;
            font-size: 13px;
            line-height: 2;
            overflow-wrap: anywhere;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            min-width: 550px;
        }

        th, td {
            text-align: left;
            padding: 13px 10px;
            border-bottom: 1px solid #334155;
        }

        th {
            color: #38bdf8;
        }

        td {
            color: #cbd5e1;
        }

        .yes {
            color: #6ee7b7;
            font-weight: bold;
        }

        .no {
            color: #fca5a5;
            font-weight: bold;
        }

        footer {
            text-align: center;
            color: #64748b;
            font-size: 12px;
            margin-top: 30px;
        }

        @media (max-width: 700px) {

            .grid {
                grid-template-columns: 1fr;
            }

            .full {
                grid-column: auto;
            }

            header h1 {
                font-size: 23px;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <header>
        <h1>SESSION & COOKIE SECURITY</h1>
        <p>PHP Authentication and Security Testing Dashboard</p>
    </header>


    <?php if ($message !== ''): ?>

        <div class="message <?= escape($messageType) ?>">
            <?= escape($message) ?>
        </div>

    <?php endif; ?>


    <!-- SYSTEM STATUS -->

    <div class="status-bar">

        <div class="status-item">
            SESSION STATUS
            <strong><?= escape($sessionStatus) ?></strong>
        </div>

        <div class="status-item">
            CONNECTION
            <strong><?= $isSecure ? 'HTTPS' : 'HTTP' ?></strong>
        </div>

        <div class="status-item">
            CURRENT USER
            <strong><?= escape($username) ?></strong>
        </div>

    </div>


    <div class="grid">


        <!-- LOGIN -->

        <div class="card">

            <h2>01. Authentication</h2>

            <?php if (!$isLoggedIn): ?>

                <p>Enter your details to simulate user authentication.</p>

                <br>

                <form method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= escape($_SESSION['csrf_token']) ?>"
                    >

                    <input
                        type="text"
                        name="username"
                        placeholder="Enter Username"
                        required
                    >

                    <input
                        type="password"
                        name="password"
                        placeholder="Enter Password"
                        required
                    >

                    <button type="submit" name="login">
                        LOGIN
                    </button>

                </form>

                <p style="margin-top:12px;color:#94a3b8;">
                    Demo mode: Any non-empty username and password work.
                </p>

            <?php else: ?>

                <p>
                    Welcome,
                    <strong><?= escape($username) ?></strong>
                </p>

                <br>

                <form method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= escape($_SESSION['csrf_token']) ?>"
                    >

                    <button
                        type="submit"
                        name="logout"
                        class="logout"
                    >
                        LOGOUT
                    </button>

                </form>

            <?php endif; ?>

        </div>


        <!-- SESSION PROTECTION -->

        <div class="card">

            <h2>02. Session Protection</h2>

            <div class="info-row">
                <span>Session Started</span>
                <span class="yes">Active</span>
            </div>

            <div class="info-row">
                <span>HttpOnly</span>
                <span class="yes">Enabled</span>
            </div>

            <div class="info-row">
                <span>SameSite</span>
                <span class="yes">Strict</span>
            </div>

            <div class="info-row">
                <span>Secure Flag</span>
                <span><?= $isSecure ? 'Enabled' : 'Disabled on HTTP' ?></span>
            </div>

            <div class="info-row">
                <span>Session ID Regeneration</span>
                <span><?= $sessionChanged ? 'Passed' : 'Login to test' ?></span>
            </div>

        </div>


        <!-- SESSION FIXATION -->

        <div class="card">

            <h2>03. Session Fixation Test</h2>

            <p>
                The session ID is regenerated after successful login.
                Fingerprints are displayed instead of real session IDs.
            </p>

            <br>

            <div class="info-row">
                <span>Previous ID Fingerprint</span>
                <span><?= escape($oldFingerprint) ?></span>
            </div>

            <div class="info-row">
                <span>New ID Fingerprint</span>
                <span><?= escape($newFingerprint) ?></span>
            </div>

            <div class="info-row">
                <span>Test Result</span>

                <span class="<?= $sessionChanged ? 'yes' : 'no' ?>">
                    <?= $sessionChanged ? 'ID CHANGED' : 'Not Tested' ?>
                </span>
            </div>

        </div>


        <!-- COOKIE COMPARISON -->

        <div class="card">

            <h2>04. Cookie Security Comparison</h2>

            <p><strong>Protected Cookie</strong></p>

            <span class="tag safe">HttpOnly = TRUE</span>

            <div class="info-row">
                <span>JavaScript Access</span>
                <span>Blocked</span>
            </div>

            <br>

            <p><strong>Unprotected Cookie</strong></p>

            <span class="tag unsafe">HttpOnly = FALSE</span>

            <div class="info-row">
                <span>JavaScript Access</span>
                <span>Allowed</span>
            </div>

            <p style="margin-top:12px;color:#94a3b8;">
                Both cookies use the same SameSite and Secure settings.
                Only HttpOnly is different.
            </p>

        </div>


        <!-- JAVASCRIPT TEST -->

        <div class="card full">

            <h2>05. JavaScript Cookie Inspection</h2>

            <p>
                JavaScript attempts to read both cookies using
                document.cookie.
            </p>

            <div class="test-box" id="cookieResult">
                Running security test...
            </div>

        </div>


        <!-- ACTUAL COOKIE HEADERS -->

        <div class="card full">

            <h2>06. Set-Cookie Header Inspection</h2>

            <p>
                These are the cookie headers generated by PHP
                in the current response. Cookie values are hidden.
            </p>

            <br>

            <div class="table-wrap">

                <table>

                    <thead>
                        <tr>
                            <th>Cookie Name</th>
                            <th>HttpOnly</th>
                            <th>Secure</th>
                            <th>SameSite</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (count($cookieDetails) > 0): ?>

                        <?php foreach ($cookieDetails as $cookie): ?>

                            <tr>

                                <td><?= escape($cookie['name']) ?></td>

                                <td class="<?= $cookie['httponly'] ? 'yes' : 'no' ?>">
                                    <?= $cookie['httponly'] ? 'Yes' : 'No' ?>
                                </td>

                                <td class="<?= $cookie['secure'] ? 'yes' : 'no' ?>">
                                    <?= $cookie['secure'] ? 'Yes' : 'No' ?>
                                </td>

                                <td><?= escape($cookie['samesite']) ?></td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4">
                                No Set-Cookie headers available in this response.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <p style="margin-top:15px;color:#94a3b8;">
                Note: PHP may not resend the session cookie on every
                request if the browser already has it.
            </p>

        </div>


        <!-- SECURITY SUMMARY -->

        <div class="card full">

            <h2>07. Security Configuration Summary</h2>

            <div class="info-row">
                <span>Session HttpOnly</span>
                <span class="yes">Enabled</span>
            </div>

            <div class="info-row">
                <span>Session SameSite</span>
                <span class="yes">Strict</span>
            </div>

            <div class="info-row">
                <span>Session Secure</span>
                <span><?= $isSecure ? 'Enabled' : 'Disabled on HTTP' ?></span>
            </div>

            <div class="info-row">
                <span>CSRF Protection</span>
                <span class="yes">Enabled</span>
            </div>

            <div class="info-row">
                <span>Session ID Regeneration</span>
                <span class="yes">After Login</span>
            </div>

            <div class="info-row">
                <span>Cookie Inspection</span>
                <span class="yes">Enabled</span>
            </div>

        </div>

    </div>


    <footer>
        Assignment 13 | Session & Cookie Security | PHP
    </footer>

</div>


<script>

// ================================================
// 8. JAVASCRIPT COOKIE SECURITY TEST
// ================================================

const cookies = document.cookie;

const protectedFound =
    cookies.includes('demo_protected=');

const unprotectedFound =
    cookies.includes('demo_unprotected=');

const result = document.getElementById('cookieResult');

if (!protectedFound && unprotectedFound) {

    result.innerHTML = `
        <strong>Protected Cookie:</strong>
        <span class="yes">
            Not Accessible - HttpOnly is working
        </span>
        <br>

        <strong>Unprotected Cookie:</strong>
        <span class="yes">
            Visible - JavaScript can read it
        </span>
        <br>

        <strong>Security Test:</strong>
        <span class="yes">PASSED</span>
    `;

} else {

    result.innerHTML = `
        <strong>Protected Cookie:</strong>
        ${protectedFound ? 'Unexpectedly visible' : 'Not visible'}
        <br>

        <strong>Unprotected Cookie:</strong>
        ${unprotectedFound ? 'Visible' : 'Not found'}
        <br>

        <strong>Security Test:</strong>
        <span class="no">
            Check cookies or refresh the page
        </span>
    `;
}

</script>

</body>
</html>
