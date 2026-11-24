<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

require_once __DIR__ . "/../../data/keranjang.php";
require_once __DIR__ . "/../../data/ongkir.php";

$checkout = $_SESSION["checkout"] ?? null;

$daftarMetodeBayar = [
    "transfer" => "Transfer Bank",
    "qris"     => "QRIS",
    "ewallet"  => "E-Wallet (GoPay / OVO / Dana)",
    "cod"      => "COD (bayar di tempat)",
];

$jumlahNav = keranjangJumlahQty();
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ringkasan Pesanan - Narahika Preloved Store</title>
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
      <?php if ($checkout === null): ?>
        <h2>Ringkasan Pesanan</h2>
        <section>
          <h3>Belum Ada Pesanan</h3>
          <p>Kamu belum membuat pesanan. Isi keranjang dan checkout dulu.</p>
          <a href="../produk/katalog.php"><button type="button">Lihat Katalog</button></a>
          <a href="keranjang.php"><button type="button">Buka Keranjang</button></a>
        </section>
      <?php else: ?>
        <h2>Pesanan Dibuat</h2>
        <p>
          Nomor pesanan <strong><?= htmlspecialchars($checkout["nomor"]) ?></strong>
          &middot; <?= htmlspecialchars(date("d M Y H:i", strtotime($checkout["waktu"]))) ?>
        </p>
        <p>
          Status: <strong>Menunggu Pembayaran</strong><br />
          <small>Selesaikan pembayaran supaya pesanan diteruskan ke penjual.</small>
        </p>

        <hr />

        <!-- BARANG -->
        <section>
          <h3>Barang</h3>
          <table border="1" cellpadding="8" cellspacing="0">
            <thead>
              <tr>
                <th>Produk</th>
                <th>Qty</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($checkout["items"] as $baris): ?>
                <tr>
                  <td>
                    <?= htmlspecialchars($baris["produk"]["judul"]) ?><br />
                    <small><?= htmlspecialchars($baris["produk"]["toko"]) ?></small>
                  </td>
                  <td><?= (int) $baris["qty"] ?></td>
                  <td><?= rupiah((int) $baris["subtotal"]) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </section>

        <hr />

        <!-- ALAMAT -->
        <section>
          <h3>Alamat Pengiriman</h3>
          <p>
            <strong><?= htmlspecialchars($checkout["alamat"]["nama"]) ?></strong><br />
            <?= htmlspecialchars($checkout["alamat"]["no_hp"]) ?><br />
            <?= htmlspecialchars($checkout["alamat"]["alamat"]) ?><br />
            <?= htmlspecialchars($checkout["alamat"]["kecamatan"] . ", " . $checkout["alamat"]["kota"]) ?><br />
            <?= htmlspecialchars($checkout["alamat"]["provinsi"]) ?>
            <?= $checkout["alamat"]["kode_pos"] !== "" ? htmlspecialchars($checkout["alamat"]["kode_pos"]) : "" ?>
          </p>

          <?php if ($checkout["alamat"]["catatan"] !== ""): ?>
            <p><small>Catatan: <?= htmlspecialchars($checkout["alamat"]["catatan"]) ?></small></p>
          <?php endif; ?>

          <p>
            <small>
              Kurir: <strong><?= htmlspecialchars(daftarKurir()[$checkout["kurir"]]["nama"] ?? $checkout["kurir"]) ?></strong>
              &middot; estimasi <?= estimasiKirim($checkout["asal"], $checkout["alamat"]["provinsi"], $checkout["kurir"]) ?>
            </small>
          </p>
          <p><small>Dikirim dari: <?= htmlspecialchars($checkout["asal"]) ?></small></p>
        </section>

        <hr />

        <!-- PEMBAYARAN -->
        <section>
          <h3>Rincian Pembayaran</h3>

          <table border="1" cellpadding="8" cellspacing="0">
            <tbody>
              <tr>
                <th>Subtotal (<?= (int) $checkout["jumlah_item"] ?> barang)</th>
                <td><?= rupiah((int) $checkout["subtotal"]) ?></td>
              </tr>
              <tr>
                <th>Ongkos Kirim</th>
                <td><?= rupiah((int) $checkout["ongkir"]) ?></td>
              </tr>
              <tr>
                <th>Biaya Layanan</th>
                <td><?= rupiah((int) $checkout["biaya_layanan"]) ?></td>
              </tr>
              <tr>
                <th>Total Bayar</th>
                <td><strong><?= rupiah((int) $checkout["total"]) ?></strong></td>
              </tr>
              <tr>
                <th>Metode</th>
                <td><?= htmlspecialchars($daftarMetodeBayar[$checkout["metode_bayar"]] ?? $checkout["metode_bayar"]) ?></td>
              </tr>
            </tbody>
          </table>
        </section>

        <hr />

        <div>
          <a href="../pembayaran/pembayaran.php"><button type="button">Lanjut ke Pembayaran</button></a>
          <a href="../profil/riwayat.php"><button type="button">Lihat Riwayat Pesanan</button></a>
          <a href="../produk/katalog.php"><button type="button">Belanja Lagi</button></a>
        </div>

        <p>
          <small>
            <em>
              Pembayaran masih simulasi (QRIS / e-wallet / rekening bersama) dan data pesanan
              tersimpan di sesi browser. Nanti dipindah ke tabel database.
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
