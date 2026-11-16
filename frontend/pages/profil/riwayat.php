<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

$peran = $_GET["peran"] ?? ($adalahPenjual ? "penjual" : "pembeli");
if (!in_array($peran, ["pembeli", "penjual"], true)) {
    $peran = "pembeli";
}

// Data sementara, nanti diambil dari database
$riwayatPembelian = [
    [
        "kode"    => "NRH-20261112-001",
        "tanggal" => "12 November 2026",
        "barang"  => "Jaket Denim Levi's Original",
        "lawan"   => "Narahika Vintage",
        "total"   => 205000,
        "status"  => "Selesai",
        "bisa_rating" => true,
    ],
    [
        "kode"    => "NRH-20261109-014",
        "tanggal" => "9 November 2026",
        "barang"  => "Kemeja Flanel Uniqlo M",
        "lawan"   => "Rafif Thrift",
        "total"   => 110000,
        "status"  => "Dikirim",
        "bisa_rating" => false,
    ],
    [
        "kode"    => "NRH-20261103-007",
        "tanggal" => "3 November 2026",
        "barang"  => "Tas Kanvas Eiger",
        "lawan"   => "Hildan Store",
        "total"   => 135000,
        "status"  => "Menunggu Pembayaran",
        "bisa_rating" => false,
    ],
    [
        "kode"    => "NRH-20261028-003",
        "tanggal" => "28 Oktober 2026",
        "barang"  => "Sneakers Vans Old Skool",
        "lawan"   => "Narahika Vintage",
        "total"   => 290000,
        "status"  => "Dibatalkan",
        "bisa_rating" => false,
    ],
];

$riwayatPenjualan = [
    [
        "kode"    => "NRH-20261111-021",
        "tanggal" => "11 November 2026",
        "barang"  => "Kemeja Flanel Uniqlo",
        "lawan"   => "Kaka",
        "total"   => 95000,
        "status"  => "Perlu Dikirim",
    ],
    [
        "kode"    => "NRH-20261107-009",
        "tanggal" => "7 November 2026",
        "barang"  => "Jaket Denim Levi's Original",
        "lawan"   => "Rafif",
        "total"   => 185000,
        "status"  => "Selesai",
    ],
    [
        "kode"    => "NRH-20261101-002",
        "tanggal" => "1 November 2026",
        "barang"  => "Tas Kanvas Eiger",
        "lawan"   => "Hildan",
        "total"   => 120000,
        "status"  => "Sengketa",
    ],
];

$daftarStatus = [
    "semua"              => "Semua Status",
    "menunggu_pembayaran" => "Menunggu Pembayaran",
    "perlu_dikirim"      => "Perlu Dikirim",
    "diproses"           => "Diproses",
    "dikirim"            => "Dikirim",
    "selesai"            => "Selesai",
    "dibatalkan"         => "Dibatalkan",
    "sengketa"           => "Sengketa",
];

$filterStatus = $_GET["status"] ?? "semua";
if (!array_key_exists($filterStatus, $daftarStatus)) {
    $filterStatus = "semua";
}

// Filter sederhana berdasarkan label status
function cocokStatus(string $statusPesanan, string $filter): bool
{
    if ($filter === "semua") {
        return true;
    }
    return strtolower(str_replace(" ", "_", $statusPesanan)) === $filter;
}

$dataTampil = $peran === "penjual" ? $riwayatPenjualan : $riwayatPembelian;
$dataFilter = [];
foreach ($dataTampil as $item) {
    if (cocokStatus($item["status"], $filterStatus)) {
        $dataFilter[] = $item;
    }
}
$dataTampil = $dataFilter;
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Riwayat Transaksi - Narahika Preloved Store</title>
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
      <h2>Riwayat Transaksi</h2>

      <?php if (!$sudahLogin): ?>
        <section>
          <h3>Masuk Dulu, Yuk</h3>
          <p>Riwayat transaksi hanya bisa dilihat setelah kamu masuk ke akun.</p>
          <a href="../auth/form_masuk.php"><button type="button">Masuk</button></a>
          <a href="../auth/form_daftar.php"><button type="button">Daftar</button></a>
        </section>
      <?php else: ?>
        <!-- TAB -->
        <nav>
          <a href="riwayat.php?peran=pembeli">
            <button type="button" <?= $peran === "pembeli" ? "disabled" : "" ?>>
              Pembelian Saya
            </button>
          </a>
          <a href="riwayat.php?peran=penjual">
            <button type="button" <?= $peran === "penjual" ? "disabled" : "" ?>>
              Penjualan Saya
            </button>
          </a>
        </nav>

        <p>
          <small>
            Menampilkan
            <strong><?= $peran === "penjual" ? "pesanan masuk sebagai penjual" : "pesanan kamu sebagai pembeli" ?></strong>.
          </small>
        </p>

        <hr />

        <!-- FILTER -->
        <form action="riwayat.php" method="GET">
          <input type="hidden" name="peran" value="<?= htmlspecialchars($peran) ?>" />

          <label for="status">Filter Status:</label>
          <select id="status" name="status">
            <?php foreach ($daftarStatus as $nilai => $label): ?>
              <option value="<?= htmlspecialchars($nilai) ?>" <?= $filterStatus === $nilai ? "selected" : "" ?>>
                <?= htmlspecialchars($label) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <button type="submit">Terapkan</button>
          <a href="riwayat.php?peran=<?= htmlspecialchars($peran) ?>">Reset</a>
        </form>

        <hr />

        <!-- DAFTAR TRANSAKSI -->
        <?php if (count($dataTampil) === 0): ?>
          <section>
            <p><em>Tidak ada transaksi dengan status tersebut.</em></p>
          </section>
        <?php else: ?>
          <table border="1" cellpadding="8" cellspacing="0">
            <thead>
              <tr>
                <th>Kode Pesanan</th>
                <th>Tanggal</th>
                <th>Barang</th>
                <th><?= $peran === "penjual" ? "Pembeli" : "Penjual" ?></th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dataTampil as $pesanan): ?>
                <tr>
                  <td><?= htmlspecialchars($pesanan["kode"]) ?></td>
                  <td><?= htmlspecialchars($pesanan["tanggal"]) ?></td>
                  <td><?= htmlspecialchars($pesanan["barang"]) ?></td>
                  <td><?= htmlspecialchars($pesanan["lawan"]) ?></td>
                  <td>Rp<?= htmlspecialchars(number_format($pesanan["total"], 0, ",", ".")) ?></td>
                  <td><?= htmlspecialchars($pesanan["status"]) ?></td>
                  <td>
                    <?php if ($peran === "pembeli" && !empty($pesanan["bisa_rating"])): ?>
                      <a href="rating.php?kode=<?= urlencode($pesanan["kode"]) ?>">
                        <button type="button">Beri Rating</button>
                      </a>
                    <?php endif; ?>

                    <?php if ($peran === "penjual" && $pesanan["status"] === "Perlu Dikirim"): ?>
                      <button type="button">Atur Pengiriman</button>
                    <?php endif; ?>

                    <?php if ($pesanan["status"] === "Sengketa"): ?>
                      <button type="button">Lihat Sengketa</button>
                    <?php endif; ?>

                    <button type="button">Lihat Detail</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>

        <hr />

        <p>
          <small>
            <em>
              Tombol detail, pengiriman, dan sengketa masih tampilan saja &ndash;
              akan disambungkan ke backend pada tahap berikutnya.
            </em>
          </small>
        </p>
      <?php endif; ?>
    </main>

    <hr />

    <footer>
      <p>&copy; 2026 Narahika Preloved Store</p>
    </footer>
  </body>
</html>
