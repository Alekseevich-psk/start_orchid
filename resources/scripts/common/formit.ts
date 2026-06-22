import Toastify from 'toastify-js';

document.addEventListener('DOMContentLoaded', () => {
    const forms: NodeList = document.querySelectorAll('[data="ajax-form"]');
    if (forms.length === 0) return;

    let userInteracted = false;

    // События, считающиеся "активностью"
    const handleInteraction = () => {
        userInteracted = true;
        // Удаляем обработчики, чтобы не висели
        ['click', 'scroll', 'touchstart', 'mousemove'].forEach(event =>
            document.removeEventListener(event, handleInteraction)
        );
    };

    ['click', 'scroll', 'touchstart', 'mousemove'].forEach(event =>
        document.addEventListener(event, handleInteraction, { passive: true })
    );

    const offButton = (button: HTMLButtonElement) => {
        button.disabled = true; button.style.opacity = "0.8";
    }

    const onButton = (button: HTMLButtonElement) => {
        button.disabled = false; button.style.opacity = "1";
    }

    forms.forEach(formNode => {
        const formEl = formNode as HTMLFormElement;
        const formStartTime = Date.now();

        let formTitle = formEl.getAttribute('data-title') as string | null;
        if (formTitle === null) formTitle = "Форма обратной связи"

        let successMessage = formEl.getAttribute('data-success-message') as string | null;
        if (successMessage === null) successMessage = "Сообщение отправлено!"

        const btnSubmit = formEl.querySelector('[type="submit"]') as HTMLButtonElement | null;
        if (btnSubmit === null) return console.error("button[submit] is null!");

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') as string | null;
        if (token === null) return console.error("token is null!");

        const action = formEl.querySelector('[name="route-action"]') as HTMLInputElement | null;
        if (action === null || action.value === '') return;

        formEl.addEventListener('submit', async (event) => {
            event.preventDefault();
            offButton(btnSubmit);

            // Если заполнено — это бот
            const hiddenInput = formEl.querySelector('[name="first-name"]') as HTMLInputElement | null;
            if (hiddenInput && hiddenInput?.value !== '') return;

            // Минимальное время заполнения (3 секунды)
            const timeElapsed = Date.now() - formStartTime;
            if (timeElapsed < 3000) {
                console.warn("Spam detected: form submitted too fast", timeElapsed);
                Toastify({
                    text: "Слишком быстро!",
                    duration: 3000,
                    close: true,
                    style: { background: "#b91c1c" },
                }).showToast();
                onButton(btnSubmit);
                return;
            }

            // Проверка активности пользователя
            if (!userInteracted) {
                console.warn("Spam detected: no user interaction");
                Toastify({
                    text: "Подождите, вы бот?",
                    duration: 3000,
                    close: true,
                    style: { background: "#b91c1c" },
                }).showToast();
                onButton(btnSubmit);
                return;
            }

            try {
                const formData = new FormData(formEl);
                
                const response = await fetch(action.value, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token,
                    },
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    console.log(data);
                    Toastify({
                        text: "В форме ошибки!",
                        duration: 3000,
                        newWindow: true,
                        close: true,
                        style: {
                            background: "linear-gradient(to right, #374D43, #37424D)",
                        },
                    }).showToast();
                } else {
                    Toastify({
                        text: successMessage,
                        duration: 3000,
                        newWindow: true,
                        close: true,
                        style: {
                            background: "linear-gradient(to right, #3F440F, #3F440F)",
                        },
                    }).showToast();

                    formEl.reset();
                }
            } catch (error) {
                console.log(error);
            }

            window.dispatchEvent(new CustomEvent('fancybox:close', {
                detail: {
                    reason: 'form-submitted'
                }
            }));
            onButton(btnSubmit);
        })
    });
})