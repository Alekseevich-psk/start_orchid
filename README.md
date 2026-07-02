# StartOrchid — Мини CMS на Laravel + Orchid

Это минималистичная CMS-сборка на базе Laravel Framework и Orchid Platform, предназначенная для быстрого старта проектов.

---

## 📋 Содержание

- [Требования](#требования)
- [Первоначальная настройка](#первоначальная-настройка)
- [Обработка файлов и ресурсов](#обработка-файлов-и-ресурсов)
- [Node.js скрипты (tasks)](#nodejs-скрипты-tasks)
- [Команды разработчика](#команды-разработчика)

---

## Требования

Для работы сборки необходимы:

- **PHP** ≥ 8.2
- **Composer** — менеджер зависимостей PHP
- **pnpm** — менеджер пакетов Node.js (рекомендуется версия ≥ 8.0)
- **MySQL** или другая СУБД (PostgreSQL, SQLite)
- **Node.js** ≥ 18.x

Проверить установку можно командами:

```bash
php -v
composer --version
pnpm --version
node -v
```

---

## Первоначальная настройка

### 1. Установка PHP-зависимостей

Установите все необходимые PHP-пакеты через Composer:

```bash
composer install
```

> 💡 Эта команда установит Laravel, Orchid Platform и все их зависимости.

---

### 2. Установка Node.js-зависимостей

Установите фронтенд-зависимости:

```bash
pnpm install
```

> 💡 Установятся Vite, Tailwind CSS и другие библиотеки.

---

### 3. Настройка файла `.env`

Скопируйте пример конфигурации и отредактируйте под вашу среду:

```bash
copy .env.example .env
```

Откройте `.env` и укажите:

- **APP_NAME** — название проекта
- **APP_URL** — URL проекта (например, `http://127.0.0.1:8000/`)
- **DB_*** — параметры подключения к базе данных:
  - `DB_CONNECTION=mysql` (или `sqlite`, `pgsql`)
  - `DB_HOST` — хост базы
  - `DB_PORT` — порт (обычно `3306`)
  - `DB_DATABASE` — имя базы
  - `DB_USERNAME` — пользователь
  - `DB_PASSWORD` — пароль

> 💡 Для локальной разработки часто используется SQLite — просто укажите `DB_CONNECTION=sqlite`.
>
> 📝 **SQLite vs MySQL — в чём разница?**
>
> | Критерий | SQLite | MySQL |
> |----------|--------|-------|
> | **Установка** | Не требуется — встроен в PHP | Требуется сервер БД (XAMPP, Docker и т.д.) |
> | **Файл** | Хранит данные в одном файле `.sqlite` | Данные хранятся в отдельной директории сервера |
> | **Производительность** | Хорош для локальной разработки и небольших проектов | Оптимизирован для высоконагруженных приложений |
> | **Конкурентность** | Ограниченная (блокировка всей БД при записи) | Высокая (многопользовательский режим) |
> | **Поддержка** | Полная поддержка всех функций Laravel | Полная поддержка всех функций Laravel |
> | **Использование** | `DB_CONNECTION=sqlite` | `DB_CONNECTION=mysql` |
> | **Путь к БД** | `DB_DATABASE=database/database.sqlite` | `DB_DATABASE=название_базы` |
>
> **Когда использовать SQLite:**
> - 🏠 Локальная разработка и тестирование
> - 🧪 Юнит-тесты
> - 📱 Маленькие проекты с низкой нагрузкой
>
> **Когда нужен MySQL:**
> - 🏢 Продакшн-среда
> - 🚀 Высокая нагрузка и множественные пользователи
> - 🔐 Сложные отношения между таблицами (внешние ключи, транзакции)
> - 🗃️ Большие объёмы данных
>
> **📌 Важно:** Orchid и Laravel полностью поддерживают оба драйвера — выбор зависит от этапа разработки и требований к проекту.

---

### 4. Генерация ключа приложения

Если ключ не сгенерирован автоматически (через `composer install`), выполните:

```bash
php artisan key:generate
```

---

### 5. Запуск миграций

Выполните миграции для создания таблиц в базе данных:

```bash
php artisan migrate
```

> ✅ Orchid создаст свои таблицы: `users`, `roles`, `permissions`, `orchid_attachments`, `orchid_users` и др.
>
> ⚠️ **Важно:** Сначала настройте подключение к базе данных в `.env`, иначе миграции не выполнятся!

---

### 6. Запуск сидеров (seeders)

Заполните базу тестовыми данными (страницы, шаблоны, настройки):

```bash
php artisan db:seed
```

> 💡 Сидеры создают:
> - Две страницы: "Главная" (`/`) и "Контакты" (`contacts`)
> - Три шаблона: "Главная", "Контакты", "Документ"
> - Настройки сайта

> Для запуска конкретного сидера используйте:
> ```bash
> php artisan db:seed --class=PageHomeSeeder
> php artisan db:seed --class=TemplateSeeder
> php artisan db:seed --class=SettingSeeder
> ```

---

### 7. Создание администратора (Orchid)

Создайте первого пользователя с правами администратора:

```bash
php artisan orchid:admin
```

Система запросит:

- Имя пользователя
- Email
- Пароль

> 🔐 Запомните учетные данные — они понадобятся для входа в админку.

---

### 8. Очистка кэша приложения

Очистите все кэши Laravel:

```bash
php artisan optimize:clear
```

Или по отдельности:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

### 9. Создание символической ссылки для файлов

> 💡 **Для Windows:** Используйте PowerShell или CMD
> 
> **Для macOS/Linux:** Используйте терминал Unix

Сначала удалите старую символическую ссылку (если существует):

```bash
# Windows (PowerShell)
Remove-Item -Path public/storage -Force -ErrorAction SilentlyContinue

# macOS/Linux
rm -f public/storage
```

Затем создайте новую символическую ссылку `storage/app/public` → `public/storage`:

```bash
php artisan storage:link
```

> ✅ Это необходимо для доступа к загруженным через Orchid файлам (изображения, документы и т.д.)

> 💡 **Альтернатива:** Если `php artisan storage:link` не срабатывает, создайте ссылку вручную:
> 
> ```bash
> # Windows (PowerShell от имени администратора)
> New-Item -ItemType SymbolicLink -Path public/storage -Target storage/app/public
> 
> # macOS/Linux
> ln -s storage/app/public public/storage
> ```

---

### 10. Обновление Orchid

При обновлении пакета Orchid до новой версии выполните:

```bash
composer update orchid/platform --with-dependencies
php artisan orchid:publish
php artisan view:clear
```

> 💡 Также можно обновить все зависимости из `composer.json` командой:
> ```bash
> composer update
> ```
>
> После обновления обязательно опубликуйте новые ассеты и очистите кэш, чтобы использовать последние изменения.

---

## Обработка файлов и ресурсов

### Сборка ресурсов

Для сборки продакшн-версии:

```bash
pnpm run build
```

Для разработки с автообновлением:

```bash
pnpm run dev
```

> 💡 Vite будет доступен по `http://localhost:5173/` по умолчанию.

---

## Node.js скрипты (`tasks/`)

В папке `tasks/` находятся скрипты для автоматизации рутинных операций.

### app-config.js

Центральный конфиг для всех Node.js скриптов:

```js
const appConfig = {
    baseFormat: ['tpl', 'scss'],
    ts: true,
    pathFonts: {
        ttf: "public/fonts/ttf/",
        woff: "public/fonts/",
        fileStyle: "resources/styles/_fonts.scss",
    },
    pathWget: "public/wget",
    sections: "resources/views/sections",
    components: "resources/views/components",
    elements: "resources/views/elements",
};

export default appConfig;
```

---

### `create.js` — Создание компонентов

Создаёт папку и файлы для компонентов, секций или элементов.

#### Использование:

```bash
node tasks/create.js --section Home
node tasks/create.js --component Header
node tasks/create.js --element Button
```

Для создания JS/TS-файлов добавьте флаг:

```bash
node tasks/create.js --section Home --js
node tasks/create.js --component Header --ts
```

> ✅ Создаются файлы: `Home.scss`, `Home.js` (или `Home.ts`) и папка `Home/`

---

### `fonts-in-style.js` — Генерация SCSS- mixin'ов для шрифтов

Автоматически добавляет `@include font(...)` в файл `resources/styles/_fonts.scss` на основе файлов в `public/fonts/`.

#### Использование:

```bash
node tasks/fonts-in-style.js
```

> 📝 Скрипт:
> - Очищает `_fonts.scss`
> - Сканирует `public/fonts/` на наличие `.woff` файлов
> - Определяет вес и стиль по названию (Thin, Bold, Italic и т.д.)
> - Генерирует `@include font(...)`

---

### `convert-fonts.js` — Конвертация шрифтов из TTF в WOFF/WOFF2

Конвертирует `.ttf` шрифты из `public/fonts/ttf/` в форматы `.woff` и `.woff2`.

#### Использование:

```bash
node tasks/convert-fonts.js
```

> 💡 Рекомендуется запускать после добавления новых `.ttf` файлов.

---

### `wget.js` — Скачивание веб-страницы

Скачивает содержимое страницы (HTML, CSS, JS, изображения) в папку `public/wget/`.

#### Использование:

```bash
node tasks/wget.js https://example.com
```

> 🔍 Работает рекурсивно на глубину 1 уровня.

---

## Команды разработчика

### Запуск в режиме разработки (одной командой)

Laravel предоставляет команду `dev` в `composer.json`, которая запускает одновременно:

- Laravel development server
- Queue listener
- Pail (логи)
- Vite dev server

```bash
composer dev
```

> 🟢 Удобно для разработки — всё работает сразу.

---

### Запуск сервера вручную

Если не используете `composer dev`:

1. **Сервер Laravel**:
   ```bash
   php artisan serve
   ```

2. **Vite для фронтенда** (в отдельном терминале):
   ```bash
   pnpm run dev
   ```

3. **Queue worker** (если используется):
   ```bash
   php artisan queue:work
   ```

---

### Тестирование

```bash
composer test
```

Или напрямую:

```bash
php artisan test
```

---

### Очистка кэша (повтор)

```bash
php artisan optimize:clear
```

---

## 🎯 Быстрый старт (чек-лист)

1. ✅ `composer install` — установка PHP-зависимостей
2. ✅ `pnpm install` — установка Node.js-зависимостей
3. ✅ `copy .env.example .env` — копирование конфигурации
4. ✅ Отредактируйте `.env` (настройки БД, APP_URL и т.д.)
5. ✅ `php artisan key:generate` — генерация ключа приложения
6. ✅ `php artisan migrate` — запуск миграций
7. ✅ `php artisan db:seed` — заполнение тестовыми данными
8. ✅ `php artisan orchid:admin` — создание администратора
9. ✅ `php artisan storage:link` — создание символической ссылки
10. ✅ `php artisan optimize:clear` — очистка кэша
11. ✅ `composer dev` или запустите отдельно `php artisan serve` + `pnpm run dev`

> 🚀 Готово! Админка доступна по `/dashboard`, главная страница по `/`
>
> 💡 **Для Windows:** При удалении символической ссылки используйте PowerShell:
> ```powershell
> Remove-Item -Path public/storage -Force -ErrorAction SilentlyContinue
> ```
>
> Если `php artisan storage:link` не создает ссылку, создайте её вручную:
> ```powershell
> New-Item -ItemType SymbolicLink -Path public/storage -Target storage/app/public
> ```

---

## 📚 Полезные ссылки

- [Laravel Documentation](https://laravel.com/docs)
- [Orchid Platform Docs](https://orchid.software/en/docs/)
- [EditorJS Docs](https://editorjs.io/)
- [Vite Documentation](https://vitejs.dev/guide/)

---

## 🐛 Частые проблемы и решения

| Проблема | Решение |
|----------|---------|
| `Error: SQLSTATE[HY000] [1045]` | Проверьте `DB_USERNAME` и `DB_PASSWORD` в `.env` |
| `No application encryption key has been specified.` | Выполните `php artisan key:generate` или `composer install` |
| `The stream or file storage/logs/laravel.log could not be opened` | Убедитесь, что папка `storage/` доступна на запись (`chmod -R 775 storage bootstrap/cache`) |
| `Vite manifest not found` | Запустите `pnpm run dev` или `pnpm run build` |
| `404 Not Found` | Убедитесь, что выполнены миграции и сидеры (`php artisan migrate && php artisan db:seed`) |
| `View [index] not found` | Убедитесь, что запущен сидер шаблонов: `php artisan db:seed --class=TemplateSeeder` |

---

## 🔧 Исправление ошибки 404 на главной странице

Если вы видите 404 ошибку при открытии главной страницы `/`, проверьте:

1. **Выполнены ли миграции?**
   ```bash
   php artisan migrate
   ```

2. **Запущены ли сидеры?**
   ```bash
   php artisan db:seed
   ```

3. **Существует ли страница с ID=1 в базе:**
   ```bash
   php artisan tinker
   >>> Page::find(1)
   ```

4. **Существует ли шаблон с ID=1:**
   ```bash
   php artisan tinker
   >>> Template::find(1)
   ```

5. **Проверьте, что страница опубликована:**
   ```bash
   php artisan tinker
   >>> $page = Page::find(1);
   >>> echo "is_published: " . $page->is_published;
   >>> echo "indexed: " . $page->indexed;
   ```

> ⚠️ **Важно:** Сидер `TemplateSeeder` создает шаблоны с правильными путями (`index`, `contacts`, `document`), а не с префиксом `resources/views/`.

---

**Удачной разработки! 🎉**
