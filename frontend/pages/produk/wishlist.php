<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

require_once __DIR__ . "/../../data/produk.php";
require_once __DIR__ . "/../../data/keranjang.php";

$semuaProduk = daftarProduk();

// --- Aksi wishlist ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $aksi = $_POST["aksi"] ?? "";
    $id   = (int) ($_POST["id"] ?? 0);

    if ($aksi === "wishlist") {
        toggleWishlist($id);
        header("Location: wishlist.php");
        exit;
    }

    if ($aksi === "cart") {
        keranjangTambah($id, 1);
        header("Location: ../keranjang/keranjang.php");
        exit;
    }
}

$idWishlist = $_SESSION["wishlist"] ?? [];

$produkWishlist = [];
$totalHarga = 0;
foreach ($idWishlist as $id) {
    $item = produkById($semuaProduk, (int) $id);
    if ($item !== null) {
        $produkWishlist[] = $item;
        $totalHarga += (int) $item["harga"];
    }
}

$jumlahWishlist = count($produkWishlist);
$jumlahKeranjang = keranjangJumlahQty();
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wishlist - Narahika Preloved Store</title>
  </head>
  <body>
    <header>
      <nav>
        <strong>Narahika</strong> |
        <a href="../../dashboard.php">Dashboard</a> |
        <a href="katalog.php">Katalog</a> |
        <a href="../keranjang/keranjang.php">Keranjang (<?= $jumlahKeranjang ?>)</a> |
        <a href="wishlist.php">Wishlist (<?= $jumlahWishlist ?>)</a> |
        <?php if ($adalahPenjual): ?>
          <a href="posting_barang.php">Jual Barang</a> |
        <?php endif; ?>
        <?php if ($sudahLogin): ?>
          <a href="../profil/profil_<?= $adalahPenjual ? "penjual" : "pembeli" ?>.php">Profil</a> |
          <a href="../profil/riwayat.php">Riwayat</a> |
          Halo, <?= htmlspecialchars($_SESSION["nama"]) ?>
          <a href="../../../backend/auth/logout.php"><button type="button">Keluar</button></a>
        <?php else: ?>
          <a href="../auth/form_masuk.php"><button type="button">Masuk</button></a>
          <a href="../auth/form_daftar.php"><button type="button">Daftar</button></a>
        <?php endif; ?>
      </nav>
    </header>

    <hr />

    <main>
      <h2>Wishlist Saya (<?= $jumlahWishlist ?>)</h2>
      <p>Barang yang kamu simpan buat dipikirkan dulu sebelum checkout.</p>

      <?php if ($jumlahWishlist === 0): ?>
        <section>
          <h3>Wishlist Masih Kosong</h3>
          <p>Tekan tombol <strong>&#9825; Simpan</strong> di katalog atau halaman produk untuk menambah barang ke sini.</p>
          <a href="katalog.php"><button type="button">Jelajahi Katalog</button></a>
        </section>
      <?php else: ?>
        <p>
          <small>
            Total estimasi: <strong><?= rupiah($totalHarga) ?></strong> untuk <?= $jumlahWishlist ?> barang
            (belum termasuk ongkir).
          </small>
        </p>

        <?php foreach ($produkWishlist as $produk): ?>
          <article>
            <p><em>[Foto produk]</em></p>

            <h3>
              <a href="detail_produk.php?id=<?= (int) $produk["id"] ?>">
                <?= htmlspecialchars($produk["judul"]) ?>
              </a>
            </h3>

            <p>
              <strong><?= rupiah((int) $produk["harga"]) ?></strong>
              <?php if ($produk["bisa_nego"]): ?>
                <small>&middot; Bisa nego</small>
              <?php endif; ?>
            </p>

            <p>
              <small>
                <?= htmlspecialchars($produk["kategori"]) ?> &middot;
                Ukuran <?= htmlspecialchars($produk["ukuran"]) ?> &middot;
                <?= htmlspecialchars($produk["kondisi"]) ?>
              </small>
            </p>

            <p>
              <small>
                <?= htmlspecialchars($produk["toko"]) ?> &middot;
                <?= htmlspecialchars($produk["lokasi"]) ?>
              </small>
            </p>

            <div>
              <a href="detail_produk.php?id=<?= (int) $produk["id"] ?>">
                <button type="button">Lihat Detail</button>
              </a>

              <form action="wishlist.php" method="POST">
                <input type="hidden" name="aksi" value="cart" />
                <input type="hidden" name="id" value="<?= (int) $produk["id"] ?>" />
                <button type="submit">Pindah ke Keranjang</button>
              </form>

              <form action="wishlist.php" method="POST">
                <input type="hidden" name="aksi" value="wishlist" />
                <input type="hidden" name="id" value="<?= (int) $produk["id"] ?>" />
                <button type="submit">Hapus dari Wishlist</button>
              </form>
            </div>
          </article>
          <hr />
        <?php endforeach; ?>

        <p>
          <small>
            <em>
              Wishlist sementara disimpan di sesi browser &ndash; hilang saat kamu keluar.
              Nanti dipindah ke tabel database supaya tersimpan permanen.
            </em>
          </small>
        </p>

        <p>
          <a href="katalog.php"><button type="button">Lanjut Lihat Katalog</button></a>
        </p>
      <?php endif; ?>
    </main>

    <hr />

    <footer>
      <p>&copy; 2026 Narahika Preloved Store</p>
    </footer>
  </body>
</html>
