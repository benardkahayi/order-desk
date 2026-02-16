<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: customer_login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];

// Get customer info
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

// Get all orders
$orders_stmt = $conn->prepare("
    SELECT o.*, p.status as payment_status, p.payment_method 
    FROM orders o
    LEFT JOIN payments p ON o.order_id = p.order_id
    WHERE o.phone = ?
    ORDER BY o.date_created DESC
");
$orders_stmt->execute([$customer['phone']]);
$orders = $orders_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oda Zangu - Order Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            max-width: 1200px;
        }
        .table th {
            background-color: #3498db;
            color: white;
        }
        .badge {
            padding: 8px 12px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">
                <i class="fas fa-shopping-cart me-2"></i> Oda Zangu
            </h2>
            <div>
                <a href="customer_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Rudi Dashboard
                </a>
                <a href="request_refund.php" class="btn btn-danger">
                    <i class="fas fa-undo me-1"></i> Omba Rudi Fedha
                </a>
            </div>
        </div>
        
        <div class="card shadow">
            <div class="card-body">
                <div class="mb-3">
                    <p>Mteja: <strong><?= htmlspecialchars($customer['name']) ?></strong> | 
                    Simu: <strong><?= htmlspecialchars($customer['phone']) ?></strong></p>
                </div>
                
                <?php if (count($orders) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID ya Oda</th>
                                <th>Bidhaa</th>
                                <th>Idadi</th>
                                <th>Bei (TZS)</th>
                                <th>Jumla (TZS)</th>
                                <th>Hali ya Oda</th>
                                <th>Malipo</th>
                                <th>Tarehe</th>
                                <th>Vitendo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?= $order['order_id'] ?></strong></td>
                                <td><?= htmlspecialchars($order['product_name']) ?></td>
                                <td><?= $order['quantity'] ?></td>
                                <td>TZS <?= number_format($order['unit_price'], 0) ?></td>
                                <td><strong>TZS <?= number_format($order['total_price'], 0) ?></strong></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $order['status'] == 'Delivered' ? 'success' : 
                                        ($order['status'] == 'Shipped' ? 'info' : 
                                        ($order['status'] == 'Processing' ? 'warning' : 'danger')) ?>">
                                        <?= $order['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $order['payment_method'] ?? 'N/A' ?><br>
                                    <small class="badge bg-<?= 
                                        ($order['payment_status'] ?? 'Pending') == 'Approved' ? 'success' : 'warning' ?>">
                                        <?= $order['payment_status'] ?? 'Pending' ?>
                                    </small>
                                </td>
                                <td><?= date('d/m/Y', strtotime($order['date_created'])) ?></td>
                                <td>
                                    <a href="request_refund.php?order_id=<?= $order['order_id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       <?= $order['status'] == 'Delivered' ? '' : 'disabled' ?>>
                                        <i class="fas fa-undo me-1"></i> Rudi Fedha
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3 text-end">
                    <p class="text-muted">
                        Jumla ya oda: <strong><?= count($orders) ?></strong> | 
                        Jumla ya thamani: <strong>TZS <?= 
                            number_format(array_sum(array_column($orders, 'total_price')), 0) 
                        ?></strong>
                    </p>
                </div>
                
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Hakuna Oda Bado</h4>
                    <p class="text-muted">Bado hujaagiza oda yoyote.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>