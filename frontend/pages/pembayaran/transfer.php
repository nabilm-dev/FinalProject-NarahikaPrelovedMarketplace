<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

require_once __DIR__ . "/../../data/pesanan.php";
require_once __DIR__ . "/../../data/keranjang.php";

$checkout  = $_SESSION["checkout"] ?? null;
$jumlahNav = keranjangJumlahQty();

// Nomor Virtual Account simulasi, tetap sama kalau halaman di-refresh
function nomorVirtualAccount(string $nomorPesanan, string $bank = "8808"): string
{
    $angka = preg_replace('/[^0-9]/', '', md5($nomorPesanan));
    $angka = str_pad($angka, 12, "0");

    return $bank . " " . substr($angka, 0, 4) . " " . substr($angka, 4, 4) . " " . substr($angka, 8, 4);
}
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Rekening Bersama - Narahika Preloved Store</title>
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
      <?php if ($checkout === null): ?>
        <h2>Rekening Bersama</h2>
        <section>
          <h3>Tidak Ada Tagihan</h3>
          <p>Selesaikan checkout dulu sebelum membayar.</p>
          <a href="../keranjang/keranjang.php"><button type="button">Buka Keranjang</button></a>
        </section>
      <?php else: ?>
        <h2>Rekening Bersama (Escrow)</h2>
        <p>
          <small>
            Pesanan <strong><?= htmlspecialchars($checkout["nomor"]) ?></strong> &middot;
            <a href="pembayaran.php">ganti metode</a>
          </small>
        </p>

        <section>
          <h3>Transfer ke Rekening Narahika</h3>
          <p><strong style="font-size: 22px"><?= rupiah((int) $checkout["total"]) ?></strong></p>

          <table border="1" cellpadding="8" cellspacing="0">
            <tbody>
              <tr>
                <th>Bank</th>
                <td>BCA (Virtual Account)</td>
              </tr>
              <tr>
                <th>Nomor VA</th>
                <td><strong><?= htmlspecialchars(nomorVirtualAccount($checkout["nomor"])) ?></strong></td>
              </tr>
              <tr>
                <th>Atas Nama</th>
                <td>Narahika Escrow</td>
              </tr>
              <tr>
                <th>Nominal</th>
                <td><strong><?= rupiah((int) $checkout["total"]) ?></strong></td>
              </tr>
              <tr>
                <th>Batas Bayar</th>
                <td>24 jam sejak pesanan dibuat (simulasi)</td>
              </tr>
            </tbody>
          </table>

          <p>
            <small>
              Transfer <strong>tepat sesuai nominal</strong>. Dana masuk ke rekening bersama
              Narahika, bukan langsung ke penjual.
            </small>
          </p>
        </section>

        <hr />

        <section>
          <h3>Kenapa Lewat Rekening Bersama?</h3>
          <ul>
            <li>Dana kamu <strong>ditahan</strong> sampai barang dikonfirmasi diterima.</li>
            <li>Kalau barang tidak dikirim atau tidak sesuai, dana bisa dikembalikan.</li>
            <li>Penjual baru menerima pembayaran setelah transaksi selesai.</li>
          </ul>
        </section>

        <hr />

        <form action="proses.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="aksi" value="bayar" />
          <input type="hidden" name="metode" value="transfer" />

          <section>
            <h3>Konfirmasi Pembayaran</h3>

            <p>
              <label for="bank_pengirim">Bank Pengirim:</label><br />
              <select id="bank_pengirim" name="bank_pengirim" required>
                <option value="BCA">BCA</option>
                <option value="BRI">BRI</option>
                <option value="BNI">BNI</option>
                <option value="Mandiri">Mandiri</option>
                <option value="CIMB Niaga">CIMB Niaga</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </p>

            <p>
              <label for="bukti">Bukti Transfer (opsional):</label><br />
              <input type="file" id="bukti" name="bukti" accept="image/*" />
              <br />
              <small>Di simulasi ini file tidak benar-benar diunggah.</small>
            </p>

            <p>
              <small>
                <em>
                  Setelah menekan tombol di bawah, status pesanan berubah jadi
                  <strong>Dibayar &ndash; Dana Ditahan</strong>.
                </em>
              </small>
            </p>
          </section>

          <hr />

          <div>
            <button type="submit">Saya Sudah Transfer (Simulasi)</button>
            <a href="pembayaran.php"><button type="button">Ganti Metode</button></a>
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
