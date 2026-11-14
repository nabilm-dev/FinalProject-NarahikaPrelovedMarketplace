<?php
session_start();
$sudahLogin   = isset($_SESSION["user_id"]);
$role         = $_SESSION["role"] ?? "buyer";
$sudahPenjual = $sudahLogin && $role === "seller";

$daftarBank = ["BCA", "BRI", "BNI", "Mandiri", "CIMB Niaga", "Permata", "Lainnya"];
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Jadi Penjual - Narahika Preloved Store</title>
  </head>
  <body>
    <header>
      <nav>
        <strong>Narahika</strong> |
        <a href="../../dashboard.php">Dashboard</a> |
        <?php if ($sudahPenjual): ?>
          <a href="../produk/posting_barang.php">Jual Barang</a> |
          Halo, <?= htmlspecialchars($_SESSION["nama"]) ?>
          <a href="../../../backend/auth/logout.php"><button type="button">Keluar</button></a>
        <?php elseif ($sudahLogin): ?>
          Jadi Penjual |
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
      <h2>Jadi Penjual</h2>
      <p>Buka toko kamu di Narahika dan mulai jual barang preloved.</p>

      <ol>
        <li>Akun pembeli kamu tetap dipakai &ndash; tidak perlu daftar ulang.</li>
        <li>Lengkapi profil toko dan data pencairan.</li>
        <li>Verifikasi identitas (opsional) menambah kepercayaan pembeli.</li>
        <li>Setelah disetujui, kamu bisa memposting barang.</li>
      </ol>

      <?php if (!$sudahLogin): ?>
        <section>
          <h3>Masuk Dulu, Yuk</h3>
          <p>Kamu harus masuk ke akun yang sudah terdaftar sebelum membuka toko.</p>
          <a href="../auth/form_masuk.php"><button type="button">Masuk</button></a>
          <a href="../auth/form_daftar.php"><button type="button">Daftar Akun Pembeli</button></a>
        </section>
      <?php elseif ($sudahPenjual): ?>
        <section>
          <h3>Toko Kamu Sudah Aktif</h3>
          <p>Akun ini sudah terdaftar sebagai penjual. Kamu bisa langsung posting barang.</p>
          <a href="../produk/posting_barang.php"><button type="button">Posting Barang</button></a>
        </section>
      <?php else: ?>
        <form action="" method="POST" enctype="multipart/form-data">
          <!-- PROFIL TOKO -->
          <section>
            <h3>Profil Toko</h3>

            <div>
              <label for="nama_toko">Nama Toko:</label><br />
              <input
                type="text"
                id="nama_toko"
                name="nama_toko"
                placeholder="Contoh: Narahika Vintage"
                maxlength="50"
                required
              />
              <p><small>Nama toko akan tampil di halaman produk dan chat.</small></p>
            </div>

            <div>
              <label for="foto_toko">Foto Profil Toko:</label><br />
              <input type="file" id="foto_toko" name="foto_toko" accept="image/jpeg, image/png, image/webp" />
              <p><small>Opsional. Format JPG, PNG, atau WEBP, maksimal 2 MB.</small></p>
            </div>

            <div>
              <label for="deskripsi_toko">Deskripsi Toko:</label><br />
              <textarea
                id="deskripsi_toko"
                name="deskripsi_toko"
                rows="4"
                cols="50"
                maxlength="500"
                placeholder="Ceritakan gaya dan jenis barang yang kamu jual."
              ></textarea>
            </div>

            <div>
              <label for="lokasi_toko">Lokasi Toko:</label><br />
              <input
                type="text"
                id="lokasi_toko"
                name="lokasi_toko"
                placeholder="Contoh: Bandung, Jawa Barat"
                maxlength="100"
                required
              />
            </div>
          </section>

          <hr />

          <!-- KONTAK -->
          <section>
            <h3>Kontak Penjual</h3>

            <div>
              <label for="no_hp">Nomor HP / WhatsApp:</label><br />
              <input
                type="tel"
                id="no_hp"
                name="no_hp"
                placeholder="Contoh: 081234567890"
                pattern="[0-9+ ]{9,15}"
                required
              />
              <p><small>Dipakai untuk notifikasi pesanan dan dihubungi kurir.</small></p>
            </div>
          </section>

          <hr />

          <!-- VERIFIKASI IDENTITAS -->
          <section>
            <h3>Verifikasi Identitas (Opsional)</h3>
            <p>
              Data KTP hanya dipakai admin untuk verifikasi dan tidak ditampilkan ke pembeli.
            </p>

            <div>
              <label for="foto_ktp">Foto KTP:</label><br />
              <input type="file" id="foto_ktp" name="foto_ktp" accept="image/jpeg, image/png" />
            </div>

            <div>
              <input type="checkbox" id="izin_ktp" name="izin_ktp" value="1" />
              <label for="izin_ktp">
                Saya setuju data KTP disimpan hanya untuk keperluan verifikasi.
              </label>
            </div>
          </section>

          <hr />

          <!-- REKENING PENCAIRAN -->
          <section>
            <h3>Rekening Pencairan</h3>
            <p>Saldo penjualan akan dicairkan ke rekening ini.</p>

            <div>
              <label for="bank">Bank:</label><br />
              <select id="bank" name="bank" required>
                <option value="">-- Pilih Bank --</option>
                <?php foreach ($daftarBank as $bank): ?>
                  <option value="<?= htmlspecialchars($bank) ?>">
                    <?= htmlspecialchars($bank) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label for="no_rekening">Nomor Rekening:</label><br />
              <input
                type="text"
                id="no_rekening"
                name="no_rekening"
                placeholder="Contoh: 1234567890"
                pattern="[0-9]{6,20}"
                required
              />
            </div>

            <div>
              <label for="nama_rekening">Nama Pemilik Rekening:</label><br />
              <input
                type="text"
                id="nama_rekening"
                name="nama_rekening"
                placeholder="Sesuai buku tabungan"
                maxlength="100"
                required
              />
            </div>
          </section>

          <hr />

          <!-- PERSETUJUAN -->
          <section>
            <h3>Persetujuan Penjual</h3>

            <div>
              <input type="checkbox" id="setuju_penjual" name="setuju_penjual" value="1" required />
              <label for="setuju_penjual">
                Saya setuju dengan syarat penjual, kebijakan retur, dan biaya layanan platform
                yang dipotong dari setiap transaksi.
              </label>
            </div>
          </section>

          <hr />

          <div>
            <button type="submit" name="aksi" value="ajukan">Ajukan Jadi Penjual</button>
            <a href="../../dashboard.php"><button type="button">Batal</button></a>
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
