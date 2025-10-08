# NOTEDPAK

<table>
  <tr>
    <td>
      <strong>Dashboard</strong><br>
      <img src="https://github.com/user-attachments/assets/eb73e52f-5741-410f-975b-845009f1a6b7" width="100%">
    </td>
    <td>
      <strong>View tasks</strong><br>
      <img src="https://github.com/user-attachments/assets/4f87c200-977e-45ab-ac56-06dba7443314" width="100%">
    </td>
  </tr>
</table>

<table>
  <tr>
    <td style="vertical-align:top;height:300px;">
      <strong>Dashboard</strong><br>
      <img src="https://github.com/user-attachments/assets/96c465f3-30f1-4d5c-92d0-b72adf6e68f5" width="100%">
    </td>
    <td style="vertical-align:top;height:300px;">
      <strong>View List/Project</strong><br>
      <img src="https://github.com/user-attachments/assets/a72af851-c671-4573-947c-c3f31442bac2" width="100%">
    </td>
    <td style="vertical-align:top;height:300px;">
      <strong>View tasks</strong><br>
      <img src="https://github.com/user-attachments/assets/87e24cb9-fce7-4ac2-bd9e-adc5f48a48f1" width="100%">
    </td>
  </tr>
</table>

NotedPak is a modern to-do list application inspired by the spirit of discipline and accountability found in Indonesian culture. The name reflects the respectful phrase "Noted, Pak!" which means "Understood, Sir!" — a way to acknowledge and accept tasks with commitment and without hesitation.

This project draws inspiration from Microsoft To Do's clean interface and functionality.

This project serves both as a learning medium to deepen my understanding of modern web development technologies and as a showcase piece for my professional portfolio. It also aims to explore and experiment with NativePHP.

---

## Sync Concept

<img width="381" height="447" alt="Image" src="https://github.com/user-attachments/assets/a4069ae4-8930-45d3-8c10-46f0e64da7c6" />

## Tech Stack

- **Laravel** – Modern PHP framework for backend development, RESTful APIs, and business logic
- **Inertia.js** – Bridges Laravel backend with Vue.js frontend for single-page app experience
- **Vue.js** – Progressive JavaScript framework for building interactive UIs
- **Tailwind CSS** – Utility-first CSS framework for rapid and responsive UI design
- **NativePHP** – Enables cross-platform mobile app development from a web codebase
- **Full-stack Architecture** – Seamless workflow from web to mobile, unified codebase
- **SQLite** – Lightweight, file-based relational database for local development and mobile
- **Supabase** – An open-source Backend as a Service (BaaS) platform built on PostgreSQL
- **MinIO** - An open-source, high-performance object storage server that is S3-compatible

---

## Features

✅ Task Management: Create, edit, delete, and mark tasks as complete  
📱 Cross-Platform: Seamless experience across web and mobile devices  
🎯 Discipline-Focused: UI/UX designed to promote accountability and task completion  
🌐 Responsive Design: Works perfectly on desktop, tablet, and mobile  
📊 Progress Tracking: Monitor your productivity and task completion rates  
🔄 Data Sync: Keep your tasks synchronized across all devices  
📦 Drag & Drop: Reorder lists or projects, and tasks with intuitive drag-and-drop interactions

---

## Requirements

- PHP >= 8.3
- Laravel >= 12.x
- Composer
- Node.js & NPM
- SQLLITE
- PostgreSQL
- NativePHP
- MiniO

---

## Instalation

```bash

composer.json add:

"repositories": [
    {
        "type": "composer",
        "url": "https://nativephp.composer.sh"
    }
],


composer install
npm install
npm run build

cp .env.example .env

php artisan key:generate
php artisan config:cache
php artisan route:cache

php artisan migrate:fresh --seed

chmod -R 775 storage
chmod -R 775 bootstrap/cache

php artisan native:install
php artisan native:run

```

Read the documentation of Nativephp
https://nativephp.com/docs/mobile/1/getting-started/introduction

## Set up env

```bash
touch database/database.sqlite

# Update .env with:

# If using local SQLite (mobile local DB), use the example below:
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/your/project/database/database.sqlite

# Supabase public API for backend (testing use)
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key

# NativePHP (Mobile App)
NATIVEPHP_APP_ID=com.example.notedpak
NATIVEPHP_APP_VERSION=DEBUG
NATIVEPHP_APP_VERSION_CODE=1

# Supabase / PostgreSQL (Web / Data Center / Server)
DB_CONNECTION=pgsql
DB_URL=postgresql://DB_USER:DB_PASSWORD@DB_HOST:DB_PORT/DB_NAME

# MiniO
AWS_ACCESS_KEY_ID=xxxxxxxx
AWS_SECRET_ACCESS_KEY=xxxxxxxx
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=todo
AWS_ENDPOINT=https://xx.xxx.xxx
AWS_USE_PATH_STYLE_ENDPOINT=true

```

## Demo Link

https://notedpak.demolite.my.id/

## Credits

Sound Effect by <a href="https://pixabay.com/users/floraphonic-38928062/?utm_source=link-attribution&utm_medium=referral&utm_campaign=music&utm_content=211683">floraphonic</a> from <a href="https://pixabay.com//?utm_source=link-attribution&utm_medium=referral&utm_campaign=music&utm_content=211683">Pixabay</a>
