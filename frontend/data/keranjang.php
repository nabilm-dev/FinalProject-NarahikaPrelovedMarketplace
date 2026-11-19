<?php
/**
 * Keranjang belanja berbasis session.
 * Struktur: $_SESSION["keranjang"] = [ id_produk => jumlah ].
 *
 * Nanti kalau sudah ada database, isi file ini diganti query ke tabel `keranjang`.
 */

require_once __DIR__ . "/produk.php";

function keranjangSesi(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION["keranjang"]) || !is_array($_SESSION["keranjang"])) {
        $_SESSION["keranjang"] = [];
    }
    return $_SESSION["keranjang"];
}

function keranjangTambah(int $id, int $qty = 1): void
{
    if ($qty < 1) {
        $qty = 1;
    }
    $keranjang = keranjangSesi();
    $sekarang  = (int) ($keranjang[$id] ?? 0);
    $_SESSION["keranjang"][$id] = min(99, $sekarang + $qty);
}

function keranjangSetQty(int $id, int $qty): void
{
    $keranjang = keranjangSesi();
    if (!isset($keranjang[$id])) {
        return;
    }
    if ($qty < 1) {
        unset($_SESSION["keranjang"][$id]);
        return;
    }
    $_SESSION["keranjang"][$id] = min(99, $qty);
}

function keranjangHapus(int $id): void
{
    keranjangSesi();
    unset($_SESSION["keranjang"][$id]);
}

function keranjangKosongkan(): void
{
    keranjangSesi();
    $_SESSION["keranjang"] = [];
}

/**
 * Ambil isi keranjang lengkap dengan data produk.
 * Produk yang sudah tidak ada di data (mis. dihapus penjual) otomatis dilewati.
 */
function keranjangIsi(array $semuaProduk): array
{
    $hasil = [];
    foreach (keranjangSesi() as $id => $qty) {
        $produk = produkById($semuaProduk, (int) $id);
        if ($produk === null) {
            continue;
        }
        $jumlah = max(1, (int) $qty);
        $hasil[] = [
            "produk"   => $produk,
            "qty"      => $jumlah,
            "subtotal" => (int) $produk["harga"] * $jumlah,
            "berat"    => (int) $produk["berat"] * $jumlah,
        ];
    }
    return $hasil;
}

function keranjangJumlahQty(): int
{
    $total = 0;
    foreach (keranjangSesi() as $qty) {
        $total += (int) $qty;
    }
    return $total;
}

function keranjangJumlahItem(array $isi): int
{
    $total = 0;
    foreach ($isi as $baris) {
        $total += (int) $baris["qty"];
    }
    return $total;
}

function keranjangSubtotal(array $isi): int
{
    $total = 0;
    foreach ($isi as $baris) {
        $total += (int) $baris["subtotal"];
    }
    return $total;
}

function keranjangBerat(array $isi): int
{
    $total = 0;
    foreach ($isi as $baris) {
        $total += (int) $baris["berat"];
    }
    return $total;
}
