<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("customer");

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

$file = __DIR__ . "/../data/orders.json";

$orders = [];

if (file_exists($file)) {
    $orders = json_decode(
        file_get_contents($file),
        true
    );
}

if (!is_array($orders)) {
    $orders = [];
}

$userOrders = [];

foreach ($orders as $order) {

    if (
        $order['user_id'] ==
        $_SESSION['user']['id']
    ) {
        $userOrders[] = $order;
    }
}

$userOrders = array_reverse($userOrders);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>My Orders</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<nav class="navbar">

    <div class="nav-container">

        <a href="dashboard.php" class="logo">
            🍔 Smart Canteen
        </a>

        <div class="nav-links">

            <a href="dashboard.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="cart.php">🛒 Cart</a>
            <a href="orders.php">Orders</a>
            <a href="profile.php">Profile</a>
            <a href="../logout.php">Logout</a>

        </div>

    </div>

</nav>

<div class="container" style="padding:40px 0;">

    <h1 class="section-title">
        📦 My Orders
    </h1>

    <?php if (empty($userOrders)): ?>

        <div class="empty">

            <h2>No orders yet.</h2>

            <br>

            <a
                href="menu.php"
                class="btn btn-primary">
                🍔 Start Ordering
            </a>

        </div>

    <?php endif; ?>

    <?php foreach ($userOrders as $order): ?>

        <?php

        $statusClass = "status-new";

        if ($order['status'] === "preparing") {
            $statusClass = "status-preparing";
        }

        if ($order['status'] === "ready") {
            $statusClass = "status-ready";
        }

        if ($order['status'] === "completed") {
            $statusClass = "status-completed";
        }

        if ($order['status'] === "cancelled") {
            $statusClass = "status-cancelled";
        }

        ?>

        <div class="order-card">

            <div class="order-header">

                <div>

                    <h2>
                        #<?= htmlspecialchars($order['order_id']) ?>
                    </h2>

                    <p>
                        <?= htmlspecialchars($order['created_at']) ?>
                    </p>

                </div>

                <span class="status <?= $statusClass ?>">

                    <?= ucfirst(
                        htmlspecialchars($order['status'])
                    ) ?>

                </span>

            </div>

            <?php foreach ($order['items'] as $item): ?>

                <p style="margin:8px 0;">

                    <?= htmlspecialchars($item['name']) ?>

                    × <?= $item['quantity'] ?>

                    =

                    <strong>
                        ₹<?= $item['price'] * $item['quantity'] ?>
                    </strong>

                </p>

            <?php endforeach; ?>

            <hr style="margin:15px 0;">

            <p>
                Pickup:
                <strong>
                    <?= htmlspecialchars($order['pickup_time']) ?>
                </strong>
            </p>

            <p>
                Payment:
                <?= htmlspecialchars($order['payment_method']) ?>
            </p>

            <br>

            <h3>
                Total: ₹<?= $order['total'] ?>
            </h3>

        </div>

    <?php endforeach; ?>

</div>

</body>

</html>