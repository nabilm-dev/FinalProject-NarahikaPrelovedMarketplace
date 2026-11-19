<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

$semuaProduk = require __DIR__ . "/../../data/produk.php";

// --- Aksi wishlist (POST, lalu redirect biar refresh tidak mengulang) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["aksi"] ?? "") === "wishlist") {
    toggleWishlist((int) ($_POST["id"] ?? 0));
    header("Location: " . $_SERVER["REQUEST_URI"]);
    exit;
}

$jumlahWishlist = count($_SESSION["wishlist"] ?? []);

// --- Filter dari URL ---
$filter = [
    "q"         => trim($_GET["q"] ?? ""),
    "kategori"  => $_GET["kategori"] ?? "",
    "kondisi"   => $_GET["kondisi"] ?? "",
    "ukuran"    => $_GET["ukuran"] ?? "",
    "harga_min" => $_GET["harga_min"] ?? "",
    "harga_max" => $_GET["harga_max"] ?? "",
];

$daftarKategori = ["Atasan", "Bawahan", "Dress", "Outerwear", "Sepatu", "Tas", "Aksesoris", "Lainnya"];
$daftarKondisi  = ["Like New", "Bekas Baik", "Ada Minus"];
$daftarUkuran   = ["XS", "S", "M", "L", "XL", "XXL", "All Size", "32", "41", "42"];

$daftarUrutan = [
    "terbaru"  => "Terbaru",
    "termurah" => "Harga Terendah",
    "termahal" => "Harga Tertinggi",
    "rating"   => "Rating Toko Tertinggi",
    "nama"     => "Nama A-Z",
];

$urutan = $_GET["urutan"] ?? "terbaru";
if (!array_key_exists($urutan, $daftarUrutan)) {
    $urutan = "terbaru";
}

// Whitelist nilai filter supaya tidak ada nilai aneh dari URL
if ($filter["kategori"] !== "" && !in_array($filter["kategori"], $daftarKategori, true)) {
    $filter["kategori"] = "";
}
if ($filter["kondisi"] !== "" && !in_array($filter["kondisi"], $daftarKondisi, true)) {
    $filter["kondisi"] = "";
}
if ($filter["ukuran"] !== "" && !in_array($filter["ukuran"], $daftarUkuran, true)) {
    $filter["ukuran"] = "";
}

$hasil = filterProduk($semuaProduk, $filter);
$hasil = urutkanProduk($hasil, $urutan);

// --- Pagination ---
$perHalaman = 6;
$totalHasil = count($hasil);
$totalHalaman = max(1, (int) ceil($totalHasil / $perHalaman));
$halaman = (int) ($_GET["hal"] ?? 1);
if ($halaman < 1) {
    $halaman = 1;
}
if ($halaman > $totalHalaman) {
    $halaman = $totalHalaman;
}
$mulai = ($halaman - 1) * $perHalaman;
$produkTampil = array_slice($hasil, $mulai, $perHalaman);

// Bantu bikin URL yang mempertahankan filter
function buatUrl(array $perubahan = []): string
{
    $params = array_merge($_GET, $perubahan);
    $params = array_filter($params, function ($nilai) {
        return $nilai !== "" && $nilai !== null;
    });
    return "katalog.php" . ($params ? "?" . http_build_query($params) : "");
}

$adaFilter = $filter["q"] !== "" || $filter["kategori"] !== "" || $filter["kondisi"] !== ""
    || $filter["ukuran"] !== "" || $filter["harga_min"] !== "" || $filter["harga_max"] !== "";
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Katalog Produk - Narahika Preloved Store</title>
  </head>
  <body>
    <header>
      <nav>
        <strong>Narahika</strong> |
        <a href="../../dashboard.php">Dashboard</a> |
        <a href="katalog.php">Katalog</a> |
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
      <h2>Katalog Produk</h2>
      <p>Cari barang preloved yang masih layak pakai, dari penjual terverifikasi.</p>

      <!-- PENCARIAN -->
      <form action="katalog.php" method="GET">
        <div>
          <label for="q">Cari barang:</label><br />
          <input
            type="search"
            id="q"
            name="q"
            value="<?= htmlspecialchars($filter["q"]) ?>"
            placeholder="Contoh: jaket denim, flanel, sepatu"
            maxlength="100"
          />
          <button type="submit">Cari</button>
        </div>

        <hr />

        <!-- FILTER -->
        <fieldset>
          <legend>Filter</legend>

          <div>
            <label for="kategori">Kategori:</label>
            <select id="kategori" name="kategori">
              <option value="">Semua Kategori</option>
              <?php foreach ($daftarKategori as $kategori): ?>
                <option value="<?= htmlspecialchars($kategori) ?>" <?= $filter["kategori"] === $kategori ? "selected" : "" ?>>
                  <?= htmlspecialchars($kategori) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="kondisi">Kondisi:</label>
            <select id="kondisi" name="kondisi">
              <option value="">Semua Kondisi</option>
              <?php foreach ($daftarKondisi as $kondisi): ?>
                <option value="<?= htmlspecialchars($kondisi) ?>" <?= $filter["kondisi"] === $kondisi ? "selected" : "" ?>>
                  <?= htmlspecialchars($kondisi) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="ukuran">Ukuran:</label>
            <select id="ukuran" name="ukuran">
              <option value="">Semua Ukuran</option>
              <?php foreach ($daftarUkuran as $ukuran): ?>
                <option value="<?= htmlspecialchars($ukuran) ?>" <?= $filter["ukuran"] === $ukuran ? "selected" : "" ?>>
                  <?= htmlspecialchars($ukuran) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="harga_min">Harga Minimal (Rp):</label>
            <input
              type="number"
              id="harga_min"
              name="harga_min"
              value="<?= htmlspecialchars((string) $filter["harga_min"]) ?>"
              min="0"
              step="10000"
              placeholder="0"
            />
          </div>

          <div>
            <label for="harga_max">Harga Maksimal (Rp):</label>
            <input
              type="number"
              id="harga_max"
              name="harga_max"
              value="<?= htmlspecialchars((string) $filter["harga_max"]) ?>"
              min="0"
              step="10000"
              placeholder="500000"
            />
          </div>

          <div>
            <label for="urutan">Urutkan:</label>
            <select id="urutan" name="urutan">
              <?php foreach ($daftarUrutan as $nilai => $label): ?>
                <option value="<?= htmlspecialchars($nilai) ?>" <?= $urutan === $nilai ? "selected" : "" ?>>
                  <?= htmlspecialchars($label) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <button type="submit">Terapkan</button>
            <?php if ($adaFilter || $urutan !== "terbaru"): ?>
              <a href="katalog.php">Reset Filter</a>
            <?php endif; ?>
          </div>
        </fieldset>
      </form>

      <hr />

      <!-- HASIL -->
      <p>
        <small>
          Menampilkan <strong><?= $totalHasil ?></strong> produk
          <?php if ($adaFilter): ?>untuk pencarian &ldquo;<?= htmlspecialchars($filter["q"]) ?>&rdquo;<?php endif; ?>
          &middot; halaman <?= $halaman ?> dari <?= $totalHalaman ?>
        </small>
      </p>

      <?php if ($totalHasil === 0): ?>
        <?php if ($adaFilter): ?>
          <section>
            <h3>Produk Tidak Ditemukan</h3>
            <p>Coba kata kunci lain, atau longgarkan filter harga dan kategori.</p>
            <a href="katalog.php"><button type="button">Lihat Semua Produk</button></a>
          </section>
        <?php else: ?>
          <section>
            <h3>Katalog Masih Kosong</h3>
            <p>Belum ada barang yang diposting. Produk akan muncul di sini setelah penjual mengunggahnya.</p>

            <?php if ($adalahPenjual): ?>
              <a href="posting_barang.php"><button type="button">+ Posting Barang Pertama</button></a>
            <?php elseif ($sudahLogin): ?>
              <p><small>Kamu pembeli. Mau mulai jualan juga?</small></p>
              <a href="../seller/jadi_penjual.php"><button type="button">Jadi Penjual</button></a>
            <?php else: ?>
              <a href="../auth/form_masuk.php"><button type="button">Masuk</button></a>
              <a href="../auth/form_daftar.php"><button type="button">Daftar</button></a>
            <?php endif; ?>
          </section>
        <?php endif; ?>
      <?php else: ?>
        <?php foreach ($produkTampil as $produk): ?>
          <article>
            <p><em>[Foto produk <?= (int) $produk["foto"] ?> buah]</em></p>

            <h3>
              <a href="detail_produk.php?id=<?= (int) $produk["id"] ?>">
                <?= htmlspecialchars($produk["judul"]) ?>
              </a>
            </h3>

            <p>
              <strong><?= rupiah((int) $produk["harga"]) ?></strong>
              <?php if ($produk["bisa_nego"]): ?>
                <small>&middot; Bisa nego</small>
              <?php endif; ?>
            </p>

            <p>
              <small>
                <?= htmlspecialchars($produk["kategori"]) ?> &middot;
                Ukuran <?= htmlspecialchars($produk["ukuran"]) ?> &middot;
                <?= htmlspecialchars($produk["kondisi"]) ?>
              </small>
            </p>

            <p><small><?= htmlspecialchars($produk["lokasi"]) ?></small></p>

            <p>
              <small>
                <?= htmlspecialchars($produk["toko"]) ?> &middot;
                &#9733; <?= htmlspecialchars(number_format((float) $produk["rating_toko"], 1)) ?>
                (<?= (int) $produk["ulasan_toko"] ?>)
              </small>
            </p>

            <div>
              <a href="detail_produk.php?id=<?= (int) $produk["id"] ?>">
                <button type="button">Lihat Detail</button>
              </a>

              <form action="<?= htmlspecialchars(buatUrl()) ?>" method="POST">
                <input type="hidden" name="aksi" value="wishlist" />
                <input type="hidden" name="id" value="<?= (int) $produk["id"] ?>" />
                <?php if (adaDiWishlist((int) $produk["id"])): ?>
                  <button type="submit">&#9829; Tersimpan</button>
                <?php else: ?>
                  <button type="submit">&#9825; Simpan</button>
                <?php endif; ?>
              </form>
            </div>
          </article>
          <hr />
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- PAGINATION -->
      <?php if ($totalHalaman > 1): ?>
        <nav>
          <?php if ($halaman > 1): ?>
            <a href="<?= htmlspecialchars(buatUrl(["hal" => $halaman - 1])) ?>">
              <button type="button">&laquo; Sebelumnya</button>
            </a>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
            <?php if ($i === $halaman): ?>
              <button type="button" disabled><?= $i ?></button>
            <?php else: ?>
              <a href="<?= htmlspecialchars(buatUrl(["hal" => $i])) ?>">
                <button type="button"><?= $i ?></button>
              </a>
            <?php endif; ?>
          <?php endfor; ?>

          <?php if ($halaman < $totalHalaman): ?>
            <a href="<?= htmlspecialchars(buatUrl(["hal" => $halaman + 1])) ?>">
              <button type="button">Berikutnya &raquo;</button>
            </a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    </main>

    <hr />

    <footer>
      <p>&copy; 2026 Narahika Preloved Store</p>
    </footer>
  </body>
</html>
