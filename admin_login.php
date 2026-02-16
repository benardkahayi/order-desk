<?php
session_start();
require_once __DIR__ . '/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['role'] = 'admin';

            header("Location: admin/admin_dashboard.php");
            exit;
        } else {
            $error = "Jina la mtumiaji au password si sahihi.";
        }
    } else {
        $error = "Tafadhali jaza jina la mtumiaji na password.";
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Order Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .admin-login-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        .admin-icon {
            background: #2c3e50;
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 30px;
        }
        .form-control {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #2c3e50;
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
        }
        .btn-admin-login {
            background: #2c3e50;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            width: 100%;
            transition: background 0.3s;
        }
        .btn-admin-login:hover {
            background: #1a252f;
        }
    </style>
</head>
<body>
    <div class="admin-login-card">
        <div class="text-center mb-4">
            <div class="admin-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h3>Admin Login</h3>
            <p class="text-muted">Ingia kwenye paneli ya usimamizi</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Jina la Mtumiaji</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="username" 
                           placeholder="Ingiza jina la mtumiaji" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                    <input type="password" class="form-control" name="password" 
                           placeholder="Ingiza password" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-admin-login mb-3">
                <i class="fas fa-sign-in-alt me-2"></i> Ingia kwenye Admin
            </button>
            
            <div class="text-center">
                <a href="index.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-home me-1"></i> Rudi Homepage
                </a>
            </div>
        </form>
        
        <div class="mt-4 text-center">
            <p class="text-muted small">
                <i class="fas fa-shield-alt me-1"></i>
                Panel hii inaweza kufikiwa na wataalamu tu.
                <br>
                <strong>Default Credentials:</strong> admin / admin123
            </p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>