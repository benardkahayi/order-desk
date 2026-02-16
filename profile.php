<?php
session_start();
require 'db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Get customer info
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

if (!$customer) {
    echo "Mteja haipo!";
    exit;
}

// Handle update form
$success_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $new_password = trim($_POST['new_password']);

    try {
        // Prepare update query
        $update_query = "UPDATE customers SET name=?, phone=?, email=?, address=?";
        $params = [$name, $phone, $email, $address];
        
        // Add password update if provided
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query .= ", password=?";
            $params[] = $hashed_password;
        }
        
        $update_query .= " WHERE id=?";
        $params[] = $customer_id;
        
        $update = $conn->prepare($update_query);
        $update->execute($params);
        
        $success_message = "Profaili imehifadhiwa kikamilifu!";
        
        // Update session
        $_SESSION['customer_name'] = $name;
        $_SESSION['customer_phone'] = $phone;
        
        // Refresh data
        $stmt->execute([$customer_id]);
        $customer = $stmt->fetch();
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error_message = "Namba ya simu tayari imesajiliwa na mteja mwingine!";
        } else {
            $error_message = "Hitilafu: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profaili Yangu - Order Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .profile-card {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .profile-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: #3498db;
            font-size: 40px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h3><?php echo htmlspecialchars($customer['name']); ?></h3>
                <p class="mb-0"><?php echo $customer['phone']; ?></p>
                <a href="customer_dashboard.php" class="btn btn-light btn-sm mt-3">
                    <i class="fas fa-arrow-left me-1"></i> Rudi Dashboard
                </a>
            </div>
            
            <div class="p-4">
                <?php if($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?= $success_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i> <?= $error_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <h5 class="mb-4"><i class="fas fa-user-edit me-2"></i> Badilisha Taarifa Zako</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jina Kamili</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?= htmlspecialchars($customer['name']) ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Namba ya Simu</label>
                            <input type="text" name="phone" class="form-control" 
                                   value="<?= htmlspecialchars($customer['phone']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Barua Pepe</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($customer['email']) ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nywila Mpya (Si lazima)</label>
                            <input type="password" name="new_password" class="form-control" 
                                   placeholder="Acha tupu kama hutaki kubadilisha">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Anwani</label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($customer['address']) ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Hifadhi Mabadiliko
                        </button>
                        
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-redo me-2"></i> Rejesha
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="account-info">
                    <h6><i class="fas fa-info-circle me-2"></i> Maelezo ya Akaunti</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>ID ya Akaunti:</strong> <?= $customer['id'] ?></p>
                            <p class="mb-1"><strong>Tarehe ya Kujiunga:</strong> <?= date('d/m/Y', strtotime($customer['date_created'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Jukumu:</strong> Mteja</p>
                            <p class="mb-1"><strong>Hali ya Akaunti:</strong> <span class="badge bg-success">Inatumika</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>