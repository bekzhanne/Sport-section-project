document.addEventListener('DOMContentLoaded', () => {
    new Swiper('.hero-swiper', {
        slidesPerView: 1,
        loop: true,
        speed: 700,
        autoplay: { delay: 6000, disableOnInteraction: false },
        navigation: { prevEl: '.hero-prev', nextEl: '.hero-next' },
        pagination: { el: '.hero-pagination', clickable: true },
    });

    new Swiper('.reviews-swiper', {
        slidesPerView: 1.15,
        centeredSlides: true,
        spaceBetween: 16,
        initialSlide: 1,
        speed: 600,
        pagination: { el: '.reviews-pagination', clickable: true },
        breakpoints: {
            901: { slidesPerView: 2.3, spaceBetween: 20 },
        },
    });

    // Mobile menu
    const body = document.body;
    const closeMenu = () => body.classList.remove('menu-open');

    document.querySelector('.burger')?.addEventListener('click', () => body.classList.add('menu-open'));
    document.querySelector('.mobile-menu-close')?.addEventListener('click', closeMenu);
    document.querySelector('.mobile-overlay')?.addEventListener('click', closeMenu);
    document.querySelectorAll('.mobile-nav a').forEach(a => a.addEventListener('click', closeMenu));

    // Services dropdown: open on tap for touch devices (hover handles desktop)
    const dropdown = document.querySelector('.nav-dropdown');
    dropdown?.querySelector('.nav-link').addEventListener('click', e => {
        if (window.matchMedia('(hover: none)').matches) {
            e.preventDefault();
            dropdown.classList.toggle('open');
        }
    });
    document.addEventListener('click', e => {
        if (dropdown && !dropdown.contains(e.target)) dropdown.classList.remove('open');
    });

    // Account dropdown: toggled by click, closed on outside click or Escape
    const account = document.querySelector('.account-dropdown');
    const accountToggle = account?.querySelector('.account-toggle');
    const setAccountOpen = open => {
        account.classList.toggle('open', open);
        accountToggle.setAttribute('aria-expanded', open);
    };

    accountToggle?.addEventListener('click', e => {
        e.stopPropagation();
        setAccountOpen(!account.classList.contains('open'));
    });
    document.addEventListener('click', e => {
        if (account && !account.contains(e.target)) setAccountOpen(false);
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && account) setAccountOpen(false);
    });

    // Scroll to top
    const toTop = document.querySelector('.to-top');
    window.addEventListener('scroll', () => {
        toTop.classList.toggle('visible', window.scrollY > 600);
    }, { passive: true });
    toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
});
