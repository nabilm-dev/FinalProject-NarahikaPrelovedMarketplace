<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

// Data toko sementara, nanti diambil dari database berdasarkan id penjual
$toko = [
    "id"              => 1,
    "nama_toko"       => "Narahika Vintage",
    "pemilik"         => "Narahika",
    "lokasi"          => "Bandung, Jawa Barat",
    "bergabung"       => "14 November 2026",
    "deskripsi"       => "Jual baju dan aksesoris preloved pilihan. Semua barang dicuci bersih sebelum dikirim.",
    "rating"          => 4.8,
    "jumlah_ulasan"   => 32,
    "transaksi_selesai" => 87,
    "response_rate"   => "95%",
    "terverifikasi"   => true,
    "aktif_terakhir"  => "hari ini",
];

$produkToko = [
    [
        "judul"   => "Jaket Denim Levi's Original",
        "kategori" => "Outerwear",
        "ukuran"  => "M",
        "harga"   => 185000,
        "kondisi" => "Bekas Baik",
        "status"  => "Tersedia",
    ],
    [
        "judul"   => "Kemeja Flanel Uniqlo",
        "kategori" => "Atasan",
        "ukuran"  => "L",
        "harga"   => 95000,
        "kondisi" => "Like New",
        "status"  => "Tersedia",
    ],
    [
        "judul"   => "Tas Kanvas Eiger",
        "kategori" => "Tas",
        "ukuran"  => "All Size",
        "harga"   => 120000,
        "kondisi" => "Bekas Baik",
        "status"  => "Terjual",
    ],
    [
        "judul"   => "Sneakers Vans Old Skool",
        "kategori" => "Sepatu",
        "ukuran"  => "42",
        "harga"   => 275000,
        "kondisi" => "Ada Minus",
        "status"  => "Tersedia",
    ],
];

$ulasanPembeli = [
    [
        "pembeli"  => "Rafif",
        "tanggal"  => "12 November 2026",
        "bintang"  => 5,
        "produk"   => "Jaket Denim Levi's Original",
        "isi"      => "Barang sesuai deskripsi, wangi, dan dikirim cepat. Recommended!",
    ],
    [
        "pembeli"  => "Hildan",
        "tanggal"  => "5 November 2026",
        "bintang"  => 5,
        "produk"   => "Kemeja Flanel Uniqlo",
        "isi"      => "Penjual responsif, packing rapi pakai bubble wrap.",
    ],
    [
        "pembeli"  => "Kaka",
        "tanggal"  => "30 Oktober 2026",
        "bintang"  => 4,
        "produk"   => "Tas Kanvas Eiger",
        "isi"      => "Sesuai foto, ada sedikit noda yang sudah dijelaskan di deskripsi.",
    ],
];
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profil Penjual - Narahika Preloved Store</title>
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
      <!-- HEADER TOKO -->
      <section>
        <p><em>[Foto profil toko]</em></p>
        <h2><?= htmlspecialchars($toko["nama_toko"]) ?></h2>
        <p>
          <?php if ($toko["terverifikasi"]): ?>
            <strong>&#10004; Terverifikasi KTP</strong> &middot;
          <?php endif; ?>
          <?= htmlspecialchars($toko["lokasi"]) ?>
        </p>
        <p>
          <strong><?= htmlspecialchars(number_format($toko["rating"], 1)) ?></strong> / 5
          <?= str_repeat("&#9733;", (int) round($toko["rating"])) ?>
          <?= str_repeat("&#9734;", 5 - (int) round($toko["rating"])) ?>
          <small>(<?= (int) $toko["jumlah_ulasan"] ?> ulasan)</small>
        </p>
        <p>
          <small>
            <?= (int) $toko["transaksi_selesai"] ?> transaksi selesai &middot;
            respons chat <?= htmlspecialchars($toko["response_rate"]) ?> &middot;
            aktif <?= htmlspecialchars($toko["aktif_terakhir"]) ?>
          </small>
        </p>
        <p><small>Bergabung sejak <?= htmlspecialchars($toko["bergabung"]) ?></small></p>

        <p>
          <button type="button" disabled>Chat Penjual</button>
          <button type="button" disabled>Laporkan Toko</button>
          <?php if ($adalahPenjual): ?>
            <a href="edit_profil.php"><button type="button">Edit Profil Toko</button></a>
          <?php endif; ?>
        </p>
        <p>
          <small><em>Fitur chat dan laporan toko akan tersedia di tahap berikutnya.</em></small>
        </p>
      </section>

      <hr />

      <!-- TENTANG TOKO -->
      <section>
        <h3>Tentang Toko</h3>
        <p><?= htmlspecialchars($toko["deskripsi"]) ?></p>
      </section>

      <hr />

      <!-- RINGKASAN PENILAIAN -->
      <section>
        <h3>Penilaian Pembeli</h3>

        <table border="1" cellpadding="8" cellspacing="0">
          <tr>
            <th>Rata-rata Rating</th>
            <th>Jumlah Ulasan</th>
            <th>Transaksi Selesai</th>
            <th>Respons Chat</th>
          </tr>
          <tr>
            <td><?= htmlspecialchars(number_format($toko["rating"], 1)) ?> / 5</td>
            <td><?= (int) $toko["jumlah_ulasan"] ?></td>
            <td><?= (int) $toko["transaksi_selesai"] ?></td>
            <td><?= htmlspecialchars($toko["response_rate"]) ?></td>
          </tr>
        </table>
      </section>

      <hr />

      <!-- PRODUK TOKO -->
      <section>
        <h3>Produk Toko (<?= count($produkToko) ?>)</h3>

        <table border="1" cellpadding="8" cellspacing="0">
          <thead>
            <tr>
              <th>Barang</th>
              <th>Kategori</th>
              <th>Ukuran</th>
              <th>Kondisi</th>
              <th>Harga</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($produkToko as $produk): ?>
              <tr>
                <td><?= htmlspecialchars($produk["judul"]) ?></td>
                <td><?= htmlspecialchars($produk["kategori"]) ?></td>
                <td><?= htmlspecialchars($produk["ukuran"]) ?></td>
                <td><?= htmlspecialchars($produk["kondisi"]) ?></td>
                <td>Rp<?= htmlspecialchars(number_format($produk["harga"], 0, ",", ".")) ?></td>
                <td><?= htmlspecialchars($produk["status"]) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

      <hr />

      <!-- ULASAN -->
      <section>
        <h3>Ulasan Pembeli</h3>

        <?php if (count($ulasanPembeli) === 0): ?>
          <p><em>Toko ini belum punya ulasan.</em></p>
        <?php else: ?>
          <?php foreach ($ulasanPembeli as $ulasan): ?>
            <article>
              <p>
                <strong><?= htmlspecialchars($ulasan["pembeli"]) ?></strong>
                <small>&middot; <?= htmlspecialchars($ulasan["tanggal"]) ?></small>
              </p>
              <p>
                <?= str_repeat("&#9733;", (int) $ulasan["bintang"]) ?>
                <?= str_repeat("&#9734;", 5 - (int) $ulasan["bintang"]) ?>
                <small>(<?= (int) $ulasan["bintang"] ?>/5)</small>
              </p>
              <p><small>Produk: <?= htmlspecialchars($ulasan["produk"]) ?></small></p>
              <p><?= htmlspecialchars($ulasan["isi"]) ?></p>
            </article>
            <hr />
          <?php endforeach; ?>
        <?php endif; ?>
      </section>
    </main>

    <hr />

    <footer>
      <p>&copy; 2026 Narahika Preloved Store</p>
    </footer>
  </body>
</html>
