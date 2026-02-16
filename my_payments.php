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

// Get customer payments
$payments_stmt = $conn->prepare("
    SELECT p.*, o.product_name, o.status as order_status
    FROM payments p
    JOIN orders o ON p.order_id = o.order_id
    WHERE o.phone = ?
    ORDER BY p.date_created DESC
");
$payments_stmt->execute([$customer['phone']]);
$payments = $payments_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malipo Yangu - Order Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">
                <i class="fas fa-credit-card me-2"></i> Malipo Yangu
            </h2>
            <a href="customer_dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Rudi Dashboard
            </a>
        </div>
        
        <div class="card shadow">
            <div class="card-body">
                <div class="mb-3">
                    <p>Mteja: <strong><?= htmlspecialchars($customer['name']) ?></strong></p>
                </div>
                
                <?php if (count($payments) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>ID ya Oda</th>
                                <th>Bidhaa</th>
                                <th>Kiasi (TZS)</th>
                                <th>Njia ya Malipo</th>
                                <th>Hali ya Malipo</th>
                                <th>Hali ya Oda</th>
                                <th>Tarehe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $index => $payment): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= $payment['order_id'] ?></strong></td>
                                <td><?= htmlspecialchars($payment['product_name']) ?></td>
                                <td class="fw-bold">TZS <?= number_format($payment['amount'], 0) ?></td>
                                <td><?= $payment['payment_method'] ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $payment['status'] == 'Approved' ? 'success' : 
                                        ($payment['status'] == 'Pending' ? 'warning' : 'danger') ?>">
                                        <?= $payment['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $payment['order_status'] == 'Delivered' ? 'success' : 
                                        ($payment['order_status'] == 'Shipped' ? 'info' : 'warning') ?>">
                                        <?= $payment['order_status'] ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($payment['date_created'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3 text-end">
                    <p class="text-muted">
                        Jumla ya malipo: <strong><?= count($payments) ?></strong> | 
                        Jumla ya fedha: <strong>TZS <?= 
                            number_format(array_sum(array_column($payments, 'amount')), 0) 
                        ?></strong>
                    </p>
                </div>
                
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-credit-card fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Hakuna Malipo Bado</h4>
                    <p class="text-muted">Hakuna malipo yaliyorekodiwa kwenye akaunti yako.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>