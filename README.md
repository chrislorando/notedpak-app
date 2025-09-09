# NOTEDPAK

![image](https://github.com/user-attachments/assets/6d56b119-c986-47c7-a3b4-f091fc5cbfd1)

NotedPak is a to-do list app inspired by the spirit of discipline and accountability. In Indonesian culture, saying "Noted, Pak!" means "Understood, Sir!" — a respectful way to acknowledge and accept a task without question or rejection.

This app is designed for unrejected work — the tasks you must complete because they come from your boss, team lead, or client. Whether you're tracking assignments, delegations, or follow-ups, NotedPak helps you stay organized, responsible, and on track.

Built with Laravel, Inertia.js (Vue), and NativePHP for mobile, NotedPak brings a seamless experience across web and mobile to ensure your tasks are always within reach — and never forgotten.

This project serves both as a learning medium to deepen my understanding of modern web development technologies and as a showcase piece for my professional portfolio.

---

## Tech Stack

- Laravel - Modern PHP framework for backend development
- Inertia.js - Bridging Laravel with modern frontend
- Vue.js - Progressive JavaScript framework for UI
- NativePHP - Cross-platform mobile development from web codebase
- Full-stack Architecture - Seamless web-to-mobile development workflow

---

## Requirements

- PHP >= 8.4
- Laravel >= 12.x
- Composer
- Node.js & NPM
- SQLLITE

---

## Set up env

```bash
touch database/database.sqlite

Update .env with:
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/your/project/database/database.sqlite

# For NativePHP build
NATIVEPHP_APP_ID=xxx.xxxx.xxx
NATIVEPHP_APP_VERSION="DEBUG"
NATIVEPHP_APP_VERSION_CODE="1"

```

## Instalation

```bash
composer install
npm install
npm run build

cp .env.example .env

php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan storage:link

php artisan migrate:fresh --seed

chmod -R 775 storage
chmod -R 775 bootstrap/cache
```
