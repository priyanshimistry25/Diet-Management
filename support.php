<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support – NutriTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="footerpages.css">
</head>
<body>

    <!-- <?php include 'header.php'; ?> -->
    <header class="site-header">
        <div class="header-inner">
            <div class="logo">
                <a href="index.php" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
                    <span class="logo-icon">🌿</span>
                    <span class="logo-text">NutriTrack</span>
                </a>
            </div>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="contact.php">Contact</a>
                <a href="login.php" class="btn-nav">Login</a>
            </nav>
        </div>
    </header>

    <div class="page-hero">
        <div class="page-hero-inner">
            <div class="page-badge">Help Centre</div>
            <h1 class="page-title">How can we help you?</h1>
            <p class="page-subtitle">Browse help topics below or contact our support team directly.</p>
        </div>
    </div>

    <main class="support-layout">

        <!-- Help Categories -->
        <section class="help-categories">
            <div class="help-grid">

                <a href="#account-faq" class="help-card">
                    <div class="help-card-icon green">👤</div>
                    <h3>Account & Profile</h3>
                    <p>Registration, login issues, changing your details</p>
                </a>

                <a href="#diet-faq" class="help-card">
                    <div class="help-card-icon teal">🥗</div>
                    <h3>Meal Tracking</h3>
                    <p>Logging meals, calorie counts, food database</p>
                </a>

                <a href="#appointment-faq" class="help-card">
                    <div class="help-card-icon amber">📅</div>
                    <h3>Dietitian Appointments</h3>
                    <p>Booking, cancelling, rescheduling consultations</p>
                </a>

                <a href="#device-faq" class="help-card">
                    <div class="help-card-icon coral">⌚</div>
                    <h3>Device Sync</h3>
                    <p>Connecting fitness bands, syncing issues</p>
                </a>

                <a href="#billing-faq" class="help-card">
                    <div class="help-card-icon green">💳</div>
                    <h3>Billing & Plans</h3>
                    <p>Subscription, payments, invoices, refunds</p>
                </a>

                <a href="#contact.php" class="help-card">
                    <div class="help-card-icon teal">💬</div>
                    <h3>Contact Support</h3>
                    <p>Didn't find an answer? Reach our team directly</p>
                </a>

            </div>
        </section>

        <!-- FAQ Sections -->
        <section class="faq-section">
            <h2 class="faq-section-title" id="account-faq">Account & Profile</h2>
            <div class="faq-list">
                <details class="faq-item">
                    <summary>How do I create a NutriTrack account?</summary>
                    <div class="faq-answer">
                        <p>Click <strong>Register Free</strong> on the homepage. Enter your name, email address, and a secure password. You'll receive a verification email — click the link to activate your account. The whole process takes under 2 minutes.</p>
                    </div>
                </details>
                <details class="faq-item">
                    <summary>I forgot my password. How do I reset it?</summary>
                    <div class="faq-answer">
                        <p>On the login page, click <strong>Forgot Password?</strong> Enter your registered email address and we'll send you a password reset link. The link expires after 30 minutes for security reasons.</p>
                    </div>
                </details>
                <details class="faq-item">
                    <summary>How do I update my personal health information?</summary>
                    <div class="faq-answer">
                        <p>Go to <strong>Dashboard → Profile → Edit Profile</strong>. You can update your weight, height, age, dietary preferences, and health goals at any time. Changes take effect immediately.</p>
                    </div>
                </details>
                <details class="faq-item">
                    <summary>How do I delete my account?</summary>
                    <div class="faq-answer">
                        <p>Go to <strong>Settings → Account → Delete Account</strong>. You'll be asked to confirm your password. All your data will be permanently deleted within 30 days. You can export your data before deletion.</p>
                    </div>
                </details>
            </div>

            <h2 class="faq-section-title" id="diet-faq">Meal Tracking</h2>
            <div class="faq-list">
                <details class="faq-item">
                    <summary>How do I log a meal?</summary>
                    <div class="faq-answer">
                        <p>From your Dashboard, click <strong>Log Meal</strong> or tap the <strong>+</strong> button. Search for a food item by name, select the quantity and serving size, then save. You can also scan barcodes for packaged foods using the mobile app.</p>
                    </div>
                </details>
                <details class="faq-item">
                    <summary>The food I'm looking for isn't in the database. What can I do?</summary>
                    <div class="faq-answer">
                        <p>You can add a custom food item. Click <strong>Add Custom Food</strong>, enter the name, serving size, and nutritional values (calories, protein, carbs, fat). Your custom food will be saved for future use.</p>
                    </div>
                </details>
                <details class="faq-item">
                    <summary>Can I set up recurring meals or meal plans?</summary>
                    <div class="faq-answer">
                        <p>Yes. Go to <strong>Meal Planner</strong> and create a weekly meal template. You can copy meals from previous days and set favourites for quick logging. Premium users can also get AI-generated personalised meal plans.</p>
                    </div>
                </details>
            </div>

            <h2 class="faq-section-title" id="appointment-faq">Dietitian Appointments</h2>
            <div class="faq-list">
                <details class="faq-item">
                    <summary>How do I book an appointment with a dietitian?</summary>
                    <div class="faq-answer">
                        <p>Go to <strong>Appointments → Find a Dietitian</strong>. Browse by specialisation (weight loss, sports nutrition, diabetes management, etc.), check available slots, and book directly. You'll receive a confirmation email with joining details.</p>
                    </div>
                </details>
                <details class="faq-item">
                    <summary>Can I cancel or reschedule an appointment?</summary>
                    <div class="faq-answer">
                        <p>Yes. Go to <strong>Appointments → My Appointments</strong> and select the booking. You can cancel or reschedule up to <strong>2 hours before</strong> the scheduled time at no charge. Late cancellations may be subject to a fee as per the dietitian's policy.</p>
                    </div>
                </details>
                <details class="faq-item">
                    <summary>Are the dietitians on NutriTrack certified?</summary>
                    <div class="faq-answer">
                        <p>Yes. All dietitians on our platform are verified registered dietitians (RD) or nutritionists with valid licences. We verify credentials during onboarding and conduct periodic re-verification. You can view each dietitian's qualifications on their profile.</p>
                    </div>
                </details>
            </div>

            <h2 class="faq-section-title" id="device-faq">Device Sync</h2>
            <div class="faq-list">
                <details class="faq-item">
                    <summary>Which fitness devices and apps does NutriTrack support?</summary>
                    <div class="faq-answer">
                        <p>We currently support: <strong>Fitbit, Apple Health, Google Fit, Garmin Connect, Samsung Health, and Mi Fit</strong>. More integrations are added regularly. Go to <strong>Settings → Connected Devices</strong> to link your device.</p>
                    </div>
                </details>
                <details class="faq-item">
                    <summary>My device isn't syncing. What should I do?</summary>
                    <div class="faq-answer">
                        <p>Try these steps: (1) Disconnect and reconnect the device under Settings → Connected Devices. (2) Make sure you've granted all required permissions to the NutriTrack app. (3) Check that your device's app (e.g. Fitbit app) is up to date. If the issue persists, contact support with your device model and OS version.</p>
                    </div>
                </details>
            </div>

            <h2 class="faq-section-title" id="billing-faq">Billing & Plans</h2>
            <div class="faq-list">
                <details class="faq-item">
                    <summary>What plans are available and what do they include?</summary>
                    <div class="faq-answer">
                        <p><strong>Free:</strong> Basic meal logging, goal tracking, limited food database.<br>
                        <strong>Pro (₹499/month):</strong> Full food database, unlimited logging, progress analytics, device sync.<br>
                        <strong>Premium (₹999/month):</strong> Everything in Pro, plus unlimited dietitian consultations and AI meal plans.</p>
                    </div>
                </details>
                <details class="faq-item">
                    <summary>How do I cancel my subscription?</summary>
                    <div class="faq-answer">
                        <p>Go to <strong>Settings → Subscription → Cancel Plan</strong>. Your access continues until the end of the current billing period. You won't be charged after cancellation.</p>
                    </div>
                </details>
                <details class="faq-item">
                    <summary>Can I get a refund?</summary>
                    <div class="faq-answer">
                        <p>Annual subscriptions are eligible for a full refund within 7 days of purchase if the premium features haven't been substantially used. Monthly subscriptions are non-refundable once the billing cycle starts. Dietitian consultation fees are non-refundable once the session has begun. Contact <a href="mailto:billing@NutriTrack.com">billing@NutriTrack.com</a> for refund requests.</p>
                    </div>
                </details>
            </div>
        </section>

        <!-- Still need help CTA -->
        <div class="support-cta">
            <h2>Still need help?</h2>
            <p>Our support team is available Monday to Friday, 9 AM – 6 PM IST.</p>
            <div class="support-cta-actions">
                <a href="contact.php" class="btn-primary">Contact Support</a>
                <a href="mailto:support@NutriTrack.com" class="btn-ghost">support@NutriTrack.com</a>
            </div>
        </div>

    </main>

    <!-- <?php include 'footer.php'; ?> -->
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
            <p class="footer-copy">&copy; 2026 NutriTrack Diet Management System. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>