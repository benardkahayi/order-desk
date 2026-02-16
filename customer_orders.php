<?php
session_start();
require '../db.php';

// Only admin can view this
if (!isset($_SESSION['admin_logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

$customer_id = intval($_GET['id'] ?? 0);
$error = '';

if ($customer_id <= 0) {
    $error = "Customer ID si sahihi.";
}

// Fetch the customer details
$customer = null;
if (!$error) {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        $error = "Mteja hajapatikana.";
    }
}

// Fetch orders
$orders = [];
if (!$error) {
    $orderStmt = $conn->prepare("
        SELECT * FROM orders
        WHERE customer_id = ?
        ORDER BY date_created DESC
    ");
    $orderStmt->execute([$customer_id]);
    $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Oda za Mteja</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
.sidebar { width: 250px; background: #2c3e50; color: white; position: fixed; height: 100vh; }
.sidebar a { color: #ecf0f1; padding: 12px 20px; display: block; text-decoration: none; border-left: 3px solid transparent; transition: all .3s; }
.sidebar a:hover, .sidebar a.active { background: #34495e; border-left: 3px solid #3498db; padding-left: 25px; }
.main-content { margin-left: 250px; padding: 20px; }
.table th { background-color: #2c3e50; color: white; }
</style>
</head>
<body>

<div class="sidebar">
    <div class="p-3">
        <h5 class="text-light">Admin Panel</h5>
        <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
        <a href="customers.php"><i class="fas fa-users me-2"></i>Wateja</a>
        <a href="orders.php" class="active"><i class="fas fa-clipboard-list me-2"></i>Oda</a>
        <a href="payments.php"><i class="fas fa-money-bill-wave me-2"></i>Malipo</a>
        <a href="../admin_logout.php" class="text-danger mt-3"><i class="fas fa-sign-out-alt me-2"></i>Ondoka</a>
    </div>
</div>

<div class="main-content">
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php else: ?>
        <h3 class="text-primary mb-3">
            <i class="fas fa-shopping-cart me-2"></i>Oda za <?= htmlspecialchars($customer['name']) ?>
        </h3>

        <a href="customers.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left me-1"></i>Rudi Orodha ya Wateja
        </a>

        <?php if (count($orders) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order ID</th>
                            <th>Bidhaa</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tarehe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $i => $ord): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($ord['order_id']) ?></td>
                            <td><?= htmlspecialchars($ord['product_name']) ?></td>
                            <td><?= intval($ord['quantity']) ?></td>
                            <td>TZS <?= number_format($ord['unit_price']) ?></td>
                            <td><strong>TZS <?= number_format($ord['total_price']) ?></strong></td>
                            <td>
                                <span class="badge bg-<?=
                                    $ord['status'] === 'Delivered' ? 'success' :
                                    ($ord['status'] === 'Shipped' ? 'info' : 'warning')
                                ?>">
                                    <?= htmlspecialchars($ord['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= !empty($ord['date_created']) ? date('d/m/Y', strtotime($ord['date_created'])) : '-' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Hakuna oda kwa mteja huyu bado.
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
