(function () {
    const header = document.querySelector('.header');
    if (!header) return;

    const btnMenu = header.querySelector('.header__btn-menu');

    if (btnMenu) {
        btnMenu.addEventListener('click', () => {
            header.classList.toggle('open-menu');
        });
    }

    window.addEventListener('scroll', () => {
        if (window.scrollY > 80) {
            header.classList.add('scroll');
        } else {
            header.classList.remove('scroll');
        }
    });

    window.addEventListener('load', () => {
        if (window.scrollY > 92) {
            header.classList.add('scroll');
        }
    });

}());
