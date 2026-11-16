<?php
session_start();
$sudahLogin    = isset($_SESSION["user_id"]);
$role          = $_SESSION["role"] ?? "buyer";
$adalahPenjual = $sudahLogin && $role === "seller";

// Data awal form (sementara, nanti diisi dari database)
$profil = [
    "nama"       => $_SESSION["nama"] ?? "",
    "email"      => $_SESSION["email"] ?? "",
    "no_hp"      => "",
    "jenis_kelamin" => "",
    "tanggal_lahir" => "",
    "alamat"     => "",
    "kota"       => "",
    "provinsi"   => "",
    "kode_pos"   => "",
    "bio"        => "",
];

$daftarProvinsi = [
    "Aceh", "Sumatera Utara", "Sumatera Barat", "Riau", "Kepulauan Riau",
    "Jambi", "Bengkulu", "Sumatera Selatan", "Bangka Belitung", "Lampung",
    "Banten", "DKI Jakarta", "Jawa Barat", "Jawa Tengah", "DI Yogyakarta",
    "Jawa Timur", "Bali", "Nusa Tenggara Barat", "Nusa Tenggara Timur",
    "Kalimantan Barat", "Kalimantan Tengah", "Kalimantan Selatan",
    "Kalimantan Timur", "Kalimantan Utara", "Sulawesi Utara", "Gorontalo",
    "Sulawesi Tengah", "Sulawesi Barat", "Sulawesi Selatan", "Sulawesi Tenggara",
    "Maluku", "Maluku Utara", "Papua", "Papua Barat",
];
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Profil - Narahika Preloved Store</title>
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
      <h2>Edit Profil</h2>
      <p>Perbarui data diri kamu supaya pembeli atau penjual lebih mudah percaya.</p>

      <?php if (!$sudahLogin): ?>
        <section>
          <h3>Masuk Dulu, Yuk</h3>
          <p>Halaman ini hanya bisa dibuka setelah kamu masuk ke akun.</p>
          <a href="../auth/form_masuk.php"><button type="button">Masuk</button></a>
          <a href="../auth/form_daftar.php"><button type="button">Daftar</button></a>
        </section>
      <?php else: ?>
        <p>
          <small>
            Kamu masuk sebagai
            <strong><?= $adalahPenjual ? "Penjual" : "Pembeli" ?></strong>.
          </small>
        </p>

        <form action="" method="POST" enctype="multipart/form-data">
          <!-- FOTO PROFIL -->
          <section>
            <h3>Foto Profil</h3>

            <div>
              <p><em>Belum ada foto profil.</em></p>
              <label for="foto_profil">Ganti Foto Profil:</label><br />
              <input
                type="file"
                id="foto_profil"
                name="foto_profil"
                accept="image/jpeg, image/png, image/webp"
              />
              <p><small>Format JPG, PNG, atau WEBP. Maksimal 2 MB. Rasio 1:1 paling rapi.</small></p>
            </div>
          </section>

          <hr />

          <!-- DATA DIRI -->
          <section>
            <h3>Data Diri</h3>

            <div>
              <label for="nama">Nama Lengkap:</label><br />
              <input
                type="text"
                id="nama"
                name="nama"
                value="<?= htmlspecialchars($profil["nama"]) ?>"
                maxlength="100"
                required
              />
              <p><small>Nama ini tampil di halaman profil dan chat.</small></p>
            </div>

            <div>
              <label for="email">Email:</label><br />
              <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($profil["email"]) ?>"
                maxlength="100"
                required
              />
              <p><small>Dipakai untuk notifikasi pesanan dan pemulihan akun.</small></p>
            </div>

            <div>
              <label for="no_hp">Nomor HP / WhatsApp:</label><br />
              <input
                type="tel"
                id="no_hp"
                name="no_hp"
                value="<?= htmlspecialchars($profil["no_hp"]) ?>"
                placeholder="Contoh: 081234567890"
                pattern="[0-9+ ]{9,15}"
              />
              <p><small>Hanya dipakai untuk keperluan pengiriman, tidak ditampilkan ke publik.</small></p>
            </div>

            <div>
              <p><strong>Jenis Kelamin:</strong></p>

              <div>
                <input
                  type="radio"
                  id="jk_laki"
                  name="jenis_kelamin"
                  value="laki_laki"
                  <?= $profil["jenis_kelamin"] === "laki_laki" ? "checked" : "" ?>
                />
                <label for="jk_laki">Laki-laki</label>
              </div>

              <div>
                <input
                  type="radio"
                  id="jk_perempuan"
                  name="jenis_kelamin"
                  value="perempuan"
                  <?= $profil["jenis_kelamin"] === "perempuan" ? "checked" : "" ?>
                />
                <label for="jk_perempuan">Perempuan</label>
              </div>

              <div>
                <input
                  type="radio"
                  id="jk_rahasia"
                  name="jenis_kelamin"
                  value="rahasia"
                  <?= $profil["jenis_kelamin"] === "rahasia" ? "checked" : "" ?>
                />
                <label for="jk_rahasia">Tidak ingin menyebutkan</label>
              </div>
            </div>

            <div>
              <label for="tanggal_lahir">Tanggal Lahir:</label><br />
              <input
                type="date"
                id="tanggal_lahir"
                name="tanggal_lahir"
                value="<?= htmlspecialchars($profil["tanggal_lahir"]) ?>"
              />
              <p><small>Dipakai untuk verifikasi usia. Tidak ditampilkan ke publik.</small></p>
            </div>

            <div>
              <label for="bio">Tentang Saya:</label><br />
              <textarea
                id="bio"
                name="bio"
                rows="4"
                cols="50"
                maxlength="300"
                placeholder="Contoh: Suka baju vintage dan sering belanja preloved."
              ><?= htmlspecialchars($profil["bio"]) ?></textarea>
              <p><small>Maksimal 300 karakter.</small></p>
            </div>
          </section>

          <hr />

          <!-- ALAMAT -->
          <section>
            <h3>Alamat Utama</h3>
            <p>Alamat ini otomatis dipakai saat checkout. Kamu tetap bisa menggantinya per pesanan.</p>

            <div>
              <label for="alamat">Alamat Lengkap:</label><br />
              <textarea
                id="alamat"
                name="alamat"
                rows="3"
                cols="50"
                maxlength="255"
                placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan."
              ><?= htmlspecialchars($profil["alamat"]) ?></textarea>
            </div>

            <div>
              <label for="provinsi">Provinsi:</label><br />
              <select id="provinsi" name="provinsi" required>
                <option value="">-- Pilih Provinsi --</option>
                <?php foreach ($daftarProvinsi as $provinsi): ?>
                  <option
                    value="<?= htmlspecialchars($provinsi) ?>"
                    <?= $profil["provinsi"] === $provinsi ? "selected" : "" ?>
                  >
                    <?= htmlspecialchars($provinsi) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label for="kota">Kota / Kabupaten:</label><br />
              <input
                type="text"
                id="kota"
                name="kota"
                value="<?= htmlspecialchars($profil["kota"]) ?>"
                placeholder="Contoh: Kota Bandung"
                maxlength="100"
                required
              />
            </div>

            <div>
              <label for="kode_pos">Kode Pos:</label><br />
              <input
                type="text"
                id="kode_pos"
                name="kode_pos"
                value="<?= htmlspecialchars($profil["kode_pos"]) ?>"
                placeholder="Contoh: 40123"
                pattern="[0-9]{5}"
              />
            </div>
          </section>

          <hr />

          <!-- KEAMANAN -->
          <section>
            <h3>Keamanan Akun</h3>
            <p>
              <a href="../auth/lupa_sandi.php">Ganti kata sandi</a>
              <small>(kamu akan diminta memasukkan kode verifikasi ke email)</small>
            </p>
            <p><small>Terakhir ganti kata sandi: belum pernah.</small></p>
          </section>

          <hr />

          <div>
            <button type="submit" name="aksi" value="simpan">Simpan Perubahan</button>
            <a href="profil_<?= $adalahPenjual ? "penjual" : "pembeli" ?>.php">
              <button type="button">Batal</button>
            </a>
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
