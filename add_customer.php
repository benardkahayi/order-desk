<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = trim($_POST['password'] ?? $phone); // Default to phone number
    
    if (empty($name) || empty($phone)) {
        $error = "Jina na namba ya simu vinahitajika!";
    } else {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("
                INSERT INTO customers (name, phone, email, address, password) 
                VALUES (:name, :phone, :email, :address, :password)
            ");
            $stmt->execute([
                ':name' => $name,
                ':phone' => $phone,
                ':email' => $email,
                ':address' => $address,
                ':password' => $hashed_password
            ]);
            
            $success = "Mteja ameingizwa kikamilifu!";
            $_POST = [];
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Namba ya simu tayari imesajiliwa!";
            } else {
                $error = "Hitilafu: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingiza Mteja - Admin</title>
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
                <a href="add_customer.php" class="active">
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
                <i class="fas fa-user-plus me-2"></i> Ingiza Mteja Mpya
            </h2>
            <a href="customers.php" class="btn btn-secondary">
                <i class="fas fa-list me-1"></i> Orodha ya Wateja
            </a>
        </div>
        
        <div class="form-container">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user me-1"></i> Jina Kamili la Mteja *
                        </label>
                        <input type="text" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                               placeholder="Mfano: John Doe" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-phone me-1"></i> Namba ya Simu *
                        </label>
                        <input type="tel" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                               placeholder="Mfano: 0712345678" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-envelope me-1"></i> Barua Pepe
                        </label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               placeholder="Mfano: mteja@example.com">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-lock me-1"></i> Password
                        </label>
                        <input type="text" name="password" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>" 
                               placeholder="Acha tupu kutumia namba ya simu">
                        <div class="form-text">Acha tupu kutumia namba ya simu kama password</div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-map-marker-alt me-1"></i> Anwani
                    </label>
                    <textarea name="address" class="form-control" rows="3"
                              placeholder="Mfano: Sokoine Street, Dar es Salaam"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Hifadhi Mteja
                    </button>
                    
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-redo me-2"></i> Safisha Fomu
                    </button>
                </div>
            </form>
            
            <hr class="my-4">
            
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle me-2"></i> Maelezo:</h6>
                <ul class="mb-0">
                    <li>Namba ya simu lazima iwe ya kipekee kwa kila mteja</li>
                    <li>Kwa password, kama utaacha tupu, mteja atatumia namba ya simu kama password</li>
                    <li>Mteja atakuwa na uwezo wa kubadilisha password baada ya kuingia kwa mara ya kwanza</li>
                    <li>Ukishahifadhi, mteja ataweza kuingia kwenye mfumo wa wateja kwa namba ya simu</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>