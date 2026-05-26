============================================
  Lost & Found Campus Hub
  File Structure Guide
============================================

FOLDER STRUCTURE:
-----------------
lost-and-found/
│
├── database.sql          ← Run this FIRST in phpMyAdmin to create all tables
├── db.php                ← MySQL connection settings (edit username/password here)
│
├── pages/                ← All your website pages
│   ├── index.php         ← Home / main feed page
│   ├── post.php          ← Post a new lost/found item
│   ├── archive.php       ← Calendar archive (dark theme, like Instagram)
│   ├── messages.php      ← DM / messages list
│   ├── profile.php       ← User profile page
│   ├── settings.php      ← Settings page
│   ├── login.php         ← Login page
│   └── register.php      ← Register page
│
├── includes/             ← Reusable parts
│   ├── header.php        ← Top header (included in every page)
│   └── footer.php        ← Bottom nav (included in every page)
│
├── css/
│   └── style.css         ← All styling for every page
│
├── js/
│   └── main.js           ← Shared JavaScript helpers
│
├── api/                  ← Backend logic (form handlers)
│   ├── claims.php        ← Handles claim form submissions
│   ├── update_status.php ← Updates item status (found/returned)
│   └── logout.php        ← Logs user out
│
└── uploads/              ← Item images get saved here automatically
    (empty folder)


HOW TO SET UP:
--------------
1. Install XAMPP (free) from https://www.apachefriends.org
2. Copy this entire folder into: C:/xampp/htdocs/
3. Start Apache + MySQL in XAMPP Control Panel
4. Open phpMyAdmin: http://localhost/phpmyadmin
5. Click "Import" → choose database.sql → click Go
6. Open db.php and set your MySQL username/password
7. Open browser: http://localhost/lost-and-found/pages/login.php
8. Register an account and start using the site!

NOTE: The uploads/ folder needs write permission.
In XAMPP this works automatically.
============================================
