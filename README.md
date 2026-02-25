# 🍳 Resepin

A smart, AI-powered recipe finder application built with Laravel and YOLOv8 that transforms your fridge ingredients into delicious meals instantly.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)

## 📑 Table of Contents

- [Features](#-features)
- [Prerequisites](#-prerequisites)
- [Getting Spoonacular API Key](#-getting-spoonacular-api-key)
- [Installation (Local)](#-installation-local)
- [Environment Variables](#-environment-variables)
- [Deployment](#-deployment)
  - [Docker Deployment](#docker-deployment)
  - [Azure App Service Deployment](#azure-app-service-deployment)
- [How to Use](#-how-to-use)
- [Technical Details](#️-technical-details)
- [Contributing](#-contributing)

---

## ✨ Features

### 🎯 Core Functionality
* **AI Ingredient Detection:** Powered by **YOLOv8**, automatically identifies ingredients from uploaded photos in seconds.
* **Smart Recipe Search:** Integrates with **Spoonacular API** to find the best recipes matching your ingredients.
* **Intelligent Filters:** Built-in dietary preferences for **Vegan**, **Vegetarian**, **Gluten-Free**, and **Halal**.
* **Spiciness Control:** Automatically filter out spicy recipes based on user preference.

### 🎨 User Experience
* **Favorites System:** Save your loved recipes to a personal cookbook.
* **Secure Authentication:** Complete Login & Register system powered by Laravel Breeze.
* **Responsive Design:** Optimized for both desktop and mobile experiences using Bootstrap 5.

---

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

| Software | Version | Required |
|----------|---------|----------|
| PHP | 8.1+ | ✅ |
| Composer | 2.x | ✅ |
| Node.js | 16.x+ | ✅ |
| MySQL | 5.7+ / 8.x | ✅ |
| Docker | Latest | Optional |
| Git | Latest | ✅ |

---

## 🔑 Getting Spoonacular API Key

Spoonacular API is used to search for recipes based on detected ingredients. Here's how to get your API Key:

### Step 1: Create a Spoonacular Account

1. Visit (https://spoonacular.com/food-api)
2. Click the **"Start Now"** or **"Sign Up"** button in the top right corner
3. Fill in the registration form with:
   - Email
   - Password
4. Verify your email

### Step 2: Access the Dashboard

1. After logging in, click **"My Console"** or go directly to [Console](https://spoonacular.com/food-api/console)
2. You will see the dashboard with your account information

### Step 3: Get Your API Key

1. In the dashboard, find the **"Profile"** or **"API Key"** section
2. Your API Key will be displayed like this:
   ```
   a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
   ```
3. **Copy** the API Key

### Step 4: Save Your API Key

Save your API Key in the `.env` file:

```env
SPOONACULAR_API_KEY=your_api_key_here
```

⚠️ **IMPORTANT:** Never commit your API Key to a public repository!

---

## 🛠️ Installation (Local)

### 1. Clone Repository

```bash
git clone https://github.com/leonardwgk/resepin.git
cd resepin
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Setup Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Database

Create a new MySQL database:

```sql
CREATE DATABASE resepin;
```

Edit the `.env` file and configure your database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=resepin
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 6. Configure API Keys

Edit the `.env` file and add your API keys:

```env
# Spoonacular API (for recipe search)
SPOONACULAR_API_KEY=your_spoonacular_api_key

# Python AI API URL (for ingredient detection from images)
PYTHON_API_URL=https://your-ai-service.azurewebsites.net/predict
```

### 7. Run Migrations

```bash
php artisan migrate
```

### 8. Build Frontend Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 9. Start Development Server

```bash
php artisan serve
```

Open your browser and navigate to: `http://localhost:8000`

---

## ⚙️ Environment Variables

Here's an explanation of the important environment variables:

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_NAME` | Application name | `resepin` |
| `APP_ENV` | Environment mode | `local` / `production` |
| `APP_DEBUG` | Debug mode | `true` / `false` |
| `APP_URL` | Application URL | `http://localhost:8000` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_DATABASE` | Database name | `resepin` |
| `DB_USERNAME` | Database username | `root` |
| `DB_PASSWORD` | Database password | `secret` |
| `SPOONACULAR_API_KEY` | Spoonacular API Key | `a1b2c3d4...` |
| `PYTHON_API_URL` | Python AI Service URL | `https://...` |

---

## 🚀 Deployment

### Docker Deployment

#### 1. Build Docker Image

```bash
docker build -t resepin:latest .
```

#### 2. Run Container

```bash
docker run -d \
  --name resepin \
  -p 8080:80 \
  -e APP_KEY=base64:your_app_key_here \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e DB_HOST=your_db_host \
  -e DB_DATABASE=resepin \
  -e DB_USERNAME=your_username \
  -e DB_PASSWORD=your_password \
  -e SPOONACULAR_API_KEY=your_api_key \
  -e PYTHON_API_URL=your_python_api_url \
  resepin:latest
```

#### 3. Using Docker Compose

Create a `docker-compose.yml` file:

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8080:80"
    environment:
      - APP_KEY=${APP_KEY}
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=db
      - DB_DATABASE=resepin
      - DB_USERNAME=resepin
      - DB_PASSWORD=secret
      - SPOONACULAR_API_KEY=${SPOONACULAR_API_KEY}
      - PYTHON_API_URL=${PYTHON_API_URL}
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: resepin
      MYSQL_USER: resepin
      MYSQL_PASSWORD: secret
      MYSQL_ROOT_PASSWORD: rootsecret
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

Run with:

```bash
docker-compose up -d
```

---

### Azure App Service Deployment

#### Azure Prerequisites
- Active Azure account
- Azure CLI installed
- Azure Container Registry (ACR) or Docker Hub

#### Step 1: Login to Azure CLI

```bash
az login
```

#### Step 2: Create Resource Group

```bash
az group create --name resepin-rg --location southeastasia
```

#### Step 3: Create Azure Container Registry (Optional)

```bash
az acr create --resource-group resepin-rg \
  --name resepinregistry --sku Basic

# Login to ACR
az acr login --name resepinregistry
```

#### Step 4: Build & Push Docker Image

```bash
# Tag image
docker tag resepin:latest resepinregistry.azurecr.io/resepin:latest

# Push to ACR
docker push resepinregistry.azurecr.io/resepin:latest
```

#### Step 5: Create Azure App Service Plan

```bash
az appservice plan create \
  --name resepin-plan \
  --resource-group resepin-rg \
  --sku B1 \
  --is-linux
```

#### Step 6: Create Web App

```bash
az webapp create \
  --resource-group resepin-rg \
  --plan resepin-plan \
  --name resepin \
  --deployment-container-image-name resepinregistry.azurecr.io/resepin:latest
```

#### Step 7: Configure Environment Variables

```bash
az webapp config appsettings set \
  --resource-group resepin-rg \
  --name resepin \
  --settings \
    APP_KEY="base64:your_generated_key" \
    APP_ENV="production" \
    APP_DEBUG="false" \
    APP_URL="https://resepin.azurewebsites.net" \
    DB_CONNECTION="mysql" \
    DB_HOST="your-mysql-server.mysql.database.azure.com" \
    DB_PORT="3306" \
    DB_DATABASE="resepin" \
    DB_USERNAME="admin@your-mysql-server" \
    DB_PASSWORD="your_password" \
    SPOONACULAR_API_KEY="your_spoonacular_api_key" \
    PYTHON_API_URL="https://your-ai-service.azurewebsites.net/analyze"
```

#### Step 8: Create Azure MySQL Database (Optional)

```bash
# Create MySQL Server
az mysql flexible-server create \
  --resource-group resepin-rg \
  --name resepin-mysql \
  --admin-user adminuser \
  --admin-password YourPassword123! \
  --sku-name Standard_B1ms \
  --tier Burstable

# Create database
az mysql flexible-server db create \
  --resource-group resepin-rg \
  --server-name resepin-mysql \
  --database-name resepin
```

#### Step 9: Enable Continuous Deployment (Optional)

```bash
az webapp deployment container config \
  --resource-group resepin-rg \
  --name resepin \
  --enable-cd true
```

#### Step 10: Access the Application

Open your browser and navigate to: `https://resepin.azurewebsites.net`

---

## 🎮 How to Use

1. **Register/Login** to access full features
2. **Upload Photo:** Click the camera icon and upload a photo of your ingredients
3. **Set Preferences:** Toggle "Halal Mode" or "No Spicy" if needed
4. **Get Recipes:** The AI will detect ingredients and suggest recipes
5. **Cook & Save:** Click on a recipe for details, or click "❤️" to save to Favorites

---

## 🛠️ Technical Details

### Tech Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | Laravel Blade, Bootstrap 5, Vite |
| **Backend** | Laravel 12, PHP 8.2 |
| **AI Engine** | YOLOv8 (Python) |
| **Database** | MySQL |
| **External API** | Spoonacular Food API |
| **Deployment** | Docker, Azure App Service |

### Architecture

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│                 │     │                 │     │                 │
│   Client/User   │────▶│  Laravel App    │────▶│  MySQL Database │
│                 │     │                 │     │                 │
└─────────────────┘     └────────┬────────┘     └─────────────────┘
                                 │
                    ┌────────────┼────────────┐
                    ▼                         ▼
           ┌─────────────────┐       ┌─────────────────┐
           │                 │       │                 │
           │  Python AI API  │       │ Spoonacular API │
           │   (YOLOv8)      │       │                 │
           └─────────────────┘       └─────────────────┘
```

---

## 🤝 Contributing

1. **Fork** this repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a **Pull Request**

---

## 👥 Team

Made with ❤️ by **Group 7 | RYSOLEI**

---

## 📞 Support

If you encounter any issues or have questions:
- Open an [Issue](https://github.com/leonardwgk/resepin/issues) on GitHub
- Contact the development team