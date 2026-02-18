# Dicksord Fest 2026 - Newcastle Event Management

A mobile-first, responsive web application for managing the Dicksord Fest 2026 event in Newcastle (November 20-22, 2026).

## Features

- **Mobile-First Design**: Optimized for smartphones with touch-friendly UI (min 44px × 44px buttons)
- **Attendance Registration**: Users can register attendance with conditional carshare and hosting offers
- **Activities & Meals**: Book activities and meals with capacity management and payment tracking
- **Carshare**: Offer and book carshare rides
- **Hosting**: Offer and book accommodation
- **Real-Time Polls**: Vote on polls with live bar chart results
- **Hotels**: Reserve hotel rooms with payment tracking
- **User Dashboard**: Personal summary of all bookings and registrations
- **Admin Panel**: Manage events, activities, meals, polls, and users
- **Secure Authentication**: PIN-based login with bcrypt hashing
- **CSRF Protection**: All forms protected against CSRF attacks

## Tech Stack

- **Backend**: PHP (procedural with OOP)
- **Frontend**: HTML5, CSS3 (Mobile-first), Vanilla JavaScript
- **Database**: MySQL/MariaDB
- **Authentication**: Session-based with bcrypt
- **Color Scheme**: 
  - Primary: #613704 (Dark Brown)
  - Secondary: #D46300 (Orange)
  - Accent: #FDDC62 (Gold)
  - Light: #FDE5B7 (Light Cream)

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB 5.7 or higher
- Apache/Nginx web server
- Composer (optional, no dependencies currently)

### Local Development Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/ian-d-co/newcastle.git
   cd newcastle
   ```

2. **Create environment file**:
   ```bash
   cp app/config/.env.example app/config/.env
   ```

3. **Configure database** (edit `app/config/.env`):
   ```
   DB_HOST=localhost
   DB_NAME=u983097270_newc
   DB_USER=your_username
   DB_PASS=your_password
   ```

4. **Import database schema**:
   ```bash
   mysql -u your_username -p your_database_name < database/schema.sql
   ```

5. **Configure web server**:
   
   Point your document root to the `public/` directory.
   
   **Apache** (.htaccess already configured):
   ```apache
   DocumentRoot /path/to/newcastle/public
   ```
   
   **Nginx**:
   ```nginx
   server {
       listen 80;
       server_name your-domain.com;
       root /path/to/newcastle/public;
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

6. **Set permissions**:
   ```bash
   chmod -R 755 public/
   chmod -R 755 app/
   ```

7. **Access the application**:
   Navigate to `http://localhost` or your configured domain.

### Default Admin Account

- **Discord Name**: Admin
- **PIN**: 123456

**Important**: Change this PIN immediately after first login!

## Deployment to Hostinger

1. **Upload files**:
   - Upload all files to your hosting directory (typically `public_html/`)
   - Ensure the `public/` folder is your web root

2. **Create database**:
   - Create a MySQL database in Hostinger control panel
   - Note the database name, username, and password

3. **Import schema**:
   - Use phpMyAdmin or MySQL command line to import `database/schema.sql`

4. **Configure environment**:
   - Create `.env` file from `app/config/.env.example`
   - Copy it to `app/config/.env`
   - Update database credentials

5. **Set file permissions**:
   ```bash
   chmod 755 public/
   chmod 755 app/
   ```

6. **Configure .htaccess** (if using subdirectory):
   ```apache
   RewriteBase /your-subdirectory/
   ```

## Project Structure

```
newcastle/
├── public/                 # Web root
│   ├── index.php          # Main entry point & router
│   ├── css/
│   │   └── styles.css     # Mobile-first CSS
│   ├── js/
│   │   ├── app.js         # Main JavaScript
│   │   ├── forms.js       # Form handling & conditional logic
│   │   ├── charts.js      # Poll charts & real-time updates
│   │   └── modals.js      # Modal management
│   ├── images/
│   └── api/               # AJAX API endpoints
│       ├── attendance.php
│       ├── activity-book.php
│       ├── meal-book.php
│       ├── carshare-book.php
│       ├── hosting-book.php
│       ├── hotel-reserve.php
│       └── poll-vote.php
├── app/
│   ├── config/
│   │   ├── .env.example   # Environment template
│   │   └── config.php     # Configuration & database connection
│   ├── models/            # Database models
│   │   ├── User.php
│   │   ├── Event.php
│   │   ├── Activity.php
│   │   ├── Meal.php
│   │   ├── Poll.php
│   │   ├── CarShare.php
│   │   ├── Hosting.php
│   │   └── Hotel.php
│   ├── controllers/
│   │   └── AuthController.php
│   ├── middleware/        # Authentication & security
│   │   ├── Auth.php
│   │   ├── AdminAuth.php
│   │   └── CSRF.php
│   └── views/
│       ├── layouts/
│       │   └── main.php
│       ├── public/
│       │   ├── home.php
│       │   ├── activities.php
│       │   ├── meals.php
│       │   ├── carshare.php
│       │   ├── hosting.php
│       │   ├── polls.php
│       │   ├── hotels.php
│       │   └── dashboard.php
│       └── auth/
│           └── login.php
├── database/
│   └── schema.sql         # Database schema
└── README.md
```

## Usage

### For Attendees

1. **Register Attendance**:
   - Visit home page
   - Click "I am attending!"
   - Fill in Discord name, name, create PIN
   - Select days and travel method
   - Optionally offer carshare or hosting

2. **Book Activities & Meals**:
   - Browse activities and meals
   - Click "Book" button
   - View payment status if prepayment required

3. **Vote on Polls**:
   - View active polls
   - Select options and vote
   - See real-time results

4. **View Dashboard**:
   - See all your bookings
   - Check payment status
   - View attendance details

### For Admins

1. **Login as Admin**:
   - Use admin credentials
   - Access Admin panel

2. **Manage Content**:
   - Create/edit activities and meals
   - Create polls with expiry dates
   - Add hotels and room types
   - Mark payments as received
   - View all attendees and bookings

## Security Features

- PIN-based authentication with bcrypt hashing
- CSRF token protection on all forms
- SQL injection prevention (prepared statements)
- XSS protection (output escaping)
- Session-based authentication
- HTTPOnly and SameSite cookies

## Mobile Optimization

- Viewport optimized for 320px+ screens
- Touch-friendly buttons (44px × 44px minimum)
- Full-width forms on mobile
- No horizontal scrolling
- Large, readable text (16px minimum on inputs)
- Swipe-friendly navigation
- Mobile-optimized modals
- Fast loading (no heavy frameworks)

## Browser Support

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (iOS 12+)
- Chrome Mobile (Android)

## License

Proprietary - Dicksord Fest 2026

## Support

For issues or questions, please contact the development team.

---

**Built with ❤️ for Dicksord Fest 2026 - Newcastle**