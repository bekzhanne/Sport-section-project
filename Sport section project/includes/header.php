<?php
require_once __DIR__ . '/session.php';

$user = current_user();
$pageTitle = $pageTitle ?? 'KBTU culturist';
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($pageTitle) ?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
        <link rel="stylesheet" href="<?= asset('static/styles/styles.css') ?>">
    </head>

    <body>
        <header class="site-header">
            <div class="container header-inner">
                <a href="mp.php" class="logo"><img src="static/img/logo.svg" alt="KBTU culturist"></a>
                <nav class="main-nav">
                    <a href="mp.php#about" class="nav-link">About</a>
                    <a href="mp.php#news" class="nav-link">News</a>
                    <div class="nav-dropdown">
                        <a href="mp.php#services" class="nav-link">Services <span class="chevron"></span></a>
                        <div class="dropdown-menu">
                            <a href="sections.php">Sport sections</a>
                            <a href="#">Instructors</a>
                            <a href="#">Gyms</a>
                        </div>
                    </div>
                    <a href="profile.php#bookings" class="nav-link">Bookings</a>
                    <?php if ($user): ?><a href="profile.php" class="nav-link">Profile</a><?php endif; ?>
                    <a href="#" class="nav-link">Map</a>
                </nav>
                <div class="header-actions">
                    <div class="account-dropdown">
                        <button class="icon-link account-toggle<?= $user ? ' logged-in' : '' ?>" aria-label="Account" aria-haspopup="true" aria-expanded="false">
                            <img src="static/img/profile-icon.svg" alt="">
                        </button>
                        <div class="dropdown-menu account-menu">
                            <?php if ($user): ?>
                                <div class="account-info">
                                    <span class="account-name"><?= e($user['name'] ?? '') ?></span>
                                    <span class="account-email"><?= e($user['email'] ?? '') ?></span>
                                </div>
                                <a href="profile.php">My profile</a>
                                <form action="logout.php" method="post">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <button type="submit" class="account-logout">Log out</button>
                                </form>
                            <?php else: ?>
                                <a href="login.php">Log in</a>
                                <a href="register.php">Register</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="https://www.instagram.com/kanattuly.br2/" class="icon-link" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><img src="static/img/instagram-icon.svg" alt=""></a>
                    <a href="https://wa.me/77066868040" class="icon-link" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer"><img src="static/img/whatsapp-icon.svg" alt=""></a>
                    <div class="lang-switch">
                        <img src="static/img/globe-icon.svg" alt="">
                        <a href="#" class="active">EN</a>
                        <span class="divider"></span>
                        <a href="#">RU</a>
                    </div>
                </div>
                <button class="burger" aria-label="Open menu">
                    <img src="static/img/burger-menu-icon.svg" alt="">
                </button>
            </div>
        </header>

        <div class="mobile-overlay"></div>
        <aside class="mobile-menu">
            <div class="mobile-menu-head">
                <a href="mp.php" class="logo"><img src="static/img/logo.svg" alt="KBTU culturist"></a>
                <button class="mobile-menu-close" aria-label="Close menu">
                    <img src="static/img/close-cross.svg" alt="">
                </button>
            </div>
            <div class="mobile-account">
                <?php if ($user): ?>
                    <div class="account-info">
                        <span class="account-name"><?= e($user['name'] ?? '') ?></span>
                        <span class="account-email"><?= e($user['email'] ?? '') ?></span>
                    </div>
                    <div class="mobile-account-actions">
                        <a href="profile.php" class="btn btn-navy">My profile</a>
                        <form action="logout.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <button type="submit" class="btn btn-outline-navy">Log out</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="mobile-account-actions">
                        <a href="login.php" class="btn btn-navy">Log in</a>
                        <a href="register.php" class="btn btn-outline-navy">Register</a>
                    </div>
                <?php endif; ?>
            </div>
            <ul class="mobile-nav">
                <li><a href="mp.php#about">About</a></li>
                <li><a href="mp.php#news">News</a></li>
                <li>
                    <a href="mp.php#services">Services</a>
                    <div class="sub">
                        <a href="sections.php">Sport sections</a>
                        <a href="#">Instructors</a>
                        <a href="#">Gyms</a>
                    </div>
                </li>
                <li><a href="profile.php#bookings">Bookings</a></li>
                <?php if ($user): ?><li><a href="profile.php">Profile</a></li><?php endif; ?>
                <li><a href="#">Map</a></li>
            </ul>
            <div class="mobile-menu-foot">
                <div class="lang-switch">
                    <img src="static/img/globe-icon.svg" alt="">
                    <a href="#" class="active">EN</a>
                    <span class="divider"></span>
                    <a href="#">RU</a>
                </div>
                <div class="mobile-socials">
                    <a href="https://www.instagram.com/kanattuly.br2/" class="icon-link" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><img src="static/img/instagram-icon.svg" alt=""></a>
                    <a href="https://wa.me/77066868040" class="icon-link" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer"><img src="static/img/whatsapp-icon.svg" alt=""></a>
                </div>
            </div>
        </aside>
