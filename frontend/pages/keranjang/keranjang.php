<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

require_once __DIR__ . "/../../data/keranjang.php";

$semuaProduk = daftarProduk();

// --- Aksi keranjang ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $aksi = $_POST["aksi"] ?? "";
    $id   = (int) ($_POST["id"] ?? 0);

    if ($aksi === "ubah") {
        keranjangSetQty($id, (int) ($_POST["qty"] ?? 1));
    } elseif ($aksi === "hapus") {
        keranjangHapus($id);
    } elseif ($aksi === "tambah") {
        keranjangTambah($id, (int) ($_POST["qty"] ?? 1));
    } elseif ($aksi === "kosongkan") {
        keranjangKosongkan();
    }

    header("Location: keranjang.php");
    exit;
}

$isi          = keranjangIsi($semuaProduk);
$jumlahItem   = keranjangJumlahItem($isi);
$subtotal     = keranjangSubtotal($isi);
$berat        = keranjangBerat($isi);
$jumlahNav    = keranjangJumlahQty();
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Keranjang - Narahika Preloved Store</title>
  </head>
  <body>
    <header>
      <nav>
        <strong>Narahika</strong> |
        <a href="../../dashboard.php">Dashboard</a> |
        <a href="../produk/katalog.php">Katalog</a> |
        <a href="keranjang.php">Keranjang (<?= $jumlahNav ?>)</a> |
        <a href="../produk/wishlist.php">Wishlist (<?= count($_SESSION["wishlist"] ?? []) ?>)</a> |
        <?php if ($adalahPenjual): ?>
          <a href="../produk/posting_barang.php">Jual Barang</a> |
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
      <h2>Keranjang Belanja (<?= $jumlahItem ?> item)</h2>

      <?php if ($jumlahItem === 0): ?>
        <section>
          <h3>Keranjang Masih Kosong</h3>
          <p>Belum ada barang di keranjang. Pilih barang dulu dari katalog.</p>
          <a href="../produk/katalog.php"><button type="button">Jelajahi Katalog</button></a>
        </section>
      <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0">
          <thead>
            <tr>
              <th>Produk</th>
              <th>Harga</th>
              <th>Jumlah</th>
              <th>Subtotal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($isi as $baris): ?>
              <?php $produk = $baris["produk"]; ?>
              <tr>
                <td>
                  <a href="../produk/detail_produk.php?id=<?= (int) $produk["id"] ?>">
                    <?= htmlspecialchars($produk["judul"]) ?>
                  </a>
                  <br />
                  <small>
                    <?= htmlspecialchars($produk["toko"]) ?> &middot;
                    Ukuran <?= htmlspecialchars($produk["ukuran"]) ?> &middot;
                    <?= htmlspecialchars($produk["kondisi"]) ?>
                  </small>
                  <br />
                  <small><?= (int) $baris["berat"] ?> gram</small>
                </td>
                <td><?= rupiah((int) $produk["harga"]) ?></td>
                <td>
                  <form action="keranjang.php" method="POST">
                    <input type="hidden" name="aksi" value="ubah" />
                    <input type="hidden" name="id" value="<?= (int) $produk["id"] ?>" />
                    <input
                      type="number"
                      name="qty"
                      value="<?= (int) $baris["qty"] ?>"
                      min="1"
                      max="99"
                      style="width: 60px"
                    />
                    <button type="submit">Ubah</button>
                  </form>
                </td>
                <td><strong><?= rupiah((int) $baris["subtotal"]) ?></strong></td>
                <td>
                  <form action="keranjang.php" method="POST">
                    <input type="hidden" name="aksi" value="hapus" />
                    <input type="hidden" name="id" value="<?= (int) $produk["id"] ?>" />
                    <button type="submit">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <hr />

        <!-- RINGKASAN -->
        <section>
          <h3>Ringkasan</h3>
          <table border="1" cellpadding="8" cellspacing="0">
            <tr>
              <th>Total Item</th>
              <td><?= $jumlahItem ?> barang</td>
            </tr>
            <tr>
              <th>Total Berat</th>
              <td><?= $berat ?> gram (<?= (int) ceil($berat / 1000) ?> kg)</td>
            </tr>
            <tr>
              <th>Subtotal</th>
              <td><strong><?= rupiah($subtotal) ?></strong></td>
            </tr>
            <tr>
              <th>Ongkos Kirim</th>
              <td><small>Dihitung di halaman checkout sesuai alamat tujuan</small></td>
            </tr>
          </table>

          <p>
            <a href="checkout.php"><button type="button">Lanjut ke Checkout</button></a>
            <a href="../produk/katalog.php"><button type="button">Lanjut Belanja</button></a>
          </p>

          <form action="keranjang.php" method="POST">
            <input type="hidden" name="aksi" value="kosongkan" />
            <button type="submit">Kosongkan Keranjang</button>
          </form>
        </section>

        <hr />

        <p>
          <small>
            <em>
              Keranjang disimpan di sesi browser &ndash; hilang saat keluar akun.
              Nanti dipindah ke tabel database supaya tersimpan permanen.
            </em>
          </small>
        </p>
      <?php endif; ?>
    </main>

    <hr />

    <footer>
      <p>&copy; 2026 Narahika Preloved Store</p>
    </footer>
  </body>
</html>
