document.addEventListener('DOMContentLoaded', () => {
    const button = document.querySelector<HTMLElement>('.btn-top');

    if (!button) {
        console.warn('Кнопка ".btn-top" не найдена на странице.');
        return;
    }
    
    button.addEventListener('click', (e) => {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        });
    });
});
