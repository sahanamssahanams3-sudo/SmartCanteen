<?php
session_start();

/*
|--------------------------------------------------------------------------
| If already logged in, go to dashboard
|--------------------------------------------------------------------------
*/
if (isset($_SESSION['user'])) {
    header("Location: /smartcanteen/user/dashboard.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Users JSON file
|--------------------------------------------------------------------------
*/
$file = __DIR__ . "/data/users.json";

/*
|--------------------------------------------------------------------------
| Create users.json if it doesn't exist
|--------------------------------------------------------------------------
*/
if (!file_exists($file)) {
    file_put_contents($file, "[]");
}

/*
|--------------------------------------------------------------------------
| Read users
|--------------------------------------------------------------------------
*/
$users = json_decode(file_get_contents($file), true);

if (!is_array($users)) {
    $users = [];
}

$error = "";

/*
|--------------------------------------------------------------------------
| Registration
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirm = $_POST['confirm_password'] ?? "";

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($name === "" || $email === "" || $password === "") {

        $error = "Please fill all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email.";

    } elseif (strlen($password) < 6) {

        $error = "Password must contain at least 6 characters.";

    } elseif ($password !== $confirm) {

        $error = "Passwords do not match.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check if email already exists
        |--------------------------------------------------------------------------
        */

        $exists = false;

        foreach ($users as $user) {

            if (
                isset($user['email']) &&
                strtolower($user['email']) === strtolower($email)
            ) {
                $exists = true;
                break;
            }
        }

        if ($exists) {

            $error = "Email already registered.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | Generate a unique ID
            |--------------------------------------------------------------------------
            */

            $newId = 1;

            if (!empty($users)) {

                $ids = array_column($users, 'id');

                $numericIds = array_filter($ids, 'is_numeric');

                if (!empty($numericIds)) {
                    $newId = max($numericIds) + 1;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Create new customer account
            |--------------------------------------------------------------------------
            */

            $newUser = [
                "id" => $newId,
                "name" => $name,
                "email" => $email,
                "password" => password_hash(
                    $password,
                    PASSWORD_DEFAULT
                ),
                "role" => "customer",
                "status" => "active"
            ];

            /*
            |--------------------------------------------------------------------------
            | Add user to array
            |--------------------------------------------------------------------------
            */

            $users[] = $newUser;

            /*
            |--------------------------------------------------------------------------
            | Save users.json
            |--------------------------------------------------------------------------
            */

            $saved = file_put_contents(
                $file,
                json_encode(
                    $users,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                ),
                LOCK_EX
            );

            /*
            |--------------------------------------------------------------------------
            | If saved successfully, redirect to login page
            |--------------------------------------------------------------------------
            */

            if ($saved !== false) {

                header("Location: index.php?registered=1");
                exit;

            } else {

                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Register - Smart Canteen</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

<div class="auth-page">

    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            🍔
        </div>

        <!-- Title -->
        <h1 class="auth-title">
            Create Account
        </h1>

        <!-- Error Message -->
        <?php if ($error): ?>

            <div
                style="
                    background:#f8d7da;
                    color:#721c24;
                    padding:12px;
                    border-radius:8px;
                    margin-bottom:20px;
                "
            >

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <!-- Registration Form -->
        <form method="POST">

            <!-- Name -->
            <div class="form-group">

                <label for="name">
                    Full Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    placeholder="Enter your name"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    required
                >

            </div>


            <!-- Email -->
            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter your email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                >

            </div>


            <!-- Password -->
            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Minimum 6 characters"
                    required
                >

            </div>


            <!-- Confirm Password -->
            <div class="form-group">

                <label for="confirm_password">
                    Confirm Password
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    class="form-control"
                    placeholder="Confirm password"
                    required
                >

            </div>


            <!-- Register Button -->
            <button
                type="submit"
                class="btn btn-primary full-btn"
            >
                📝 Create Account
            </button>

        </form>


        <!-- Login Link -->
        <div class="auth-footer">

            Already have an account?

            <a href="index.php">
                Login
            </a>

        </div>

    </div>

</div>

</body>

</html>