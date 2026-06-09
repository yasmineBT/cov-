<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoRide - Carpooling Made Easy</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="images/logo.svg" alt="CoRide Logo" class="logo-img">
                <span class="logo-text">CoRide</span>
            </div>
            <div class="nav-menu">
                <a href="signup.php" class="nav-link">Sign Up</a>
                <a href="login.php" class="nav-link">Login</a>
                <a href="#about" class="nav-link">About Us</a>
                <a href="#contact" class="nav-link">Contact</a>
            </div>
        </div>
    </nav>

    
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">Share Your Journey, Save Money & Reduce Emissions</h1>
                <p class="hero-subtitle">Join CoRide and connect with travelers going your way. Experience affordable, eco-friendly, and social carpooling.</p>
                <div class="hero-buttons">
                    <a href="signup.php" class="btn btn-primary">Get Started</a>
                    <a href="#about" class="btn btn-secondary">Learn More</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="car-illustration">
                    <div class="car-icon">🚗</div>
                </div>
            </div>
        </div>
    </section>

    
    <section id="about" class="about">
        <div class="container">
            <h2 class="section-title">Why Choose CoRide?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Save Money</h3>
                    <p>Split costs with fellow travelers and reduce your transportation expenses by up to 60%.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌱</div>
                    <h3>Eco-Friendly</h3>
                    <p>Reduce your carbon footprint by sharing rides and contributing to a greener environment.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🤝</div>
                    <h3>Meet New People</h3>
                    <p>Connect with like-minded travelers and make your journey more enjoyable and social.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⏰</div>
                    <h3>Flexible Schedule</h3>
                    <p>Choose rides that fit your schedule. No more waiting for public transport.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Safe & Secure</h3>
                    <p>Verified users and ratings ensure a safe carpooling experience for everyone.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Easy to Use</h3>
                    <p>Simple interface makes finding and offering rides quick and hassle-free.</p>
                </div>
            </div>
        </div>
    </section>

    
    <section id="contact" class="contact">
        <div class="container">
            <h2 class="section-title">Get in Touch</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">📧</div>
                        <div>
                            <h4>Email</h4>
                            <p>info@coride.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div>
                            <h4>Phone</h4>
                            <p>+216 93 953 550</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div>
                            <h4>Address</h4>
                            <p>Ariana soghra, Tunis</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form">
                    <form>
                        <input type="text" placeholder="Your Name" required>
                        <input type="email" placeholder="Your Email" required>
                        <textarea placeholder="Your Message" rows="5" required></textarea>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

   
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="images/logo.svg" alt="CoRide Logo" class="logo-img">
                        <span class="logo-text">CoRide</span>
                    </div>
                    <p>Making carpooling simple, safe, and sustainable for everyone.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="signup.php">Sign Up</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 CoRide. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/common.js"></script>
    <script src="js/index.js"></script>
</body>
</html>
