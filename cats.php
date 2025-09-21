<?php
require_once __DIR__ . '/../db.php';
$pageTitle = 'Cats – Cat Care Diary';
$pdo = db();

/* CREATE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
  $st = $pdo->prepare("INSERT INTO cats(name,birth_date,breed,sex,color,allergies) VALUES(?,?,?,?,?,?)");
  $st->execute([
    $_POST['name'],
    $_POST['birth_date'] ?: null,
    $_POST['breed'] ?: null,
    $_POST['sex'] ?? 'Unknown',
    $_POST['color'] ?: null,
    $_POST['allergies'] ?: null
  ]);
  header('Location: ' . BASE_URL . '/cats.php'); exit;
}

/* UPDATE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $st = $pdo->prepare("UPDATE cats SET name=?, birth_date=?, breed=?, sex=?, color=?, allergies=? WHERE id=?");
  $st->execute([
    $_POST['name'],
    $_POST['birth_date'] ?: null,
    $_POST['breed'] ?: null,
    $_POST['sex'] ?? 'Unknown',
    $_POST['color'] ?: null,
    $_POST['allergies'] ?: null,
    (int)$_POST['id']
  ]);
  header('Location: ' . BASE_URL . '/cats.php'); exit;
}

/* DELETE */
if (isset($_GET['delete'])) {
  $pdo->prepare("DELETE FROM cats WHERE id=?")->execute([(int)$_GET['delete']]);
  header('Location: ' . BASE_URL . '/cats.php'); exit;
}

/* READ */
$cats = $pdo->query("SELECT * FROM cats ORDER BY created_at DESC")->fetchAll();

require __DIR__ . '/partials/header.php';
?>
<header class="hero">
  <div class="hero-inner">
    <span class="cat-logo"></span>
    <div>
      <h1 class="hero-title">Kucing</h1>
      <p class="hero-sub">Profil: nama, ras, warna, alergi.</p>
    </div>
  </div>
</header>

<h3 class="paw-title">Tambah Kucing</h3>
<form method="post" class="section">
  <input type="hidden" name="create" value="1">
  <div class="grid">
    <input name="name" placeholder="Nama" required>
    <input type="date" name="birth_date" placeholder="Tanggal lahir">
    <input name="breed" placeholder="Ras">
  </div>
  <div class="grid">
    <select name="sex">
      <?php foreach (['Unknown','Male','Female'] as $s): ?>
        <option value="<?= $s ?>"><?= $s ?></option>
      <?php endforeach; ?>
    </select>
    <input name="color" placeholder="Warna">
    <input name="allergies" placeholder="Alergi (opsional)">
  </div>
  <button type="submit">Simpan</button>
</form>

<h3 class="paw-title">Daftar Kucing</h3>
<table role="grid">
  <thead><tr><th>Nama</th><th>Ras</th><th>Sex</th><th>Warna</th><th>Aksi</th></tr></thead>
  <tbody>
    <?php foreach ($cats as $c): ?>
    <tr>
      <td><?= h($c['name']) ?></td>
      <td><?= h($c['breed']) ?></td>
      <td><?= h($c['sex']) ?></td>
      <td><?= h($c['color']) ?></td>
      <td>
        <details>
          <summary>Edit</summary>
          <form method="post" class="section">
            <input type="hidden" name="update" value="1">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <div class="grid">
              <input name="name" value="<?= h($c['name']) ?>" required>
              <input type="date" name="birth_date" value="<?= h($c['birth_date']) ?>">
              <input name="breed" value="<?= h($c['breed']) ?>">
            </div>
            <div class="grid">
              <select name="sex">
                <?php foreach (['Unknown','Male','Female'] as $s): ?>
                  <option value="<?= $s ?>" <?= $c['sex']===$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
              <input name="color" value="<?= h($c['color']) ?>">
              <input name="allergies" value="<?= h($c['allergies']) ?>">
            </div>
            <button type="submit">Update</button>
            <a class="contrast" href="?delete=<?= (int)$c['id'] ?>" onclick="return confirm('Hapus kucing ini?')">Hapus</a>
          </form>
        </details>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!count($cats)): ?>
      <tr><td colspan="5">Belum ada data kucing 🐾</td></tr>
    <?php endif; ?>
  </tbody>
</table>
<?php require __DIR__ . '/partials/footer.php'; ?>
