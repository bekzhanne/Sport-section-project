        <footer class="site-footer">
            <div class="container footer-main">
                <a href="<?= e(url('mp.php')) ?>" class="footer-logo"><img src="<?= e(url('static/img/logo.svg')) ?>" alt="KBTU culturist"></a>
                <div class="footer-col">
                    <a href="<?= e(url('mp.php')) ?>#services">Services</a>
                    <ul class="footer-sublinks">
                        <li><a href="<?= e(url('sections.php')) ?>">Sport sections</a></li>
                        <li><a href="#">Instructors</a></li>
                        <li><a href="#">Gyms</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <a href="<?= e(url('mp.php')) ?>#about">About</a>
                    <a href="<?= e(url('mp.php')) ?>#news">News</a>
                    <a href="#">Map</a>
                </div>
                <div class="footer-socials">
                    <a href="https://wa.me/77066868040" class="icon-link" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer"><img src="<?= e(url('static/img/whatsapp-icon.svg')) ?>" alt=""></a>
                    <a href="https://www.instagram.com/kanattuly.br2/" class="icon-link" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><img src="<?= e(url('static/img/instagram-icon.svg')) ?>" alt=""></a>
                </div>
            </div>
            <div class="footer-bottom"><?= date('Y') ?> © kbtu-culturist.kz</div>
        </footer>

        <button class="to-top" aria-label="Scroll to top">
            <img src="<?= e(url('static/img/arrow-return.svg')) ?>" alt="">
        </button>

        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script src="<?= asset('static/scripts/script.js') ?>"></script>
        <script src="<?= asset('static/scripts/validation.js') ?>"></script>
    </body>
</html>
