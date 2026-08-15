<?php
require_once __DIR__ . '/auth.php';
requireRole('admin');
function adminStart(string $title, string $active='dashboard'): void { ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title><?= h($title) ?> | SmartCanteen Admin</title><link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="../assets/css/admin.css"></head><body>
<div class="admin-shell"><aside class="admin-sidebar"><div class="admin-brand">🍔 <span>SmartCanteen</span><small>Admin Panel</small></div>
<nav>
<a class="<?= $active==='dashboard'?'active':'' ?>" href="dashboard.php">📊 Dashboard</a>
<a class="<?= $active==='orders'?'active':'' ?>" href="orders.php">📦 Orders</a>
<a class="<?= $active==='products'?'active':'' ?>" href="products.php">🍔 Products</a>
<a class="<?= $active==='inventory'?'active':'' ?>" href="inventory.php">📋 Inventory</a>
<a class="<?= $active==='users'?'active':'' ?>" href="users.php">👥 Users</a>
<a class="<?= $active==='offers'?'active':'' ?>" href="offers.php">🏷️ Offers</a>
<a class="<?= $active==='reports'?'active':'' ?>" href="reports.php">📈 Reports</a>
</nav><div class="admin-side-bottom"><a href="../index.php">👤 View User Site</a><a href="../logout.php">🚪 Logout</a></div></aside>
<main class="admin-main"><header class="admin-top"><div><h1><?= h($title) ?></h1><p>Welcome, <?= h($_SESSION['user']['name'] ?? 'Administrator') ?></p></div><div class="admin-user">👨‍💼 Admin</div></header>
<?php }
function adminEnd(): void { ?></main></div></body></html><?php }
function readJson(string $file): array { $d=file_exists($file)?json_decode(file_get_contents($file),true):[]; return is_array($d)?$d:[]; }
function writeJson(string $file,array $data): void { file_put_contents($file,json_encode(array_values($data),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX); }
function nextId(array $rows,string $key='id'): int { $ids=array_map(fn($x)=>(int)($x[$key]??0),$rows); return ($ids?max($ids):0)+1; }
?>