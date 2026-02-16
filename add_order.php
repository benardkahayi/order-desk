<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// Get customers for dropdown
$customers = $conn->query("SELECT id, name, phone FROM customers ORDER BY name")->fetchAll();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $product_name = trim($_POST['product_name'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 1);
    $unit_price = (float)($_POST['unit_price'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($customer_id <= 0 || empty($product_name)) {
        $error = "Chagua mteja na andika jina la bidhaa.";
    } elseif ($quantity <= 0 || $unit_price <= 0) {
        $error = "Idadi na bei lazima ziwe zaidi ya sifuri.";
    } else {
        try {
            // Get customer details
            $stmt = $conn->prepare("SELECT name, phone FROM customers WHERE id = ?");
            $stmt->execute([$customer_id]);
            $customer = $stmt->fetch();

            if (!$customer) {
                throw new Exception("Mteja hajapatikana.");
            }

            // Generate unique order ID
            $order_id = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
            $total_price = $quantity * $unit_price;

            // Insert order
            $stmt = $conn->prepare("
                INSERT INTO orders 
                (order_id, customer_id, customer_name, phone, product_name, 
                 quantity, unit_price, total_price, description, status)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Processing')
            ");
            
            $stmt->execute([
                $order_id,
                $customer_id,
                $customer['name'],
                $customer['phone'],
                $product_name,
                $quantity,
                $unit_price,
                $total_price,
                $description
            ]);

            $success = "✅ Oda imehifadhiwa! Order ID: <strong>$order_id</strong>";
            $_POST = [];

        } catch (PDOException $e) {
            $error = "Hitilafu ya database: " . $e->getMessage();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingiza Oda - Admin</title>
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
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .total-box {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
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
                <a href="add_order.php" class="active">
                    <i class="fas fa-shopping-cart me-2"></i> Ingiza Oda
                </a>
                <a href="orders.php">
                    <i class="fas fa-clipboard-list me-2"></i> Oda
                </a>
                <a href="payments.php">
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
                <i class="fas fa-shopping-cart me-2"></i> Ingiza Oda Mpya
            </h2>
            <a href="orders.php" class="btn btn-secondary">
                <i class="fas fa-list me-1"></i> Orodha ya Oda
            </a>
        </div>
        
        <div class="form-container">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="orderForm">
                <div class="mb-3">
                    <label class="form-label fw-bold">Mteja *</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">-- Chagua Mteja --</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>">
                                <?= htmlspecialchars($customer['name'] . ' - ' . $customer['phone']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Jina la Bidhaa *</label>
                    <input type="text" name="product_name" class="form-control" 
                           value="<?= htmlspecialchars($_POST['product_name'] ?? '') ?>" 
                           placeholder="Mfano: Laptop Dell" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Idadi *</label>
                        <input type="number" name="quantity" class="form-control" 
                               value="<?= $_POST['quantity'] ?? 1 ?>" min="1" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Bei kwa Kimoja (TZS) *</label>
                        <input type="number" name="unit_price" class="form-control" 
                               value="<?= $_POST['unit_price'] ?? '' ?>" min="0" step="0.01" required>
                    </div>
                </div>
                
                <div class="total-box">
                    <h5>Jumla ya Malipo: <span id="totalAmount" class="text-success">TZS 0.00</span></h5>
                    <small class="text-muted">(Idadi × Bei kwa Kimoja)</small>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Maelezo ya Ziada</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="Maelezo ya ziada kuhusu oda..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Hifadhi Oda
                    </button>
                    
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-redo me-2"></i> Safisha Fomu
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const quantityInput = document.querySelector('[name="quantity"]');
    const priceInput = document.querySelector('[name="unit_price"]');
    const totalAmount = document.getElementById('totalAmount');
    
    function calculateTotal() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const total = quantity * price;
        totalAmount.textContent = 'TZS ' + total.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    quantityInput.addEventListener('input', calculateTotal);
    priceInput.addEventListener('input', calculateTotal);
    
    // Initial calculation
    calculateTotal();
    
    // Reset form after successful submission
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                this.reset();
                calculateTotal();
            }, 1000);
        }
    });
    </script>
</body>
</html>