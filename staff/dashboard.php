<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("staff");

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

/* Update order status */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $orderId = $_POST['order_id'] ?? "";
    $newStatus = $_POST['status'] ?? "";

    $allowedStatuses = [
        "new",
        "preparing",
        "ready",
        "completed",
        "cancelled"
    ];

    if (in_array($newStatus, $allowedStatuses)) {

        foreach ($orders as &$order) {

            if ($order['order_id'] === $orderId) {
                $order['status'] = $newStatus;
                break;
            }
        }

        unset($order);

        file_put_contents(
            $file,
            json_encode($orders, JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }

    header("Location: dashboard.php");
    exit;
}

$orders = array_reverse($orders);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Staff Dashboard</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<nav class="navbar">

    <div class="nav-container">

        <div class="logo">
            👨‍🍳 Kitchen Dashboard
        </div>

        <a
            href="../logout.php"
            class="btn btn-danger">
            Logout
        </a>

    </div>

</nav>

<div class="container" style="padding:40px 0;">

    <h1 class="section-title">
        📦 Customer Orders
    </h1>

    <?php foreach ($orders as $order): ?>

        <div class="order-card">

            <div class="order-header">

                <div>

                    <h2>
                        #<?= htmlspecialchars($order['order_id']) ?>
                    </h2>

                    <p>
                        <?= htmlspecialchars($order['customer_name']) ?>
                    </p>

                </div>

                <span>
                    <?= ucfirst(
                        htmlspecialchars($order['status'])
                    ) ?>
                </span>

            </div>

            <?php foreach ($order['items'] as $item): ?>

                <p style="margin:8px 0;">

                    <?= htmlspecialchars($item['name']) ?>

                    × <?= $item['quantity'] ?>

                </p>

            <?php endforeach; ?>

            <br>

            <p>
                Pickup:
                <strong>
                    <?= htmlspecialchars($order['pickup_time']) ?>
                </strong>
            </p>

            <br>

            <?php if ($order['status'] === "new"): ?>

                <form method="POST">

                    <input
                        type="hidden"
                        name="order_id"
                        value="<?= htmlspecialchars($order['order_id']) ?>">

                    <input
                        type="hidden"
                        name="status"
                        value="preparing">

                    <button class="btn btn-primary">
                        👨‍🍳 Start Preparing
                    </button>

                </form>

            <?php elseif ($order['status'] === "preparing"): ?>

                <form method="POST">

                    <input
                        type="hidden"
                        name="order_id"
                        value="<?= htmlspecialchars($order['order_id']) ?>">

                    <input
                        type="hidden"
                        name="status"
                        value="ready">

                    <button class="btn btn-success">
                        🔔 Mark Ready
                    </button>

                </form>

            <?php elseif ($order['status'] === "ready"): ?>

                <form method="POST">

                    <input
                        type="hidden"
                        name="order_id"
                        value="<?= htmlspecialchars($order['order_id']) ?>">

                    <input
                        type="hidden"
                        name="status"
                        value="completed">

                    <button class="btn btn-secondary">
                        ✅ Mark Completed
                    </button>

                </form>

            <?php else: ?>

                <strong>
                    Order <?= ucfirst(
                        htmlspecialchars($order['status'])
                    ) ?>
                </strong>

            <?php endif; ?>

        </div>

    <?php endforeach; ?>

</div>

</body>

</html>