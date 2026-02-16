<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: customer_login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$order_id = $_SESSION['order_id'] ?? '';

// Get customer info
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

if (!$customer) {
    session_destroy();
    header("Location: customer_login.php");
    exit;
}

// Get current order if exists
$current_order = null;
if (!empty($order_id)) {
    $order_stmt = $conn->prepare("
        SELECT o.*, 
               COALESCE(p.payment_method, 'Not Set') as payment_method,
               COALESCE(p.status, 'Pending') as payment_status
        FROM orders o
        LEFT JOIN payments p ON o.order_id = p.order_id
        WHERE o.order_id = ? AND o.phone = ?
    ");
    $order_stmt->execute([$order_id, $customer['phone']]);
    $current_order = $order_stmt->fetch();
}

// Get all customer orders
$all_orders_stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE phone = ? 
    ORDER BY date_created DESC
");
$all_orders_stmt->execute([$customer['phone']]);
$all_orders = $all_orders_stmt->fetchAll();

// Get customer payments
$payments_stmt = $conn->prepare("
    SELECT p.*, o.product_name 
    FROM payments p
    JOIN orders o ON p.order_id = o.order_id
    WHERE o.phone = ?
    ORDER BY p.date_created DESC
");
$payments_stmt->execute([$customer['phone']]);
$payments = $payments_stmt->fetchAll();

// Get customer feedback
$feedback_stmt = $conn->prepare("
    SELECT * FROM feedback 
    WHERE customer_phone = ?
    ORDER BY date_created DESC 
    LIMIT 3
");
$feedback_stmt->execute([$customer['phone']]);
$feedbacks = $feedback_stmt->fetchAll();

// Calculate stats
$total_orders = count($all_orders);
$delivered_orders = 0;
$pending_orders = 0;
$total_spent = 0;

foreach ($all_orders as $order) {
    if ($order['status'] == 'Delivered') {
        $delivered_orders++;
    }
    if ($order['status'] == 'Processing') {
        $pending_orders++;
    }
    $total_spent += $order['total_price'];
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard ya Mteja - Order Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
            position: fixed;
            height: 100vh;
            color: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar a {
            color: #ecf0f1;
            padding: 12px 20px;
            text-decoration: none;
            display: block;
            border-left: 3px solid transparent;
            transition: all 0.3s;
            margin-bottom: 2px;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #34495e;
            border-left: 3px solid #3498db;
            padding-left: 25px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .user-info {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-radius: 10px;
            padding: 15px;
            color: white;
            margin-bottom: 20px;
        }
        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="p-3">
            <div class="text-center mb-3">
                <i class="fas fa-shopping-cart fa-2x text-primary mb-2"></i>
                <h5 class="mt-2">Order Desk</h5>
            </div>
            
            <div class="user-info">
                <h6 class="mb-1"><?php echo htmlspecialchars($customer['name']); ?></h6>
                <small class="opacity-75"><?php echo $customer['phone']; ?></small>
            </div>
            
            <nav class="mt-3">
                <a href="customer_dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="my_orders.php">
                    <i class="fas fa-shopping-cart me-2"></i> Oda Zangu
                </a>
                <a href="my_payments.php">
                    <i class="fas fa-credit-card me-2"></i> Malipo
                </a>
                <a href="request_refund.php">
                    <i class="fas fa-undo me-2"></i> Omba Rudi Fedha
                </a>
                <a href="give_feedback.php">
                    <i class="fas fa-comment me-2"></i> Toa Maoni
                </a>
                <a href="profile.php">
                    <i class="fas fa-user me-2"></i> Profaili Yangu
                </a>
                <hr class="bg-light">
                <a href="customer_logout.php" class="text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Ondoka
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">Karibu, <?php echo htmlspecialchars($customer['name']); ?>!</h2>
            <div class="text-muted">
                <i class="fas fa-calendar me-2"></i>
                <?php echo date('l, j F Y'); ?>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Oda Zote</h6>
                                <h3 class="text-primary"><?php echo $total_orders; ?></h3>
                            </div>
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-box text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Zilizofika</h6>
                                <h3 class="text-success"><?php echo $delivered_orders; ?></h3>
                            </div>
                            <div class="icon-circle bg-success">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Inasubiri</h6>
                                <h3 class="text-warning"><?php echo $pending_orders; ?></h3>
                            </div>
                            <div class="icon-circle bg-warning">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card stat-card border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Jumla ya Fedha</h6>
                                <h3 class="text-info">TZS <?php echo number_format($total_spent, 0); ?></h3>
                            </div>
                            <div class="icon-circle bg-info">
                                <i class="fas fa-money-bill-wave text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Order Section -->
        <?php if ($current_order): ?>
        <div class="card stat-card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-box-open me-2"></i> Oda Ya Sasa (<?php echo $current_order['order_id']; ?>)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <strong>Bidhaa:</strong><br>
                            <?php echo $current_order['product_name']; ?>
                        </div>
                        <div class="mb-3">
                            <strong>Idadi:</strong><br>
                            <?php echo $current_order['quantity']; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <strong>Hali ya Oda:</strong><br>
                            <span class="badge bg-<?php 
                                echo $current_order['status'] == 'Delivered' ? 'success' : 
                                       ($current_order['status'] == 'Shipped' ? 'info' : 'warning'); ?>">
                                <?php echo $current_order['status']; ?>
                            </span>
                        </div>
                        <div class="mb-3">
                            <strong>Jumla:</strong><br>
                            <span class="text-success">TZS <?php echo number_format($current_order['total_price'], 0); ?></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <strong>Malipo:</strong><br>
                            <?php echo $current_order['payment_method']; ?>
                        </div>
                        <div class="mb-3">
                            <strong>Hali ya Malipo:</strong><br>
                            <span class="badge bg-<?php 
                                echo $current_order['payment_status'] == 'Approved' ? 'success' : 'warning'; ?>">
                                <?php echo $current_order['payment_status']; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Orders -->
        <div class="card stat-card mt-4">
            <div class="card-header bg-secondary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i> Oda Zako Zote</h5>
                    <a href="my_orders.php" class="btn btn-sm btn-light">Angalia Zote</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (count($all_orders) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID ya Oda</th>
                                <th>Bidhaa</th>
                                <th>Jumla</th>
                                <th>Hali</th>
                                <th>Tarehe</th>
                                <th>Vitendo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_orders as $order): ?>
                            <tr <?php echo ($order['order_id'] == $order_id) ? 'class="table-primary"' : ''; ?>>
                                <td>
                                    <?php echo $order['order_id']; ?>
                                    <?php if ($order['order_id'] == $order_id): ?>
                                    <span class="badge bg-info ms-2">Ya Sasa</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $order['product_name']; ?></td>
                                <td>TZS <?php echo number_format($order['total_price'], 0); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $order['status'] == 'Delivered' ? 'success' : 
                                               ($order['status'] == 'Shipped' ? 'info' : 'warning'); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($order['date_created'])); ?></td>
                                <td>
                                    <?php if ($order['order_id'] != $order_id): ?>
                                    <a href="switch_order.php?order_id=<?php echo $order['order_id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        Chagua
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Huna Oda Bado</h5>
                    <p class="text-muted">Oda zako zitaonekana hapa baada ya kuagizwa.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Payments -->
        <?php if (count($payments) > 0): ?>
        <div class="card stat-card mt-4">
            <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i> Malipo Ya Hivi Karibuni</h5>
                    <a href="my_payments.php" class="btn btn-sm btn-light">Angalia Yote</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach (array_slice($payments, 0, 4) as $payment): ?>
                    <div class="col-md-3 mb-3">
                        <div class="card h-100 border-<?php echo $payment['status'] == 'Approved' ? 'success' : 'warning'; ?>">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo $payment['order_id']; ?></h6>
                                <p class="card-text mb-1">
                                    <strong>Kiasi:</strong> 
                                    <span class="text-success">TZS <?php echo number_format($payment['amount'], 0); ?></span>
                                </p>
                                <p class="card-text mb-1">
                                    <strong>Njia:</strong> <?php echo $payment['payment_method']; ?>
                                </p>
                                <p class="card-text mb-0">
                                    <span class="badge bg-<?php 
                                        echo $payment['status'] == 'Approved' ? 'success' : 'warning'; ?>">
                                        <?php echo $payment['status']; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Feedback -->
        <?php if (count($feedbacks) > 0): ?>
        <div class="card stat-card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-comments me-2"></i> Maoni Yako Ya Hivi Karibuni</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($feedbacks as $feedback): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="card-text">"<?php echo substr($feedback['message'], 0, 100); ?>..."</p>
                                <small class="text-muted">
                                    <?php echo date('d/m/Y', strtotime($feedback['date_created'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-undo fa-2x text-danger"></i>
                        </div>
                        <h5>Omba Rudi Fedha</h5>
                        <p class="text-muted">Rudisha fedha kwa oda yako</p>
                        <a href="request_refund.php" class="btn btn-outline-danger w-100">Omba Sasa</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-headset fa-2x text-primary"></i>
                        </div>
                        <h5>Wasiliana na Usaidizi</h5>
                        <p class="text-muted">Pata usaidizi kuhusu oda yako</p>
                        <a href="contact.php" class="btn btn-outline-primary w-100">Wasiliana</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-comment fa-2x text-success"></i>
                        </div>
                        <h5>Toa Maoni Yako</h5>
                        <p class="text-muted">Tupe maoni kuhusu huduma yetu</p>
                        <a href="give_feedback.php" class="btn btn-outline-success w-100">Toa Maoni</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const cards = document.querySelectorAll('.stat-card');
        cards.forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
        
        function updateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            const timeElement = document.querySelector('.text-muted i.fa-calendar').parentElement;
            if (timeElement) {
                timeElement.innerHTML = `<i class="fas fa-calendar me-2"></i>${now.toLocaleDateString('sw-TZ', options)}`;
            }
        }
        
        setInterval(updateTime, 60000);
    });
    </script>
</body>
</html>