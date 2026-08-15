<?php
require_once __DIR__ . "/../includes/auth.php";
requireRole("customer");

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cart - Smart Canteen</title>

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
            <a href="orders.php">Orders</a>
            <a href="profile.php">Profile</a>
            <a href="../logout.php">Logout</a>

        </div>

    </div>

</nav>

<div class="container cart-container">

    <h1 class="section-title">
        🛒 Your Cart
    </h1>

    <div id="cart-items"></div>

    <div id="cart-summary"></div>

</div>

<script src="../assets/js/script.js"></script>

<script>

function displayCart() {

    const cartContainer = document.getElementById("cart-items");
    const summaryContainer = document.getElementById("cart-summary");

    let cart = JSON.parse(
        localStorage.getItem("canteenCart")
    ) || [];

    if (cart.length === 0) {

        cartContainer.innerHTML = `
            <div class="empty">
                <h2>🛒 Your cart is empty</h2>
                <br>
                <a href="menu.php" class="btn btn-primary">
                    Browse Menu
                </a>
            </div>
        `;

        summaryContainer.innerHTML = "";

        return;
    }

    let html = "";
    let subtotal = 0;

    cart.forEach(item => {

        let itemTotal = item.price * item.quantity;

        subtotal += itemTotal;

        html += `

            <div class="cart-item">

                <div>

                    <h3>${item.name}</h3>

                    <p>
                        ₹${item.price} × ${item.quantity}
                    </p>

                </div>

                <div class="quantity-control">

                    <button
                        class="quantity-btn"
                        onclick="decreaseQuantity(${item.id})">
                        -
                    </button>

                    <strong>
                        ${item.quantity}
                    </strong>

                    <button
                        class="quantity-btn"
                        onclick="increaseQuantity(${item.id})">
                        +
                    </button>

                </div>

                <strong>
                    ₹${itemTotal}
                </strong>

                <button
                    class="btn btn-danger"
                    onclick="removeFromCart(${item.id})">
                    Remove
                </button>

            </div>
        `;
    });

    cartContainer.innerHTML = html;

    let discount = subtotal >= 200 ? 20 : 0;

    let total = subtotal - discount;

    summaryContainer.innerHTML = `

        <div class="cart-summary">

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

            <br>

            <a
                href="checkout.php"
                class="btn btn-primary"
                style="width:100%;text-align:center;">
                Proceed to Checkout
            </a>

            <br><br>

            <button
                onclick="clearCart()"
                class="btn btn-danger"
                style="width:100%;">
                Clear Cart
            </button>

        </div>
    `;
}

displayCart();

</script>

</body>
</html>