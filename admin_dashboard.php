<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location:admin_login.php");
    exit;
}

// Get statistics
$customers = $conn->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$orders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$payments = $conn->query("SELECT COUNT(*) FROM payments WHERE status = 'Approved'")->fetchColumn();
$revenue = $conn->query("SELECT SUM(amount) FROM payments WHERE status = 'Approved'")->fetchColumn() ?: 0;
$pending_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'Processing'")->fetchColumn();
$recent_orders = $conn->query("
    SELECT o.*, c.name as customer_name 
    FROM orders o 
    JOIN customers c ON o.customer_id = c.id 
    ORDER BY o.date_created DESC 
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Order Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
            position: fixed;
            height: 100vh;
            color: white;
        }
        .sidebar a {
            color: #ecf0f1;
            padding: 12px 20px;
            text-decoration: none;
            display: block;
            border-left: 3px solid transparent;
            transition: all 0.3s;
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
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .table th {
            background-color: #2c3e50;
            color: white;
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
                <i class="fas fa-cogs fa-2x text-primary mb-2"></i>
                <h5 class="mt-2">Admin Panel</h5>
                <p class="small text-muted"><?php echo $_SESSION['admin_name']; ?></p>
            </div>
            
            <nav class="mt-3">
                <a href="admin_dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="add_customer.php">
                    <i class="fas fa-user-plus me-2"></i> Ingiza Mteja
                </a>
                <a href="customers.php">
                    <i class="fas fa-users me-2"></i> Wateja
                </a>
                <a href="add_order.php">
                    <i class="fas fa-shopping-cart me-2"></i> Ingiza Oda
                </a>
                <a href="orders.php">
                    <i class="fas fa-clipboard-list me-2"></i> Oda
                </a>
                <a href="payments.php">
                    <i class="fas fa-money-bill-wave me-2"></i> Malipo
                </a>
                <a href="refunds.php">
                    <i class="fas fa-undo me-2"></i> Rudi Fedha
                </a>
                <hr class="bg-light">
                <a href="../admin_logout.php" class="text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Ondoka
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">Admin Dashboard</h2>
            <div class="text-muted">
                <i class="fas fa-calendar me-2"></i>
                <?php echo date('l, j F Y'); ?>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Wateja</h6>
                                <h3 class="text-primary"><?php echo number_format($customers); ?></h3>
                            </div>
                            <div class="stat-icon bg-primary text-white">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Oda</h6>
                                <h3 class="text-success"><?php echo number_format($orders); ?></h3>
                            </div>
                            <div class="stat-icon bg-success text-white">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Inasubiri</h6>
                                <h3 class="text-warning"><?php echo number_format($pending_orders); ?></h3>
                            </div>
                            <div class="stat-icon bg-warning text-white">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Mapato</h6>
                                <h3 class="text-info">TZS <?php echo number_format($revenue, 0); ?></h3>
                            </div>
                            <div class="stat-icon bg-info text-white">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i> Oda za Hivi Karibuni</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($recent_orders) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Mteja</th>
                                        <th>Bidhaa</th>
                                        <th>Jumla</th>
                                        <th>Hali</th>
                                        <th>Tarehe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td><?php echo $order['order_id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                        <td>TZS <?php echo number_format($order['total_price'], 0); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $order['status'] == 'Delivered' ? 'success' : 
                                                       ($order['status'] == 'Shipped' ? 'info' : 'warning'); ?>">
                                                <?php echo $order['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($order['date_created'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-2">
                            <a href="orders.php" class="btn btn-sm btn-primary">Angalia Oda Zote</a>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Hakuna oda bado</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i> Vitendo vya Haraka</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="add_customer.php" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i> Ingiza Mteja Mpya
                            </a>
                            <a href="add_order.php" class="btn btn-success">
                                <i class="fas fa-shopping-cart me-2"></i> Tengeza Oda Mpya
                            </a>
                            <a href="customers.php" class="btn btn-info">
                                <i class="fas fa-users me-2"></i> Angalia Wateja
                            </a>
                            <a href="payments.php" class="btn btn-warning">
                                <i class="fas fa-credit-card me-2"></i> Angalia Malipo
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- System Info -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Maelezo ya Mfumo</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Matumizi ya Database:</span>
                                <span class="badge bg-info"><?php echo $customers + $orders; ?> Records</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Malipo Yalidhinishwa:</span>
                                <span class="badge bg-success"><?php echo $payments; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Mapato Yaliyokusanywa:</span>
                                <span class="fw-bold">TZS <?php echo number_format($revenue, 0); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Tarehe ya Mfumo:</span>
                                <span><?php echo date('Y-m-d'); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Update time every minute
    function updateTime() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        const timeElement = document.querySelector('.text-muted i.fa-calendar').parentElement;
        if (timeElement) {
            timeElement.innerHTML = `<i class="fas fa-calendar me-2"></i>${now.toLocaleDateString('sw-TZ', options)}`;
        }
    }
    
    setInterval(updateTime, 1000);
    </script>
</body>
</html>