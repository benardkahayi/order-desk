<?php
session_start();
require '../db.php';

// --------------------
// Check if admin is logged in
// --------------------
if (!isset($_SESSION['admin_logged_in']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../admin_login.php");
    exit();
}

// --------------------
// Initialize variables
// --------------------
$customers = [];  // Prevent undefined variable warning
$search = $_GET['search'] ?? '';

// --------------------
// Handle deletion
// --------------------
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: customers.php?deleted=1");
        exit();
    }
}

// --------------------
// Fetch customers
// --------------------
if (!empty($search)) {
    // Search query
    $stmt = $conn->prepare("
        SELECT * FROM customers 
        WHERE name LIKE ? OR phone LIKE ? OR email LIKE ?
        ORDER BY id DESC
    ");
    $searchTerm = "%$search%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Fetch all
    $stmt = $conn->query("SELECT * FROM customers ORDER BY id DESC");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wateja - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
        .sidebar { width: 250px; background: #2c3e50; color: white; position: fixed; height: 100vh; }
        .sidebar a { color: #ecf0f1; padding: 12px 20px; display: block; text-decoration: none; border-left: 3px solid transparent; transition: all 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: #34495e; border-left: 3px solid #3498db; padding-left: 25px; }
        .main-content { margin-left: 250px; padding: 20px; }
        .table th { background-color: #2c3e50; color: white; }
        .action-btns { display: flex; gap: 5px; }
        @media (max-width: 768px) { .sidebar { width: 100%; position: relative; height: auto; } .main-content { margin-left: 0; } .action-btns { flex-direction: column; } }
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
            <nav>
                <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                <a href="add_customer.php"><i class="fas fa-user-plus me-2"></i> Ingiza Mteja</a>
                <a href="customers.php" class="active"><i class="fas fa-users me-2"></i> Wateja</a>
                <a href="add_order.php"><i class="fas fa-shopping-cart me-2"></i> Ingiza Oda</a>
                <a href="orders.php"><i class="fas fa-clipboard-list me-2"></i> Oda</a>
                <a href="payments.php"><i class="fas fa-money-bill-wave me-2"></i> Malipo</a>
                <hr class="bg-light">
                <a href="../admin_logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Ondoka</a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary"><i class="fas fa-users me-2"></i> Orodha ya Wateja</h2>
            <div>
                <span class="badge bg-primary fs-6">Jumla: <?= count($customers) ?></span>
                <a href="add_customer.php" class="btn btn-success ms-2"><i class="fas fa-user-plus me-1"></i> Mteja Mpya</a>
            </div>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> Mteja amefutwa kikamilifu!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Search Box -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tafuta mteja kwa jina, simu au email...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Tafuta</button>
                    </div>
                </form>
                <?php if (!empty($search)): ?>
                    <div class="mt-2">
                        <a href="customers.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times me-1"></i> Ondoa Utafutaji</a>
                        <span class="text-muted ms-2">Matokeo: <?= count($customers) ?> mteja</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if (count($customers) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Jina</th>
                                    <th>Simu</th>
                                    <th>Email</th>
                                    <th>Anwani</th>
                                    <th>Tarehe</th>
                                    <th>Vitendo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?= $customer['id'] ?></td>
                                        <td>
                                            <?= htmlspecialchars($customer['name']) ?>
                                            <?php if (($customer['role'] ?? '') === 'admin'): ?>
                                                <span class="badge bg-danger ms-1">Admin</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($customer['phone']) ?></td>
                                        <td><?= htmlspecialchars($customer['email'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars(substr($customer['address'] ?? '', 0, 30)) . (strlen($customer['address'] ?? '') > 30 ? '...' : '') ?></td>
                                        <td><?= !empty($customer['date_created']) ? date('d/m/Y', strtotime($customer['date_created'])) : '-' ?></td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="customer_orders.php?id=<?= $customer['id'] ?>" class="btn btn-sm btn-info" title="Angalia oda"><i class="fas fa-shopping-cart"></i></a>
                                                <a href="edit_customer.php?id=<?= $customer['id'] ?>" class="btn btn-sm btn-warning" title="Hariri"><i class="fas fa-edit"></i></a>
                                                <button onclick="deleteCustomer(<?= $customer['id'] ?>)" class="btn btn-sm btn-danger" title="Futa" <?= (($customer['role'] ?? '') === 'admin') ? 'disabled' : '' ?>><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Hakuna Wateja Bado</h4>
                        <p class="text-muted"><?= !empty($search) ? "Hakuna mteja aliyepatikana kwa utaftuaji wako." : "Bado hujaingiza mteja yeyote. Anza kwa kuongeza mteja mpya." ?></p>
                        <a href="add_customer.php" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i> Ingiza Mteja wa Kwanza</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteCustomer(id) {
            if (confirm('Una uhakika unataka kufuta mteja huyu?\nHii haitaweza kurekebishwa na oda zote za mteja zitafutwa pia.')) {
                window.location.href = 'customers.php?delete=' + id;
            }
        }
    </script>
</body>
</html>
