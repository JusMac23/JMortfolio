<?php
// PHP Configurations
$title = "CDA-ICT Helpdesk System";
$year = date("Y"); // Dynamic year for the footer
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="icons/cdalogo.png" type="image/png">
    <link rel="stylesheet" href="css/index-styles.css">
    <script src="js/landing-page.js" defer></script>
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-laptop-code"></i> CDA-ICT Helpdesk System</h1>
            <nav>
                <ul class="nav-links">
                    <?php if (!isset($_SESSION['logged_in'])): ?>
                        <li><a href="login.php" class="btn login-btn"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <?php else: ?>
                        <li><a href="dashboard.php" class="btn dashboard-btn">Dashboard</a></li>
                        <li><a href="logout.php" class="btn logout-btn">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" style="background-image: url('images/hero-bg.jpg');">
        <div class="hero-content">
            <h2 class="hero-heading">Welcome to Your CDA-ICT Helpdesk System</h2>
            <p>We provide comprehensive IT support to ensure your technical challenges are addressed efficiently and effectively.</p>
            <a href="add-ticket.php" class="btn">Need Technical Assistance? Click <b><u>HERE</u></b></a> 
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer">
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> CDA ICTD. All rights reserved.</p>
            <p>For more information, contact us at <a href="https://mail.google.com/mail/u/1/#inbox?compose=new">ictd@cda.gov.ph</a></p>
            <p>Follow us on 
                <a href="https://facebook.com/CDAPhilippines" target="_blank" aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a> 
                <a href="https://twitter.com/CDAPhilippines" target="_blank" aria-label="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://youtube.com/CDAPhilippines" target="_blank" aria-label="Youtube">
                    <i class="fab fa-youtube"></i>
                </a>
            </p>
        </div>
    </footer>
</body>
</html>
