<?php
require_once __DIR__ . '/../functions.php';

// Handle update status log (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_log'])) {
  $pdo      = db();
  $id       = (int)$_POST['id'];
  $status   = $_POST['status'];
  $quantity = $_POST['quantity'] ?? null;
  $notes    = $_POST['notes'] ?? null;
  $actual   = ($status === 'Done') ? (new DateTime())->format('Y-m-d H:i:s') : null;

  $st = $pdo->prepare("UPDATE care_logs SET status=?, quantity=?, notes=?, actual_at=? WHERE id=?");
  $st->execute([$status, $quantity, $notes, $actual, $id]);

  header('Location: ' . BASE_URL . '/index.php');
  exit;
}

// Generate tugas untuk hari ini (demo; di produksi pakai cron/scheduler)
generate_today_logs();
$tasks = fetch_today_tasks();

$pageTitle = 'Dashboard – Cat Care Diary';
require __DIR__ . '/partials/header.php';
?>
<header class="hero">
  <div class="hero-inner">
    <span class="cat-logo" aria-hidden="true"></span>
    <div>
      <h1 class="hero-title">Cat Care Diary</h1>
      <p class="hero-sub">Checklist harian perawatan kucing 🐾</p>
    </div>
  </div>
</header>

<h2 class="paw-title">Tugas Hari Ini</h2>

<?php if (!$tasks): ?>
  <p>Belum ada tugas untuk hari ini. Tambahkan routine dulu ya di halaman <a href="<?= h(BASE_URL) ?>/routines.php">Routines</a>.</p>
<?php else: ?>
  <table role="grid">
    <thead>
      <tr>
        <th>Waktu</th>
        <th>Kucing</th>
        <th>Tugas</th>
        <th>Kategori</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tasks as $t): ?>
        <tr>
          <td><?= h((new DateTime($t['planned_at']))->format('H:i')) ?></td>
          <td><?= h($t['cat_name']) ?></td>
          <td><?= h($t['routine_title'] ?? 'Ad-hoc') ?></td>
          <td><span class="pill"><?= h($t['category'] ?? '-') ?></span></td>
          <td><?= h($t['status']) ?></td>
          <td>
            <form method="post" action="<?= h(BASE_URL) ?>/index.php" class="row-form">
              <input type="hidden" name="update_log" value="1">
              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <select name="status">
                <?php foreach (['Planned','Done','Skipped','Rescheduled'] as $s): ?>
                  <option value="<?= $s ?>" <?= $t['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
              <input name="quantity" placeholder="qty" value="<?= h($t['quantity']) ?>">
              <input name="notes" placeholder="catatan" value="<?= h($t['notes']) ?>">
              <button type="submit">Simpan</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
