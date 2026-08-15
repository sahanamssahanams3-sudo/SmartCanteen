<?php

require_once __DIR__ . '/includes/auth.php';

/*
|--------------------------------------------------------------------------
| If already logged in, redirect according to role
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['user'])) {
    redirectByRole();
}


/*
|--------------------------------------------------------------------------
| Users JSON file
|--------------------------------------------------------------------------
*/

$dataFile = __DIR__ . '/data/users.json';


/*
|--------------------------------------------------------------------------
| Load users
|--------------------------------------------------------------------------
*/

$users = file_exists($dataFile)
    ? json_decode(file_get_contents($dataFile), true)
    : [];

$users = is_array($users) ? $users : [];


/*
|--------------------------------------------------------------------------
| Default accounts
|
| These accounts are automatically created if they do not exist.
| They are NOT displayed on the login page.
|--------------------------------------------------------------------------
*/

$defaults = [

    [
        'id' => 1,
        'name' => 'Canteen Administrator',
        'email' => 'admin@canteen.com',
        'password' => password_hash(
            'admin123',
            PASSWORD_DEFAULT
        ),
        'role' => 'admin',
        'status' => 'active'
    ],

    [
        'id' => 2,
        'name' => 'Canteen Staff',
        'email' => 'staff@canteen.com',
        'password' => password_hash(
            'staff123',
            PASSWORD_DEFAULT
        ),
        'role' => 'staff',
        'status' => 'active'
    ],

    [
        'id' => 3,
        'name' => 'Demo Customer',
        'email' => 'user@canteen.com',
        'password' => password_hash(
            'user123',
            PASSWORD_DEFAULT
        ),
        'role' => 'customer',
        'status' => 'active'
    ]

];


/*
|--------------------------------------------------------------------------
| Add default accounts if they don't already exist
|--------------------------------------------------------------------------
*/

$changed = false;

foreach ($defaults as $default) {

    $found = false;

    foreach ($users as $user) {

        if (
            strtolower($user['email'] ?? '') ===
            strtolower($default['email'])
        ) {
            $found = true;
            break;
        }
    }

    if (!$found) {

        $users[] = $default;

        $changed = true;
    }
}


/*
|--------------------------------------------------------------------------
| Save users.json if default users were added
|--------------------------------------------------------------------------
*/

if ($changed) {

    file_put_contents(
        $dataFile,
        json_encode(
            $users,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ),
        LOCK_EX
    );
}


/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim(
        $_POST['email'] ?? ''
    );

    $password = $_POST['password'] ?? '';

    $loginSuccessful = false;


    foreach ($users as $user) {

        /*
        |--------------------------------------------------------------------------
        | Check email
        |--------------------------------------------------------------------------
        */

        if (
            strtolower($user['email'] ?? '') !==
            strtolower($email)
        ) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Check password
        |--------------------------------------------------------------------------
        */

        if (
            !password_verify(
                $password,
                $user['password'] ?? ''
            )
        ) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Check account status
        |--------------------------------------------------------------------------
        */

        if (
            isset($user['status']) &&
            strtolower($user['status']) !== 'active'
        ) {

            $error = 'Your account is in-active. Please contact the administrator.';

            break;
        }


        /*
        |--------------------------------------------------------------------------
        | Store logged-in user in session
        |--------------------------------------------------------------------------
        */

        $_SESSION['user'] = [

            'id' => $user['id'],

            'name' => $user['name'],

            'email' => $user['email'],

            'role' => $user['role']

        ];


        $loginSuccessful = true;


        /*
        |--------------------------------------------------------------------------
        | Redirect according to role
        |--------------------------------------------------------------------------
        */

        redirectByRole();

        break;
    }


    /*
    |--------------------------------------------------------------------------
    | Invalid login
    |--------------------------------------------------------------------------
    */

    if (!$loginSuccessful && $error === '') {

        $error = 'Invalid email or password.';
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

    <title>
        Login - Smart Canteen
    </title>


    <!-- Main CSS -->

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >


    <!-- Theme JavaScript -->

    <script
        src="assets/js/theme.js"
    ></script>

</head>


<body>


<!-- =========================================================
     LOGIN PAGE
========================================================= -->

<div class="auth-page">


    <!-- =====================================================
         LOGIN CARD
    ====================================================== -->

    <div class="auth-card">


        <!-- Logo -->

        <div class="auth-logo">
            🍔
        </div>


        <!-- Title -->

        <h1 class="auth-title">
            Smart Canteen
        </h1>


        <!-- =================================================
             Registration Success Message
        ================================================== -->

        <?php if (isset($_GET['registered']) && $_GET['registered'] === '1'): ?>

            <div
                style="
                    background:#d4edda;
                    color:#155724;
                    padding:12px;
                    border-radius:8px;
                    margin-bottom:20px;
                    text-align:center;
                "
            >

                ✅ Registration successful!
                Please login with your new account.

            </div>

        <?php endif; ?>


        <!-- =================================================
             Error Message
        ================================================== -->

        <?php if ($error): ?>

            <p class="alert-error">

                <?= h($error) ?>

            </p>

        <?php endif; ?>


        <!-- =================================================
             LOGIN FORM
        ================================================== -->

        <form method="POST">


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
                    autocomplete="email"
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
                    placeholder="Enter password"
                    autocomplete="current-password"
                    required
                >

            </div>


            <!-- Login Button -->

            <button
                type="submit"
                class="btn btn-primary full-btn"
            >

                🔐 Login

            </button>


        </form>


        <!-- =================================================
             Registration Link
        ================================================== -->

        <div class="auth-footer">

            <p>

                New user?

                <a href="register.php">
                    Create Account
                </a>

            </p>

        </div>


    </div>

</div>


</body>

</html>