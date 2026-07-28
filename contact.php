<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us – NutriTrack</title>
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
                <a href="support.php">Support</a>
                <a href="login.php" class="btn-nav">Login</a>
            </nav>
        </div>
    </header>

    <div class="page-hero">
        <div class="page-hero-inner">
            <div class="page-badge">Get in Touch</div>
            <h1 class="page-title">Contact Us</h1>
            <p class="page-subtitle">We're here to help. Send us a message and we'll get back to you within 24 hours.</p>
        </div>
    </div>

    <main class="contact-layout">

        <!-- Contact Info Cards -->
        <div class="contact-cards">
            <div class="contact-card">
                <div class="contact-card-icon">📧</div>
                <h3>Email Us</h3>
                <p>For general enquiries and feedback</p>
                <a href="mailto:hello@NutriTrack.com">hello@NutriTrack.com</a>
            </div>
            <div class="contact-card">
                <div class="contact-card-icon">📞</div>
                <h3>Call Us</h3>
                <p>Mon – Fri, 9 AM to 6 PM IST</p>
                <a href="tel:+911800123456">+91 1800 123 456</a>
            </div>
            <div class="contact-card">
                <div class="contact-card-icon">📍</div>
                <h3>Our Office</h3>
                <p>42 Wellness Avenue<br>Mumbai 400001, India</p>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="contact-form-wrap">
            <div class="form-card">
                <h2>Send a Message</h2>
                <p class="form-desc">Fill in the form below and our team will respond within one business day.</p>

                <form action="contact_process.php" method="POST" class="contact-form">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name <span class="required">*</span></label>
                            <input type="text" id="first_name" name="first_name" placeholder="e.g. Priya" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name <span class="required">*</span></label>
                            <input type="text" id="last_name" name="last_name" placeholder="e.g. Sharma" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject <span class="required">*</span></label>
                        <select id="subject" name="subject" required>
                            <option value="" disabled selected>Select a topic</option>
                            <option value="general">General Enquiry</option>
                            <option value="account">Account Issue</option>
                            <option value="billing">Billing & Payments</option>
                            <option value="dietitian">Dietitian Appointment</option>
                            <option value="device">Device Sync Problem</option>
                            <option value="feedback">Feedback & Suggestions</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Message <span class="required">*</span></label>
                        <textarea id="message" name="message" rows="5" placeholder="Describe your query in detail..." required></textarea>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" id="privacy_agree" name="privacy_agree" required>
                        <label for="privacy_agree">
                            I agree to the <a href="privacy.php">Privacy Policy</a> and consent to NutriTrack processing my data to respond to this enquiry.
                        </label>
                    </div>

                    <button type="submit" class="btn-submit">Send Message &rarr;</button>

                </form>
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