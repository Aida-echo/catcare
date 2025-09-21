<?php
require_once __DIR__ . '/../db.php';
$pageTitle = 'Logs – Cat Care Diary';
$pdo = db();

/* UPDATE status log */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $id      = (int)$_POST['id'];
  $status  = $_POST['status'];
  $qty     = $_POST['quantity'] ?: null;
  $notes   = $_POST['notes'] ?: null;
  $actual  = ($status === 'Done') ? (new DateTime())->format('Y-m-d H:i:s') : null;

  $pdo->prepare("UPDATE care_logs SET status=?, quantity=?, notes=?, actual_at=? WHERE id=?")
      ->execute([$status, $qty, $notes, $actual, $id]);

  header('Location: ' . BASE_URL . '/logs.php'); exit;
}

/* RANGE default 7 hari */
$from = (new DateTime('-7 days'))->format('Y-m-d 00:00:00');
$to   = (new DateTime('+1 day'))->format('Y-m-d 00:00:00');
$params = [$from, $to];
$where  = ["l.planned_at >= ? AND l.planned_at < ?"];

/* Filter opsional */
if (!empty($_GET['q']))      { $where[] = "c.name LIKE ?"; $params[] = '%' . $_GET['q'] . '%'; }
if (!empty($_GET['status'])) { $where[] = "l.status = ?";  $params[] = $_GET['status']; }
if (!empty($_GET['dari']))   { $params[0] = $_GET['dari'] . ' 00:00:00'; }
if (!empty($_GET['sampai'])) { $params[1] = (new DateTime($_GET['sampai'].' +1 day'))->format('Y-m-d 00:00:00'); }

$sql = "SELECT l.*, c.name AS cat_name, r.title AS routine_title
        FROM care_logs l
        JOIN cats c ON c.id = l.cat_id
        LEFT JOIN care_routines r ON r.id = l.routine_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY l.planned_at DESC";

$st = $pdo->prepare($sql);
$st->execute($params);
$logs = $st->fetchAll();

require __DIR__ . '/partials/header.php';
?>
<header class="hero">
  <div class="hero-inner">
    <span class="cat-logo"></span>
    <div>
      <h1 class="hero-title">Logs</h1>
      <p class="hero-sub">Riwayat 7 hari terakhir — update status cepat.</p>
    </div>
  </div>
</header>

<form method="get" class="grid section" style="align-items:end">
  <label>Nama Kucing
    <input type="text" name="q" value="<?= h($_GET['q'] ?? '') ?>" placeholder="Cari nama kucing">
  </label>
  <label>Status
    <select name="status">
      <?php $opts=['','Planned','Done','Skipped','Rescheduled']; $cur=$_GET['status']??''; ?>
      <?php foreach ($opts as $o): ?>
        <option value="<?= h($o) ?>" <?= $cur===$o ? 'selected' : '' ?>><?= $o===''?'(semua)':$o ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Dari
    <input type="date" name="dari" value="<?= h($_GET['dari'] ?? '') ?>">
  </label>
  <label>Sampai
    <input type="date" name="sampai" value="<?= h($_GET['sampai'] ?? '') ?>">
  </label>
  <button type="submit">Filter</button>
</form>

<table role="grid">
  <thead>
    <tr><th>Planned</th><th>Kucing</th><th>Tugas</th><th>Status</th><th>Qty</th><th>Catatan</th><th>Aksi</th></tr>
  </thead>
  <tbody>
    <?php foreach ($logs as $l): ?>
    <tr>
      <td><?= h((new DateTime($l['planned_at']))->format('Y-m-d H:i')) ?></td>
      <td><?= h($l['cat_name']) ?></td>
      <td><?= h($l['routine_title'] ?? 'Ad-hoc') ?></td>
      <td><?= h($l['status']) ?></td>
      <td><?= h($l['quantity']) ?></td>
      <td><?= h($l['notes']) ?></td>
      <td>
        <form method="post" class="row-form">
          <input type="hidden" name="update" value="1">
          <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
          <select name="status">
            <?php foreach (['Planned','Done','Skipped','Rescheduled'] as $s): ?>
              <option value="<?= $s ?>" <?= $l['status']===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
          <input name="quantity" placeholder="qty" value="<?= h($l['quantity']) ?>">
          <input name="notes" placeholder="catatan" value="<?= h($l['notes']) ?>">
          <button type="submit">Simpan</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!count($logs)): ?>
      <tr><td colspan="7">Belum ada log pada rentang ini 🐾</td></tr>
    <?php endif; ?>
  </tbody>
</table>
<?php require __DIR__ . '/partials/footer.php'; ?>