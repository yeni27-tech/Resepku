<?php
ob_start();
require_once(__DIR__ . '/session.php');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resepku - Koleksi Resep Terbaik</title>
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
    <base href="/resep_website_native/">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.12.3/cdn.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* ===== MODERN VARIABLES ===== */
        :root {
            /* Primary Colors - White & Orange Theme */
            --primary-color: #ffffff;
            --primary-dark: #f8f9fa;
            --primary-light: #ffffff;
            --secondary-color: #ff6b35;
            --secondary-light: #ff8c62;
            --secondary-dark: #e54e1b;
            --accent-color: #48bb78;

            /* Text Colors */
            --text-color: #2d3748;
            --text-light: #718096;
            --text-muted: #a0aec0;

            /* Background Colors */
            --bg-primary: #ffffff;
            --bg-secondary: #f7fafc;
            --bg-overlay: rgba(255, 255, 255, 0.95);

            /* Border & Shadow */
            --border-color: rgba(255, 107, 53, 0.1);
            --shadow-light: 0 2px 8px rgba(255, 107, 53, 0.08);
            --shadow-medium: 0 4px 16px rgba(255, 107, 53, 0.12);
            --shadow-heavy: 0 8px 32px rgba(255, 107, 53, 0.15);
            --shadow-glow: 0 0 20px rgba(255, 107, 53, 0.3);

            /* Gradients */
            --gradient-orange: linear-gradient(135deg, #ff6b35 0%, #ff8c62 100%);
            --gradient-white: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            --gradient-hover: linear-gradient(135deg, #e54e1b 0%, #ff6b35 100%);

            /* Typography */
            --font-weight-light: 300;
            --font-weight-normal: 400;
            --font-weight-medium: 500;
            --font-weight-semibold: 600;
            --font-weight-bold: 700;
            --font-weight-extrabold: 800;
            --font-weight-black: 900;

            /* Sizing */
            --header-height: 80px;
            --container-width: 1280px;
            --border-radius: 12px;
            --border-radius-lg: 16px;
            --border-radius-xl: 20px;

            /* Transitions */
            --transition-fast: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-normal: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        /* ===== MODERN BASE STYLES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            background: var(--bg-secondary);
            font-size: 16px;
            font-weight: var(--font-weight-normal);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ===== MODERN LOGO IMAGE STYLE ===== */
        .logo img {
            width: 40px;
            height: 40px;
            transition: var(--transition-bounce);
        }

        .logo a:hover img {
            transform: translateY(-2px) scale(1.05);
        }

        /* ===== MODERN NAVBAR ===== */
        nav {
            background: var(--bg-overlay);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: var(--transition-normal);
        }

        nav::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-white);
            opacity: 0.9;
            z-index: -1;
        }

        .nav-container {
            max-width: var(--container-width);
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: var(--header-height);
            position: relative;
        }

        /* ===== MODERN LOGO ===== */
        .logo {
            display: flex;
            align-items: center;
            position: relative;
        }

        .logo a {
            display: flex;
            align-items: center;
            font-size: 25px;
            font-weight: var(--font-weight-extrabold);
            color: var(--secondary-color);
            text-decoration: none;
            transition: var(--transition-bounce);
            position: relative;
            padding: 8px 16px;
            border-radius: var(--border-radius);
        }

        .logo a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0;
            border-radius: var(--border-radius);
            transition: var(--transition-normal);
            z-index: -1;
        }

        .logo a:hover::before {
            opacity: 0.1;
        }

        .logo a:hover {
            transform: translateY(-2px) scale(1.05);
            filter: drop-shadow(var(--shadow-glow));
        }

        .logo span {
            background: var(--gradient-orange);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
        }

        /* ===== MODERN NAVIGATION LINKS ===== */
        .desktop-nav {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .nav-links {
            display: flex;
            gap: 24px;
            list-style: none;
        }

        .nav-links a {
            color: var(--text-color);
            font-weight: var(--font-weight-semibold);
            font-size: 16px;
            padding: 12px 20px;
            position: relative;
            transition: var(--transition-normal);
            border-radius: var(--border-radius);
            text-decoration: none;
        }

        .nav-links a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-orange);
            opacity: 0;
            border-radius: var(--border-radius);
            transition: var(--transition-normal);
            z-index: -1;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 3px;
            background: var(--gradient-orange);
            border-radius: 2px;
            transition: var(--transition-bounce);
        }

        .nav-links a:hover {
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .nav-links a:hover::before {
            opacity: 1;
        }

        .nav-links a:hover::after {
            width: 60%;
        }

        /* ===== MODERN DROPDOWN ===== */
        .dropdown {
            position: relative;
        }

        .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--gradient-orange);
            color: var(--primary-color);
            font-size: 16px;
            font-weight: var(--font-weight-semibold);
            padding: 14px 24px;
            border-radius: var(--border-radius-lg);
            border: none;
            cursor: pointer;
            transition: var(--transition-bounce);
            box-shadow: var(--shadow-light);
            position: relative;
            overflow: hidden;
        }

        .dropdown-toggle::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .dropdown-toggle:hover::before {
            left: 100%;
        }

        .dropdown-toggle:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: var(--shadow-heavy);
            background: var(--gradient-hover);
        }

        .dropdown-toggle i {
            transition: var(--transition-bounce);
        }

        .dropdown-toggle:hover i {
            transform: rotate(180deg);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: var(--bg-primary);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-lg);
            min-width: 220px;
            z-index: 2000;
            box-shadow: var(--shadow-heavy);
            overflow: hidden;
        }

        .dropdown-menu.active {
            display: block;
            animation: dropdownSlide 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes dropdownSlide {
            0% {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            color: var(--text-color);
            font-size: 15px;
            font-weight: var(--font-weight-medium);
            transition: var(--transition-fast);
            text-decoration: none;
            position: relative;
        }

        .dropdown-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 0;
            background: var(--gradient-orange);
            transition: var(--transition-fast);
        }

        .dropdown-menu a:hover {
            color: var(--secondary-color);
            background: rgba(255, 107, 53, 0.05);
            transform: translateX(8px);
        }

        .dropdown-menu a:hover::before {
            width: 4px;
        }

        /* ===== MODERN BUTTONS ===== */
        .btn-nav {
            padding: 12px 24px;
            border-radius: var(--border-radius-lg);
            font-size: 15px;
            font-weight: var(--font-weight-semibold);
            transition: var(--transition-bounce);
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .btn-nav::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: var(--transition-fast);
        }

        .btn-nav:hover::before {
            width: 200px;
            height: 200px;
        }

        .btn-nav-primary {
            background: var(--gradient-orange);
            color: var(--primary-color);
            box-shadow: var(--shadow-light);
        }

        .btn-nav-primary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: var(--shadow-heavy);
            background: var(--gradient-hover);
        }

        .btn-nav-outline {
            border: 2px solid var(--secondary-color);
            color: var(--secondary-color);
            background: transparent;
            position: relative;
        }

        .btn-nav-outline::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient-orange);
            transition: var(--transition-normal);
            z-index: -1;
        }

        .btn-nav-outline:hover {
            color: var(--primary-color);
            border-color: var(--secondary-dark);
            transform: translateY(-3px) scale(1.05);
            box-shadow: var(--shadow-medium);
        }

        .btn-nav-outline:hover::after {
            left: 0;
        }

        /* ===== MOBILE NAVIGATION ===== */
        .mobile-nav-btn {
            display: none;
            font-size: 24px;
            background: var(--gradient-orange);
            color: var(--primary-color);
            border: none;
            padding: 12px;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition-bounce);
            box-shadow: var(--shadow-light);
        }

        .mobile-nav-btn:hover {
            transform: scale(1.1) rotate(90deg);
            box-shadow: var(--shadow-medium);
        }

        .mobile-menu {
            display: none;
            background: var(--bg-overlay);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--border-color);
            margin: 0;
            border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);
            overflow: hidden;
        }

        .mobile-menu.active {
            display: block;
            animation: mobileSlide 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes mobileSlide {
            0% {
                opacity: 0;
                transform: translateY(-100%);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mobile-menu a {
            display: flex;
            align-items: center;
            padding: 16px 24px;
            color: var(--text-color);
            font-size: 16px;
            font-weight: var(--font-weight-medium);
            transition: var(--transition-fast);
            text-decoration: none;
            border-bottom: 1px solid var(--border-color);
            position: relative;
        }

        .mobile-menu a:last-child {
            border-bottom: none;
        }

        .mobile-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 0;
            background: var(--gradient-orange);
            transition: var(--transition-fast);
        }

        .mobile-menu a:hover {
            color: var(--secondary-color);
            background: rgba(255, 107, 53, 0.05);
            transform: translateX(12px);
        }

        .mobile-menu a:hover::before {
            width: 4px;
        }

        /* ===== MODERN NOTIFICATION ===== */
        .notification {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 3000;
            padding: 16px 24px;
            background: var(--gradient-orange);
            color: var(--primary-color);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-heavy);
            font-weight: var(--font-weight-semibold);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* ===== SCROLL ANIMATIONS ===== */
        .nav-scroll {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-medium);
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 1024px) {
            .nav-container {
                padding: 0 20px;
            }

            .desktop-nav {
                gap: 24px;
            }
        }

        @media (max-width: 768px) {
            .desktop-nav {
                display: none;
            }

            .mobile-nav-btn {
                display: flex;
            }

            .nav-container {
                padding: 0 16px;
                height: 70px;
            }

            .logo a {
                font-size: 24px;
            }

            .logo i {
                font-size: 28px;
                margin-right: 8px;
            }
        }

        @media (max-width: 480px) {
            .nav-container {
                padding: 0 12px;
            }

            .logo a {
                font-size: 20px;
            }

            .logo i {
                font-size: 24px;
            }

            .notification {
                right: 12px;
                left: 12px;
                text-align: center;
            }
        }

        /* ===== ACCESSIBILITY ===== */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Enhanced notification animations
            const notification = document.getElementById('notification');
            if (notification) {
                notification.classList.add('animate__animated', 'animate__bounceInRight');
                setTimeout(() => {
                    notification.classList.replace('animate__bounceInRight', 'animate__bounceOutRight');
                    setTimeout(() => {
                        notification.classList.add('hidden');
                    }, 1000);
                }, 4000);
            }

            // Smooth scroll effect for navbar
            let lastScrollTop = 0;
            const navbar = document.querySelector('nav');

            window.addEventListener('scroll', () => {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                if (scrollTop > 50) {
                    navbar.classList.add('nav-scroll');
                } else {
                    navbar.classList.remove('nav-scroll');
                }

                lastScrollTop = scrollTop;
            });

            // Enhanced mobile menu toggle with animation
            window.toggleMobileMenu = function() {
                const mobileMenu = document.getElementById('mobile-menu');
                const button = document.querySelector('.mobile-nav-btn');

                mobileMenu.classList.toggle('active');

                // Animate hamburger icon
                const icon = button.querySelector('i');
                if (mobileMenu.classList.contains('active')) {
                    icon.classList.replace('fa-bars', 'fa-times');
                    button.style.transform = 'scale(1.1) rotate(180deg)';
                } else {
                    icon.classList.replace('fa-times', 'fa-bars');
                    button.style.transform = 'scale(1) rotate(0deg)';
                }
            }

            // Enhanced dropdown toggle with better UX
            window.toggleDropdown = function() {
                const dropdownMenu = document.getElementById('dropdown-menu');
                const button = document.querySelector('.dropdown-toggle');

                dropdownMenu.classList.toggle('active');

                // Add ripple effect
                const ripple = document.createElement('div');
                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.5);
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                `;

                const rect = button.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = (rect.width / 2 - size / 2) + 'px';
                ripple.style.top = (rect.height / 2 - size / 2) + 'px';

                button.appendChild(ripple);

                setTimeout(() => {
                    ripple.remove();
                }, 600);
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                const dropdown = document.querySelector('.dropdown');
                const dropdownMenu = document.getElementById('dropdown-menu');

                if (!dropdown.contains(e.target)) {
                    dropdownMenu.classList.remove('active');
                }
            });

            // Add ripple effect to buttons
            document.querySelectorAll('.btn-nav, .dropdown-toggle').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;

                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255, 255, 255, 0.3);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        pointer-events: none;
                    `;

                    this.appendChild(ripple);

                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</head>

<body class="min-h-screen flex flex-col">
    <!-- Modern Navbar -->
    <nav>
        <div class="nav-container">
            <!-- Enhanced Logo with Image -->
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/logo.png" alt="Resepku Logo" style="width: 90px; height: 90px;">
                    <span>Resepku</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="desktop-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="dropdown-toggle" onclick="toggleDropdown()">
                            <i class="fas fa-user-circle"></i>
                            <span>Akun Saya</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div id="dropdown-menu" class="dropdown-menu">
                            <a href="pages/user/dashboard.php">
                                <i class="fas fa-tachometer-alt" style="margin-right: 12px; color: var(--secondary-color);"></i>
                                Dashboard
                            </a>
                            <a href="pages/user/profile.php">
                                <i class="fas fa-user" style="margin-right: 12px; color: var(--secondary-color);"></i>
                                Profil
                            </a>
                            <a href="pages/user/manage_recipes.php">
                                <i class="fas fa-book" style="margin-right: 12px; color: var(--secondary-color);"></i>
                                Resep Ku
                            </a>
                            <a href="pages/user/preferences.php">
                                <i class="fas fa-cog" style="margin-right: 12px; color: var(--secondary-color);"></i>
                                Preferensi
                            </a>
                            <a href="pages/user/favorites.php">
                                <i class="fas fa-heart" style="margin-right: 12px; color: var(--secondary-color);"></i>
                                Favorit
                            </a>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <a href="pages/admin/dashboard.php">
                                    <i class="fas fa-shield-alt" style="margin-right: 12px; color: var(--secondary-color);"></i>
                                    Admin
                                </a>
                            <?php endif; ?>
                            <a href="pages/logout.php">
                                <i class="fas fa-sign-out-alt" style="margin-right: 12px; color: var(--secondary-color);"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <ul class="nav-links">
                        <li><a href="pages/login.php" class="btn-nav btn-nav-outline">
                                <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>
                                Login
                            </a></li>
                        <li><a href="pages/register.php" class="btn-nav btn-nav-primary">
                                <i class="fas fa-user-plus" style="margin-right: 8px;"></i>
                                Register
                            </a></li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Enhanced Mobile Navigation Button -->
            <button class="mobile-nav-btn" onclick="toggleMobileMenu()" aria-label="Toggle Mobile Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Enhanced Mobile Menu -->
        <div id="mobile-menu" class="mobile-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="pages/user/dashboard.php">
                    <i class="fas fa-tachometer-alt" style="margin-right: 16px; color: var(--secondary-color);"></i>
                    Dashboard
                </a>
                <a href="pages/user/profile.php">
                    <i class="fas fa-user" style="margin-right: 16px; color: var(--secondary-color);"></i>
                    Profil
                </a>
                <a href="pages/user/manage_recipes.php">
                    <i class="fas fa-book" style="margin-right: 16px; color: var(--secondary-color);"></i>
                    Resep Ku
                </a>
                <a href="pages/user/preferences.php">
                    <i class="fas fa-cog" style="margin-right: 16px; color: var(--secondary-color);"></i>
                    Preferensi
                </a>
                <a href="pages/user/favorites.php">
                    <i class="fas fa-heart" style="margin-right: 16px; color: var(--secondary-color);"></i>
                    Favorit
                </a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="pages/admin/dashboard.php">
                        <i class="fas fa-shield-alt" style="margin-right: 16px; color: var(--secondary-color);"></i>
                        Admin
                    </a>
                <?php endif; ?>
                <a href="pages/logout.php">
                    <i class="fas fa-sign-out-alt" style="margin-right: 16px; color: var(--secondary-color);"></i>
                    Logout
                </a>
            <?php else: ?>
                <a href="pages/login.php" class="btn-nav btn-nav-outline">
                    <i class="fas fa-sign-in-alt" style="margin-right: 16px;"></i>
                    Login
                </a>
                <a href="pages/register.php" class="btn-nav btn-nav-primary">
                    <i class="fas fa-user-plus" style="margin-right: 16px;"></i>
                    Register
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Notification -->
    <?php if (isset($_SESSION['notification'])): ?>
        <div id="notification" class="notification animate__animated animate__bounceInRight">
            <?php echo $_SESSION['notification']; ?>
            <?php unset($_SESSION['notification']); ?>
        </div>
    <?php endif;
    ob_end_flush();
    ?>

</body>

</html>
<!-- <?php ob_end_flush(); ?> -->