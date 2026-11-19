<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

require_once __DIR__ . "/../../data/keranjang.php";
require_once __DIR__ . "/../../data/ongkir.php";

$semuaProduk = daftarProduk();

$isi        = keranjangIsi($semuaProduk);
$jumlahItem = keranjangJumlahItem($isi);
$subtotal   = keranjangSubtotal($isi);
$berat      = keranjangBerat($isi);
$jumlahNav  = keranjangJumlahQty();

// Asal pengiriman: ambil provinsi dari lokasi produk pertama
$asalProvinsi = "";
if (count($isi) > 0) {
    $bagian = explode(",", $isi[0]["produk"]["lokasi"]);
    $asalProvinsi = trim(end($bagian));
}

$daftarProvinsi = daftarProvinsi();
$daftarMetodeBayar = [
    "transfer"    => "Transfer Bank",
    "qris"        => "QRIS",
    "ewallet"     => "E-Wallet (GoPay / OVO / Dana)",
    "cod"         => "COD (bayar di tempat)",
];

// Nilai awal form
$data = [
    "nama"        => $_SESSION["nama"] ?? "",
    "no_hp"       => "",
    "alamat"      => "",
    "provinsi"    => "",
    "kota"        => "",
    "kecamatan"   => "",
    "kode_pos"    => "",
    "catatan"     => "",
    "kurir"       => "JNE",
    "metode_bayar" => "transfer",
];

$errors   = [];
$tahap    = "alamat";   // alamat -> kirim -> (buat pesanan)
$opsiKurir = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $aksi = $_POST["aksi"] ?? "";

    foreach ($data as $kunci => $nilai) {
        $data[$kunci] = trim((string) ($_POST[$kunci] ?? $nilai));
    }

    // --- Validasi alamat ---
    if ($data["nama"] === "") {
        $errors[] = "Nama penerima wajib diisi.";
    }
    if (!preg_match('/^[0-9+ ]{9,15}$/', $data["no_hp"])) {
        $errors[] = "Nomor HP tidak valid (9-15 digit).";
    }
    if (strlen($data["alamat"]) < 10) {
        $errors[] = "Alamat terlalu pendek, tulis nama jalan dan nomor rumah.";
    }
    if (!in_array($data["provinsi"], $daftarProvinsi, true)) {
        $errors[] = "Pilih provinsi tujuan yang benar.";
    }
    if ($data["kota"] === "") {
        $errors[] = "Kota / kabupaten wajib diisi.";
    }
    if ($data["kecamatan"] === "") {
        $errors[] = "Kecamatan wajib diisi.";
    }
    if ($data["kode_pos"] !== "" && !preg_match('/^[0-9]{5}$/', $data["kode_pos"])) {
        $errors[] = "Kode pos harus 5 angka.";
    }
    if (!array_key_exists($data["kurir"], daftarKurir())) {
        $data["kurir"] = "JNE";
    }
    if (!array_key_exists($data["metode_bayar"], $daftarMetodeBayar)) {
        $data["metode_bayar"] = "transfer";
    }

    if ($aksi === "hitung" && count($errors) === 0) {
        $tahap = "kirim";
    }

    if ($aksi === "buat") {
        if (count($errors) > 0) {
            $tahap = "alamat";
        } else {
            $ongkir       = hitungOngkir($asalProvinsi, $data["provinsi"], $berat, $data["kurir"]);
            $biayaLayanan = 2000;
            $total        = $subtotal + $ongkir + $biayaLayanan;

            $_SESSION["checkout"] = [
                "nomor"         => "NRH-" . date("Ymd") . "-" . random_int(100, 999),
                "waktu"         => date("Y-m-d H:i"),
                "items"         => $isi,
                "jumlah_item"   => $jumlahItem,
                "berat"         => $berat,
                "subtotal"      => $subtotal,
                "ongkir"        => $ongkir,
                "biaya_layanan" => $biayaLayanan,
                "total"         => $total,
                "asal"          => $asalProvinsi,
                "alamat"        => $data,
                "kurir"         => $data["kurir"],
                "metode_bayar"  => $data["metode_bayar"],
            ];

            keranjangKosongkan();
            header("Location: ringkasan.php");
            exit;
        }
    }

    if ($tahap === "kirim") {
        $opsiKurir = ongkirSemuaKurir($asalProvinsi, $data["provinsi"], $berat);
    }
}

// Tetap hitung opsi kurir kalau tahap kirim (termasuk setelah validasi buat gagal)
if ($tahap === "kirim" && count($opsiKurir) === 0) {
    $opsiKurir = ongkirSemuaKurir($asalProvinsi, $data["provinsi"], $berat);
}

$ongkirTerpilih = isset($opsiKurir[$data["kurir"]]) ? (int) $opsiKurir[$data["kurir"]]["biaya"] : 0;
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Checkout - Narahika Preloved Store</title>
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
      <h2>Checkout</h2>

      <?php if ($jumlahItem === 0): ?>
        <section>
          <h3>Keranjang Kosong</h3>
          <p>Isi keranjang dulu sebelum checkout.</p>
          <a href="../produk/katalog.php"><button type="button">Lihat Katalog</button></a>
        </section>
      <?php else: ?>
        <p>
          <small>
            <a href="keranjang.php">Keranjang</a> &rsaquo;
            <?= $tahap === "kirim" ? "Pilih Pengiriman" : "Alamat &amp; Pengiriman" ?>
          </small>
        </p>

        <?php if (count($errors) > 0): ?>
          <section>
            <h3>Periksa Lagi</h3>
            <ul>
              <?php foreach ($errors as $pesan): ?>
                <li><?= htmlspecialchars($pesan) ?></li>
              <?php endforeach; ?>
            </ul>
          </section>
        <?php endif; ?>

        <form action="checkout.php" method="POST">
          <?php if ($tahap === "alamat"): ?>
            <input type="hidden" name="aksi" value="hitung" />
          <?php else: ?>
            <input type="hidden" name="aksi" value="buat" />
          <?php endif; ?>

          <!-- ALAMAT -->
          <section>
            <h3>Alamat Pengiriman</h3>

            <div>
              <label for="nama">Nama Penerima:</label><br />
              <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($data["nama"]) ?>" maxlength="100" required />
            </div>

            <div>
              <label for="no_hp">Nomor HP:</label><br />
              <input type="tel" id="no_hp" name="no_hp" value="<?= htmlspecialchars($data["no_hp"]) ?>" placeholder="081234567890" pattern="[0-9+ ]{9,15}" required />
            </div>

            <div>
              <label for="alamat">Alamat Lengkap:</label><br />
              <textarea id="alamat" name="alamat" rows="3" cols="50" maxlength="255" placeholder="Jalan, nomor rumah, RT/RW" required><?= htmlspecialchars($data["alamat"]) ?></textarea>
            </div>

            <div>
              <label for="provinsi">Provinsi:</label><br />
              <select id="provinsi" name="provinsi" required>
                <option value="">-- Pilih Provinsi --</option>
                <?php foreach ($daftarProvinsi as $provinsi): ?>
                  <option value="<?= htmlspecialchars($provinsi) ?>" <?= $data["provinsi"] === $provinsi ? "selected" : "" ?>>
                    <?= htmlspecialchars($provinsi) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label for="kota">Kota / Kabupaten:</label><br />
              <input type="text" id="kota" name="kota" value="<?= htmlspecialchars($data["kota"]) ?>" maxlength="100" required />
            </div>

            <div>
              <label for="kecamatan">Kecamatan:</label><br />
              <input type="text" id="kecamatan" name="kecamatan" value="<?= htmlspecialchars($data["kecamatan"]) ?>" maxlength="100" required />
            </div>

            <div>
              <label for="kode_pos">Kode Pos:</label><br />
              <input type="text" id="kode_pos" name="kode_pos" value="<?= htmlspecialchars($data["kode_pos"]) ?>" pattern="[0-9]{5}" maxlength="5" />
            </div>

            <div>
              <label for="catatan">Catatan untuk Penjual (opsional):</label><br />
              <textarea id="catatan" name="catatan" rows="2" cols="50" maxlength="200" placeholder="Contoh: tolong packing bubble wrap"><?= htmlspecialchars($data["catatan"]) ?></textarea>
            </div>

            <p><small>Asal pengiriman: <strong><?= htmlspecialchars($asalProvinsi !== "" ? $asalProvinsi : "belum diketahui") ?></strong> &middot; total berat <?= $berat ?> g</small></p>
          </section>

          <?php if ($tahap === "kirim"): ?>
            <hr />

            <!-- PILIH KURIR -->
            <section>
              <h3>Pilih Pengiriman</h3>
              <p>
                <small>
                  Tujuan: <?= htmlspecialchars($data["kota"] . ", " . $data["provinsi"]) ?> &middot;
                  <?= zonaOngkir($asalProvinsi, $data["provinsi"])["label"] ?>
                </small>
              </p>

              <table border="1" cellpadding="8" cellspacing="0">
                <thead>
                  <tr>
                    <th>Pilih</th>
                    <th>Kurir</th>
                    <th>Estimasi</th>
                    <th>Biaya</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($opsiKurir as $kode => $opsi): ?>
                    <tr>
                      <td>
                        <input
                          type="radio"
                          id="kurir_<?= htmlspecialchars($kode) ?>"
                          name="kurir"
                          value="<?= htmlspecialchars($kode) ?>"
                          <?= $data["kurir"] === $kode ? "checked" : "" ?>
                        />
                      </td>
                      <td><label for="kurir_<?= htmlspecialchars($kode) ?>"><?= htmlspecialchars($opsi["nama"]) ?></label></td>
                      <td><?= $opsi["estimasi"] ?></td>
                      <td><?= rupiah((int) $opsi["biaya"]) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>

              <p>
                <small>
                  Ongkir dihitung dari berat <?= $berat ?> g (<?= (int) ceil($berat / 1000) ?> kg).
                  <a href="cek_ongkir.php?asal=<?= urlencode($asalProvinsi) ?>&tujuan=<?= urlencode($data["provinsi"]) ?>&berat=<?= $berat ?>">
                    Bandingkan semua kurir
                  </a>
                </small>
              </p>
            </section>

            <hr />

            <!-- METODE BAYAR -->
            <section>
              <h3>Metode Pembayaran</h3>

              <?php foreach ($daftarMetodeBayar as $kode => $label): ?>
                <div>
                  <input
                    type="radio"
                    id="bayar_<?= htmlspecialchars($kode) ?>"
                    name="metode_bayar"
                    value="<?= htmlspecialchars($kode) ?>"
                    <?= $data["metode_bayar"] === $kode ? "checked" : "" ?>
                  />
                  <label for="bayar_<?= htmlspecialchars($kode) ?>"><?= htmlspecialchars($label) ?></label>
                </div>
              <?php endforeach; ?>

              <p>
                <small>
                  <em>
                    Pembayaran masih simulasi &ndash; halaman QRIS/e-wallet dan rekening bersama
                    dibuat di tahap pembayaran-escrow.
                  </em>
                </small>
              </p>
            </section>
          <?php else: ?>
            <input type="hidden" name="kurir" value="<?= htmlspecialchars($data["kurir"]) ?>" />
            <input type="hidden" name="metode_bayar" value="<?= htmlspecialchars($data["metode_bayar"]) ?>" />
          <?php endif; ?>

          <hr />

          <!-- RINGKASAN -->
          <section>
            <h3>Ringkasan Pesanan</h3>

            <table border="1" cellpadding="8" cellspacing="0">
              <tbody>
                <?php foreach ($isi as $baris): ?>
                  <tr>
                    <td>
                      <?= htmlspecialchars($baris["produk"]["judul"]) ?>
                      <small>&times;<?= (int) $baris["qty"] ?></small>
                    </td>
                    <td><?= rupiah((int) $baris["subtotal"]) ?></td>
                  </tr>
                <?php endforeach; ?>
                <tr>
                  <th>Subtotal (<?= $jumlahItem ?> barang)</th>
                  <td><?= rupiah($subtotal) ?></td>
                </tr>
                <tr>
                  <th>Ongkos Kirim</th>
                  <td>
                    <?php if ($tahap === "kirim"): ?>
                      <?= rupiah($ongkirTerpilih) ?>
                    <?php else: ?>
                      <small>Pilih kurir dulu</small>
                    <?php endif; ?>
                  </td>
                </tr>
                <tr>
                  <th>Biaya Layanan</th>
                  <td><?= rupiah(2000) ?></td>
                </tr>
                <tr>
                  <th>Total</th>
                  <td>
                    <strong>
                      <?= rupiah($subtotal + $ongkirTerpilih + 2000) ?>
                    </strong>
                  </td>
                </tr>
              </tbody>
            </table>
          </section>

          <hr />

          <div>
            <?php if ($tahap === "alamat"): ?>
              <button type="submit">Hitung Ongkir &amp; Lanjut</button>
            <?php else: ?>
              <button type="submit">Buat Pesanan</button>
            <?php endif; ?>
            <a href="keranjang.php"><button type="button">Kembali ke Keranjang</button></a>
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
