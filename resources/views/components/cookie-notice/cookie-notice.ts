function showCookieNotice() {
    const cookieNotice = document.createElement('div');
    cookieNotice.className = 'cookie-notice';
    cookieNotice.innerHTML = `
    <div class="cookie-notice__container container">
        <div class="cookie-notice__wrapper">
        <p class="cookie-notice__text">Мы используем файлы cookie, чтобы обеспечить вам наилучший опыт на нашем веб-сайте.</p>
        <button class="cookie-notice__button button">Принять</button>
      </div>
    </div>
  `;
    document.body.appendChild(cookieNotice);

    // Закрытие уведомления по кнопке
    const button = cookieNotice.querySelector('.cookie-notice__button');
    button?.addEventListener('click', () => {
        cookieNotice.remove();
        localStorage.setItem('cookieNoticeAccepted', 'true');
    });

    // Проверка, было ли уже принято
    if (localStorage.getItem('cookieNoticeAccepted') === 'true') {
        cookieNotice.remove();
    }
}

// Показ уведомления после загрузки DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', showCookieNotice);
} else {
    showCookieNotice();
}