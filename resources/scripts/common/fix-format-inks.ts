document.addEventListener('DOMContentLoaded', () => {
    const phoneElements = document.querySelectorAll<HTMLElement>('[data-fix-format="phone"]');

    phoneElements.forEach((element) => {
        const phoneNumberText = element.textContent?.trim();
        if (!phoneNumberText) return;

        let formattedNumber = phoneNumberText.replace(/\D/g, '');

        if (formattedNumber.startsWith('8') && formattedNumber.length === 11) {
            formattedNumber = '7' + formattedNumber.slice(1);
        }

        if (formattedNumber.startsWith('7') && formattedNumber.length === 11) {
        } else if (formattedNumber.length === 10) {
            formattedNumber = '7' + formattedNumber;
        }

        const telLink = `tel:${formattedNumber}`;

        if (element.tagName === 'A') {
            element.setAttribute('href', telLink);
        }
    });

    const emailElements = document.querySelectorAll<HTMLElement>('[data-fix-format="email"]');

    emailElements.forEach((element) => {
        const emailText = element.textContent?.trim();
        if (!emailText) return;

        const cleanedEmail = emailText.replace(/\s+/g, ''); // Убираем все пробелы
        const mailtoLink = `mailto:${cleanedEmail}`;

        if (element.tagName === 'A') {
            element.setAttribute('href', mailtoLink);
        }
    });
});
