<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// Handle payment status update
if (isset($_GET['update_payment'])) {
    $id = (int)$_GET['update_payment'];
    $status = $_GET['status'] ?? '';
    
    if ($id > 0 && in_array($status, ['Pending', 'Approved', 'Failed'])) {
        $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        
        // If approved, update order payment status in orders table
        if ($status == 'Approved') {
            $payment = $conn->prepare("SELECT order_id FROM payments WHERE id = ?")->execute([$id]);
            $payment = $payments->fetch();
            if ($payment) {
                $conn->prepare("UPDATE orders SET payment_status = 'Paid' WHERE order_id = ?")
                     ->execute([$payment['order_id']]);
            }
        }
        
        header("Location: payments.php?updated=1");
        exit();
    }
}

// Fetch payments with customer and order info
$stmt = $conn->query("
    SELECT p.*, c.name as customer_name, o.product_name, o.total_price
    FROM payments p
    LEFT JOIN customers c ON p.customer_id = c.id
    LEFT JOIN orders o ON p.order_id = o.order_id
    ORDER BY p.date_created DESC
");
$payments = $stmt->fetchAll();

// Calculate totals
$total_payments = count($payments);
$total_amount = array_sum(array_column($payments, 'amount'));
$approved_amount = array_sum(array_column(
    array_filter($payments, fn($p) => $p['status'] == 'Approved'),
    'amount'
));
$pending_amount = array_sum(array_column(
    array_filter($payments, fn($p) => $p['status'] == 'Pending'),
    'amount'
));
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malipo - Admin Panel</title>
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
        .stats-card {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            color: white;
            text-align: center;
        }
        .stats-card.total { background: linear-gradient(135deg, #3498db, #2980b9); }
        .stats-card.approved { background: linear-gradient(135deg, #27ae60, #229954); }
        .stats-card.pending { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .stats-card.failed { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .table th {
            background-color: #2c3e50;
            color: white;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-failed { background: #f8d7da; color: #721c24; }
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
            </div>
            
            <nav class="mt-3">
                <a href="admin_dashboard.php">
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
                <a href="payments.php" class="active">
                    <i class="fas fa-money-bill-wave me-2"></i> Malipo
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">
                <i class="fas fa-money-bill-wave me-2"></i> Orodha ya Malipo
            </h2>
            <div>
                <a href="record_payment.php" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Rekodi Malipo
                </a>
            </div>
        </div>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> Hali ya malipo imesasishwa!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Stats Summary -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card total">
                    <h4><?= $total_payments ?></h4>
                    <p>Malipo Yote</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card approved">
                    <h4>TZS <?= number_format($approved_amount, 0) ?></h4>
                    <p>Yalidhinishwa</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card pending">
                    <h4>TZS <?= number_format($pending_amount, 0) ?></h4>
                    <p>Inasubiri</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stats-card failed">
                    <h4>TZS <?= number_format($total_amount - $approved_amount - $pending_amount, 0) ?></h4>
                    <p>Yamekataliwa</p>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Hali ya Malipo</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">Zote</option>
                            <option value="Pending">Inasubiri</option>
                            <option value="Approved">Imekubaliwa</option>
                            <option value="Failed">Imekataliwa</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Njia ya Malipo</label>
                        <select class="form-select" id="methodFilter">
                            <option value="">Zote</option>
                            <option value="M-Pesa">M-Pesa</option>
                            <option value="Airtel Money">Airtel Money</option>
                            <option value="Tigo Pesa">Tigo Pesa</option>
                            <option value="Bank Transfer">Uhamisho wa Benki</option>
                            <option value="Credit Card">Kadi ya Mkopo</option>
                            <option value="Cash">Fedha Taslimu</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary w-100" onclick="applyFilters()">
                            <i class="fas fa-filter me-1"></i> Tafuta
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <?php if (count($payments) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="paymentsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ID ya Oda</th>
                                <th>Mteja</th>
                                <th>Bidhaa</th>
                                <th>Kiasi (TZS)</th>
                                <th>Njia</th>
                                <th>Hali</th>
                                <th>Tarehe</th>
                                <th>Vitendo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $index => $payment): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= $payment['order_id'] ?></strong></td>
                                <td><?= htmlspecialchars($payment['customer_name']) ?></td>
                                <td><?= htmlspecialchars($payment['product_name']) ?></td>
                                <td><strong>TZS <?= number_format($payment['amount'], 0) ?></strong></td>
                                <td><?= $payment['payment_method'] ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($payment['status']) ?>">
                                        <?= $payment['status'] ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($payment['date_created'])) ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="payment_details.php?id=<?= $payment['id'] ?>">
                                                    <i class="fas fa-eye me-2"></i> Angalia Maelezo
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <h6 class="dropdown-header">Badilisha Hali</h6>
                                                <a class="dropdown-item" href="?update_payment=<?= $payment['id'] ?>&status=Pending">
                                                    <span class="status-badge status-pending me-2">●</span> Inasubiri
                                                </a>
                                                <a class="dropdown-item" href="?update_payment=<?= $payment['id'] ?>&status=Approved">
                                                    <span class="status-badge status-approved me-2">●</span> Imekubaliwa
                                                </a>
                                                <a class="dropdown-item" href="?update_payment=<?= $payment['id'] ?>&status=Failed">
                                                    <span class="status-badge status-failed me-2">●</span> Imekataliwa
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted">
                                Jumla: <strong>TZS <?= number_format($total_amount, 0) ?></strong> | 
                                Imekubaliwa: <strong>TZS <?= number_format($approved_amount, 0) ?></strong> | 
                                Inasubiri: <strong>TZS <?= number_format($pending_amount, 0) ?></strong>
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-outline-primary" onclick="exportPayments()">
                                <i class="fas fa-file-excel me-1"></i> Pakua Ripoti
                            </button>
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-credit-card fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Hakuna Malipo Bado</h4>
                    <p class="text-muted">Bado hujarekodi malipo yoyote.</p>
                    <a href="record_payment.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Rekodi Malipo ya Kwanza
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function applyFilters() {
        const status = document.getElementById('statusFilter').value;
        const method = document.getElementById('methodFilter').value;
        
        let query = 'payments.php?';
        const params = [];
        
        if (status) params.push(`status=${status}`);
        if (method) params.push(`method=${method}`);
        
        if (params.length > 0) {
            window.location.href = query + params.join('&');
        }
    }
    
    function exportPayments() {
        // Get current filters
        const status = document.getElementById('statusFilter').value;
        const method = document.getElementById('methodFilter').value;
        
        let url = 'export_payments.php?';
        const params = [];
        
        if (status) params.push(`status=${status}`);
        if (method) params.push(`method=${method}`);
        
        if (params.length > 0) {
            url += params.join('&');
        }
        
        window.location.href = url;
    }
    
    // Initialize filters from URL parameters
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const method = urlParams.get('method');
        
        if (status) {
            document.getElementById('statusFilter').value = status;
        }
        if (method) {
            document.getElementById('methodFilter').value = method;
        }
        
        // Highlight rows based on status
        document.querySelectorAll('.status-pending').forEach(el => {
            el.closest('tr').classList.add('table-warning');
        });
        document.querySelectorAll('.status-failed').forEach(el => {
            el.closest('tr').classList.add('table-danger');
        });
        document.querySelectorAll('.status-approved').forEach(el => {
            el.closest('tr').classList.add('table-success');
        });
    });
    </script>
</body>
</html>