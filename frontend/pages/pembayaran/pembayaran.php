<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

require_once __DIR__ . "/../../data/pesanan.php";
require_once __DIR__ . "/../../data/keranjang.php";

$checkout = $_SESSION["checkout"] ?? null;
$jumlahNav = keranjangJumlahQty();

$daftarMetode = [
    "qris"     => [
        "nama"  => "QRIS",
        "info"  => "Scan pakai aplikasi bank / e-wallet apa saja.",
    ],
    "ewallet"  => [
        "nama"  => "E-Wallet",
        "info"  => "GoPay, OVO, DANA, atau ShopeePay.",
    ],
    "transfer" => [
        "nama"  => "Rekening Bersama (Escrow)",
        "info"  => "Transfer ke rekening Narahika, dana ditahan sampai barang diterima.",
    ],
    "cod"      => [
        "nama"  => "COD (Bayar di Tempat)",
        "info"  => "Bayar langsung ke kurir saat barang datang. Tanpa escrow.",
    ],
];

if ($_SERVER["REQUEST_METHOD"] === "POST" && $checkout !== null) {
    $aksi = $_POST["aksi"] ?? "";

    if ($aksi === "pilih") {
        $metode = $_POST["metode"] ?? "qris";
        if (!array_key_exists($metode, $daftarMetode)) {
            $metode = "qris";
        }

        $_SESSION["checkout"]["metode_bayar"] = $metode;

        if ($metode === "cod") {
            $pesanan = pesananBuat($_SESSION["checkout"]);
            unset($_SESSION["checkout"]);
            header("Location: status_pesanan.php?nomor=" . urlencode($pesanan["nomor"]));
            exit;
        }

        $tujuan = [
            "qris"     => "qris.php",
            "ewallet"  => "ewallet.php",
            "transfer" => "transfer.php",
        ][$metode];

        header("Location: " . $tujuan);
        exit;
    }

    if ($aksi === "batal") {
        // Kembalikan barang ke keranjang supaya tidak hilang
        $_SESSION["keranjang"] = [];
        foreach ($checkout["items"] as $baris) {
            $_SESSION["keranjang"][(int) $baris["produk"]["id"]] = (int) $baris["qty"];
        }
        unset($_SESSION["checkout"]);
        header("Location: ../keranjang/keranjang.php");
        exit;
    }
}

$metodeTerpilih = $checkout["metode_bayar"] ?? "qris";
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pembayaran - Narahika Preloved Store</title>
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
      <h2>Pembayaran</h2>

      <?php if ($checkout === null): ?>
        <section>
          <h3>Tidak Ada Tagihan</h3>
          <p>Kamu belum punya pesanan yang menunggu pembayaran. Checkout dulu dari keranjang.</p>
          <a href="../keranjang/keranjang.php"><button type="button">Buka Keranjang</button></a>
          <a href="../produk/katalog.php"><button type="button">Lihat Katalog</button></a>
        </section>
      <?php else: ?>
        <p>
          <small>
            Nomor pesanan <strong><?= htmlspecialchars($checkout["nomor"]) ?></strong>
            &middot; dibuat <?= htmlspecialchars(date("d M Y H:i", strtotime($checkout["waktu"]))) ?>
          </small>
        </p>

        <!-- TAGIHAN -->
        <section>
          <h3>Total yang Harus Dibayar</h3>
          <p><strong style="font-size: 22px"><?= rupiah((int) $checkout["total"]) ?></strong></p>
          <p><small><?= (int) $checkout["jumlah_item"] ?> barang &middot; ongkir <?= rupiah((int) $checkout["ongkir"]) ?> &middot; biaya layanan <?= rupiah((int) $checkout["biaya_layanan"]) ?></small></p>
          <p><small>Batas pembayaran: <strong>24 jam</strong> sejak pesanan dibuat (simulasi).</small></p>
        </section>

        <hr />

        <!-- PILIH METODE -->
        <form action="pembayaran.php" method="POST">
          <input type="hidden" name="aksi" value="pilih" />

          <section>
            <h3>Pilih Metode Pembayaran</h3>

            <?php foreach ($daftarMetode as $kode => $metode): ?>
              <div>
                <input
                  type="radio"
                  id="metode_<?= htmlspecialchars($kode) ?>"
                  name="metode"
                  value="<?= htmlspecialchars($kode) ?>"
                  <?= $metodeTerpilih === $kode ? "checked" : "" ?>
                />
                <label for="metode_<?= htmlspecialchars($kode) ?>">
                  <strong><?= htmlspecialchars($metode["nama"]) ?></strong>
                </label>
                <br />
                <small><?= htmlspecialchars($metode["info"]) ?></small>
              </div>
              <hr />
            <?php endforeach; ?>
          </section>

          <div>
            <button type="submit">Lanjutkan Pembayaran</button>
            <a href="../keranjang/keranjang.php"><button type="button">Kembali ke Keranjang</button></a>
          </div>
        </form>

        <hr />

        <form action="pembayaran.php" method="POST">
          <input type="hidden" name="aksi" value="batal" />
          <button type="submit">Batalkan Pesanan</button>
        </form>
        <p><small><em>Kalau dibatalkan, barang otomatis kembali ke keranjang.</em></small></p>
      <?php endif; ?>
    </main>

    <hr />

    <footer>
      <p>&copy; 2026 Narahika Preloved Store</p>
    </footer>
  </body>
</html>
