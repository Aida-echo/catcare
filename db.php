<?php
/**
 * Cat Care Diary – db.php (v2)
 * Menyediakan koneksi PDO + helper aman untuk HTML.
 */

require_once __DIR__ . '/config.php';

/**
 * Ambil koneksi PDO (singleton).
 *
 * @return PDO
 */
function db(): PDO {
  static $pdo = null;

  if ($pdo !== null) {
    return $pdo;
  }

  $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
  $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];

  try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
  } catch (PDOException $e) {
    if (APP_DEBUG) {
      die("Database connection failed: " . $e->getMessage());
    } else {
      die("Database error.");
    }
  }

  return $pdo;
}

/**
 * Escape HTML (XSS-safe).
 *
 * @param string|null $str
 * @return string
 */
function h(?string $str): string {
  return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
