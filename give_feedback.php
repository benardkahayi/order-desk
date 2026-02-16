<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: customer_login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = (int)($_POST['rating'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    
    if ($rating >= 1 && $rating <= 5 && !empty($message)) {
        $stmt = $conn->prepare("
            INSERT INTO feedback (customer_phone, rating, message) 
            VALUES (?, ?, ?)
        ");
        if ($stmt->execute([$customer['phone'], $rating, $message])) {
            $success = "Asante kwa maoni yako!";
            $_POST = []; // Clear form
        } else {
            $error = "Hitilafu imetokea wakati wa kuhifadhi maoni.";
        }
    } else {
        $error = "Tafadhali chagua rating na uandike ujumbe wa maoni.";
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toa Maoni - Order Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .feedback-card {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .feedback-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .star-rating {
            direction: rtl;
            text-align: center;
            margin: 20px 0;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            font-size: 40px;
            color: #ccc;
            padding: 10px;
            cursor: pointer;
            transition: color 0.3s;
        }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffc107;
        }
        textarea {
            resize: vertical;
            min-height: 150px;
        }
    </style>
</head>
<body>
    <div class="feedback-card">
        <div class="feedback-header">
            <h2><i class="fas fa-comment-dots me-2"></i> Toa Maoni Yako</h2>
            <p>Tupe maoni yako kuhusu huduma yetu</p>
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
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Mteja: <?= htmlspecialchars($customer['name']) ?></h5>
                    <p class="card-text">Simu: <?= htmlspecialchars($customer['phone']) ?></p>
                    <small class="text-muted">Maoni yako yatasaidia kuboresha huduma zetu.</small>
                </div>
            </div>
            
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label fw-bold">Ukadiriaji (Nyota 1-5)</label>
                    <p class="text-muted mb-2">1 - Duni, 5 - Bora sana</p>
                    
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5">
                        <label for="star5">★</label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4">★</label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3">★</label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2">★</label>
                        <input type="radio" id="star1" name="rating" value="1" checked>
                        <label for="star1">★</label>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Maoni Yako</label>
                    <textarea name="message" class="form-control" 
                              placeholder="Andika maoni yako hapa..." required><?= $_POST['message'] ?? '' ?></textarea>
                    <div class="form-text">Tafadhali eleza uzoefu wako na huduma zetu kwa kina.</div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i> Tuma Maoni
                    </button>
                </div>
            </form>
            
            <div class="mt-4">
                <h6><i class="fas fa-info-circle me-2"></i> Mwongozo wa Maoni</h6>
                <ul class="text-muted">
                    <li>Eleza uzoefu wako wote kuhusu oda yako</li>
                    <li>Tupe mapendekezo ya kuboresha huduma zetu</li>
                    <li>Maoni yako yatatibiwa kwa siri</li>
                    <li>Tutajibu maoni yako ndani ya siku 2 za kazi</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-select 5 stars by default
        document.getElementById('star5').checked = true;
        
        // Character counter for feedback
        const textarea = document.querySelector('textarea[name="message"]');
        const charCount = document.createElement('small');
        charCount.className = 'form-text text-end';
        charCount.style.display = 'block';
        charCount.textContent = 'Herufi: 0';
        
        textarea.parentNode.appendChild(charCount);
        
        textarea.addEventListener('input', function() {
            charCount.textContent = 'Herufi: ' + this.value.length;
            if (this.value.length > 500) {
                charCount.style.color = '#dc3545';
            } else if (this.value.length > 300) {
                charCount.style.color = '#ffc107';
            } else {
                charCount.style.color = '#198754';
            }
        });
    });
    </script>
</body>
</html>