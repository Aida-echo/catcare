<?php
/**
 * Cat Care Diary – functions.php (v2)
 * Berisi logika utama:
 *  - generate_today_logs(): membuat care_logs untuk hari ini dari care_routines aktif
 *  - fetch_today_tasks(): mengambil daftar tugas (care_logs) untuk hari ini
 */

require_once __DIR__ . '/db.php';

/**
 * Generate care_logs untuk HARI INI berdasarkan care_routines yang aktif.
 * Dipanggil di Dashboard (index.php) tiap halaman dimuat (demo).
 * Di produksi, sebaiknya pakai cron/scheduler harian.
 */
function generate_today_logs(): void {
  $pdo = db();

  $today    = (new DateTime('today'))->format('Y-m-d');
  $tomorrow = (new DateTime('tomorrow'))->format('Y-m-d');

  // Ambil semua routine aktif
  $routines = $pdo->query("SELECT * FROM care_routines WHERE is_active = 1")->fetchAll();

  foreach ($routines as $r) {
    $shouldCreate = false;

    // Tentukan apakah hari ini perlu dibuat log untuk routine ini
    switch ($r['frequency']) {
      case 'Daily':
        $shouldCreate = true;
        break;
      case 'Weekly':
        // buat di weekday yang sama dengan tanggal dibuatnya routine
        $created = new DateTime($r['created_at']);
        $shouldCreate = ((new DateTime($today))->format('w') === $created->format('w'));
        break;
      case 'Monthly':
        // buat di tanggal (day-of-month) yang sama
        $created = new DateTime($r['created_at']);
        $shouldCreate = ((new DateTime($today))->format('j') === $created->format('j'));
        break;
      case 'Custom':
      default:
        // interval_days: buat jika jarak hari dari planned terakhir >= interval
        $last = $pdo->prepare("SELECT planned_at FROM care_logs WHERE routine_id=? ORDER BY planned_at DESC LIMIT 1");
        $last->execute([$r['id']]);
        $lastDate = $last->fetchColumn();

        if (!$lastDate) {
          $shouldCreate = true;
        } else {
          $diffDays = (new DateTime($today))->diff(new DateTime(substr($lastDate, 0, 10)))->days;
          $interval = max(1, (int)$r['interval_days']);
          $shouldCreate = ($diffDays >= $interval);
        }
        break;
    }

    if (!$shouldCreate) continue;

    // Cegah duplikasi planned di hari yang sama
    $exists = $pdo->prepare(
      "SELECT COUNT(*) FROM care_logs
       WHERE routine_id = ?
         AND planned_at >= ?
         AND planned_at < ?"
    );
    $exists->execute([$r['id'], $today . ' 00:00:00', $tomorrow . ' 00:00:00']);
    if ($exists->fetchColumn() > 0) continue;

    // Tentukan waktu planned
    $plannedTime = $r['preferred_time'] ?: '09:00:00';
    $plannedAt   = $today . ' ' . $plannedTime;

    // Insert log baru
    $ins = $pdo->prepare("INSERT INTO care_logs (cat_id, routine_id, planned_at, status) VALUES (?, ?, ?, 'Planned')");
    $ins->execute([$r['cat_id'], $r['id'], $plannedAt]);
  }
}

/**
 * Ambil semua tugas untuk HARI INI (ditampilkan di Dashboard).
 *
 * @return array<int, array<string, mixed>>
 */
function fetch_today_tasks(): array {
  $pdo = db();

  $today    = (new DateTime('today'))->format('Y-m-d');
  $tomorrow = (new DateTime('tomorrow'))->format('Y-m-d');

  $sql = "SELECT
            l.*,
            c.name  AS cat_name,
            r.title AS routine_title,
            r.category
          FROM care_logs l
          JOIN cats c ON c.id = l.cat_id
          LEFT JOIN care_routines r ON r.id = l.routine_id
          WHERE l.planned_at >= ? AND l.planned_at < ?
          ORDER BY l.planned_at";

  $st = $pdo->prepare($sql);
  $st->execute([$today . ' 00:00:00', $tomorrow . ' 00:00:00']);

  return $st->fetchAll();
}
