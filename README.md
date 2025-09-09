# NOTEDPAK

![image](https://github.com/user-attachments/assets/6d56b119-c986-47c7-a3b4-f091fc5cbfd1)

NotedPak is a modern to-do list application inspired by the spirit of discipline and accountability found in Indonesian culture. The name reflects the respectful phrase "Noted, Pak!" which means "Understood, Sir!" — a way to acknowledge and accept tasks with commitment and without hesitation.

This project draws inspiration from Microsoft To Do's clean interface and functionality.

This project serves both as a learning medium to deepen my understanding of modern web development technologies and as a showcase piece for my professional portfolio.

---

## Tech Stack

- Laravel - Modern PHP framework for backend development
- Inertia.js - Bridging Laravel with modern frontend
- Vue.js - Progressive JavaScript framework for UI
- NativePHP - Cross-platform mobile development from web codebase
- Full-stack Architecture - Seamless web-to-mobile development workflow

---

## Features

✅ Task Management: Create, edit, delete, and mark tasks as complete
📱 Cross-Platform: Seamless experience across web and mobile devices
🎯 Discipline-Focused: UI/UX designed to promote accountability and task completion
🌐 Responsive Design: Works perfectly on desktop, tablet, and mobile
📊 Progress Tracking: Monitor your productivity and task completion rates
🔄 Real-time Sync: Keep your tasks synchronized across all devices

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

# For NativePHP
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
