<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("customer");

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Profile</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<nav class="navbar">

    <div class="nav-container">

        <a href="dashboard.php" class="logo">
            🍔 Smart Canteen
        </a>

        <a href="../logout.php" class="btn btn-danger">
            Logout
        </a>

    </div>

</nav>

<div class="container" style="max-width:700px;margin:40px auto;">

    <div class="cart-summary">

        <div style="
            text-align:center;
            font-size:70px;
        ">
            👤
        </div>

        <h1 style="text-align:center;">
            <?= htmlspecialchars($user['name']) ?>
        </h1>

        <br>

        <p>
            <strong>Email:</strong>
            <?= htmlspecialchars($user['email']) ?>
        </p>

        <p>
            <strong>Role:</strong>
            <?= htmlspecialchars($user['role']) ?>
        </p>

        <hr style="margin:25px 0;">

        <h2>🎨 Choose Theme</h2>

        <br>

        <button
            class="btn btn-primary"
            onclick="setTheme('theme-orange')">
            🍊 Orange
        </button>

        <button
            class="btn btn-success"
            onclick="setTheme('theme-green')">
            🍃 Green
        </button>

        <button
            class="btn"
            style="background:#1565c0;color:white;"
            onclick="setTheme('theme-blue')">
            💙 Blue
        </button>

        <button
            class="btn"
            style="background:#222;color:white;"
            onclick="setTheme('theme-dark')">
            🌙 Dark
        </button>

        <br><br>

        <a
            href="dashboard.php"
            class="btn btn-primary">
            ← Back Home
        </a>

    </div>

</div>

<script src="theme.js"></script>

</body>

</html>