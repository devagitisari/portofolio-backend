# Docker Deployment Guide for Render with Supabase

## Prerequisites
- Docker installed locally
- Render account (free tier available)
- Git repository with your Laravel backend
- Supabase account (free tier available)

## Files Created
1. `Dockerfile` - Multi-stage build configuration
2. `.dockerignore` - Files to exclude from Docker build
3. `render.yaml` - Render service configuration

## Setup Steps

### 1. Create Supabase Project
1. Go to [Supabase Dashboard](https://supabase.com/dashboard)
2. Click "New Project"
3. Choose:
   - **Name**: Your project name
   - **Database Password**: Generate a strong password
   - **Region**: Choose nearest region (Singapore recommended for Indonesia)
4. Click "Create new project"
5. Wait for project to be ready (1-2 minutes)

### 2. Get Supabase Database Credentials
1. In Supabase Dashboard, go to **Settings** → **Database**
2. Copy the following:
   - **Host**: `your-project.ref.supabase.co`
   - **Database name**: `postgres`
   - **Port**: `5432`
   - **User**: `postgres`
   - **Password**: Your database password

### 3. Push to Git Repository
Make sure your backend code is pushed to a Git repository (GitHub, GitLab, or Bitbucket).

### 4. Create Web Service on Render
1. Go to Render Dashboard
2. Click "New" → "Web Service"
3. Connect your Git repository
4. Select "Docker" as runtime
5. Configure:
   - **Name**: laravel-backend (or your preferred name)
   - **Region**: Singapore
   - **Branch**: main
6. Add Environment Variables:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=your-generated-app-key
   DB_CONNECTION=pgsql
   DB_HOST=your-project.supabase.co
   DB_PORT=5432
   DB_DATABASE=postgres
   DB_USERNAME=postgres
   DB_PASSWORD=your-supabase-password
   ```
7. Click "Create Web Service"

### 5. Alternative: Use render.yaml
If you want to use the `render.yaml` file:
1. Update `render.yaml` with your Supabase credentials
2. Push `render.yaml` to your repository
3. In Render, click "New" → "Blueprint"
4. Connect your repository
5. Render will read the `render.yaml` and create the web service

## Environment Variables Required

Set these in Render Dashboard:

### Required
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` - Generate with: `php artisan key:generate`
- `DB_CONNECTION=pgsql`
- `DB_HOST` - From Supabase (e.g., `your-project.ref.supabase.co`)
- `DB_PORT=5432`
- `DB_DATABASE=postgres`
- `DB_USERNAME=postgres`
- `DB_PASSWORD` - Your Supabase database password

### Optional (depending on your app)
- `CACHE_DRIVER=redis` (if using Supabase Redis)
- `QUEUE_CONNECTION=database` (default)
- `SESSION_DRIVER=database` (default)
- `FILESYSTEM_DISK=local` (default)

## Database Migration

After deployment, you need to run migrations. You can:

### Option 1: SSH into the container
1. Go to your web service in Render
2. Click "SSH" tab
3. Run: `php artisan migrate --force`

### Option 2: Add to Dockerfile
Add this line before `CMD` in Dockerfile:
```dockerfile
RUN php artisan migrate --force
```

### Option 3: Use Render Deploy Hooks
Add a post-deploy hook in Render dashboard:
```bash
php artisan migrate --force
```

## Testing Locally

Build and test the Docker image locally:

```bash
# Build the image
docker build -t laravel-backend .

# Run the container
docker run -p 80:80 -e APP_KEY=your-app-key laravel-backend
```

## Troubleshooting

### Build Failures
- Check Docker build logs in Render
- Ensure all dependencies are in `composer.json`
- Verify PHP extensions are installed

### Runtime Errors
- Check Render logs
- Verify environment variables are set correctly
- Ensure database connection details are correct

### Permission Issues
The Dockerfile sets proper permissions for storage and cache directories.

### Database Connection
- Ensure Supabase project is created before web service
- Verify database credentials match Supabase settings
- Check Supabase project is accessible from Render (no IP restrictions)
- If using Supabase Pooler, use port 6543 instead of 5432

## Notes

- The Dockerfile uses PHP 8.3 with Apache
- Frontend assets are built during Docker build
- The application is optimized for production
- Port 80 is exposed as required by Render
- Multi-stage build reduces final image size
