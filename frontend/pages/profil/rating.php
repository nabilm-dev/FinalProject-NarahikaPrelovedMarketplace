<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

// Pesanan yang sudah selesai dan bisa dinilai (sementara hardcode)
$pesananSelesai = [
    "NRH-20261112-001" => "Jaket Denim Levi's Original &middot; Narahika Vintage &middot; Rp205.000",
    "NRH-20261022-018" => "Kemeja Flanel Uniqlo M &middot; Rafif Thrift &middot; Rp110.000",
];

$kodeTerpilih = $_GET["kode"] ?? "";
if (!array_key_exists($kodeTerpilih, $pesananSelesai)) {
    $kodeTerpilih = "";
}

$aspekPenilaian = [
    "kesesuaian"  => "Kesesuaian dengan deskripsi",
    "komunikasi"  => "Komunikasi penjual",
    "pengiriman"  => "Kecepatan pengiriman",
    "kemasan"     => "Kerapian kemasan",
];

// Riwayat rating yang sudah pernah dikirim (sementara hardcode)
$ratingTerkirim = [
    [
        "tanggal" => "10 November 2026",
        "produk"  => "Kemeja Flanel Uniqlo M",
        "toko"    => "Rafif Thrift",
        "bintang" => 4,
        "ulasan"  => "Sesuai foto, cuma packing agak lama. Overall oke.",
    ],
    [
        "tanggal" => "28 Oktober 2026",
        "produk"  => "Tas Kanvas Eiger",
        "toko"    => "Hildan Store",
        "bintang" => 5,
        "ulasan"  => "Murah, kondisi masih bagus, penjual ramah.",
    ],
];
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Beri Rating - Narahika Preloved Store</title>
  </head>
  <body>
    <header>
      <nav>
        <strong>Narahika</strong> |
        <a href="../../dashboard.php">Dashboard</a> |
        <?php if ($adalahPenjual): ?>
          <a href="../produk/posting_barang.php">Jual Barang</a> |
        <?php endif; ?>
        <?php if ($sudahLogin): ?>
          <a href="profil_<?= $adalahPenjual ? "penjual" : "pembeli" ?>.php">Profil</a> |
          <a href="riwayat.php">Riwayat</a> |
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
      <h2>Beri Rating &amp; Ulasan</h2>
      <p>Penilaianmu membantu pembeli lain dan membuat penjual yang baik makin dipercaya.</p>

      <?php if (!$sudahLogin): ?>
        <section>
          <h3>Masuk Dulu, Yuk</h3>
          <p>Kamu harus masuk untuk memberi rating pada pesanan.</p>
          <a href="../auth/form_masuk.php"><button type="button">Masuk</button></a>
          <a href="../auth/form_daftar.php"><button type="button">Daftar</button></a>
        </section>
      <?php else: ?>
        <form action="" method="POST" enctype="multipart/form-data">
          <!-- PILIH PESANAN -->
          <section>
            <h3>Pesanan yang Dinilai</h3>

            <div>
              <label for="kode_pesanan">Pilih Pesanan:</label><br />
              <select id="kode_pesanan" name="kode_pesanan" required>
                <option value="">-- Pilih Pesanan Selesai --</option>
                <?php foreach ($pesananSelesai as $kode => $keterangan): ?>
                  <option value="<?= htmlspecialchars($kode) ?>" <?= $kodeTerpilih === $kode ? "selected" : "" ?>>
                    <?= $kode ?> &ndash; <?= $keterangan ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <p><small>Hanya pesanan berstatus selesai yang bisa dinilai.</small></p>
            </div>

            <?php if ($kodeTerpilih !== ""): ?>
              <p><small>Kamu akan menilai pesanan <strong><?= htmlspecialchars($kodeTerpilih) ?></strong>.</small></p>
            <?php endif; ?>
          </section>

          <hr />

          <!-- RATING BINTANG -->
          <section>
            <h3>Rating Keseluruhan</h3>

            <?php for ($bintang = 5; $bintang >= 1; $bintang--): ?>
              <div>
                <input
                  type="radio"
                  id="bintang_<?= $bintang ?>"
                  name="bintang"
                  value="<?= $bintang ?>"
                  <?= $bintang === 5 ? "required" : "" ?>
                />
                <label for="bintang_<?= $bintang ?>">
                  <?= str_repeat("&#9733;", $bintang) ?>
                  <?= str_repeat("&#9734;", 5 - $bintang) ?>
                  <small>(<?= $bintang ?>/5)</small>
                </label>
              </div>
            <?php endfor; ?>
          </section>

          <hr />

          <!-- ASPEK PENILAIAN -->
          <section>
            <h3>Penilaian Detail</h3>

            <?php foreach ($aspekPenilaian as $name => $label): ?>
              <div>
                <label for="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($label) ?>:</label><br />
                <select id="<?= htmlspecialchars($name) ?>" name="aspek[<?= htmlspecialchars($name) ?>]">
                  <option value="">-- Pilih Nilai --</option>
                  <option value="5">5 &ndash; Sangat Baik</option>
                  <option value="4">4 &ndash; Baik</option>
                  <option value="3">3 &ndash; Cukup</option>
                  <option value="2">2 &ndash; Kurang</option>
                  <option value="1">1 &ndash; Sangat Kurang</option>
                </select>
              </div>
            <?php endforeach; ?>
          </section>

          <hr />

          <!-- ULASAN -->
          <section>
            <h3>Ulasan Tertulis</h3>

            <div>
              <label for="ulasan">Ceritakan Pengalamanmu:</label><br />
              <textarea
                id="ulasan"
                name="ulasan"
                rows="5"
                cols="55"
                minlength="20"
                maxlength="1000"
                placeholder="Contoh: Barang sesuai deskripsi, wangi, dan dikirim cepat."
                required
              ></textarea>
              <p><small>Minimal 20 karakter, maksimal 1000 karakter.</small></p>
            </div>

            <div>
              <label for="foto_ulasan">Tambah Foto (opsional):</label><br />
              <input
                type="file"
                id="foto_ulasan"
                name="foto_ulasan[]"
                accept="image/jpeg, image/png, image/webp"
                multiple
              />
              <p><small>Maksimal 3 foto, 5 MB per foto.</small></p>
            </div>

            <div>
              <input type="checkbox" id="anonim" name="anonim" value="1" />
              <label for="anonim">Tampilkan ulasan sebagai anonim</label>
            </div>
          </section>

          <hr />

          <div>
            <button type="submit" name="aksi" value="kirim">Kirim Rating</button>
            <a href="riwayat.php?peran=pembeli"><button type="button">Batal</button></a>
          </div>

          <p><small>Rating yang sudah dikirim tidak bisa diubah. Pastikan ulasanmu sudah benar.</small></p>
        </form>

        <hr />

        <!-- RATING YANG SUDAH DIKIRIM -->
        <section>
          <h3>Rating yang Sudah Saya Kirim</h3>

          <?php if (count($ratingTerkirim) === 0): ?>
            <p><em>Belum ada rating yang kamu kirim.</em></p>
          <?php else: ?>
            <table border="1" cellpadding="8" cellspacing="0">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Produk</th>
                  <th>Toko</th>
                  <th>Rating</th>
                  <th>Ulasan</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($ratingTerkirim as $rating): ?>
                  <tr>
                    <td><?= htmlspecialchars($rating["tanggal"]) ?></td>
                    <td><?= htmlspecialchars($rating["produk"]) ?></td>
                    <td><?= htmlspecialchars($rating["toko"]) ?></td>
                    <td>
                      <?= str_repeat("&#9733;", (int) $rating["bintang"]) ?>
                      <?= str_repeat("&#9734;", 5 - (int) $rating["bintang"]) ?>
                    </td>
                    <td><?= htmlspecialchars($rating["ulasan"]) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </section>
      <?php endif; ?>
    </main>

    <hr />

    <footer>
      <p>&copy; 2026 Narahika Preloved Store</p>
    </footer>
  </body>
</html>
