# NOTEDPAK

<table>
  <tr>
    <td>
      <strong>Dashboard</strong><br>
      <img src="https://github.com/user-attachments/assets/0f2a6e2c-4263-480c-a2cf-e1244c0020ac" width="100%">
    </td>
    <td>
      <strong>View tasks</strong><br>
      <img src="https://github.com/user-attachments/assets/4f87c200-977e-45ab-ac56-06dba7443314" width="100%">
    </td>
  </tr>
 
</table>

NotedPak is a modern to-do list application inspired by the spirit of discipline and accountability found in Indonesian culture. The name reflects the respectful phrase "Noted, Pak!" which means "Understood, Sir!" — a way to acknowledge and accept tasks with commitment and without hesitation.

This project draws inspiration from Microsoft To Do's clean interface and functionality.

This project serves both as a learning medium to deepen my understanding of modern web development technologies and as a showcase piece for my professional portfolio.

---

## Tech Stack

- Laravel - Modern PHP framework for backend development
- Inertia.js - Bridging Laravel with modern frontend
- Vue.js - Progressive JavaScript framework for UI
- Tailwind CSS - Utility-first CSS framework
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
📦 Drag & Drop: Reorder lists or projects, and tasks with intuitive drag-and-drop interactions

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

## Credits

Sound Effect by <a href="https://pixabay.com/users/floraphonic-38928062/?utm_source=link-attribution&utm_medium=referral&utm_campaign=music&utm_content=211683">floraphonic</a> from <a href="https://pixabay.com//?utm_source=link-attribution&utm_medium=referral&utm_campaign=music&utm_content=211683">Pixabay</a>
