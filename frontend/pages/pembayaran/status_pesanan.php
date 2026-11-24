<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

require_once __DIR__ . "/../../data/pesanan.php";
require_once __DIR__ . "/../../data/keranjang.php";
require_once __DIR__ . "/../../data/ongkir.php";

$nomor   = trim((string) ($_GET["nomor"] ?? ""));
$pesanan = $nomor !== "" ? pesananAmbil($nomor) : null;

// --- Aksi ubah status (POST lalu redirect) ---
if ($pesanan !== null && $_SERVER["REQUEST_METHOD"] === "POST") {
    $aksi = $_POST["aksi"] ?? "";

    if ($aksi === "kirim" && in_array($pesanan["status"], ["dibayar", "diproses"], true)) {
        $kurir = daftarKurir()[$pesanan["kurir"]]["nama"] ?? $pesanan["kurir"];
        pesananUbahStatus(
            $nomor,
            "dikirim",
            "Penjual mengirim barang via " . $kurir . ". Nomor resi menyusul (simulasi)."
        );
    } elseif ($aksi === "terima" && $pesanan["status"] === "dikirim") {
        pesananUbahStatus(
            $nomor,
            "selesai",
            "Barang diterima pembeli. Dana diteruskan ke penjual."
        );
    } elseif ($aksi === "batal" && in_array($pesanan["status"], ["menunggu_pembayaran", "dibayar", "diproses"], true)) {
        pesananUbahStatus(
            $nomor,
            "dibatalkan",
            "Pesanan dibatalkan. Dana dikembalikan ke pembeli (simulasi)."
        );
    }

    header("Location: status_pesanan.php?nomor=" . urlencode($nomor));
    exit;
}

$jumlahNav = keranjangJumlahQty();

// Label tahap untuk indikator sederhana
$langkah = $pesanan !== null && $pesanan["metode_bayar"] === "cod"
    ? ["diproses", "dikirim", "selesai"]
    : statusPesananLangkah();
$posisiLangkah = $pesanan !== null ? array_search($pesanan["status"], $langkah, true) : false;
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Status Pesanan - Narahika Preloved Store</title>
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
      <?php if ($pesanan === null): ?>
        <h2>Status Pesanan</h2>
        <section>
          <h3>Pesanan Tidak Ditemukan</h3>
          <p>
            Nomor pesanan tidak ada di sesi ini. Pesanan demo hanya tersimpan sampai
            kamu keluar akun atau menutup browser.
          </p>
          <a href="../keranjang/keranjang.php"><button type="button">Buka Keranjang</button></a>
          <a href="../produk/katalog.php"><button type="button">Lihat Katalog</button></a>
        </section>
      <?php else: ?>
        <h2>Status Pesanan</h2>
        <p>
          <small>
            Nomor <strong><?= htmlspecialchars($pesanan["nomor"]) ?></strong> &middot;
            dibuat <?= htmlspecialchars(date("d M Y H:i", strtotime($pesanan["waktu"]))) ?>
          </small>
        </p>

        <section>
          <h3><?= htmlspecialchars(statusPesananLabel($pesanan["status"])) ?></h3>

          <?php if ($posisiLangkah !== false): ?>
            <p>
              <small>
                <?php foreach ($langkah as $i => $tahap): ?>
                  <?= $i > 0 ? " &rarr; " : "" ?>
                  <?php if ($i <= $posisiLangkah): ?>
                    <strong>[&#10003;] <?= htmlspecialchars(statusPesananLabel($tahap)) ?></strong>
                  <?php else: ?>
                    [ ] <?= htmlspecialchars(statusPesananLabel($tahap)) ?>
                  <?php endif; ?>
                <?php endforeach; ?>
              </small>
            </p>
          <?php endif; ?>

          <p>
            <small>
              Metode: <strong><?= htmlspecialchars(labelMetodeBayar($pesanan["metode_bayar"])) ?></strong>
              <?php if (($pesanan["metode_detail"] ?? "") !== ""): ?>
                &middot; <?= htmlspecialchars($pesanan["metode_detail"]) ?>
              <?php endif; ?>
              &middot; Kurir: <?= htmlspecialchars(daftarKurir()[$pesanan["kurir"]]["nama"] ?? $pesanan["kurir"]) ?>
            </small>
          </p>
        </section>

        <hr />

        <!-- TIMELINE -->
        <section>
          <h3>Riwayat Status</h3>
          <table border="1" cellpadding="8" cellspacing="0">
            <thead>
              <tr>
                <th>Waktu</th>
                <th>Status</th>
                <th>Catatan</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pesanan["timeline"] as $baris): ?>
                <tr>
                  <td><?= htmlspecialchars($baris["waktu"]) ?></td>
                  <td><?= htmlspecialchars(statusPesananLabel($baris["status"])) ?></td>
                  <td><?= htmlspecialchars($baris["catatan"]) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </section>

        <hr />

        <!-- ESCROW -->
        <?php if ($pesanan["metode_bayar"] === "cod"): ?>
          <section>
            <h3>Pembayaran COD</h3>
            <p>
              Pesanan ini <strong>tanpa escrow</strong>. Bayar langsung ke kurir saat barang
              datang sebesar <strong><?= rupiah((int) $pesanan["total"]) ?></strong>.
            </p>
            <p><small>Pastikan barang dicek dulu sebelum membayar kurir.</small></p>
          </section>
        <?php else: ?>
          <section>
            <h3>Rincian Dana Escrow</h3>
            <table border="1" cellpadding="8" cellspacing="0">
              <tbody>
                <tr>
                  <th>Subtotal Barang</th>
                  <td><?= rupiah((int) $pesanan["subtotal"]) ?></td>
                </tr>
                <tr>
                  <th>Ongkos Kirim</th>
                  <td><?= rupiah((int) $pesanan["ongkir"]) ?></td>
                </tr>
                <tr>
                  <th>Biaya Layanan</th>
                  <td><?= rupiah((int) $pesanan["biaya_layanan"]) ?></td>
                </tr>
                <tr>
                  <th>Total Dibayar Pembeli</th>
                  <td><strong><?= rupiah((int) $pesanan["total"]) ?></strong></td>
                </tr>
                <tr>
                  <th>Komisi Narahika (5% subtotal)</th>
                  <td>&minus;<?= rupiah((int) $pesanan["komisi"]) ?></td>
                </tr>
                <tr>
                  <th>Dana untuk Penjual</th>
                  <td><strong><?= rupiah(pesananDanaPenjual($pesanan)) ?></strong></td>
                </tr>
              </tbody>
            </table>
            <p>
              <small>
                Komisi <?= rupiah((int) $pesanan["komisi"]) ?> dihitung 5% dari subtotal,
                dibulatkan ke atas per Rp100. Ongkir dan biaya layanan tidak masuk ke penjual.
              </small>
            </p>
            <p>
              <small>
                <em>
                  <?php if ($pesanan["status"] === "selesai"): ?>
                    Dana sudah diteruskan ke penjual. Transaksi selesai.
                  <?php elseif ($pesanan["status"] === "dibatalkan"): ?>
                    Dana dikembalikan ke pembeli.
                  <?php else: ?>
                    Dana masih <strong>ditahan Narahika</strong> sampai pembeli konfirmasi barang diterima.
                  <?php endif; ?>
                </em>
              </small>
            </p>
          </section>
        <?php endif; ?>

        <hr />

        <!-- BARANG & ALAMAT -->
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
              <?php foreach ($pesanan["items"] as $baris): ?>
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

        <section>
          <h3>Alamat Pengiriman</h3>
          <p>
            <strong><?= htmlspecialchars($pesanan["alamat"]["nama"] ?? "") ?></strong><br />
            <?= htmlspecialchars($pesanan["alamat"]["no_hp"] ?? "") ?><br />
            <?= htmlspecialchars($pesanan["alamat"]["alamat"] ?? "") ?><br />
            <?= htmlspecialchars(($pesanan["alamat"]["kecamatan"] ?? "") . ", " . ($pesanan["alamat"]["kota"] ?? "")) ?><br />
            <?= htmlspecialchars($pesanan["alamat"]["provinsi"] ?? "") ?>
            <?= ($pesanan["alamat"]["kode_pos"] ?? "") !== "" ? htmlspecialchars($pesanan["alamat"]["kode_pos"]) : "" ?>
          </p>
          <p>
            <small>
              Estimasi tiba:
              <?= estimasiKirim($pesanan["asal"], $pesanan["alamat"]["provinsi"] ?? "", $pesanan["kurir"]) ?>
            </small>
          </p>
        </section>

        <hr />

        <!-- AKSI -->
        <section>
          <h3>Aksi</h3>

          <?php if ($pesanan["status"] === "menunggu_pembayaran"): ?>
            <p>
              <a href="pembayaran.php"><button type="button">Bayar Sekarang</button></a>
            </p>
          <?php elseif (in_array($pesanan["status"], ["dibayar", "diproses"], true)): ?>
            <form action="status_pesanan.php?nomor=<?= urlencode($pesanan["nomor"]) ?>" method="POST">
              <input type="hidden" name="aksi" value="kirim" />
              <button type="submit">Tandai Sudah Dikirim</button>
            </form>
            <p><small>Tombol ini nanti khusus penjual.</small></p>

            <form action="status_pesanan.php?nomor=<?= urlencode($pesanan["nomor"]) ?>" method="POST">
              <input type="hidden" name="aksi" value="batal" />
              <button type="submit">Batalkan Pesanan</button>
            </form>
            <p><small>Pembatalan mengembalikan dana ke pembeli (simulasi).</small></p>
          <?php elseif ($pesanan["status"] === "dikirim"): ?>
            <form action="status_pesanan.php?nomor=<?= urlencode($pesanan["nomor"]) ?>" method="POST">
              <input type="hidden" name="aksi" value="terima" />
              <button type="submit">Barang Diterima</button>
            </form>
            <p>
              <small>
                Tekan setelah barang benar-benar sampai. Dana akan diteruskan ke penjual.
                Tombol ini nanti khusus pembeli.
              </small>
            </p>
          <?php elseif ($pesanan["status"] === "selesai"): ?>
            <p><strong>Terima kasih!</strong> Pesanan selesai dan dana sudah diteruskan ke penjual.</p>
            <p><a href="../produk/katalog.php"><button type="button">Belanja Lagi</button></a></p>
          <?php else: ?>
            <p>Pesanan sudah dibatalkan. Tidak ada aksi lain.</p>
            <p><a href="../produk/katalog.php"><button type="button">Lihat Katalog</button></a></p>
          <?php endif; ?>

          <p>
            <small>
              <em>
                Di demo ini satu akun bisa mencoba tombol penjual dan pembeli.
                Di produksi tombol muncul sesuai peran masing-masing.
              </em>
            </small>
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
