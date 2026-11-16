<?php
session_start();
$sudahLogin = isset($_SESSION["user_id"]);
$role       = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Narahika Preloved Store</title>
</head>
<body>

  <header>
    <nav>
      <strong>Narahika</strong> |
      <?php if ($adalahPenjual): ?>
        <a href="pages/produk/posting_barang.php">Jual Barang</a> |
      <?php elseif ($sudahLogin): ?>
        <a href="pages/seller/jadi_penjual.php">Jadi Penjual</a> |
      <?php endif; ?>
      <?php if ($sudahLogin): ?>
        <a href="pages/profil/profil_<?= $adalahPenjual ? "penjual" : "pembeli" ?>.php">Profil</a> |
        <a href="pages/profil/riwayat.php">Riwayat</a> |
        Halo, <?= htmlspecialchars($_SESSION["nama"]) ?>
        <small>(<?= $adalahPenjual ? "Penjual" : "Pembeli" ?>)</small>
        <a href="../backend/auth/logout.php"><button type="button">Keluar</button></a>
      <?php else: ?>
        <a href="pages/auth/form_masuk.php"><button type="button">Masuk</button></a>
        <a href="pages/auth/form_daftar.php"><button type="button">Daftar</button></a>
      <?php endif; ?>
    </nav>
  </header>

  <hr>

  <main>
    <h2>Selamat Datang di Dashboard</h2>
    <p>Ini adalah halaman utama Narahika Preloved Store.</p>

    <p>
      <?php if ($adalahPenjual): ?>
        <a href="pages/produk/posting_barang.php">
          <button type="button">+ Posting Barang</button>
        </a>
      <?php elseif ($sudahLogin): ?>
        <a href="pages/seller/jadi_penjual.php">
          <button type="button">Buka Toko (Jadi Penjual)</button>
        </a>
      <?php else: ?>
        <a href="pages/auth/form_masuk.php">
          <button type="button">Masuk</button>
        </a>
        <a href="pages/auth/form_daftar.php">
          <button type="button">Daftar</button>
        </a>
      <?php endif; ?>
    </p>
  </main>

  <hr>

  <footer>
    <p>&copy; 2026 Narahika Preloved Store</p>
  </footer>

</body>
</html>