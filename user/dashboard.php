<?php

require_once __DIR__ . "/../includes/auth.php";

requireRole("customer");

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}


/* =========================
   LOAD MENU
========================= */

$menuFile = __DIR__ . "/../data/menu.json";

$menu = [];

if (file_exists($menuFile)) {

    $menu = json_decode(
        file_get_contents($menuFile),
        true
    );

}

if (!is_array($menu)) {
    $menu = [];
}


/* =========================
   IMAGE PATH FUNCTION
========================= */

function getDashboardImagePath($image)
{
    $image = trim($image);

    if ($image === "") {
        return "../assets/images/no-image.png";
    }

    /* Full URL */
    if (
        str_starts_with($image, "http://") ||
        str_starts_with($image, "https://")
    ) {
        return $image;
    }

    /* Remove ./ */
    $image = ltrim($image, "./");

    /*
     * JSON contains:
     *
     * images/veg burger.webp
     *
     * dashboard.php is inside /user/
     *
     * Therefore:
     *
     * ../images/veg burger.webp
     */

    if (str_starts_with($image, "images/")) {
        return "../" . $image;
    }

    /*
     * Uploaded images
     */

    if (str_starts_with($image, "uploads/")) {
        return "../" . $image;
    }

    return "../" . $image;
}


/* =========================
   GET AVAILABLE PRODUCTS
========================= */

$availableProducts = array_filter(
    $menu,
    function ($item) {

        return !empty($item['available']);

    }
);


/* =========================
   POPULAR PRODUCTS
   SHOW FIRST 3
========================= */

$popularProducts = array_slice(
    array_values($availableProducts),
    0,
    3
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard - Smart Canteen</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<!-- =========================
     NAVIGATION
========================= -->

<nav class="navbar">

    <div class="nav-container">


        <a
            href="dashboard.php"
            class="logo"
        >
            🍔 Smart Canteen
        </a>


        <div class="nav-links">

            <a href="dashboard.php">
                Home
            </a>

            <a href="menu.php">
                Menu
            </a>

            <a href="cart.php">
                🛒 Cart
                <span id="cart-count">0</span>
            </a>

            <a href="orders.php">
                Orders
            </a>

            <a href="profile.php">
                Profile
            </a>

            <a href="../logout.php">
                Logout
            </a>

        </div>


    </div>

</nav>



<!-- =========================
     HERO SECTION
========================= -->

<section class="hero">

    <div class="container">

        <div class="hero-content">

            <h1>
                Welcome to Smart Canteen
            </h1>

            <p>
                What would you like to eat today?
            </p>


            <a
                href="menu.php"
                class="btn btn-primary"
            >
                🍔 Order Food
            </a>

        </div>

    </div>

</section>



<!-- =========================
     FOOD CATEGORIES
========================= -->

<section class="section">

    <div class="container">


        <h2 class="section-title">
            🍽️ Food Categories
        </h2>


        <div class="categories">


            <a
                href="menu.php?category=Meals"
                class="category-btn"
            >
                🍛 Meals
            </a>


            <a
                href="menu.php?category=Snacks"
                class="category-btn"
            >
                🥪 Snacks
            </a>


            <a
                href="menu.php?category=Fast%20Food"
                class="category-btn"
            >
                🍕 Fast Food
            </a>


            <a
                href="menu.php?category=Drinks"
                class="category-btn"
            >
                🥤 Drinks
            </a>


        </div>


    </div>

</section>



<!-- =========================
     POPULAR FOOD
========================= -->

<section class="section">

    <div class="container">


        <h2 class="section-title">
            🔥 Popular Food
        </h2>


        <div class="food-grid">


            <?php if (empty($popularProducts)): ?>

                <div class="empty">

                    <h3>
                        No food available.
                    </h3>

                </div>

            <?php endif; ?>



            <?php foreach ($popularProducts as $item): ?>


                <?php

                /*
                 * Convert:
                 *
                 * images/veg burger.webp
                 *
                 * into:
                 *
                 * ../images/veg burger.webp
                 */

                $imagePath = getDashboardImagePath(
                    $item['image'] ?? ''
                );

                ?>


                <div class="food-card">


                    <!-- =========================
                         FOOD IMAGE
                    ========================= -->

                    <img
                        src="<?= htmlspecialchars($imagePath) ?>"
                        alt="<?= htmlspecialchars($item['name'] ?? 'Food') ?>"
                        class="food-image"
                        onerror="this.onerror=null; this.src='../assets/images/no-image.png';"
                    >


                    <!-- =========================
                         FOOD INFORMATION
                    ========================= -->

                    <div class="food-info">


                        <h3>

                            <?= htmlspecialchars(
                                $item['name'] ?? ''
                            ) ?>

                        </h3>


                        <p class="food-description">

                            <?= htmlspecialchars(
                                $item['description'] ?? ''
                            ) ?>

                        </p>


                        <div class="food-bottom">


                            <span class="price">

                                ₹<?= htmlspecialchars(
                                    $item['price'] ?? '0'
                                ) ?>

                            </span>


                            <span class="rating">

                                ⭐ <?= htmlspecialchars(
                                    $item['rating'] ?? '0'
                                ) ?>

                            </span>


                        </div>


                    </div>


                </div>


            <?php endforeach; ?>


        </div>


        <!-- VIEW MENU BUTTON -->

        <div
            style="
                text-align:center;
                margin-top:30px;
            "
        >

            <a
                href="menu.php"
                class="btn btn-primary"
            >
                🍔 View Full Menu
            </a>

        </div>


    </div>

</section>



<script src="../assets/js/script.js"></script>


</body>

</html>