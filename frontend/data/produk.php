<?php
/**
 * Data produk.
 *
 * SEMENTARA KOSONG — silakan isi sendiri di array paling bawah.
 * Format satu produk (salin blok ini, hapus tanda //):
 *
 * [
 *     "id"          => 1,
 *     "judul"       => "Jaket Denim Levi's Original",
 *     "kategori"    => "Outerwear",           // Atasan|Bawahan|Dress|Outerwear|Sepatu|Tas|Aksesoris|Lainnya
 *     "ukuran"      => "M",                   // XS|S|M|L|XL|XXL|All Size|nomor sepatu
 *     "kondisi"     => "Bekas Baik",          // Like New|Bekas Baik|Ada Minus
 *     "harga"       => 185000,
 *     "lokasi"      => "Bandung, Jawa Barat",
 *     "toko"        => "Narahika Vintage",
 *     "rating_toko" => 4.8,
 *     "ulasan_toko" => 32,
 *     "berat"       => 900,                   // gram
 *     "bisa_nego"   => true,
 *     "metode"      => "Kirim & COD",         // Kirim|COD|Kirim & COD
 *     "tanggal"     => "2026-11-12",          // YYYY-MM-DD, dipakai untuk urutan Terbaru
 *     "foto"        => 4,                     // jumlah foto (ilustrasi galeri)
 *     "deskripsi"   => "Deskripsi barang, termasuk minus kalau ada.",
 * ],
 */

function rupiah(int $angka): string
{
    return "Rp" . number_format($angka, 0, ",", ".");
}

function produkById(array $produk, int $id): ?array
{
    foreach ($produk as $item) {
        if ((int) $item["id"] === $id) {
            return $item;
        }
    }
    return null;
}

function filterProduk(array $produk, array $filter): array
{
    $q         = strtolower(trim($filter["q"] ?? ""));
    $kategori  = $filter["kategori"] ?? "";
    $kondisi   = $filter["kondisi"] ?? "";
    $ukuran    = $filter["ukuran"] ?? "";
    $hargaMin  = (int) ($filter["harga_min"] ?? 0);
    $hargaMax  = (int) ($filter["harga_max"] ?? 0);

    $hasil = [];
    foreach ($produk as $item) {
        if ($q !== "") {
            $teks = strtolower($item["judul"] . " " . $item["deskripsi"] . " " . $item["toko"] . " " . $item["lokasi"]);
            if (strpos($teks, $q) === false) {
                continue;
            }
        }
        if ($kategori !== "" && $item["kategori"] !== $kategori) {
            continue;
        }
        if ($kondisi !== "" && $item["kondisi"] !== $kondisi) {
            continue;
        }
        if ($ukuran !== "" && $item["ukuran"] !== $ukuran) {
            continue;
        }
        if ($hargaMin > 0 && $item["harga"] < $hargaMin) {
            continue;
        }
        if ($hargaMax > 0 && $item["harga"] > $hargaMax) {
            continue;
        }
        $hasil[] = $item;
    }

    return $hasil;
}

function urutkanProduk(array $produk, string $urutan): array
{
    usort($produk, function ($a, $b) use ($urutan) {
        switch ($urutan) {
            case "termurah":
                return $a["harga"] <=> $b["harga"];
            case "termahal":
                return $b["harga"] <=> $a["harga"];
            case "rating":
                return $b["rating_toko"] <=> $a["rating_toko"];
            case "nama":
                return strcmp($a["judul"], $b["judul"]);
            case "terbaru":
            default:
                return strcmp($b["tanggal"], $a["tanggal"]);
        }
    });

    return $produk;
}

function adaDiWishlist(int $id): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return in_array($id, $_SESSION["wishlist"] ?? [], true);
}

function toggleWishlist(int $id): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION["wishlist"])) {
        $_SESSION["wishlist"] = [];
    }

    $posisi = array_search($id, $_SESSION["wishlist"], true);
    if ($posisi === false) {
        $_SESSION["wishlist"][] = $id;
    } else {
        unset($_SESSION["wishlist"][$posisi]);
        $_SESSION["wishlist"] = array_values($_SESSION["wishlist"]);
    }
}

return [
    // CONTOH — ganti/hapus setelah kamu isi produk sendiri.
    [
        "id"          => 1,
        "judul"       => "Jaket Denim Levi's Original",
        "kategori"    => "Outerwear",
        "ukuran"      => "M",
        "kondisi"     => "Bekas Baik",
        "harga"       => 185000,
        "lokasi"      => "Bandung, Jawa Barat",
        "toko"        => "Narahika Vintage",
        "rating_toko" => 4.8,
        "ulasan_toko" => 32,
        "berat"       => 900,
        "bisa_nego"   => true,
        "metode"      => "Kirim & COD",
        "tanggal"     => "2026-11-12",
        "foto"        => 4,
        "deskripsi"   => "Levi's vintage original, bahan tebal dan masih kokoh. Ada sedikit fading wajar di bagian siku, tidak ada sobek atau bolong. Sudah dicuci bersih.",
    ],
];
