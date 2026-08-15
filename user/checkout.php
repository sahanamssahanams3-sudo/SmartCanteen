<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("customer");

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cart = json_decode($_POST['cart'] ?? "[]", true);

    if (!is_array($cart) || empty($cart)) {
        die("Your cart is empty.");
    }

    $menuFile = __DIR__ . "/../data/menu.json";
    $ordersFile = __DIR__ . "/../data/orders.json";

    $menu = json_decode(file_get_contents($menuFile), true);
    $orders = json_decode(file_get_contents($ordersFile), true);

    if (!is_array($orders)) {
        $orders = [];
    }

    $validItems = [];
    $subtotal = 0;

    foreach ($cart as $cartItem) {

        foreach ($menu as $food) {

            if (
                $food['id'] == $cartItem['id'] &&
                $food['available'] === true
            ) {

                $quantity = max(
                    1,
                    intval($cartItem['quantity'])
                );

                $price = floatval($food['price']);

                $validItems[] = [
                    "id" => $food['id'],
                    "name" => $food['name'],
                    "price" => $price,
                    "quantity" => $quantity
                ];

                $subtotal += $price * $quantity;

                break;
            }
        }
    }

    if (empty($validItems)) {
        die("No valid items in your cart.");
    }

    $discount = $subtotal >= 200 ? 20 : 0;

    $total = $subtotal - $discount;

    $orderNumber = "A" . rand(100, 999);

    $existingNumbers = array_column(
        $orders,
        "order_id"
    );

    while (in_array($orderNumber, $existingNumbers)) {
        $orderNumber = "A" . rand(100, 999);
    }

    $order = [
        "order_id" => $orderNumber,
        "user_id" => $_SESSION['user']['id'],
        "customer_name" => $_SESSION['user']['name'],
        "items" => $validItems,
        "subtotal" => $subtotal,
        "discount" => $discount,
        "total" => $total,
        "payment_method" => $_POST['payment_method'] ?? "Cash",
        "payment_status" => "paid",
        "pickup_time" => $_POST['pickup_time'] ?? "ASAP",
        "status" => "new",
        "created_at" => date("Y-m-d H:i:s")
    ];

    $orders[] = $order;

    file_put_contents(
        $ordersFile,
        json_encode($orders, JSON_PRETTY_PRINT),
        LOCK_EX
    );

    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0">

        <title>Order Confirmed</title>

        <link rel="stylesheet" href="../assets/css/style.css">

    </head>

    <body>

    <div class="auth-page">

        <div class="auth-card" style="text-align:center;">

            <div style="font-size:70px;">
                🎉
            </div>

            <h1>
                Order Confirmed!
            </h1>

            <br>

            <h2 style="color:var(--primary);">
                #<?= htmlspecialchars($orderNumber) ?>
            </h2>

            <br>

            <p>
                Thank you,
                <?= htmlspecialchars($_SESSION['user']['name']) ?>!
            </p>

            <br>

            <p>
                Your food is being prepared.
            </p>

            <br>

            <p>
                Pickup Time:
                <strong>
                    <?= htmlspecialchars($order['pickup_time']) ?>
                </strong>
            </p>

            <br>

            <h2>
                Total: ₹<?= $total ?>
            </h2>

            <br>

            <a
                href="orders.php"
                class="btn btn-primary">
                📦 Track Order
            </a>

            <a
                href="menu.php"
                class="btn btn-secondary">
                🍔 Order More
            </a>

        </div>

    </div>

    <script>
        localStorage.removeItem("canteenCart");
    </script>

    </body>

    </html>

    <?php

    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Checkout</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<nav class="navbar">

    <div class="nav-container">

        <a href="dashboard.php" class="logo">
            🍔 Smart Canteen
        </a>

        <a href="cart.php" class="btn btn-primary">
            ← Back to Cart
        </a>

    </div>

</nav>

<div class="container" style="max-width:700px;margin:40px auto;">

    <h1 class="section-title">
        💳 Checkout
    </h1>

    <form method="POST" id="checkout-form">

        <input
            type="hidden"
            name="cart"
            id="cart-data">

        <div class="cart-summary">

            <h2>🕐 Pickup Time</h2>

            <br>

            <div class="form-group">

                <select
                    name="pickup_time"
                    class="form-control"
                    required>

                    <option value="">
                        Select Pickup Time
                    </option>

                    <option>12:30 PM</option>
                    <option>12:45 PM</option>
                    <option>1:00 PM</option>
                    <option>1:15 PM</option>
                    <option>1:30 PM</option>

                </select>

            </div>

            <br>

            <h2>💳 Payment Method</h2>

            <br>

            <div class="form-group">

                <label>
                    <input
                        type="radio"
                        name="payment_method"
                        value="UPI"
                        required>
                    UPI
                </label>

                <br><br>

                <label>
                    <input
                        type="radio"
                        name="payment_method"
                        value="Card">
                    Card
                </label>

                <br><br>

                <label>
                    <input
                        type="radio"
                        name="payment_method"
                        value="Cash">
                    Cash on Pickup
                </label>

            </div>

            <br>

            <div id="checkout-total"></div>

            <br>

            <button
                type="submit"
                class="btn btn-primary full-btn">
                🍔 Place Order
            </button>

        </div>

    </form>

</div>

<script>

const cart =
    JSON.parse(localStorage.getItem("canteenCart")) || [];

document.getElementById("cart-data").value =
    JSON.stringify(cart);

let subtotal = 0;

cart.forEach(item => {
    subtotal += item.price * item.quantity;
});

let discount = subtotal >= 200 ? 20 : 0;

let total = subtotal - discount;

document.getElementById("checkout-total").innerHTML = `

    <div class="summary-row">
        <span>Subtotal</span>
        <strong>₹${subtotal}</strong>
    </div>

    <div class="summary-row">
        <span>Discount</span>
        <strong>₹${discount}</strong>
    </div>

    <div class="summary-row summary-total">
        <span>Total</span>
        <strong>₹${total}</strong>
    </div>

`;

</script>

</body>

</html>