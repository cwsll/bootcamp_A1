
<?php

// ================================================
//          SQL INJECTION DEFENSE TOOL
// ================================================

header("Content-Type: text/html; charset=UTF-8");


// ================================================
//          DATABASE CONNECTION
// ================================================

try {

    $pdo = new PDO("sqlite::memory:");

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create Users Table

    $pdo->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            role TEXT NOT NULL
        )
    ");

    // Insert Test Users

    $pdo->exec("
        INSERT INTO users (username, role) VALUES
        ('admin', 'Administrator'),
        ('alice', 'User'),
        ('bob', 'User')
    ");

} catch (PDOException $err) {

    http_response_code(500);
    die("Database connection failed.");

}


// ================================================
//          GET USER INPUT
// ================================================

$username = $_GET["username"] ?? "";
$mode = $_GET["mode"] ?? "safe";

$results = [];
$error = "";
$query = "";


// ================================================
//          VALIDATE INPUT
// ================================================

if (!is_string($username) || !is_string($mode)) {

    $error = "Invalid input.";

} elseif (!in_array($mode, ["safe", "unsafe"], true)) {

    $error = "Invalid operation mode.";

} elseif ($username !== "") {

    try {

        // ========================================
        //          UNSAFE QUERY
        // ========================================

        if ($mode === "unsafe") {

            $query = "SELECT id, username, role FROM users WHERE username = '$username'";

            $results = $pdo->query($query)->fetchAll();

        }

        // ========================================
        //          SAFE QUERY
        // ========================================

        else {

            $query = "SELECT id, username, role FROM users WHERE username = :username";

            $statement = $pdo->prepare($query);

            $statement->execute([
                ":username" => $username
            ]);

            $results = $statement->fetchAll();

        }

    } catch (PDOException $err) {

        $error = "The request could not be processed.";

    }

}


// ================================================
//          DISPLAY HELPER
// ================================================

function clean($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SQL Injection Defense Tool</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 15px;
            background: #101827;
            color: #e5e7eb;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 850px;
            margin: auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #f59e0b;
            margin-bottom: 10px;
        }

        .header p {
            color: #9ca3af;
        }

        .panel {
            background: #1e293b;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid #334155;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 13px;
            margin-bottom: 18px;
            border: 1px solid #475569;
            border-radius: 7px;
            background: #0f172a;
            color: white;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #f59e0b;
            border: none;
            border-radius: 7px;
            color: #111827;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #d97706;
        }

        .safe {
            color: #4ade80;
        }

        .unsafe {
            color: #f87171;
        }

        .message {
            padding: 13px;
            background: #0f172a;
            border-radius: 7px;
            margin-top: 15px;
            overflow-wrap: anywhere;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #475569;
            text-align: left;
        }

        th {
            color: #f59e0b;
        }

        .empty {
            color: #fbbf24;
            margin-top: 15px;
        }

        .footer {
            text-align: center;
            color: #64748b;
            margin-top: 30px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="header">

        <h1>SQL INJECTION DEFENSE</h1>

        <p>
            Compare vulnerable SQL queries with secure PDO Prepared Statements.
        </p>

    </div>


    <!-- SEARCH FORM -->

    <div class="panel">

        <h2>Database User Search</h2>

        <form method="GET">

            <label>Username</label>

            <input
                type="text"
                name="username"
                placeholder="Enter username..."
                value="<?= clean($username) ?>"
                required
            >

            <label>Security Mode</label>

            <select name="mode">

                <option value="safe" <?= $mode === "safe" ? "selected" : "" ?>>
                    Safe Mode
                </option>

                <option value="unsafe" <?= $mode === "unsafe" ? "selected" : "" ?>>
                    Unsafe Mode
                </option>

            </select>

            <button type="submit">
                Search User
            </button>

        </form>

    </div>


    <!-- RESULTS -->

    <?php if ($username !== ""): ?>

        <div class="panel">

            <h2>Search Results</h2>

            <?php if ($error !== ""): ?>

                <p class="unsafe">
                    <?= clean($error) ?>
                </p>

            <?php else: ?>

                <p>
                    Selected Mode:

                    <strong class="<?= $mode === "safe" ? "safe" : "unsafe" ?>">

                        <?= strtoupper(clean($mode)) ?>

                    </strong>

                </p>

                <div class="message">

                    <strong>SQL Query:</strong>

                    <p><?= clean($query) ?></p>

                </div>

                <p>
                    Total Results:
                    <strong><?= count($results) ?></strong>
                </p>

                <?php if (count($results) > 0): ?>

                    <table>

                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Role</th>
                        </tr>

                        <?php foreach ($results as $user): ?>

                            <tr>

                                <td><?= clean($user["id"]) ?></td>

                                <td><?= clean($user["username"]) ?></td>

                                <td><?= clean($user["role"]) ?></td>

                            </tr>

                        <?php endforeach; ?>

                    </table>

                <?php else: ?>

                    <p class="empty">
                        No matching users found.
                    </p>

                <?php endif; ?>

                <?php if ($mode === "unsafe" && count($results) > 1): ?>

                    <p class="unsafe">
                        Warning: SQL Injection detected!
                        Multiple users were returned.
                    </p>

                <?php elseif ($mode === "safe"): ?>

                    <p class="safe">
                        Prepared Statement executed successfully.
                    </p>

                <?php endif; ?>

            <?php endif; ?>

        </div>

    <?php endif; ?>


    <div class="footer">

        Cyber Security Toolkit | SQL Injection Defense

    </div>

</div>

</body>

</html>
