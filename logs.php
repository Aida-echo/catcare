<?php
// public/logs.php
require_once __DIR__ . '/../functions.php';

$pdo = db();

/* ---------- Ambil filter ---------- */
$catId  = isset($_GET['cat_id']) ? trim($_GET['cat_id']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$params = [];
$where  = ["1=1"];

if ($catId !== '') {
  $where[] = "l.cat_id = ?";
  $params[] = (int)$catId;
}
if ($status !== '') {
  $where[] = "l.status = ?";
  $params[] = $status;
}

/* ---------- Query data logs (JOIN ke care_routines) ---------- */
$sql = "
  SELECT
    l.*,
    c.name AS cat_name,
    r.title AS routine_title,
    r.category
  FROM care_logs l
  LEFT JOIN cats c ON c.id = l.cat_id
  LEFT JOIN care_routines r ON r.id = l.routine_id
  WHERE " . implode(' AND ', $where) . "
  ORDER BY l.planned_at DESC
";
$st = $pdo->prepare($sql);
$st->execute($params);
$logs = $st->fetchAll(PDO::FETCH_ASSOC);

/* ---------- Dropdown kucing ---------- */
$cats = $pdo->query("SELECT id, name FROM cats ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

/* ---------- Templating ---------- */
$pageTitle = 'Logs – Cat Care Diary';
require __DIR__ . '/partials/header.php';
?>

<header class="hero">
  <div class="hero-inner">
    <span class="cat-logo" aria-hidden="true"></span>
    <div>
      <h1 class="hero-title">Cat Care Logs</h1>
      <p class="hero-sub">Riwayat semua aktivitas perawatan kucing 🐾</p>
    </div>
  </div>
</header>

<h2 class="paw-title">Filter Logs</h2>

<form method="get" class="grid section" style="align-items:end">
  <label>
    Pilih Kucing
    <select name="cat_id">
      <option value="">(Semua)</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= ($catId !== '' && (int)$catId === (int)$c['id']) ? 'selected' : '' ?>>
          <?= h($c['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <label>
    Status
    <select name="status">
      <?php foreach (['','Planned','Done','Skipped','Rescheduled'] as $s): ?>
        <option value="<?= h($s) ?>" <?= $status === $s ? 'selected' : '' ?>>
          <?= $s === '' ? '(Semua)' : $s ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <button type="submit">Terapkan</button>
</form>

<h2 class="paw-title">Daftar Logs</h2>

<?php if (!$logs): ?>
  <p>Belum ada log yang cocok dengan filter.</p>
<?php else: ?>
  <div class="table-scroll">
    <table role="grid">
      <thead>
        <tr>
          <th>Planned</th>
          <th>Kucing</th>
          <th>Tugas</th>
          <th>Kategori</th>
          <th>Status</th>
          <th>Qty</th>
          <th>Catatan</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td><?= h((new DateTime($l['planned_at']))->format('Y-m-d H:i')) ?></td>
          <td><?= h($l['cat_name']) ?></td>
          <td><?= h($l['routine_title'] ?? 'Ad-hoc') ?></td>
          <td>
            <span class="pill pill--<?= h($l['category'] ?? 'Other') ?>">
              <?= h($l['category'] ?? '-') ?>
            </span>
          </td>
          <td><?= h($l['status']) ?></td>
          <td><?= h($l['quantity']) ?></td>
          <td><?= h($l['notes']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
