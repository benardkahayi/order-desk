<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: customer_login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$order_id = $_GET['order_id'] ?? '';

// Get customer info
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

// Get order details if order_id is provided
$order = null;
if (!empty($order_id)) {
    $order_stmt = $conn->prepare("
        SELECT o.*, p.status as payment_status
        FROM orders o
        LEFT JOIN payments p ON o.order_id = p.order_id
        WHERE o.order_id = ? AND o.customer_id = ?
    ");
    $order_stmt->execute([$order_id, $customer_id]);
    $order = $order_stmt->fetch();
}

// Handle refund request
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id_refund = trim($_POST['order_id']);
    $reason = trim($_POST['reason']);
    
    if (!empty($order_id_refund) && !empty($reason)) {
        // Check if order exists and belongs to customer
        $check_order = $conn->prepare("
            SELECT id FROM orders 
            WHERE order_id = ? AND customer_id = ? AND status = 'Delivered'
        ");
        $check_order->execute([$order_id_refund, $customer_id]);
        
        if ($check_order->fetch()) {
            // Check if refund already requested
            $check_refund = $conn->prepare("
                SELECT id FROM refunds 
                WHERE order_id = ? AND customer_id = ?
            ");
            $check_refund->execute([$order_id_refund, $customer_id]);
            
            if (!$check_refund->fetch()) {
                $stmt = $conn->prepare("
                    INSERT INTO refunds (order_id, customer_id, reason) 
                    VALUES (?, ?, ?)
                ");
                if ($stmt->execute([$order_id_refund, $customer_id, $reason])) {
                    $success = "Ombi lako la kurudishwa kwa fedha limewasilishwa! Tutakujulisha baada ya kupitia.";
                    $_POST = []; // Clear form
                } else {
                    $error = "Hitilafu imetokea wakati wa kuwasilisha ombi.";
                }
            } else {
                $error = "Ombi la kurudishwa kwa fedha kwa oda hii tayari limewasilishwa.";
            }
        } else {
            $error = "Oda haipo au haijafika bado. Unaweza kuomba rudisho la fedha tu kwa oda zilizofika.";
        }
    } else {
        $error = "Tafadhali jaza namba ya oda na sababu ya kudai rudisho la fedha.";
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Omba Rudi Fedha - Order Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .refund-card {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .refund-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .order-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .requirements {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .requirement-item {
            margin-bottom: 10px;
            padding-left: 25px;
            position: relative;
        }
        .requirement-item:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #27ae60;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="refund-card">
        <div class="refund-header">
            <h2><i class="fas fa-undo me-2"></i> Omba Rudi Fedha</h2>
            <p>Wasilisha ombi lako la kurudishwa kwa fedha kwa oda yako</p>
        </div>
        
        <div class="p-4">
            <div class="mb-3">
                <a href="customer_dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Rudi Dashboard
                </a>
            </div>
            
            <?php if($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="requirements mb-4">
                <h5><i class="fas fa-list-check me-2"></i> Masharti ya Kudai Rudisho la Fedha</h5>
                <div class="requirement-item">Oda lazima iwe imefika (Delivered)</div>
                <div class="requirement-item">Oda lazima iwe na malipo yaliyoidhinishwa</div>
                <div class="requirement-item">Oda haipaswi kuwa imepita zaidi ya siku 30 tangu ilipofika</div>
                <div class="requirement-item">Kudai rudisho la fedha lazima lifanyike ndani ya siku 7 baada ya kupokea bidhaa</div>
            </div>
            
            <?php if ($order): ?>
            <div class="order-info">
                <h5>Oda Inayohusika</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID ya Oda:</strong> <?= $order['order_id'] ?></p>
                        <p><strong>Bidhaa:</strong> <?= htmlspecialchars($order['product_name']) ?></p>
                        <p><strong>Idadi:</strong> <?= $order['quantity'] ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Jumla:</strong> TZS <?= number_format($order['total_price'], 0) ?></p>
                        <p><strong>Hali ya Oda:</strong> 
                            <span class="badge bg-<?= $order['status'] == 'Delivered' ? 'success' : 'warning' ?>">
                                <?= $order['status'] ?>
                            </span>
                        </p>
                        <p><strong>Malipo:</strong> 
                            <span class="badge bg-<?= $order['payment_status'] == 'Approved' ? 'success' : 'warning' ?>">
                                <?= $order['payment_status'] ?? 'Pending' ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Namba ya Oda *</label>
                    <input type="text" name="order_id" class="form-control" 
                           value="<?= $order_id ?: ($_POST['order_id'] ?? '') ?>" 
                           placeholder="Mfano: ORD-20240115-001" required>
                    <div class="form-text">Ingiza namba ya oda unayotaka kudai rudisho la fedha</div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Sababu ya Kudai Rudisho la Fedha *</label>
                    <textarea name="reason" class="form-control" rows="5" 
                              placeholder="Eleza kwa kina kwa nini unataka kurudishwa kwa fedha..." required><?= $_POST['reason'] ?? '' ?></textarea>
                    <div class="form-text">Tafadhali eleza kwa kina tatizo lililokuwapo (mfano: bidhaa imeharibika, sio sawa na ilivyoelezwa, n.k)</div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Njia ya Kurudishwa Fedha</label>
                    <select class="form-select" name="refund_method">
                        <option value="M-Pesa">M-Pesa</option>
                        <option value="Airtel Money">Airtel Money</option>
                        <option value="Tigo Pesa">Tigo Pesa</option>
                        <option value="Bank Transfer">Uhamisho wa Benki</option>
                        <option value="Credit Card">Kadi ya Mkopo</option>
                    </select>
                    <div class="form-text">Chagua njia utakayopokea fedha zako zikirudishwa</div>
                </div>
                
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms">
                            Nakubali sheria na masharti ya kudai rudisho la fedha
                        </label>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-danger btn-lg">
                        <i class="fas fa-paper-plane me-2"></i> Wasilisha Ombi la Rudisho la Fedha
                    </button>
                </div>
            </form>
            
            <div class="mt-4 text-center">
                <p class="text-muted">
                    <i class="fas fa-clock me-1"></i> 
                    Ombi lako litapitiwa ndani ya siku 2-5 za kazi. Tutawasiliana nako kwa namba ya simu: 
                    <strong><?= htmlspecialchars($customer['phone']) ?></strong>
                </p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Character counter for reason
        const textarea = document.querySelector('textarea[name="reason"]');
        const charCount = document.createElement('small');
        charCount.className = 'form-text text-end';
        charCount.style.display = 'block';
        charCount.textContent = 'Herufi: 0';
        
        textarea.parentNode.appendChild(charCount);
        
        textarea.addEventListener('input', function() {
            charCount.textContent = 'Herufi: ' + this.value.length;
            if (this.value.length < 20) {
                charCount.style.color = '#dc3545';
                charCount.innerHTML += ' (Herufi chache sana)';
            } else if (this.value.length > 1000) {
                charCount.style.color = '#dc3545';
            } else if (this.value.length > 500) {
                charCount.style.color = '#ffc107';
            } else {
                charCount.style.color = '#198754';
            }
        });
    });
    </script>
</body>
</html>