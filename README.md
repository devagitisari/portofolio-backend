# Portofolio API

API backend untuk website portofolio yang dibangun dengan Laravel 13.

## Fitur

- ✅ Authentication dengan Laravel Sanctum
- ✅ CRUD Projects dengan upload gambar
- ✅ CRUD Skills dengan kategori
- ✅ CRUD Experiences
- ✅ Contact Form & Inquiry Management
- ✅ Site Settings Management
- ✅ Auto-generate slug untuk projects
- ✅ File upload handling
- ✅ API Resources untuk response yang konsisten

## Tech Stack

- Laravel 13
- SQLite Database
- Laravel Sanctum (API Authentication)
- PHP 8.3+

## Installation

### 1. Clone Repository

```bash
git clone <repository-url>
cd portofolio-api
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Setup Environment

```bash
copy .env.example .env
php artisan key:generate
```

### 4. Install Laravel Sanctum

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 5. Run Migrations & Seeders

```bash
php artisan migrate
php artisan db:seed
```

### 6. Create Storage Link

```bash
php artisan storage:link
```

### 7. Run Development Server

```bash
php artisan serve
```

API akan berjalan di `http://localhost:8000`

## Default Credentials

Setelah menjalankan seeder, gunakan kredensial berikut untuk login:

- **Email**: admin@portofolio.com
- **Password**: password

## API Documentation

Dokumentasi lengkap API tersedia di file `API_SETUP.md`

### Quick Start

#### 1. Login

```bash
POST /api/login
Content-Type: application/json

{
  "email": "admin@portofolio.com",
  "password": "password"
}
```

Response:
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@portofolio.com",
    "role": "admin"
  },
  "token": "1|xxxxxxxxxxxxx"
}
```

#### 2. Get Projects (Public)

```bash
GET /api/projects
```

#### 3. Create Project (Admin)

```bash
POST /api/admin/projects
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "title": "My Project",
  "description": "Project description",
  "category": "Web Development",
  "status": "published",
  "featured": true
}
```

## Testing dengan Postman

Import file `postman_collection.json` ke Postman untuk testing API dengan mudah.

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ProjectController.php
│   │   ├── SkillController.php
│   │   ├── ExperienceController.php
│   │   ├── InquiryController.php
│   │   └── SettingController.php
│   └── Resources/
│       ├── ProjectResource.php
│       └── ProjectImageResource.php
└── Models/
    ├── User.php
    ├── Project.php
    ├── ProjectImage.php
    ├── Skill.php
    ├── Experience.php
    ├── Inquiry.php
    └── Setting.php
```

## Database Schema

### Users
- id, name, email, password, role, remember_token, timestamps

### Projects
- id, title, slug, description, category, status, thumbnail, demo_url, github_url, featured, timestamps

### Project Images
- id, project_id, image, timestamps

### Skills
- id, name, category, percentage, timestamps

### Experiences
- id, company, position, description, start_date, end_date, is_current, timestamps

### Inquiries
- id, name, email, subject, message, is_read, timestamps

### Settings
- id, site_name, tagline, bio, profile_image, resume, github, linkedin, instagram, timestamps

## License

MIT License
