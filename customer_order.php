<?php
session_start();
require 'db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];

// Get customer info
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

// Get all orders for this customer
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
<html>
<head>
    <title>My Orders - Order Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>My Orders</h2>
    <p>Customer: <strong><?= htmlspecialchars($customer['name']) ?></strong></p>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['order_id'] ?></td>
                <td><?= $order['product_name'] ?></td>
                <td><?= $order['quantity'] ?></td>
                <td>$<?= number_format($order['unit_price'], 2) ?></td>
                <td>$<?= number_format($order['total_price'], 2) ?></td>
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
                    <small><?= $order['payment_status'] ?? 'Pending' ?></small>
                </td>
                <td><?= date('M d, Y', strtotime($order['date_created'])) ?></td>
                <td>
                    <a href="request_refund.php?order_id=<?= $order['order_id'] ?>" 
                       class="btn btn-sm btn-danger" <?= $order['status'] == 'Delivered' ? '' : 'disabled' ?>>
                        Request Refund
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <a href="customer_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>
</body>
</html>