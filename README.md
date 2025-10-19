# Бронирование услуг (Laravel 10 + Inertia/Vue 3 + Vite + Tailwind)

Готовый прототип SPA-приложения для бронирования услуг с вариантами длительности и техническим интервалом.  
Клиентская часть — Inertia + Vue 3, стили — Tailwind via Vite.  
Админка без авторизации (тестовое задание все же): управление услугами, вариантами, расписанием, история.

## Требования

- PHP ≥ 8.1
- Composer ≥ 2.x
- Node.js ≥ 18, npm ≥ 9
- MySQL/MariaDB

## Быстрый старт

```bash
# 1) Ставим зависимости
composer install

# 2) Сгенерим ключ приложения
cp .env.example .env
php artisan key:generate

# 3) Прописываем доступ к БД в .env и следом:
php artisan migrate --seed     # таблицы и демо-данные

# 4) Для отдачи загруженных картинок
php artisan storage:link

# 5) Фронтенд-зависимости и ассеты
npm ci || npm install
npm run build   # ну или npm run dev

# 6) Запуск
php artisan serve

# И перейдите на http://localhost:8000
