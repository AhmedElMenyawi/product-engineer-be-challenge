# HelloChef Product Engineer Backend Challenge - Installation Guide

Hi there 👋

Thank you for taking the time to review and test my code! This guide will walk you through setting up the project locally without Docker.

## 📋 Prerequisites

Make sure your local setup meets the following requirements:

- **PHP**: 8.1.31 or higher
- **Composer**: 2.8.6 or higher
- **MySQL**: 8.0.41 or higher
- **Node.js**: 18.x or higher (for frontend assets)
- **npm**: 9.x or higher
- **Git**: For cloning the repository

## 🚀 Installation Steps

### 1. Clone and Navigate to Project
```bash
git clone https://github.com/AhmedElMenyawi/product-engineer-be-challenge.git
cd product-engineer-be-challenge
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node.js Dependencies
```bash
npm install
```

### 4. Environment Setup
```bash
cp .env.example .env
```

### 5. Configure Database
Edit your `.env` file and update the database configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hellochef_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**Important**: Create a MySQL database named `hellochef_db` (or your preferred name) before proceeding.

### 6. Generate Application Key
```bash
php artisan key:generate
```

### 8. Run Database Migrations
```bash
php artisan migrate
```

### 9. Seed Database with Test Data
```bash
php artisan db:seed
```

This will create test users and teams for API testing.

### 10. Start the Development Server
```bash
php artisan serve
```

The application will be available at: **http://127.0.0.1:8000**

## 🔐 Test Credentials

The seeder creates the following test users for API testing:

| Email | Password | Name |
|-------|----------|------|
| `anthonyponcio@hellchef.com` | `AP@Random123` | Anthony Poncio |
| `mohsinali@hellchef.com` | `MA@Random123` | Mohsin Ali |
| `ahmedelmenyawi@hellchef.com` | `AE@Random123` | Ahmed ElMenyawi |

## 🧪 API Testing

### Postman Collection
A complete Postman collection is included in the `docs/` folder: `Hello Chef Task API Collection.postman_collection.json`

To use it:
1. Import the collection into Postman
2. Set up the environment variable `{{base_url}}` to `http://127.0.0.1:8000`
3. Start with the Login request to get an access token
4. Copy the token from the response and update the Bearer token in other requests
5. Run the requests in sequence

### Authentication
Start by logging in to get an access token:

### Available API Endpoints

#### Authentication
- `POST /api/v1/login` - User login
- `POST /api/v1/logout` - User logout (requires authentication)

#### Tasks (All require authentication)
- `GET /api/v1/tasks` - List all tasks
- `POST /api/v1/tasks` - Create a new task
- `GET /api/v1/tasks/{task_token}` - Get specific task
- `PUT /api/v1/tasks/{task_token}` - Update task
- `DELETE /api/v1/tasks/{task_token}` - Delete task
- `POST /api/v1/tasks/bulk-create` - Create multiple tasks
- `GET /api/v1/tasks/status-summary` - Get task status summary

#### Users (All require authentication)
- `POST /api/v1/users` - Create a new user
- `POST /api/v1/users/bulk-create` - Create multiple users

That's it! The application should now be up and running on your local machine. Happy testing! 🎉