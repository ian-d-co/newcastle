#!/bin/bash

# Dicksord Fest 2026 - Newcastle - Basic Structure Test

echo "=== Testing Application Structure ==="
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} $1"
        return 0
    else
        echo -e "${RED}✗${NC} $1 - MISSING"
        return 1
    fi
}

check_dir() {
    if [ -d "$1" ]; then
        echo -e "${GREEN}✓${NC} $1/"
        return 0
    else
        echo -e "${RED}✗${NC} $1/ - MISSING"
        return 1
    fi
}

echo "Checking directory structure..."
check_dir "public"
check_dir "public/css"
check_dir "public/js"
check_dir "public/api"
check_dir "app"
check_dir "app/config"
check_dir "app/models"
check_dir "app/controllers"
check_dir "app/middleware"
check_dir "app/views"
check_dir "app/views/layouts"
check_dir "app/views/public"
check_dir "app/views/admin"
check_dir "app/views/auth"
check_dir "database"

echo ""
echo "Checking core files..."
check_file "public/index.php"
check_file "public/.htaccess"
check_file "app/config/config.php"
check_file "app/config/.env.example"
check_file "database/schema.sql"
check_file "README.md"
check_file ".gitignore"

echo ""
echo "Checking CSS files..."
check_file "public/css/styles.css"

echo ""
echo "Checking JavaScript files..."
check_file "public/js/app.js"
check_file "public/js/modals.js"
check_file "public/js/forms.js"
check_file "public/js/charts.js"

echo ""
echo "Checking model files..."
check_file "app/models/User.php"
check_file "app/models/Event.php"
check_file "app/models/Activity.php"
check_file "app/models/Meal.php"
check_file "app/models/Poll.php"
check_file "app/models/CarShare.php"
check_file "app/models/Hosting.php"
check_file "app/models/Hotel.php"

echo ""
echo "Checking middleware files..."
check_file "app/middleware/Auth.php"
check_file "app/middleware/AdminAuth.php"
check_file "app/middleware/CSRF.php"

echo ""
echo "Checking controller files..."
check_file "app/controllers/AuthController.php"

echo ""
echo "Checking view files..."
check_file "app/views/layouts/main.php"
check_file "app/views/auth/login.php"
check_file "app/views/public/home.php"
check_file "app/views/public/activities.php"
check_file "app/views/public/meals.php"
check_file "app/views/public/carshare.php"
check_file "app/views/public/hosting.php"
check_file "app/views/public/polls.php"
check_file "app/views/public/hotels.php"
check_file "app/views/public/dashboard.php"
check_file "app/views/admin/dashboard.php"

echo ""
echo "Checking API files..."
check_file "public/api/attendance.php"
check_file "public/api/activity-book.php"
check_file "public/api/activity-cancel.php"
check_file "public/api/meal-book.php"
check_file "public/api/meal-cancel.php"
check_file "public/api/carshare-book.php"
check_file "public/api/carshare-cancel.php"
check_file "public/api/hosting-book.php"
check_file "public/api/hosting-cancel.php"
check_file "public/api/hotel-reserve.php"
check_file "public/api/hotel-cancel.php"
check_file "public/api/poll-vote.php"
check_file "public/api/poll-results.php"
check_file "public/api/poll-check-expired.php"

echo ""
echo "=== Structure Test Complete ==="
echo ""
echo "Next steps:"
echo "1. Create .env file from app/config/.env.example"
echo "2. Import database/schema.sql into MySQL"
echo "3. Configure web server to point to public/ directory"
echo "4. Access the application via browser"
echo ""
