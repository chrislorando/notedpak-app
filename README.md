# FMS – NOTEDPAK

NotedPak is a to-do list app inspired by the spirit of discipline and accountability. In Indonesian culture, saying "Noted, Pak!" means "Understood, Sir!" — a respectful way to acknowledge and accept a task without question or rejection.

This app is designed for unrejected work — the tasks you must complete because they come from your boss, team lead, or client. Whether you're tracking assignments, delegations, or follow-ups, NotedPak helps you stay organized, responsible, and on track.

Built with Laravel, Inertia.js (Vue), and NativePHP for mobile, NotedPak brings a seamless experience across web and mobile to ensure your tasks are always within reach — and never forgotten.

---

## Requirements

- PHP >= 8.4
- Laravel >= 12.x
- Composer
- Node.js & NPM
- SQLLITE

---

## Instalasi

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
