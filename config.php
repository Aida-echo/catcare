<?php
/**
 * Cat Care Diary – config.php (v2)
 * Menyimpan pengaturan dasar aplikasi.
 */

/* =========================
   1) Timezone & Debug Mode
   ========================= */
date_default_timezone_set('Asia/Jakarta'); // WIB

define('APP_DEBUG', true); // ubah ke false saat produksi

if (APP_DEBUG) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', 0);
  error_reporting(0);
}

/* =========================
   2) Database Credentials
   ========================= */
// Default XAMPP: user root tanpa password
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'cat_care_diary');
define('DB_USER', 'root');
define('DB_PASS', ''); // kosong di XAMPP default

/* =========================
   3) Base URL
   =========================
   Sesuaikan dengan folder public.
   Jika file ada di C:/xampp/htdocs/cat-care-diary/public,
   maka BASE_URL = '/cat-care-diary/public'
*/
define('BASE_URL', '/cat-care-diary/public');

/* =========================
   4) Root Path (opsional)
   ========================= */
define('ROOT_PATH', __DIR__); // folder root project
