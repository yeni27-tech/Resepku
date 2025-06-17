<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResepKu - Koleksi Resep Terbaik</title>
    <link rel="icon" type="image/x-icon" href="assets/images/logo.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            overflow-x: hidden;
            background: linear-gradient(135deg, #ff6b35, #f7931e, #ffd700);
            background-size: 400% 400%;
            animation: gradientShift 8s ease-in-out infinite;
            min-height: 100vh;
        }

        @keyframes gradientShift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        .animation-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .logo-wrapper {
            position: relative;
            z-index: 10;
            animation: logoEntrance 2s ease-out;
        }

        .logo {
            max-width: 90vw;
            max-height: 70vh;
            width: auto;
            height: auto;
            filter: drop-shadow(0 10px 30px rgba(255, 255, 255, 0.3));
            animation: logoFloat 4s ease-in-out infinite;
        }

        @keyframes logoEntrance {
            0% {
                opacity: 0;
                transform: scale(0.3) rotate(-10deg);
            }

            50% {
                opacity: 0.7;
                transform: scale(1.1) rotate(2deg);
            }

            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }

        @keyframes logoFloat {

            0%,
            100% {
                transform: translateY(0px) scale(1);
            }

            50% {
                transform: translateY(-10px) scale(1.02);
            }
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%;
            pointer-events: none;
            animation: particleFloat 6s linear infinite;
        }

        @keyframes particleFloat {
            0% {
                opacity: 0;
                transform: translateY(100vh) scale(0);
            }

            10% {
                opacity: 1;
                transform: translateY(90vh) scale(1);
            }

            90% {
                opacity: 1;
                transform: translateY(-10vh) scale(1);
            }

            100% {
                opacity: 0;
                transform: translateY(-20vh) scale(0);
            }
        }

        .cooking-icons {
            position: absolute;
            font-size: 2rem;
            color: rgba(255, 255, 255, 0.6);
            animation: iconFloat 8s ease-in-out infinite;
        }

        .icon-1 {
            top: 20%;
            left: 10%;
            animation-delay: -2s;
        }

        .icon-2 {
            top: 70%;
            right: 15%;
            animation-delay: -4s;
        }

        .icon-3 {
            top: 40%;
            left: 5%;
            animation-delay: -6s;
        }

        .icon-4 {
            top: 15%;
            right: 8%;
            animation-delay: -1s;
        }

        @keyframes iconFloat {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.6;
            }

            50% {
                transform: translateY(-20px) rotate(10deg);
                opacity: 0.9;
            }
        }

        .title-text a {
            text-decoration: none;
            position: absolute;
            bottom: 15%;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: clamp(1.2rem, 4vw, 2.5rem);
            font-weight: bold;
            text-align: center;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
            opacity: 0;
            animation: textFadeIn 2s ease-out 1.5s forwards;
        }

        @keyframes textFadeIn {
            0% {
                opacity: 0;
                transform: translateX(-50%) translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        .pulse-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 300px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: pulseRing 3s ease-out infinite;
        }

        @keyframes pulseRing {
            0% {
                transform: translate(-50%, -50%) scale(0.5);
                opacity: 1;
            }

            100% {
                transform: translate(-50%, -50%) scale(2);
                opacity: 0;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .logo {
                max-width: 95vw;
                max-height: 60vh;
            }

            .cooking-icons {
                font-size: 1.5rem;
            }

            .title-text {
                bottom: 10%;
                font-size: clamp(1rem, 5vw, 1.8rem);
            }
        }

        @media (max-width: 480px) {
            .logo {
                max-width: 90vw;
                max-height: 50vh;
            }

            .cooking-icons {
                font-size: 1.2rem;
            }
        }

        /* Hover Effects */
        .logo-wrapper:hover .logo {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }

        /* Loading Animation */
        .loading-bar {
            position: absolute;
            bottom: 5%;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
            overflow: hidden;
        }

        .loading-progress {
            height: 100%;
            background: linear-gradient(90deg, #ff6b35, #f7931e);
            border-radius: 2px;
            animation: loadingProgress 3s ease-out;
        }

        @keyframes loadingProgress {
            0% {
                width: 0%;
            }

            100% {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="animation-container">
        <!-- Pulse Ring Effect -->
        <div class="pulse-ring"></div>

        <!-- Cooking Icons Floating -->
        <div class="cooking-icons icon-1">🍳</div>
        <div class="cooking-icons icon-2">🥘</div>
        <div class="cooking-icons icon-3">🍽️</div>
        <div class="cooking-icons icon-4">👨‍🍳</div>

        <!-- Logo Container -->
        <div class="logo-wrapper">
            <img src="./assets/images/logo.png"
                alt="ResepKu Logo"
                class="logo"
                id="mainLogo">
        </div>

        <!-- Title Text -->
        <div class="title-text">
            <a href="#">
                Temukan Resep Terbaik Untuk Keluarga
            </a>
        </div>

        <!-- Loading Bar -->
        <div class="loading-bar">
            <div class="loading-progress"></div>
        </div>
    </div>

    <script>
        // Create floating particles
        function createParticle() {
            const particle = document.createElement('div');
            particle.classList.add('particle');

            const size = Math.random() * 6 + 2;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.left = Math.random() * 100 + 'vw';
            particle.style.animationDuration = (Math.random() * 3 + 4) + 's';
            particle.style.animationDelay = Math.random() * 2 + 's';

            document.body.appendChild(particle);

            setTimeout(() => {
                particle.remove();
            }, 8000);
        }

        // Create particles continuously  
        setInterval(createParticle, 500);

        // Logo interaction
        const logo = document.getElementById('mainLogo');

        logo.addEventListener('mouseenter', function() {
            this.style.filter = 'drop-shadow(0 15px 40px rgba(255,107,53,0.5)) brightness(1.1)';
        });

        logo.addEventListener('mouseleave', function() {
            this.style.filter = 'drop-shadow(0 10px 30px rgba(0,0,0,0.3))';
        });

        // Replace placeholder logo with actual uploaded image
        // Note: In a real implementation, you would replace the src with your actual logo file
        logo.onerror = function() {
            // Fallback styling if logo doesn't load
            this.style.display = 'none';
            const fallback = document.createElement('div');
            fallback.innerHTML = '<h1 style="color: white; font-size: clamp(3rem, 8vw, 6rem); font-weight: bold; text-shadow: 3px 3px 15px rgba(0,0,0,0.5);">ResepKu</h1>';
            fallback.style.textAlign = 'center';
            this.parentNode.appendChild(fallback);
        };

        // Auto-redirect after animation (optional)
        // setTimeout(() => {
        //     // You can add redirect logic here
        //     // window.location.href = 'main-page.html';
        //     window.location.href = 'index.php';
        // }, 9000);
    </script>
</body>

</html>