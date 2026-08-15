<?php
require_once __DIR__ . '/../includes/admin_layout.php';

$menuFile = __DIR__ . '/../data/menu.json';
$imageDir = __DIR__ . '/../assets/images/products/';
$menu = file_exists($menuFile) ? json_decode(file_get_contents($menuFile), true) : [];
$menu = is_array($menu) ? $menu : [];
$message = '';
$error = '';

function saveMenu($file, $menu) {
    file_put_contents($file, json_encode(array_values($menu), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
}
function nextProductId($menu) {
    return empty($menu) ? 1 : max(array_map(fn($i) => (int)($i['id'] ?? 0), $menu)) + 1;
}
function cleanImageName($name) {
    $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($name));
    return trim($name, '._') ?: 'product';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $menu = array_values(array_filter($menu, fn($item) => (int)$item['id'] !== $id));
        saveMenu($menuFile, $menu);
        header('Location: products.php?msg=deleted'); exit;
    }

    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        foreach ($menu as &$item) if ((int)$item['id'] === $id) $item['available'] = !($item['available'] ?? false);
        unset($item);
        saveMenu($menuFile, $menu);
        header('Location: products.php?msg=updated'); exit;
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $rating = max(0, min(5, (float)($_POST['rating'] ?? 0)));
        $available = isset($_POST['available']);

        if ($name === '' || $category === '' || $price <= 0) {
            $error = 'Please enter product name, category and a valid price.';
        } else {
            $image = '';
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg','jpeg','png','gif','webp','svg'];
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed, true)) {
                    $error = 'Only JPG, PNG, GIF, WEBP or SVG images are allowed.';
                } else {
                    if (!is_dir($imageDir)) mkdir($imageDir, 0777, true);
                    $image = 'assets/images/products/' . time() . '_' . cleanImageName($_FILES['image']['name']);
                    move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../' . $image);
                }
            }

            if ($error === '') {
                if ($id > 0) {
                    foreach ($menu as &$item) {
                        if ((int)$item['id'] === $id) {
                            $item['name'] = $name;
                            $item['category'] = $category;
                            $item['price'] = $price;
                            $item['description'] = $description;
                            $item['rating'] = $rating;
                            $item['available'] = $available;
                            if ($image !== '') $item['image'] = $image;
                            break;
                        }
                    }
                    unset($item);
                } else {
                    $menu[] = [
                        'id' => nextProductId($menu), 'name' => $name, 'category' => $category,
                        'price' => $price, 'image' => $image ?: 'assets/images/products/placeholder.svg',
                        'description' => $description, 'rating' => $rating, 'available' => $available
                    ];
                }
                saveMenu($menuFile, $menu);
                header('Location: products.php?msg=saved'); exit;
            }
        }
    }
}

if (isset($_GET['msg'])) {
    $message = ['saved'=>'Product saved successfully.','deleted'=>'Product deleted successfully.','updated'=>'Product availability updated.'][$_GET['msg']] ?? '';
}
$edit = null;
if (isset($_GET['edit'])) foreach ($menu as $item) if ((int)$item['id'] === (int)$_GET['edit']) $edit = $item;
?>
<?php adminStart('Manage Products','products'); ?>
<?php if ($message): ?><div class="notice success">✅ <?= h($message) ?></div><?php endif; ?><?php if ($error): ?><div class="notice error">❌ <?= h($error) ?></div><?php endif; ?>
<div class="admin-two"><section class="admin-panel"><h2><?= $edit ? '✏️ Edit Product' : '➕ Add Product' ?></h2><form method="POST" enctype="multipart/form-data"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= h($edit['id'] ?? 0) ?>"><div class="form-group"><label>Product Name</label><input class="form-control" name="name" required value="<?= h($edit['name'] ?? '') ?>"></div><div class="form-group"><label>Category</label><input class="form-control" name="category" required value="<?= h($edit['category'] ?? '') ?>" placeholder="Meals, Snacks, Fast Food, Drinks"></div><div class="form-group"><label>Price (₹)</label><input class="form-control" type="number" name="price" min="1" step="0.01" required value="<?= h($edit['price'] ?? '') ?>"></div><div class="form-group"><label>Rating</label><input class="form-control" type="number" name="rating" min="0" max="5" step="0.1" value="<?= h($edit['rating'] ?? '4.5') ?>"></div><div class="form-group"><label>Description</label><textarea class="form-control" name="description" rows="4"><?= h($edit['description'] ?? '') ?></textarea></div><div class="form-group"><label>Product Image</label><input class="form-control" type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp,.svg"></div><label><input type="checkbox" name="available" <?= (($edit['available'] ?? true) ? 'checked' : '') ?>> Available for customers</label><br><br><button class="btn btn-primary">💾 Save Product</button> <?php if ($edit): ?><a class="btn btn-secondary" href="products.php">Cancel</a><?php endif; ?></form></section>
<section class="admin-panel"><h2>📋 Product List</h2><div class="table-wrap"><table><tr><th>Image</th><th>Product</th><th>Category</th><th>Price</th><th>Status</th><th>Actions</th></tr><?php foreach ($menu as $item): ?><tr><td><img src="../<?= h($item['image'] ?? 'assets/images/products/placeholder.svg') ?>" alt="" class="product-thumb"></td><td><b><?= h($item['name']) ?></b><br><small>⭐ <?= h($item['rating'] ?? 0) ?></small></td><td><?= h($item['category']) ?></td><td>₹<?= number_format((float)$item['price'],2) ?></td><td><?= !empty($item['available']) ? '🟢 Available' : '🔴 Sold Out' ?></td><td><a class="btn btn-secondary" href="products.php?edit=<?= (int)$item['id'] ?>">✏️ Edit</a><form method="POST" style="display:inline"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><button class="btn btn-primary">Toggle</button></form><form method="POST" style="display:inline" onsubmit="return confirm('Delete this product?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><button class="btn btn-danger">🗑️ Delete</button></form></td></tr><?php endforeach; ?></table></div></section></div><?php adminEnd(); ?>