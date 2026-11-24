<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

require_once __DIR__ . "/../../data/pesanan.php";
require_once __DIR__ . "/../../data/keranjang.php";

$checkout  = $_SESSION["checkout"] ?? null;
$jumlahNav = keranjangJumlahQty();

// QR palsu yang deterministik dari nomor pesanan (bukan QR sungguhan)
function qrPalsu(string $seed, int $n = 21): string
{
    $hash = md5($seed);
    $bit  = "";
    for ($i = 0; $i < strlen($hash); $i++) {
        $bit .= str_pad(decbin(hexdec($hash[$i])), 4, "0", STR_PAD_LEFT);
    }
    $panjang = strlen($bit);

    $baris = [];
    for ($y = 0; $y < $n; $y++) {
        $line = "";
        for ($x = 0; $x < $n; $x++) {
            $diFinder = ($x < 7 && $y < 7) || ($x >= $n - 7 && $y < 7) || ($x < 7 && $y >= $n - 7);

            if ($diFinder) {
                $lx = $x >= $n - 7 ? $x - ($n - 7) : $x;
                $ly = $y >= $n - 7 ? $y - ($n - 7) : $y;
                $bingkai = $lx === 0 || $lx === 6 || $ly === 0 || $ly === 6;
                $tengah  = $lx >= 2 && $lx <= 4 && $ly >= 2 && $ly <= 4;
                $hitam   = $bingkai || $tengah;
            } else {
                $hitam = $bit[($y * $n + $x) % $panjang] === "1";
            }

            $line .= $hitam ? "██" : "  ";
        }
        $baris[] = $line;
    }

    return implode("\n", $baris);
}
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bayar dengan QRIS - Narahika Preloved Store</title>
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
        <h2>Pembayaran QRIS</h2>
        <section>
          <h3>Tidak Ada Tagihan</h3>
          <p>Selesaikan checkout dulu sebelum membayar.</p>
          <a href="../keranjang/keranjang.php"><button type="button">Buka Keranjang</button></a>
        </section>
      <?php else: ?>
        <h2>Bayar dengan QRIS</h2>
        <p>
          <small>
            Pesanan <strong><?= htmlspecialchars($checkout["nomor"]) ?></strong> &middot;
            <a href="pembayaran.php">ganti metode</a>
          </small>
        </p>

        <section>
          <p><strong style="font-size: 22px"><?= rupiah((int) $checkout["total"]) ?></strong></p>
          <p><small>Batas bayar: <strong>23:59:59</strong> (hitungan simulasi)</small></p>

          <pre style="line-height: 1; font-size: 10px; letter-spacing: 1px"><?= qrPalsu($checkout["nomor"]) ?></pre>
          <p><small>QR di atas contoh tampilan, bukan QR pembayaran asli.</small></p>
        </section>

        <hr />

        <section>
          <h3>Cara Bayar</h3>
          <ol>
            <li>Buka aplikasi bank atau e-wallet yang mendukung QRIS.</li>
            <li>Pilih menu <em>Scan QR</em>, arahkan ke kode di atas.</li>
            <li>Pastikan nominal sesuai, lalu selesaikan pembayaran.</li>
            <li>Dana akan <strong>ditahan Narahika</strong> sampai kamu konfirmasi barang diterima.</li>
          </ol>
        </section>

        <hr />

        <div>
          <form action="proses.php" method="POST">
            <input type="hidden" name="aksi" value="bayar" />
            <input type="hidden" name="metode" value="qris" />
            <button type="submit">Saya Sudah Bayar (Simulasi)</button>
          </form>
          <a href="pembayaran.php"><button type="button">Ganti Metode</button></a>
        </div>
      <?php endif; ?>
    </main>

    <hr />

    <footer>
      <p>&copy; 2026 Narahika Preloved Store</p>
    </footer>
  </body>
</html>
