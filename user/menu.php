<?php

require_once __DIR__ . "/../includes/auth.php";

requireRole("customer");

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}


/* =========================
   MENU FILE
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

function getUserImagePath($image)
{
    $image = trim($image);

    if ($image === "") {
        return "../assets/images/no-image.png";
    }

    /*
     * If image is already a complete URL
     */
    if (
        str_starts_with($image, "http://") ||
        str_starts_with($image, "https://")
    ) {
        return $image;
    }

    /*
     * Remove ./ if present
     */
    $image = ltrim($image, "./");


    /*
     * Images stored in root /images/
     *
     * JSON:
     * images/veg burger.webp
     *
     * Browser:
     * ../images/veg burger.webp
     */

    if (str_starts_with($image, "images/")) {
        return "../" . $image;
    }


    /*
     * If admin uploads are stored in uploads/
     */

    if (str_starts_with($image, "uploads/")) {
        return "../" . $image;
    }


    /*
     * If image path already contains user/
     */

    if (str_starts_with($image, "user/")) {
        return "../" . $image;
    }


    /*
     * Default
     */

    return "../" . $image;
}


/* =========================
   CATEGORY FILTER
========================= */

$selectedCategory = $_GET['category'] ?? "All";

if ($selectedCategory !== "All") {

    $menu = array_filter(
        $menu,
        function ($item) use ($selectedCategory) {

            return ($item['category'] ?? '') === $selectedCategory;

        }
    );

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

    <title>Menu - Smart Canteen</title>

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
     MENU SECTION
========================= -->

<section class="section">

    <div class="container">


        <h1 class="section-title">
            🍔 Our Menu
        </h1>



        <!-- =========================
             CATEGORY BUTTONS
        ========================= -->

        <div class="categories">


            <a
                href="menu.php"
                class="category-btn
                <?= $selectedCategory === 'All' ? 'active' : '' ?>"
            >
                All
            </a>


            <a
                href="menu.php?category=Meals"
                class="category-btn
                <?= $selectedCategory === 'Meals' ? 'active' : '' ?>"
            >
                🍛 Meals
            </a>


            <a
                href="menu.php?category=Snacks"
                class="category-btn
                <?= $selectedCategory === 'Snacks' ? 'active' : '' ?>"
            >
                🥪 Snacks
            </a>


            <a
                href="menu.php?category=Fast%20Food"
                class="category-btn
                <?= $selectedCategory === 'Fast Food' ? 'active' : '' ?>"
            >
                🍕 Fast Food
            </a>


            <a
                href="menu.php?category=Drinks"
                class="category-btn
                <?= $selectedCategory === 'Drinks' ? 'active' : '' ?>"
            >
                🥤 Drinks
            </a>


        </div>



        <!-- =========================
             FOOD GRID
        ========================= -->

        <div class="food-grid">


            <?php if (empty($menu)): ?>

                <div class="empty">

                    <h3>
                        No food items found.
                    </h3>

                </div>

            <?php endif; ?>



            <?php foreach ($menu as $item): ?>


                <?php

                /*
                 * Get correct image URL
                 */

                $imagePath = getUserImagePath(
                    $item['image'] ?? ''
                );

                ?>


                <div class="food-card">


                    <!-- FOOD IMAGE -->

                    <img
                        src="<?= htmlspecialchars($imagePath) ?>"
                        alt="<?= htmlspecialchars($item['name'] ?? 'Food') ?>"
                        class="food-image"
                        onerror="this.onerror=null; this.src='../assets/images/no-image.png';"
                    >



                    <!-- FOOD INFORMATION -->

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



                        <p class="rating">

                            ⭐

                            <?= htmlspecialchars(
                                $item['rating'] ?? '0'
                            ) ?>

                        </p>



                        <div class="food-bottom">


                            <span class="price">

                                ₹<?= htmlspecialchars(
                                    $item['price'] ?? '0'
                                ) ?>

                            </span>



                            <?php if (!empty($item['available'])): ?>


                                <button
                                    class="btn btn-primary"
                                    onclick='addToCart(
                                        <?= json_encode($item["id"]) ?>,
                                        <?= json_encode($item["name"]) ?>,
                                        <?= json_encode($item["price"]) ?>,
                                        <?= json_encode($imagePath) ?>
                                    )'
                                >
                                    Add to Cart
                                </button>


                            <?php else: ?>


                                <button
                                    class="btn"
                                    disabled
                                >
                                    Sold Out
                                </button>


                            <?php endif; ?>


                        </div>


                    </div>


                </div>


            <?php endforeach; ?>


        </div>


    </div>

</section>



<script src="../assets/js/script.js"></script>


</body>

</html>