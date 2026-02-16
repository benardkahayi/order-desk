<?php
session_start();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wasiliana Nasi - Order Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .contact-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 50px;
        }
        .contact-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        .contact-card:hover {
            transform: translateY(-5px);
        }
        .contact-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 20px;
        }
        .contact-form {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 40px;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shopping-cart me-2"></i>Order Desk
            </a>
            <div class="navbar-nav">
                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] == 'customer'): ?>
                        <a class="nav-link" href="customer_dashboard.php">Dashboard</a>
                        <a class="nav-link" href="customer_logout.php">Ondoka</a>
                    <?php elseif ($_SESSION['role'] == 'admin'): ?>
                        <a class="nav-link" href="admin/admin_dashboard.php">Admin Dashboard</a>
                        <a class="nav-link" href="admin_logout.php">Ondoka</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a class="nav-link" href="customer_login.php">Mteja Login</a>
                    <a class="nav-link" href="admin_login.php">Admin Login</a>
                <?php endif; ?>
                <a class="nav-link active" href="contact.php">Wasiliana</a>
            </div>
        </div>
    </nav>

    <!-- Contact Header -->
    <div class="contact-header">
        <div class="container">
            <h1 class="display-4">Wasiliana Nasi</h1>
            <p class="lead">Tuko hapa kukusikiliza na kukusaidia</p>
        </div>
    </div>

    <div class="container">
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="contact-card text-center">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h4>Simu</h4>
                    <p class="text-muted">Tupigie kwa maswali yoyote</p>
                    <h5>+255 759 431 401</h5>
                    <small>Piga kati ya saa 8:00 asubuhi - 6:00 usiku</small>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="contact-card text-center">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h4>Barua Pepe</h4>
                    <p class="text-muted">Tutumie barua pepe kwa maswali yoyote</p>
                    <h5>benardkahayi@gmail.com</h5>
                    <small>Tunajibu ndani ya masaa 24</small>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="contact-card text-center">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h4>Ofisi</h4>
                    <p class="text-muted">Tembelea ofisi zetu</p>
                    <h5>Dar es Salaam</h5>
                    <small>Mbezi,magarisaba</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="contact-form">
                    <h3 class="mb-4">Tuma Ujumbe</h3>
                    
                    <?php
                    $success = '';
                    $error = '';
                    
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $name = trim($_POST['name'] ?? '');
                        $email = trim($_POST['email'] ?? '');
                        $subject = trim($_POST['subject'] ?? '');
                        $message = trim($_POST['message'] ?? '');
                        
                        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                            $error = "Tafadhali jaza sehemu zote zinazohitajika.";
                        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $error = "Barua pepe si sahihi.";
                        } else {
                            // In a real application, you would send an email here
                            $success = "Asante kwa ujumbe wako! Tutawasiliana nako hivi karibuni.";
                            // Clear form
                            $_POST = [];
                        }
                    }
                    ?>
                    
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
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jina Lako *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?= $_POST['name'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Barua Pepe *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?= $_POST['email'] ?? '' ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Mada *</label>
                            <input type="text" name="subject" class="form-control" 
                                   value="<?= $_POST['subject'] ?? '' ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Ujumbe *</label>
                            <textarea name="message" class="form-control" rows="6" 
                                      required><?= $_POST['message'] ?? '' ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="newsletter">
                                <label class="form-check-label" for="newsletter">
                                    Ningependa kupokea taarifa za mabadiliko na huduma mpya
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i> Tuma Ujumbe
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Maswali Yanayoulizwa Mara Kwa Mara</h5>
                        
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        Ninawezaje kubadilisha oda yangu?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Oda inaweza kubadilishwa kabla ya kusafirishwa. Wasiliana nasi kwa simu au barua pepe.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Muda wa ugavi ni upi?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Muda wa ugavi ni siku 2-5 za kazi kwa nchi, na siku 7-14 za kazi kwa anwani za kimataifa.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        Ninawezaje kufuatilia oda yangu?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Ingia kwenye akaunti yako na nenda kwenye sehemu ya "Oda Zangu" ili kufuatilia hali ya oda yako.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                        Njia zipi za malipo zinazokubalika?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Tunakubali M-Pesa, Airtel Money, Tigo Pesa, uhamisho wa benki, na kadi za mkopo.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Muda wa Huduma</h5>
                        <ul class="list-unstyled">
                            <li><strong>Jumatatu - Ijumaa:</strong> 8:00 AM - 6:00 PM</li>
                            <li><strong>Jumamosi:</strong> 9:00 AM - 2:00 PM</li>
                            <li><strong>Jumapili:</strong> Imefungwa</li>
                        </ul>
                        <p class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Kwa dharura wakati wa wikendi, tumia barua pepe.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h4>Jisikie huru kuwasiliana nasi</h4>
                        <p class="text-muted">Timu yetu ya usaidizi inafanya kazi masaa 24 kukuhudumia.</p>
                        <div class="mt-3">
                            <a href="tel:+255712345678" class="btn btn-outline-primary me-2">
                                <i class="fas fa-phone me-1"></i> Piga Sasa
                            </a>
                            <a href="mailto:info@orderdesk.co.tz" class="btn btn-outline-success">
                                <i class="fas fa-envelope me-1"></i> Tuma Barua Pepe
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Order Desk System</h5>
                    <p>Mfumo bora wa usimamizi wa oda na malipo.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>&copy; <?php echo date('Y'); ?> Order Desk. Haki zote zimehifadhiwa.</p>
                    <p>
                        <a href="index.php" class="text-white me-3">Home</a>
                        <a href="contact.php" class="text-white me-3">Wasiliana</a>
                        <a href="#" class="text-white">Sheria</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Character counter for message
    const messageTextarea = document.querySelector('textarea[name="message"]');
    if (messageTextarea) {
        const charCount = document.createElement('small');
        charCount.className = 'form-text text-end';
        charCount.style.display = 'block';
        charCount.textContent = 'Herufi: 0';
        
        messageTextarea.parentNode.appendChild(charCount);
        
        messageTextarea.addEventListener('input', function() {
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
    }
    </script>
</body>
</html>