# 🍳 Resepin

A smart, AI-powered recipe finder application built with Laravel and YOLOv8 that transforms your fridge ingredients into delicious meals instantly.

## ✨ Features

### 🎯 Core Functionality
* **AI Ingredient Detection:** Powered by **YOLOv8**, automatically identifies ingredients from uploaded photos in seconds.
* **Smart Recipe Search:** Integrates with **Spoonacular API** to find the best recipes matching your ingredients.
* **Intelligent Filters:** Built-in dietary preferences for **Vegan**, **Vegetarian**, **Gluten-Free**, and **Halal**.
* **Spiciness Control:** Automatically filter out spicy recipes based on user preference.

### 🎨 User Experience
* **Scan History:** Automatically saves your scan results so you can revisit them anytime.
* **Favorites System:** Save your loved recipes to a personal cookbook.
* **Secure Authentication:** Complete Login & Register system powered by Laravel Breeze.
* **Responsive Design:** Optimized for both desktop and mobile experiences using Tailwind CSS.

---

## 🚀 Getting Started

### Prerequisites
* **PHP 8.1+** & **Composer**
* **Node.js 16.x** or later
* **MySQL** (XAMPP/Laragon)
* **Python 3.x** (Optional, only for local AI development)

### Installation

1.  **Clone the repository**
    ```bash
    git clone [https://github.com/leonardwgk/resepin.git](https://github.com/leonardwgk/resepin.git)
    cd resepin
    ```

2.  **Install dependencies**
    ```bash
    composer install
    npm install
    ```

3.  **Configure Environment**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Open `.env` and set your Database & API Keys:*
    * `DB_DATABASE=resepin`
    * `SPOONACULAR_API_KEY=your_key`
    * `PYTHON_API_URL=https://your-azure-app.azurewebsites.net/analyze`

4.  **Run Migrations & Build**
    ```bash
    php artisan migrate
    npm run build
    ```

5.  **Start the Server**
    ```bash
    php artisan serve
    ```
    Navigate to `http://localhost:8000`

---

## 🎮 How to Use

1.  **Register/Login** to access full features.
2.  **Upload Photo:** Click the Camera icon and upload a photo of your ingredients (e.g., Chicken & Tomato).
3.  **Set Preferences:** Toggle "Halal Mode" or "No Spicy" if needed.
4.  **Get Recipes:** The AI will detect the ingredients and suggest recipes.
5.  **Cook & Save:** Click on a recipe to see details, or click "❤️" to save it to Favorites.

---

## 🛠️ Technical Details

### Tech Stack
* **Frontend:** Laravel Blade, Tailwind CSS
* **Backend:** Laravel 11 (PHP)
* **AI Engine:** YOLOv8 (Python/Flask)
* **Database:** MySQL
* **External API:** Spoonacular

### Deployment
* **Web App:** Ready for Shared Hosting, VPS, or Railway.
* **AI Model:** Deployed on **Microsoft Azure Web App**.

---

## 🤝 Contributing

1.  **Fork** the repository
2.  Create your feature branch (`git checkout -b feature/amazing-feature`)
3.  Commit your changes (`git commit -m 'Add amazing feature'`)
4.  Push to the branch (`git push origin feature/amazing-feature`)
5.  Open a **Pull Request**

---

Made with ❤️ and 🌶️ by **Resepin Team**
*Transform any ingredient into a 5-star meal!* 🍳✨