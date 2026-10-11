<?php
$pageTitle = 'KBTU culturist';
require __DIR__ . '/includes/header.php';
?>
        <main>
            <section class="hero">
                <div class="swiper hero-swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide hero-slide" style="background-image: url('static/img/hero-1.jpg');">
                            <div class="container">
                                <h1 class="hero-title">In healthy body - strong soul</h1>
                                <p class="hero-text">Enlist to section now. For tough soul and build figure</p>
                                <a href="sections.php" class="btn btn-navy hero-btn">Register</a>
                            </div>
                        </div>
                        <div class="swiper-slide hero-slide" style="background-image: url('static/img/hero-2.jpg');">
                            <div class="container">
                                <h2 class="hero-title">In healthy body - strong soul</h2>
                                <p class="hero-text">Enlist to section now. For tough soul and build figure</p>
                                <a href="sections.php" class="btn btn-navy hero-btn">Register</a>
                            </div>
                        </div>
                        <div class="swiper-slide hero-slide" style="background-image: url('static/img/hero-3.jpg');">
                            <div class="container">
                                <h2 class="hero-title">In healthy body - strong soul</h2>
                                <p class="hero-text">Enlist to section now. For tough soul and build figure</p>
                                <a href="sections.php" class="btn btn-navy hero-btn">Register</a>
                            </div>
                        </div>
                        <div class="swiper-slide hero-slide" style="background-image: url('static/img/hero-4.jpg');">
                            <div class="container">
                                <h2 class="hero-title">In healthy body - strong soul</h2>
                                <p class="hero-text">Enlist to section now. For tough soul and build figure</p>
                                <a href="sections.php" class="btn btn-navy hero-btn">Register</a>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="hero-arrow hero-prev" aria-label="Previous slide"></button>
                <button class="hero-arrow hero-next" aria-label="Next slide"></button>
                <div class="line-pagination hero-pagination"></div>
            </section>

            <section class="about" id="about">
                <div class="container">
                    <h2 class="section-title">About us</h2>
                    <p>KBTU culturist is the athletic and sports community of Kazakh-British Technical University, dedicated to fostering physical fitness, sportsmanship, and student engagement. Through internal tournaments, varsity team competitions, and active lifestyle initiatives, it brings together students, alumni, and faculty to build discipline, team spirit, and a healthy campus culture.</p>
                    <a href="#" class="btn btn-outline-white">More</a>
                </div>
            </section>

            <section class="services" id="services">
                <div class="container">
                    <h2 class="section-title">Services</h2>
                    <div class="services-grid">
                        <a href="sections.php" class="service-card" style="background-image: url('static/img/sport-sections.jpg');">
                            <h3>Sport sections</h3>
                            <p>Find your style</p>
                        </a>
                        <a href="#" class="service-card no-image">
                            <h3>Instructors</h3>
                            <p>Guided plan for your progression</p>
                        </a>
                        <a href="#" class="service-card" style="background-image: url('static/img/gyms.jpg');">
                            <h3>Gyms</h3>
                            <p>For independent achievement</p>
                        </a>
                    </div>
                </div>
            </section>

            <section class="reviews">
                <div class="container">
                    <h2 class="section-title">Reviews</h2>
                    <div class="swiper reviews-swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide review-card">
                                <div class="stars"><span></span><span></span><span></span><span></span><span></span></div>
                                <p class="review-text">Some comment or review</p>
                                <div class="review-author">
                                    <div class="avatar">
                                        <img src="static/img/profile-icon.svg" alt="">
                                        <span class="badge"><img src="static/img/instagram-icon.svg" alt=""></span>
                                    </div>
                                    <div>
                                        <p class="author-name">John Doe</p>
                                        <p class="author-role">Gym enjoyer</p>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide review-card">
                                <div class="stars"><span></span><span></span><span></span><span></span><span></span></div>
                                <p class="review-text">Some comment or review</p>
                                <div class="review-author">
                                    <div class="avatar">
                                        <img src="static/img/profile-icon.svg" alt="">
                                        <span class="badge"><img src="static/img/whatsapp-icon.svg" alt=""></span>
                                    </div>
                                    <div>
                                        <p class="author-name">Jane Doe</p>
                                        <p class="author-role">Yoga fan</p>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide review-card">
                                <div class="stars"><span></span><span></span><span></span><span></span><span></span></div>
                                <p class="review-text">Some good review</p>
                                <div class="review-author">
                                    <div class="avatar">
                                        <img src="static/img/profile-icon.svg" alt="">
                                        <span class="badge"><img src="static/img/instagram-icon.svg" alt=""></span>
                                    </div>
                                    <div>
                                        <p class="author-name">Jack Doe</p>
                                        <p class="author-role">Powerlifter</p>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide review-card">
                                <div class="stars"><span></span><span></span><span></span><span></span><span></span></div>
                                <p class="review-text">Some comment or review</p>
                                <div class="review-author">
                                    <div class="avatar">
                                        <img src="static/img/profile-icon.svg" alt="">
                                        <span class="badge"><img src="static/img/whatsapp-icon.svg" alt=""></span>
                                    </div>
                                    <div>
                                        <p class="author-name">Jill Doe</p>
                                        <p class="author-role">Runner</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="line-pagination reviews-pagination"></div>
                </div>
            </section>

            <section class="news" id="news">
                <div class="container">
                    <h2 class="section-title">News</h2>
                    <div class="news-grid">
                        <a href="#" class="news-card">
                            <div class="news-img"></div>
                            <h3>Which protein should you choose to achieve your goals?</h3>
                            <time datetime="2026-01-01">01.01.2026</time>
                        </a>
                        <a href="#" class="news-card">
                            <div class="news-img"></div>
                            <h3>Almaty Marathon 2026 by Kaspi</h3>
                            <time datetime="2026-01-01">01.01.2026</time>
                        </a>
                        <a href="#" class="news-card">
                            <div class="news-img"></div>
                            <h3>Creatine: Benefits and Uses</h3>
                            <time datetime="2026-01-01">01.01.2026</time>
                        </a>
                    </div>
                    <div class="news-more">
                        <a href="#" class="btn btn-navy">All news</a>
                    </div>
                </div>
            </section>
        </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
