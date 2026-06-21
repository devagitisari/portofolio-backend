# Portofolio API - Setup Guide

## Installation Steps

### 1. Install Laravel Sanctum

```bash
composer require laravel/sanctum
```

### 2. Publish Sanctum Configuration

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Create Storage Link

```bash
php artisan storage:link
```

### 5. Create Admin User (Optional)

Buat seeder atau gunakan tinker untuk membuat user admin:

```bash
php artisan tinker
```

Kemudian jalankan:

```php
\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role' => 'admin'
]);
```

## API Endpoints

### Public Endpoints

#### Authentication
- `POST /api/login` - Login user
  ```json
  {
    "email": "admin@example.com",
    "password": "password"
  }
  ```

- `POST /api/logout` - Logout user (requires authentication)

#### Projects
- `GET /api/projects` - Get all published projects
- `GET /api/projects/{slug}` - Get project by slug

#### Skills
- `GET /api/skills` - Get all skills (grouped by category)

#### Experiences
- `GET /api/experiences` - Get all experiences

#### Settings
- `GET /api/settings` - Get site settings

#### Contact
- `POST /api/contact` - Submit contact form
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "subject": "Inquiry",
    "message": "Your message here"
  }
  ```

### Admin Endpoints (Requires Authentication)

All admin endpoints require `Authorization: Bearer {token}` header.

#### Projects
- `POST /api/admin/projects` - Create project
- `PUT /api/admin/projects/{id}` - Update project
- `DELETE /api/admin/projects/{id}` - Delete project

#### Skills
- `GET /api/admin/skills` - Get all skills
- `POST /api/admin/skills` - Create skill
- `GET /api/admin/skills/{id}` - Get skill by ID
- `PUT /api/admin/skills/{id}` - Update skill
- `DELETE /api/admin/skills/{id}` - Delete skill

#### Experiences
- `GET /api/admin/experiences` - Get all experiences
- `POST /api/admin/experiences` - Create experience
- `GET /api/admin/experiences/{id}` - Get experience by ID
- `PUT /api/admin/experiences/{id}` - Update experience
- `DELETE /api/admin/experiences/{id}` - Delete experience

#### Inquiries
- `GET /api/admin/inquiries` - Get all inquiries
- `GET /api/admin/inquiries/{id}` - Get inquiry by ID (marks as read)
- `PUT /api/admin/inquiries/{id}` - Update inquiry
- `DELETE /api/admin/inquiries/{id}` - Delete inquiry

#### Settings
- `GET /api/admin/settings` - Get all settings
- `POST /api/admin/settings` - Create settings
- `GET /api/admin/settings/{id}` - Get settings by ID
- `PUT /api/admin/settings/{id}` - Update settings
- `DELETE /api/admin/settings/{id}` - Delete settings

## Request Examples

### Create Project

```bash
POST /api/admin/projects
Content-Type: multipart/form-data
Authorization: Bearer {token}

{
  "title": "My Portofolio Website",
  "description": "A beautiful portofolio website built with Laravel and React",
  "category": "Web Development",
  "status": "published",
  "thumbnail": [file],
  "demo_url": "https://example.com",
  "github_url": "https://github.com/username/repo",
  "featured": true,
  "images": [file1, file2, file3]
}
```

### Create Skill

```bash
POST /api/admin/skills
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "Laravel",
  "category": "Backend",
  "percentage": 90
}
```

### Create Experience

```bash
POST /api/admin/experiences
Content-Type: application/json
Authorization: Bearer {token}

{
  "company": "Tech Company",
  "position": "Full Stack Developer",
  "description": "Developed web applications using Laravel and React",
  "start_date": "2023-01-01",
  "end_date": "2024-12-31",
  "is_current": false
}
```

### Update Settings

```bash
PUT /api/admin/settings/1
Content-Type: multipart/form-data
Authorization: Bearer {token}

{
  "site_name": "My Portofolio",
  "tagline": "Full Stack Developer",
  "bio": "I am a passionate developer...",
  "profile_image": [file],
  "resume": [file],
  "github": "https://github.com/username",
  "linkedin": "https://linkedin.com/in/username",
  "instagram": "https://instagram.com/username"
}
```

## Features

### Models
- ✅ User (with Sanctum authentication)
- ✅ Project (with images relationship)
- ✅ ProjectImage
- ✅ Skill
- ✅ Experience
- ✅ Inquiry
- ✅ Setting

### Controllers
- ✅ AuthController - Login/Logout
- ✅ ProjectController - CRUD operations with image upload
- ✅ SkillController - CRUD operations
- ✅ ExperienceController - CRUD operations
- ✅ InquiryController - Contact form & CRUD
- ✅ SettingController - Site settings management

### Features
- ✅ Auto-generate slug from project title
- ✅ File upload handling (images, resume)
- ✅ Automatic file deletion when updating/deleting
- ✅ Mark inquiry as read when viewed
- ✅ Filter published projects for public
- ✅ Group skills by category
- ✅ Sort experiences by date
- ✅ Featured projects support

## CORS Configuration

Jika Anda menggunakan frontend terpisah, tambahkan konfigurasi CORS di `config/cors.php` atau install package:

```bash
composer require fruitcake/laravel-cors
```

## Environment Variables

Pastikan file `.env` sudah dikonfigurasi dengan benar:

```env
APP_URL=http://localhost:8000
FILESYSTEM_DISK=public

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

## Testing

Anda bisa test API menggunakan Postman, Insomnia, atau curl:

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Get Projects
curl http://localhost:8000/api/projects

# Create Skill (with token)
curl -X POST http://localhost:8000/api/admin/skills \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"name":"PHP","category":"Backend","percentage":85}'
```
