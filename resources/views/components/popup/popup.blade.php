<div class="popup" id="order-popup">
    <div class="popup__body">
        <form method="POST" data="ajax-form" class="popup__form"
            data-title="Форма обратной связи. Попап.">
            @csrf

            <input type="hidden" name="route-action" value="{{ route('feedback.send') }}">
            <input type="hidden" name="form_id">
            <input type="hidden" name="form_title" value="Форма - попап">
            <input type="text" name="first-name" id="first-name">

            <div class="popup__title">Оставьте заявку</div>
            <div class="popup__desc">Наш менеджер свяжется с вами в ближайшее время</div>
            <div class="popup__input input">
                <input type="text" name="name" placeholder="Ваше имя*" required>
            </div>

            <div class="popup__input input">
                <input type="text" name="phone" placeholder="Ваш телефон*" required>
            </div>

            <div class="popup__cop">
                <input required type="checkbox" class="checkbox" id="cop-popup" name="cop-popup">
                <label for="cop-popup">
                    <p>Отправляя заявку, Вы соглашаетесь
                        с условиями обработки<a href="/"> Персональных данных</a></p>
                </label>
            </div>

            <div class="popup__inner popup__inner--btn">
                <button type="submit" class="popup__button button button--bc">
                    <span class="button__text">Отправить</span>
                    <span class="button__ico"></span>
                </button>
            </div>

        </form>
    </div>
</div>
