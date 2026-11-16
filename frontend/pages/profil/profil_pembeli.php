<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

// Data sementara, nanti diambil dari database
$profil = [
    "nama"           => $_SESSION["nama"] ?? "",
    "kota"           => "Bandung, Jawa Barat",
    "bergabung"      => "14 November 2026",
    "alamat"         => "Jl. Contoh No. 12, Sukajadi, Kota Bandung, Jawa Barat 40162",
    "bio"            => "Suka baju vintage dan sering belanja preloved.",
    "total_beli"     => 8,
    "pesanan_aktif"  => 2,
    "total_ulasan"   => 5,
    "rating_diberi"  => 4.6,
];

$ulasanDiberikan = [
    [
        "toko"    => "Narahika Vintage",
        "produk"  => "Jaket Denim Levi's Original",
        "tanggal" => "10 November 2026",
        "bintang" => 5,
        "isi"     => "Barang sesuai deskripsi, wangi, dan dikirim cepat. Recommended!",
    ],
    [
        "toko"    => "Rafif Thrift",
        "produk"  => "Kemeja Flanel Uniqlo M",
        "tanggal" => "2 November 2026",
        "bintang" => 4,
        "isi"     => "Sesuai foto, cuma packing agak lama. Overall oke.",
    ],
    [
        "toko"    => "Hildan Store",
        "produk"  => "Tas Kanvas Eiger",
        "tanggal" => "28 Oktober 2026",
        "bintang" => 5,
        "isi"     => "Murah, kondisi masih bagus, penjual ramah.",
    ],
];
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profil Pembeli - Narahika Preloved Store</title>
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
      <?php if (!$sudahLogin): ?>
        <h2>Profil Pembeli</h2>
        <section>
          <h3>Masuk Dulu, Yuk</h3>
          <p>Silakan masuk untuk melihat profil, riwayat, dan ulasan kamu.</p>
          <a href="../auth/form_masuk.php"><button type="button">Masuk</button></a>
          <a href="../auth/form_daftar.php"><button type="button">Daftar</button></a>
        </section>
      <?php else: ?>
        <!-- KARTU PROFIL -->
        <section>
          <p><em>[Foto profil]</em></p>
          <h2><?= htmlspecialchars($profil["nama"]) ?></h2>
          <p>
            <strong>Pembeli</strong> &middot;
            <?= htmlspecialchars($profil["kota"]) ?>
          </p>
          <p>Bergabung sejak <?= htmlspecialchars($profil["bergabung"]) ?></p>
          <p><?= htmlspecialchars($profil["bio"]) ?></p>

          <p>
            <a href="edit_profil.php"><button type="button">Edit Profil</button></a>
            <a href="riwayat.php?peran=pembeli"><button type="button">Riwayat Pembelian</button></a>
          </p>
        </section>

        <hr />

        <!-- STATISTIK -->
        <section>
          <h3>Ringkasan Aktivitas</h3>

          <table border="1" cellpadding="8" cellspacing="0">
            <tr>
              <th>Total Pembelian</th>
              <th>Pesanan Aktif</th>
              <th>Ulasan Diberikan</th>
              <th>Rata-rata Rating Diberi</th>
            </tr>
            <tr>
              <td><?= (int) $profil["total_beli"] ?> transaksi</td>
              <td><?= (int) $profil["pesanan_aktif"] ?> pesanan</td>
              <td><?= (int) $profil["total_ulasan"] ?> ulasan</td>
              <td><?= htmlspecialchars(number_format($profil["rating_diberi"], 1)) ?> / 5</td>
            </tr>
          </table>
        </section>

        <hr />

        <!-- ALAMAT -->
        <section>
          <h3>Alamat Pengiriman Utama</h3>
          <p><?= htmlspecialchars($profil["alamat"]) ?></p>
          <p><a href="edit_profil.php">Ubah alamat</a></p>
        </section>

        <hr />

        <!-- ULASAN YANG DIBERIKAN -->
        <section>
          <h3>Ulasan yang Saya Berikan</h3>

          <?php if (count($ulasanDiberikan) === 0): ?>
            <p><em>Kamu belum pernah memberi ulasan.</em></p>
          <?php else: ?>
            <table border="1" cellpadding="8" cellspacing="0">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Toko</th>
                  <th>Produk</th>
                  <th>Rating</th>
                  <th>Ulasan</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($ulasanDiberikan as $ulasan): ?>
                  <tr>
                    <td><?= htmlspecialchars($ulasan["tanggal"]) ?></td>
                    <td><?= htmlspecialchars($ulasan["toko"]) ?></td>
                    <td><?= htmlspecialchars($ulasan["produk"]) ?></td>
                    <td>
                      <?= str_repeat("&#9733;", (int) $ulasan["bintang"]) ?>
                      <?= str_repeat("&#9734;", 5 - (int) $ulasan["bintang"]) ?>
                      <small>(<?= (int) $ulasan["bintang"] ?>/5)</small>
                    </td>
                    <td><?= htmlspecialchars($ulasan["isi"]) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>

          <p><a href="rating.php">Beri rating untuk pesanan yang sudah selesai</a></p>
        </section>
      <?php endif; ?>
    </main>

    <hr />

    <footer>
      <p>&copy; 2026 Narahika Preloved Store</p>
    </footer>
  </body>
</html>
