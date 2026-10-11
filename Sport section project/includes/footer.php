        <footer class="site-footer">
            <div class="container footer-main">
                <a href="mp.php" class="footer-logo"><img src="static/img/logo.svg" alt="KBTU culturist"></a>
                <div class="footer-col">
                    <a href="mp.php#services">Services</a>
                    <ul class="footer-sublinks">
                        <li><a href="sections.php">Sport sections</a></li>
                        <li><a href="#">Instructors</a></li>
                        <li><a href="#">Gyms</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <a href="mp.php#about">About</a>
                    <a href="mp.php#news">News</a>
                    <a href="#">Map</a>
                </div>
                <div class="footer-socials">
                    <a href="#" class="icon-link" aria-label="WhatsApp"><img src="static/img/whatsapp-icon.svg" alt=""></a>
                    <a href="#" class="icon-link" aria-label="Instagram"><img src="static/img/instagram-icon.svg" alt=""></a>
                </div>
            </div>
            <div class="footer-bottom"><?= date('Y') ?> © kbtu-culturist.kz</div>
        </footer>

        <button class="to-top" aria-label="Scroll to top">
            <img src="static/img/arrow-return.svg" alt="">
        </button>

        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script src="<?= asset('static/scripts/script.js') ?>"></script>
    </body>
</html>
