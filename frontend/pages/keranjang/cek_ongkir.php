<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

require_once __DIR__ . "/../../data/keranjang.php";
require_once __DIR__ . "/../../data/ongkir.php";

$daftarProvinsi = daftarProvinsi();
$daftarKurir    = daftarKurir();

// Nilai awal
$asal    = $_GET["asal"] ?? "Jawa Barat";
$tujuan  = $_GET["tujuan"] ?? "";
$berat   = (int) ($_GET["berat"] ?? 1000);
$kurir   = $_GET["kurir"] ?? "semua";

// Whitelist
if (!in_array($asal, $daftarProvinsi, true)) {
    $asal = "Jawa Barat";
}
if ($tujuan !== "" && !in_array($tujuan, $daftarProvinsi, true)) {
    $tujuan = "";
}
if ($berat < 1) {
    $berat = 1000;
}
if ($berat > 50000) {
    $berat = 50000;
}
if ($kurir !== "semua" && !array_key_exists($kurir, $daftarKurir)) {
    $kurir = "semua";
}

$hitung     = $tujuan !== "" && isset($_GET["tujuan"]);
$hasilOngkir = $hitung ? ongkirSemuaKurir($asal, $tujuan, $berat) : [];
$zona        = $hitung ? zonaOngkir($asal, $tujuan) : null;

$jumlahNav = keranjangJumlahQty();
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cek Ongkir - Narahika Preloved Store</title>
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
      <h2>Cek Ongkos Kirim</h2>
      <p>Perkiraan biaya pengiriman sebelum checkout. Angka ini simulasi, bukan tarif kurir resmi.</p>

      <form action="cek_ongkir.php" method="GET">
        <div>
          <label for="asal">Dikirim dari (provinsi):</label><br />
          <select id="asal" name="asal" required>
            <?php foreach ($daftarProvinsi as $provinsi): ?>
              <option value="<?= htmlspecialchars($provinsi) ?>" <?= $asal === $provinsi ? "selected" : "" ?>>
                <?= htmlspecialchars($provinsi) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="tujuan">Tujuan (provinsi):</label><br />
          <select id="tujuan" name="tujuan" required>
            <option value="">-- Pilih Provinsi --</option>
            <?php foreach ($daftarProvinsi as $provinsi): ?>
              <option value="<?= htmlspecialchars($provinsi) ?>" <?= $tujuan === $provinsi ? "selected" : "" ?>>
                <?= htmlspecialchars($provinsi) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label for="berat">Berat Paket (gram):</label><br />
          <input type="number" id="berat" name="berat" value="<?= (int) $berat ?>" min="1" max="50000" step="100" required />
          <p><small>Contoh: 1 jaket &asymp; 900 gram, 1 pasang sepatu &asymp; 1100 gram.</small></p>
        </div>

        <div>
          <label for="kurir">Kurir:</label><br />
          <select id="kurir" name="kurir">
            <option value="semua" <?= $kurir === "semua" ? "selected" : "" ?>>Bandingkan semua kurir</option>
            <?php foreach ($daftarKurir as $kode => $info): ?>
              <option value="<?= htmlspecialchars($kode) ?>" <?= $kurir === $kode ? "selected" : "" ?>>
                <?= htmlspecialchars($info["nama"]) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <button type="submit">Cek Ongkir</button>
          <a href="cek_ongkir.php">Reset</a>
        </div>
      </form>

      <hr />

      <?php if (!$hitung): ?>
        <p><small>Isi provinsi tujuan lalu tekan <strong>Cek Ongkir</strong>.</small></p>
      <?php else: ?>
        <section>
          <h3>Hasil Perhitungan</h3>
          <p>
            <small>
              <?= htmlspecialchars($asal) ?> &rarr; <?= htmlspecialchars($tujuan) ?>
              &middot; <?= htmlspecialchars($zona["label"]) ?>
              &middot; <?= (int) $berat ?> g (<?= (int) ceil($berat / 1000) ?> kg)
            </small>
          </p>

          <table border="1" cellpadding="8" cellspacing="0">
            <thead>
              <tr>
                <th>Kurir</th>
                <th>Estimasi</th>
                <th>Biaya</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($hasilOngkir as $kode => $opsi): ?>
                <?php if ($kurir !== "semua" && $kurir !== $kode) { continue; } ?>
                <tr>
                  <td><?= htmlspecialchars($opsi["nama"]) ?></td>
                  <td><?= $opsi["estimasi"] ?></td>
                  <td><strong><?= rupiah((int) $opsi["biaya"]) ?></strong></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <p>
            <small>
              Perhitungan: tarif dasar &times; berat (kg, dibulatkan ke atas) &times; faktor zona
              (<?= htmlspecialchars((string) $zona["faktor"]) ?>).
            </small>
          </p>

          <p>
            <a href="checkout.php"><button type="button">Lanjut ke Checkout</button></a>
            <a href="../produk/katalog.php"><button type="button">Kembali ke Katalog</button></a>
          </p>
        </section>
      <?php endif; ?>
    </main>

    <hr />

    <footer>
      <p>&copy; 2026 Narahika Preloved Store</p>
    </footer>
  </body>
</html>
