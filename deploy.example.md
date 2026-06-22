# 🚀 Шпаргалка: Автоматический деплой Laravel на REG.RU через GitHub Actions

Этот гайд поможет быстро настроить непрерывный деплой (CD) для Laravel-проекта на хостинге REG.RU с использованием GitHub Actions и SSH.  
Подходит для shared-хостинга REG.RU, где нет доступа к CI/CD, но есть SSH.

---

## 🔐 1. Настройка Deploy Key (SSH) — безопасный доступ к репозиторию

### На сервере (REG.RU):

Создайте SSH-ключ без пароля (для автоматизации):

```bash
ssh-keygen -t ed25519 -C "deploy@yoursite.ru" -f ~/.ssh/id_ed25519_deploy

⚠️ При запросе Enter passphrase — нажмите Enter дважды, не вводя ничего.


Просмотр публичного ключа:
Bash
cat ~/.ssh/id_ed25519_deploy.pub
→ Скопируйте весь вывод (начинается с ssh-ed25519 ...). Он понадобится для GitHub.

На GitHub:
Перейдите в ваш репозиторий → Settings → Deploy keys → Add deploy key
Укажите:
Title: REG.RU server - yoursite.ru
Key: вставьте скопированный публичный ключ
✅ Allow write access — можно поставить (если планируете пушить с сервера), но для деплоя достаточно чтения

Нажмите Add key
На сервере: настройка SSH-конфигурации
Отредактируйте файл конфигурации SSH:

Bash
nano ~/.ssh/config
Добавьте:

Host github.com-deploy
    HostName github.com
    IdentityFile ~/.ssh/id_ed25519_deploy
    IdentitiesOnly yes

Сохраните: Ctrl+O → Enter → Ctrl+X


Установите правильные права доступа:
Bash
chmod 700 ~/.ssh
chmod 600 ~/.ssh/config
chmod 600 ~/.ssh/id_ed25519_deploy

SSH требует строгих прав. Иначе будет ошибка Bad owner or permissions.


Проверьте подключение:
Bash
ssh -T git@github.com-deploy
✅ Ожидаемый ответ:

Hi username/repo! You've successfully authenticated, but GitHub does not provide shell access.
Если ошибка — проверьте права и имя хоста в ~/.ssh/config.

🌐 2. Переключите репозиторий на SSH
Перейдите в папку проекта и измените URL удалённого репозитория:

Bash
cd /www/yoursite.ru
git remote set-url origin git@github.com-deploy:username/your-repo.git

Замените:


yoursite.ru → ваш домен
username/your-repo.git → ваш GitHub репозиторий

Первый pull (проверка):
Bash
git pull origin main
Если прошло без запроса пароля — всё настроено правильно ✅

🤖 3. GitHub Actions Workflow — .github/workflows/deploy.yml
Создайте файл в репозитории:

.github/workflows/deploy.yml

YAML
name: Deploy Laravel to REG.RU

on:
  push:
    branches: [main]  # измените на master, если нужно

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Execute SSH Commands
        uses: appleboy/ssh-action@v0.1.9
        with:
          host: ${{ secrets.REG_RU_HOST }}
          username: ${{ secrets.REG_RU_USER }}
          password: ${{ secrets.REG_RU_PASSWORD }}
          port: 22
          script: |
            cd /www/yoursite.ru

            # Получаем последние изменения
            git pull origin main

            # Очистка кеша Laravel
            php artisan config:clear
            php artisan cache:clear
            php artisan route:clear
            php artisan view:clear

            # (Опционально) Применение миграций (осторожно!)
            # php artisan migrate --force

            # (Опционально) Кэширование конфигов и маршрутов
            # php artisan config:cache
            # php artisan route:cache

            # Лог успешного деплоя
            echo "✅ Deployed commit $(git rev-parse --short HEAD) at $(date)"
🔐 4. Секреты в GitHub
Перейдите: Settings → Secrets and variables → Actions → New repository secret

Добавьте:

Name	Value
REG_RU_HOST	Адрес сервера (например, server203.hosting.reg.ru)
REG_RU_USER	Логин от хостинга (например, u0536746)
REG_RU_PASSWORD	Пароль от хостинга
✅ 5. Проверка работы
Внесите небольшое изменение в код (например, в routes/web.php)
Запушьте в ветку main:
Bash
git add .
git commit -m "Test deploy"
git push origin main

Перейдите в GitHub → Actions
Убедитесь, что workflow запустился и завершился зелёной галочкой ✅
Откройте сайт — изменения должны быть видны
💡 Полезные команды
Назначение	Команда
Последний коммит	git rev-parse --short HEAD
Проверить статус	git status
Обновить код вручную	git pull origin main
Права на storage	chmod -R 755 storage bootstrap/cache
Просмотр логов	tail -f storage/logs/laravel.log
⚠️ Важные замечания
❌ Не коммитьте .env, .env.local, .git-credentials
✅ Используйте .env.example в репозитории
🔐 На сервере .env должен быть уже создан и заполнен
🔄 Deploy Key даёт доступ только к одному репозиторию — безопаснее, чем PAT
🧹 При смене сервера — создайте новый ключ и добавьте его как Deploy Key
💬 Советы
Для продвинутых проектов рассмотрите GitHub Apps или self-hosted runners
Добавьте уведомления в Telegram при деплое
Используйте отдельную ветку production, если нужно контролировать релизы
🚀 Готово! Теперь вы можете быстро настраивать деплой для любых Laravel-проектов на REG.RU.

Храните этот файл — он сэкономит вам часы в будущем!


---

✅ Теперь вы можете:
- Сохранить этот текст как `deploy.example.md`
- Использовать как шаблон для всех новых проектов
- Поделиться с коллегами

Если хотите — могу сделать **PDF**, **HTML** или **сокращённую версию-чеклист**.