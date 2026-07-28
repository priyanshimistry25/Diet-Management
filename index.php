<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriLife – Diet Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
</head>
<body>

    <!-- ===== HEADER ===== -->
    <!-- <?php include 'header.php'; ?> -->
    <!-- For preview without PHP, replace above with: -->
    <header class="site-header">
        <div class="header-inner">
            <div class="logo">
                <span class="logo-icon">🌿</span>
                <span class="logo-text">NutriTrack</span>
            </div>
            <nav class="nav-links"> 
                <!-- <a href="#">Home</a> -->
                <!-- <a href="#">Features</a> -->
                 <a href="registration.php" >Registration</a>
                <a href="login.php">Login</a>
            </nav>
        </div>
    </header>

    <!-- ===== HERO SECTION ===== -->
    <section class="hero">
        <div class="hero-badge">Diet Management System</div>
        <h1 class="hero-title">
            Welcome to<br>
            <em>Fitness World</em>
        </h1>
        <p class="hero-subtitle">
            Your all-in-one platform to track nutrition, consult dietitians,<br>
            set personal health goals, and connect your fitness devices.
        </p>
        <div class="hero-cta">
            <a href="registration.php" class="btn-primary">Get Started — Register Free</a>
            <a href="login.php" class="btn-ghost">Already a member? Login</a>
        </div>

        <!-- Decorative floating cards -->
        <!-- <div class="hero-float-card card-1">
            <span class="card-icon">🥗</span>
            <span>Meal Planner</span>
        </div>
        <div class="hero-float-card card-2">
            <span class="card-icon">📊</span>
            <span>Progress Charts</span>
        </div>
        <div class="hero-float-card card-3">
            <span class="card-icon">💧</span>
            <span>Hydration Tracker</span>
        </div> -->
    </section>

    <!-- ===== FEATURES SECTION ===== -->
    <section class="features" id="features">
        <div class="section-label">What We Offer</div>
        <h2 class="section-title">Everything You Need to Stay Healthy</h2>

        <div class="features-grid">

            <div class="feature-card">
                <div class="feature-icon-wrap green">🥦</div>
                <h3>Stay Fit & Healthy</h3>
                <p>Track your daily calorie intake, macros, and activity levels with smart insights personalised just for you.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrap teal">👨‍⚕️</div>
                <h3>Book a Dietitian</h3>
                <p>Schedule appointments with certified dietitians and nutritionists from the comfort of your home.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrap amber">🎯</div>
                <h3>Set Health Goals</h3>
                <p>Define weight-loss, muscle-gain, or wellness targets and monitor your progress with detailed dashboards.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-wrap coral">⌚</div>
                <h3>Connect Devices</h3>
                <p>Sync your fitness bands, smartwatches, and health apps for automatic, real-time data tracking.</p>
            </div>

        </div>
    </section>

    <!-- ===== HOW IT WORKS SECTION ===== -->
    <section class="how-it-works">
        <div class="section-label">Simple Steps</div>
        <h2 class="section-title">Get Started in Minutes</h2>

        <div class="steps-row">
            <div class="step">
                <div class="step-number">01</div>
                <h4>Create Your Account</h4>
                <p>Register with your basic details and health information to personalise your experience.</p>
            </div>
            <div class="step-divider"></div>
            <div class="step">
                <div class="step-number">02</div>
                <h4>Set Your Goals</h4>
                <p>Tell us what you want to achieve — weight loss, better stamina, or a healthier lifestyle.</p>
            </div>
            <div class="step-divider"></div>
            <div class="step">
                <div class="step-number">03</div>
                <h4>Track & Improve</h4>
                <p>Log meals, workouts, and get AI-powered recommendations from your personalised dashboard.</p>
            </div>
        </div>
    </section>

    <!-- ===== STATS SECTION ===== -->
    <!-- <section class="stats">
        <div class="stat-item">
            <div class="stat-number">10,000+</div>
            <div class="stat-label">Active Users</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="stat-number">200+</div>
            <div class="stat-label">Certified Dietitians</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="stat-number">50,000+</div>
            <div class="stat-label">Meals Logged Daily</div>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <div class="stat-number">4.9★</div>
            <div class="stat-label">User Rating</div>
        </div>
    </section> -->

    <!-- ===== CTA BANNER ===== -->
    <section class="cta-banner">
        <h2>Ready to Transform Your Health?</h2>
        <p>Join thousands of users who've already started their wellness journey with NutriLife.</p>
        <a href="registration.php" class="btn-primary large">Register Now — It's Free</a>
    </section>

    <!-- ===== FOOTER ===== -->
    <!-- <?php include 'footer.php'; ?> -->
    <!-- For preview without PHP, replace above with: -->
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <span class="logo-icon">🌿</span>
                <span class="logo-text">NutriTrack</span>
                <p>Your trusted partner in health and wellness.</p>
            </div>
            <div class="footer-links">
                <a href="privacy.php">Privacy Policy</a>
                <a href="terms.php">Terms of Service</a>
                <a href="contact.php">Contact Us</a>
                <a href="support.php">Support</a>
            </div>
            <p class="footer-copy">&copy; 2026 NutriLife Diet Management System. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>

