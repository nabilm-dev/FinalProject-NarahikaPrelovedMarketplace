<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

require_once __DIR__ . "/../../data/produk.php";
require_once __DIR__ . "/../../data/keranjang.php";

$semuaProduk = daftarProduk();

$id = (int) ($_GET["id"] ?? 0);
$produk = produkById($semuaProduk, $id);

// --- Aksi wishlist / keranjang (POST, lalu redirect) ---
if ($produk !== null && $_SERVER["REQUEST_METHOD"] === "POST") {
    $aksi = $_POST["aksi"] ?? "";

    if ($aksi === "wishlist") {
        toggleWishlist($id);
    } elseif ($aksi === "cart") {
        keranjangTambah($id, (int) ($_POST["qty"] ?? 1));
    }

    header("Location: " . $_SERVER["REQUEST_URI"]);
    exit;
}

$jumlahWishlist  = count($_SESSION["wishlist"] ?? []);
$jumlahKeranjang = keranjangJumlahQty();

// Foto aktif (ilustrasi galeri)
$jumlahFoto = $produk !== null ? max(1, (int) $produk["foto"]) : 1;
$fotoAktif = (int) ($_GET["foto"] ?? 1);
if ($fotoAktif < 1 || $fotoAktif > $jumlahFoto) {
    $fotoAktif = 1;
}

// Produk lain dari toko yang sama
$produkTokoLain = [];
if ($produk !== null) {
    foreach ($semuaProduk as $item) {
        if ($item["toko"] === $produk["toko"] && $item["id"] !== $produk["id"]) {
            $produkTokoLain[] = $item;
        }
    }
    $produkTokoLain = array_slice($produkTokoLain, 0, 3);
}
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $produk !== null ? htmlspecialchars($produk["judul"]) . " - " : "" ?>Detail Produk - Narahika Preloved Store</title>
  </head>
  <body>
    <header>
      <nav>
        <strong>Narahika</strong> |
        <a href="../../dashboard.php">Dashboard</a> |
        <a href="katalog.php">Katalog</a> |
        <a href="../keranjang/keranjang.php">Keranjang (<?= $jumlahKeranjang ?>)</a> |
        <a href="wishlist.php">Wishlist (<?= $jumlahWishlist ?>)</a> |
        <?php if ($adalahPenjual): ?>
          <a href="posting_barang.php">Jual Barang</a> |
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
      <?php if ($produk === null): ?>
        <h2>Produk Tidak Ditemukan</h2>
        <section>
          <p>Barang yang kamu cari mungkin sudah terjual atau tautannya salah.</p>
          <a href="katalog.php"><button type="button">Kembali ke Katalog</button></a>
        </section>
      <?php else: ?>
        <p>
          <small>
            <a href="katalog.php">Katalog</a> &rsaquo;
            <a href="katalog.php?kategori=<?= urlencode($produk["kategori"]) ?>">
              <?= htmlspecialchars($produk["kategori"]) ?>
            </a> &rsaquo;
            <?= htmlspecialchars($produk["judul"]) ?>
          </small>
        </p>

        <!-- GALERI -->
        <section>
          <h2><?= htmlspecialchars($produk["judul"]) ?></h2>

          <p>
            <em>[Foto utama <?= $fotoAktif ?> dari <?= $jumlahFoto ?>]</em>
          </p>

          <div>
            <?php for ($i = 1; $i <= $jumlahFoto; $i++): ?>
              <?php if ($i === $fotoAktif): ?>
                <button type="button" disabled>Foto <?= $i ?></button>
              <?php else: ?>
                <a href="detail_produk.php?id=<?= (int) $produk["id"] ?>&foto=<?= $i ?>">
                  <button type="button">Foto <?= $i ?></button>
                </a>
              <?php endif; ?>
            <?php endfor; ?>
          </div>

          <p><small>Klik foto untuk melihat versi lain. Video belum tersedia.</small></p>
        </section>

        <hr />

        <!-- HARGA & INFO -->
        <section>
          <p>
            <strong><?= rupiah((int) $produk["harga"]) ?></strong>
            <?php if ($produk["bisa_nego"]): ?>
              <small>&middot; Harga bisa dinegosiasi</small>
            <?php else: ?>
              <small>&middot; Harga pas</small>
            <?php endif; ?>
          </p>

          <table border="1" cellpadding="8" cellspacing="0">
            <tr>
              <th>Kategori</th>
              <td><?= htmlspecialchars($produk["kategori"]) ?></td>
            </tr>
            <tr>
              <th>Ukuran</th>
              <td><?= htmlspecialchars($produk["ukuran"]) ?></td>
            </tr>
            <tr>
              <th>Kondisi</th>
              <td><?= htmlspecialchars($produk["kondisi"]) ?></td>
            </tr>
            <tr>
              <th>Berat</th>
              <td><?= (int) $produk["berat"] ?> gram</td>
            </tr>
            <tr>
              <th>Lokasi</th>
              <td><?= htmlspecialchars($produk["lokasi"]) ?></td>
            </tr>
            <tr>
              <th>Metode</th>
              <td><?= htmlspecialchars($produk["metode"]) ?></td>
            </tr>
            <tr>
              <th>Diposting</th>
              <td><?= htmlspecialchars(date("d M Y", strtotime($produk["tanggal"]))) ?></td>
            </tr>
          </table>

          <div>
            <form action="<?= htmlspecialchars($_SERVER["REQUEST_URI"]) ?>" method="POST">
              <input type="hidden" name="aksi" value="wishlist" />
              <input type="hidden" name="id" value="<?= (int) $produk["id"] ?>" />
              <?php if (adaDiWishlist((int) $produk["id"])): ?>
                <button type="submit">&#9829; Tersimpan di Wishlist</button>
              <?php else: ?>
                <button type="submit">&#9825; Simpan ke Wishlist</button>
              <?php endif; ?>
            </form>
          </div>

          <div>
            <form action="<?= htmlspecialchars($_SERVER["REQUEST_URI"]) ?>" method="POST">
              <input type="hidden" name="aksi" value="cart" />
              <input type="hidden" name="id" value="<?= (int) $produk["id"] ?>" />

              <label for="qty">Jumlah:</label>
              <input type="number" id="qty" name="qty" value="1" min="1" max="99" />
              <button type="submit">+ Tambah ke Keranjang</button>
            </form>

            <a href="../keranjang/keranjang.php"><button type="button">Lihat Keranjang</button></a>
          </div>
          <p>
            <small>
              <em>
                Beli Sekarang digabung ke alur keranjang-checkout: tambah ke keranjang,
                lalu lanjut checkout.
              </em>
            </small>
          </p>
        </section>

        <hr />

        <!-- PENJUAL -->
        <section>
          <h3>Penjual</h3>
          <p>
            <strong><?= htmlspecialchars($produk["toko"]) ?></strong><br />
            <small>
              &#9733; <?= htmlspecialchars(number_format((float) $produk["rating_toko"], 1)) ?> / 5
              dari <?= (int) $produk["ulasan_toko"] ?> ulasan
            </small>
          </p>
          <p><small>Lokasi toko: <?= htmlspecialchars($produk["lokasi"]) ?></small></p>

          <p>
            <a href="../profil/profil_penjual.php"><button type="button">Lihat Profil Toko</button></a>
            <button type="button" disabled>Chat Penjual</button>
            <button type="button" disabled>Laporkan Produk</button>
          </p>
          <p><small><em>Chat dan laporan produk masuk di tahap chat-offer &amp; kepercayaan-keamanan.</em></small></p>
        </section>

        <hr />

        <!-- DESKRIPSI -->
        <section>
          <h3>Deskripsi Barang</h3>
          <p><?= nl2br(htmlspecialchars($produk["deskripsi"])) ?></p>
        </section>

        <hr />

        <!-- TIPS AMAN -->
        <section>
          <h3>Transaksi Aman</h3>
          <ul>
            <li>Bayar lewat Narahika supaya dana ditahan sampai barang kamu terima.</li>
            <li>Jangan transfer langsung ke rekening pribadi penjual.</li>
            <li>Cek foto dan deskripsi sebelum membeli; tanya lewat chat kalau kurang jelas.</li>
            <li>Kalau barang tidak sesuai, ajukan retur/sengketa maksimal 2 hari setelah diterima.</li>
          </ul>
        </section>

        <hr />

        <!-- PRODUK LAIN -->
        <?php if (count($produkTokoLain) > 0): ?>
          <section>
            <h3>Produk Lain dari <?= htmlspecialchars($produk["toko"]) ?></h3>

            <?php foreach ($produkTokoLain as $lain): ?>
              <article>
                <p><em>[Foto]</em></p>
                <h4>
                  <a href="detail_produk.php?id=<?= (int) $lain["id"] ?>">
                    <?= htmlspecialchars($lain["judul"]) ?>
                  </a>
                </h4>
                <p><strong><?= rupiah((int) $lain["harga"]) ?></strong></p>
                <p>
                  <small>
                    <?= htmlspecialchars($lain["kategori"]) ?> &middot;
                    Ukuran <?= htmlspecialchars($lain["ukuran"]) ?> &middot;
                    <?= htmlspecialchars($lain["kondisi"]) ?>
                  </small>
                </p>
              </article>
              <hr />
            <?php endforeach; ?>
          </section>
        <?php endif; ?>
      <?php endif; ?>
    </main>

    <hr />

    <footer>
      <p>&copy; 2026 Narahika Preloved Store</p>
    </footer>
  </body>
</html>
