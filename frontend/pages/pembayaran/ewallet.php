<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

require_once __DIR__ . "/../../data/pesanan.php";
require_once __DIR__ . "/../../data/keranjang.php";

$checkout  = $_SESSION["checkout"] ?? null;
$jumlahNav = keranjangJumlahQty();

$daftarEwallet = [
    "gopay"      => ["nama" => "GoPay", "info" => "Saldo GoPay / Gojek"],
    "ovo"        => ["nama" => "OVO", "info" => "Saldo OVO Cash"],
    "dana"       => ["nama" => "DANA", "info" => "Saldo DANA"],
    "shopeepay"  => ["nama" => "ShopeePay", "info" => "Saldo ShopeePay"],
];
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bayar dengan E-Wallet - Narahika Preloved Store</title>
  </head>
  <body>
    <header>
      <nav>
        <strong>Narahika</strong> |
        <a href="../../dashboard.php">Dashboard</a> |
        <a href="../produk/katalog.php">Katalog</a> |
        <a href="../keranjang/keranjang.php">Keranjang (<?= $jumlahNav ?>)</a> |
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
        <h2>Pembayaran E-Wallet</h2>
        <section>
          <h3>Tidak Ada Tagihan</h3>
          <p>Selesaikan checkout dulu sebelum membayar.</p>
          <a href="../keranjang/keranjang.php"><button type="button">Buka Keranjang</button></a>
        </section>
      <?php else: ?>
        <h2>Bayar dengan E-Wallet</h2>
        <p>
          <small>
            Pesanan <strong><?= htmlspecialchars($checkout["nomor"]) ?></strong> &middot;
            <a href="pembayaran.php">ganti metode</a>
          </small>
        </p>

        <section>
          <p><strong style="font-size: 22px"><?= rupiah((int) $checkout["total"]) ?></strong></p>
          <p><small>Batas bayar: <strong>24 jam</strong> sejak pesanan dibuat (simulasi).</small></p>
        </section>

        <hr />

        <form action="proses.php" method="POST">
          <input type="hidden" name="aksi" value="bayar" />
          <input type="hidden" name="metode" value="ewallet" />

          <section>
            <h3>Pilih E-Wallet</h3>

            <?php foreach ($daftarEwallet as $kode => $ewallet): ?>
              <div>
                <input
                  type="radio"
                  id="ewallet_<?= htmlspecialchars($kode) ?>"
                  name="ewallet"
                  value="<?= htmlspecialchars($kode) ?>"
                  <?= $kode === "gopay" ? "checked" : "" ?>
                />
                <label for="ewallet_<?= htmlspecialchars($kode) ?>">
                  <strong><?= htmlspecialchars($ewallet["nama"]) ?></strong>
                </label>
                <small>&ndash; <?= htmlspecialchars($ewallet["info"]) ?></small>
              </div>
            <?php endforeach; ?>

            <p>
              <label for="no_hp">Nomor HP terdaftar:</label><br />
              <input
                type="tel"
                id="no_hp"
                name="no_hp"
                placeholder="081234567890"
                pattern="[0-9+ ]{9,15}"
                maxlength="15"
                required
              />
            </p>

            <p>
              <small>
                Nomor HP dipakai untuk mengirim tagihan ke aplikasi e-wallet kamu.
                Di simulasi ini tidak ada notifikasi sungguhan.
              </small>
            </p>
          </section>

          <hr />

          <section>
            <h3>Cara Bayar</h3>
            <ol>
              <li>Pilih aplikasi e-wallet yang aktif.</li>
              <li>Masukkan nomor HP yang terdaftar di aplikasi tersebut.</li>
              <li>Tekan <em>Bayar Sekarang</em>, lalu setujui tagihan di aplikasi (simulasi).</li>
              <li>Dana akan <strong>ditahan Narahika</strong> sampai kamu konfirmasi barang diterima.</li>
            </ol>
          </section>

          <hr />

          <div>
            <button type="submit">Bayar Sekarang (Simulasi)</button>
            <a href="pembayaran.php"><button type="button">Ganti Metode</button></a>
          </div>
        </form>
      <?php endif; ?>
    </main>

    <hr />

    <footer>
      <p>&copy; 2026 Narahika Preloved Store</p>
    </footer>
  </body>
</html>
