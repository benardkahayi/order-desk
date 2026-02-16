<?php
session_start();
?>
<!DOCTYPE html>
<html lang="En">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Desk - Usimamizi wa Oda na Malipo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #008060;
            --primary-dark: #006e52;
            --secondary-color: #6b7177;
            --light-color: #ffffff;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin:0;
            background: var(--light-color);
            color: #202123;
        }

        /* Navbar */
        .navbar {
            background: var(--light-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
        }

        .nav-link {
            color: var(--secondary-color) !important;
            font-weight: 500;
            margin: 0 0.5rem;
        }

        .btn-primary-custom {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary-custom:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-outline-custom {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 6px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-outline-custom:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Hero */
        .hero-section {
            padding: 8rem 1rem 4rem;
            text-align: center;
        }

        .hero-title {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            color: var(--secondary-color);
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .hero-cta {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* Features */
        .features-section {
            padding: 4rem 1rem;
            background: #f8f9fa;
        }

        .section-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            text-align: center;
            color: var(--secondary-color);
            margin-bottom: 3rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,128,96,0.1);
        }

        .feature-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        /* Footer */
        footer {
            background: #202123;
            color: white;
            padding: 3rem 1rem 2rem;
        }

        footer a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: var(--transition);
        }

        footer a:hover {
            color: white;
        }

        .copyright {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.5);
        }

        @media (max-width: 768px){
            .hero-title{font-size:2rem;}
            .hero-subtitle{font-size:1rem;}
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shopping-cart me-1"></i> AB Store China 
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if (isset($_SESSION['role'])): ?>
                        <?php if ($_SESSION['role']=='customer'): ?>
                            <li class="nav-item ms-2"><a class="btn btn-primary-custom" href="customer_dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                            <li class="nav-item ms-2"><a class="btn btn-outline-custom" href="customer_logout.php"><i class="fas fa-sign-out-alt me-1"></i> Ondoka</a></li>

                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item ms-2"><a class="btn btn-outline-custom" href="customer_login.php"><i class="fas fa-user me-1"></i> Mteja Login</a></li>
   
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <h1 class="hero-title">Usimamizi wa Oda na Malipo </h1>
        <p class="hero-subtitle">Mfumo rahisi, salama na wa kisasa wa kufuatilia oda,na kuhifadhi taarifa.</p>
        <div class="hero-cta">
            <a href="customer_login.php" class="btn btn-primary-custom btn-lg">Mteja Login</a>
          
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <h2 class="section-title">Vipengele Muhimu</h2>
        <p class="section-subtitle">Zilizoundwa kusaidia oda zako kufanikisha kila operesheni kwa urahisi.</p>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shopping-cart"></i></div>
                    <h4>Usimamizi wa Oda</h4>
                    <p>Kufuatilia, kusimamia na kupata ripoti za oda zako kwa urahisi.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-credit-card"></i></div>
                    <h4>Malipo Salama</h4>
                    <p>Fanya malipo kwa njia mbalimbali kama M-Pesa, benki au kadi kwa usalama.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-user-friends"></i></div>
                    <h4>Usimamizi wa Wateja</h4>
                    <p>Hifadhi taarifa za wateja na historia yao ya oda kwa urahisi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p>Wasiliana nasi: <i class="fas fa-phone me-1"></i> +255 759 431 401 | <i class="fas fa-envelope me-1"></i> benardkahayi@gmail.com</p>
            <div class="copyright">&copy; <?php echo date('Y'); ?> Order Desk. Haki zote zimehifadhiwa.</div>
        </div>
    </footer>
</body>
</html>
