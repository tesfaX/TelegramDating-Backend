## Project Setup Instructions

### Prerequisites

1. **Server Requirements**:
   - Ensure you have access to a web server or hosting environment with PHP installed.
   - Make sure your server meets the Laravel framework's [server requirements](https://laravel.com/docs/10.x/deployment#server-requirements).

2. **Composer**:
   - Install Composer, a PHP package manager, if you haven't already. You can download it from [Composer's official website](https://getcomposer.org/download/).

3. **Git**:
   - Install Git for version control. You can download it from [Git's official website](https://git-scm.com/downloads).

### Step 1: Clone the Repository

1. Open your terminal or command prompt.

2. Navigate to the directory where you want to store the project.

3. Clone the GitHub repository:
   ```bash
   git clone https://github.com/tesfaX/TelegramDating-Backend.git
### Step 2: Install Dependencies

1. **Navigate to the project directory:**

   ```bash
   cd TelegramDating-Backend
2. **Install PHP dependencies using Composer:**

   ```bash
   composer install
### Step 3: Configure Environment Variables

1. Create a copy of the `.env.example` file and name it `.env`:

   ```bash
   cp .env.example .env
2. Open the .env file using a text editor and update the following environment variables

    ```env
    TELEGRAM_BOT_TOKEN=your-telegram-bot-token
    TELEGRAM_MINI_APP_URL=your-mini-app-web-url
    PAYMENT_PROVIDER_TOKEN=your-payment-provider-token
    
    DB_DATABASE=your-database-name
    DB_USERNAME=your-database-username
    DB_PASSWORD=your-database-password
    ```
### Step 4: Generate Application Key
Generate a unique application key by running the following command:
```bash
php artisan key:generate
```

### Step 6: Migrate and Seed the Database
Run the following command to create and populate the database tables:
```bash
php artisan migrate 
```
