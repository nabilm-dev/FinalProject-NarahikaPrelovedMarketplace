<?php
session_start();
$sudahLogin = isset($_SESSION["user_id"]);
$role       = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

$daftarKategori = ["Atasan", "Bawahan", "Dress", "Outerwear", "Sepatu", "Tas", "Aksesoris", "Lainnya"];
$daftarUkuran   = ["XS", "S", "M", "L", "XL", "XXL", "All Size"];
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Posting Barang - Narahika Preloved Store</title>
  </head>
  <body>
    <header>
      <nav>
        <strong>Narahika</strong> |
        <a href="../../dashboard.php">Dashboard</a> |
        <a href="posting_barang.php">Jual Barang</a> |
        <?php if ($sudahLogin): ?>
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
      <h2>Posting Barang</h2>
      <p>Isi data barang preloved kamu dengan lengkap supaya cepat terjual.</p>

      <?php if (!$sudahLogin): ?>
        <section>
          <h3>Masuk Dulu, Yuk</h3>
          <p>Halaman ini khusus penjual. Silakan masuk atau daftar akun dulu.</p>
          <a href="../auth/form_masuk.php"><button type="button">Masuk</button></a>
          <a href="../auth/form_daftar.php"><button type="button">Daftar</button></a>
        </section>
      <?php elseif (!$adalahPenjual): ?>
        <section>
          <h3>Akun Kamu Masih Pembeli</h3>
          <p>
            Untuk memposting barang, kamu perlu membuka toko dulu. Akun pembeli kamu
            tetap dipakai, jadi tidak perlu daftar ulang.
          </p>
          <a href="../seller/jadi_penjual.php"><button type="button">Jadi Penjual</button></a>
        </section>
      <?php else: ?>
        <form action="" method="POST" enctype="multipart/form-data">
        <!-- FOTO BARANG -->
        <section>
          <h3>Foto Barang</h3>
          <p>Unggah 1&ndash;8 foto. Foto pertama akan jadi sampul barang.</p>

          <div>
            <label for="foto">Pilih Foto:</label><br />
            <input
              type="file"
              id="foto"
              name="foto[]"
              accept="image/jpeg, image/png, image/webp"
              multiple
              required
            />
          </div>
          <p>
            <small>Format JPG, PNG, atau WEBP. Maksimal 5 MB per foto.</small>
          </p>

          <div id="pratinjau-foto">
            <p><em>Pratinjau foto akan tampil di sini.</em></p>
          </div>
        </section>

        <hr />

        <!-- INFORMASI BARANG -->
        <section>
          <h3>Informasi Barang</h3>

          <div>
            <label for="judul">Judul Barang:</label><br />
            <input
              type="text"
              id="judul"
              name="judul"
              placeholder="Contoh: Jaket Denim Levi's Original"
              maxlength="100"
              required
            />
            <p><small>Maksimal 100 karakter.</small></p>
          </div>

          <div>
            <label for="kategori">Kategori:</label><br />
            <select id="kategori" name="kategori" required>
              <option value="">-- Pilih Kategori --</option>
              <?php foreach ($daftarKategori as $kategori): ?>
                <option value="<?= htmlspecialchars($kategori) ?>">
                  <?= htmlspecialchars($kategori) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="ukuran">Ukuran:</label><br />
            <select id="ukuran" name="ukuran" required>
              <option value="">-- Pilih Ukuran --</option>
              <?php foreach ($daftarUkuran as $ukuran): ?>
                <option value="<?= htmlspecialchars($ukuran) ?>">
                  <?= htmlspecialchars($ukuran) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label for="berat">Berat Barang (gram):</label><br />
            <input
              type="number"
              id="berat"
              name="berat"
              min="1"
              max="50000"
              placeholder="Contoh: 500"
              required
            />
            <p><small>Dipakai untuk menghitung ongkos kirim.</small></p>
          </div>

          <div>
            <p><strong>Kondisi Barang:</strong></p>

            <div>
              <input type="radio" id="kondisi_like_new" name="kondisi" value="like_new" required />
              <label for="kondisi_like_new">
                <strong>Like New</strong> &ndash; seperti baru, jarang atau belum pernah dipakai
              </label>
            </div>

            <div>
              <input type="radio" id="kondisi_bekas_baik" name="kondisi" value="bekas_baik" />
              <label for="kondisi_bekas_baik">
                <strong>Bekas Baik</strong> &ndash; sudah dipakai, masih bagus dan layak
              </label>
            </div>

            <div>
              <input type="radio" id="kondisi_ada_minus" name="kondisi" value="ada_minus" />
              <label for="kondisi_ada_minus">
                <strong>Ada Minus</strong> &ndash; ada cacat atau kekurangan, jelaskan di deskripsi
              </label>
            </div>
          </div>

          <div>
            <label for="deskripsi">Deskripsi:</label><br />
            <textarea
              id="deskripsi"
              name="deskripsi"
              rows="6"
              cols="50"
              maxlength="2000"
              placeholder="Ceritakan bahan, brand, pemakaian, dan minus barang (jika ada)."
              required
            ></textarea>
            <p><small>Maksimal 2000 karakter.</small></p>
          </div>
        </section>

        <hr />

        <!-- HARGA & LOKASI -->
        <section>
          <h3>Harga &amp; Lokasi</h3>

          <div>
            <label for="harga">Harga (Rp):</label><br />
            <input
              type="number"
              id="harga"
              name="harga"
              min="1000"
              step="1000"
              placeholder="Contoh: 150000"
              required
            />
            <p><small>Minimal Rp1.000 dan tanpa titik atau koma.</small></p>
          </div>

          <div>
            <input type="checkbox" id="bisa_nego" name="bisa_nego" value="1" />
            <label for="bisa_nego">Harga masih bisa dinegosiasi</label>
          </div>

          <div>
            <label for="lokasi">Lokasi Barang:</label><br />
            <input
              type="text"
              id="lokasi"
              name="lokasi"
              placeholder="Contoh: Bandung, Jawa Barat"
              maxlength="100"
              required
            />
            <p><small>Tulis kota dan provinsi supaya pembeli tahu estimasi ongkir.</small></p>
          </div>

          <div>
            <p><strong>Metode Penyerahan:</strong></p>

            <div>
              <input type="radio" id="metode_kirim" name="metode" value="kirim" required />
              <label for="metode_kirim">Kirim via ekspedisi</label>
            </div>

            <div>
              <input type="radio" id="metode_cod" name="metode" value="cod" />
              <label for="metode_cod">COD (ketemuan langsung)</label>
            </div>

            <div>
              <input type="radio" id="metode_keduanya" name="metode" value="keduanya" />
              <label for="metode_keduanya">Keduanya</label>
            </div>
          </div>
        </section>

        <hr />

        <div>
          <button type="submit" name="aksi" value="draft">Simpan sebagai Draft</button>
          <button type="submit" name="aksi" value="posting">Posting Barang</button>
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
