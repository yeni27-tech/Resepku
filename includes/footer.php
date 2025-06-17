<style>
    /* ===== FOOTER ===== */
    footer {
        background-color: var(--primary-color);
        color: var(--white);
        padding: 48px 0 24px;
    }

    .footer-container {
        max-width: var(--container-width);
        margin: 0 auto;
        padding: 0 16px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 32px;
    }

    .footer-column h3 {
        color: var(--white);
        font-size: var(--text-lg);
        margin-bottom: 16px;
        position: relative;
        padding-bottom: 8px;
    }

    .footer-column h3::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 3px;
        background-color: var(--white);
    }

    .footer-column p {
        font-size: var(--text-sm);
        line-height: 1.8;
    }

    .footer-links {
        list-style: none;
    }

    .footer-links li {
        margin-bottom: 8px;
    }

    .footer-links a {
        color: var(--white);
        font-size: var(--text-sm);
        transition: all var(--transition-normal);
    }

    .footer-links a:hover {
        color: var(--white);
        padding-left: 5px;
    }

    .social-links {
        display: flex;
        gap: 12px;
        margin-top: 16px;
    }

    .social-links a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        color: var(--white);
        transition: all var(--transition-normal);
    }

    .social-links a:hover {
        background-color: var(--white);
        transform: translateY(-3px);
    }

    .footer-copyright {
        margin-top: 32px;
        text-align: center;
        padding-top: 16px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        font-size: var(--text-sm);
        color: var(--text-primary);
    }
</style>
<footer>
    <div class="footer-container">
        <div class="footer-column">
            <h3>Tentang Resepku</h3>
            <p>Resepku adalah platform untuk menemukan dan berbagi resep masakan lezat dari komunitas kuliner Indonesia.</p>
        </div>
        <div class="footer-column">
            <h3>Navigasi</h3>
            <ul class="footer-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="pages/recipes/all.php">Semua Resep</a></li>
                <li><a href="pages/recipes/popular.php">Resep Populer</a></li>
                <li><a href="pages/categories/all.php">Kategori</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h3>Ikuti Kami</h3>
            <div class="social-links">
                <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="https://twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://youtube.com" target="_blank"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>
    <div class="footer-copyright">
        © 2025 Resepku. 
    </div>
</footer>