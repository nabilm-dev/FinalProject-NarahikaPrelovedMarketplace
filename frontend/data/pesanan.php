<?php
/**
 * Data pesanan & status (berbasis session, belum database).
 *
 * Alur status:
 *   menunggu_pembayaran -> dibayar (dana ditahan escrow) -> dikirim -> selesai
 *   COD:            menunggu_pembayaran -> diproses -> dikirim -> selesai
 *   Batal dari mana saja sebelum dikirim.
 *
 * Nanti diganti tabel `pesanan` + `pesanan_timeline`.
 */

require_once __DIR__ . "/produk.php";

function pesananSesi(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION["pesanan"]) || !is_array($_SESSION["pesanan"])) {
        $_SESSION["pesanan"] = [];
    }
    return $_SESSION["pesanan"];
}

function pesananAmbil(string $nomor): ?array
{
    $semua = pesananSesi();
    return $semua[$nomor] ?? null;
}

function pesananSimpan(array $pesanan): void
{
    pesananSesi();
    $_SESSION["pesanan"][$pesanan["nomor"]] = $pesanan;
}

function statusPesananLabel(string $status): string
{
    $daftar = [
        "menunggu_pembayaran" => "Menunggu Pembayaran",
        "dibayar"             => "Dibayar - Dana Ditahan",
        "diproses"            => "Diproses",
        "dikirim"             => "Dikirim",
        "selesai"             => "Selesai - Dana Diteruskan",
        "dibatalkan"          => "Dibatalkan",
    ];
    return $daftar[$status] ?? $status;
}

function statusPesananLangkah(): array
{
    // Urutan tahap yang ditampilkan di timeline
    return ["dibayar", "diproses", "dikirim", "selesai"];
}

function komisiPlatform(int $subtotal): int
{
    // Komisi platform 5% dari subtotal, dibulatkan ke atas per Rp100
    $komisi = $subtotal * 0.05;
    return (int) (ceil($komisi / 100) * 100);
}

function labelMetodeBayar(string $metode): string
{
    $daftar = [
        "qris"     => "QRIS",
        "ewallet"  => "E-Wallet",
        "transfer" => "Rekening Bersama (Escrow)",
        "cod"      => "COD (Bayar di Tempat)",
    ];
    return $daftar[$metode] ?? $metode;
}

/**
 * Buat pesanan dari data checkout, lalu simpan di session.
 * Mengembalikan data pesanan lengkap.
 */
function pesananBuat(array $checkout): array
{
    $metode = $checkout["metode_bayar"] ?? "transfer";
    $statusAwal = $metode === "cod" ? "diproses" : "dibayar";
    $nomor = $checkout["nomor"] ?? ("NRH-" . date("Ymd") . "-" . random_int(100, 999));

    $detail = $checkout["metode_detail"] ?? "";
    $lewat  = labelMetodeBayar($metode) . ($detail !== "" ? " - " . $detail : "");

    $catatanAwal = $metode === "cod"
        ? "Pesanan COD dikonfirmasi. Bayar langsung saat barang diterima."
        : "Pembayaran diterima via " . $lewat . ". Dana ditahan Narahika sampai barang dikonfirmasi diterima.";

    $pesanan = [
        "nomor"         => $nomor,
        "waktu"         => $checkout["waktu"] ?? date("Y-m-d H:i"),
        "items"         => $checkout["items"] ?? [],
        "jumlah_item"   => $checkout["jumlah_item"] ?? 0,
        "berat"         => $checkout["berat"] ?? 0,
        "subtotal"      => $checkout["subtotal"] ?? 0,
        "ongkir"        => $checkout["ongkir"] ?? 0,
        "biaya_layanan" => $checkout["biaya_layanan"] ?? 0,
        "total"         => $checkout["total"] ?? 0,
        "asal"          => $checkout["asal"] ?? "",
        "alamat"        => $checkout["alamat"] ?? [],
        "kurir"         => $checkout["kurir"] ?? "JNE",
        "metode_bayar"  => $metode,
        "metode_detail" => $detail,
        "status"        => $statusAwal,
        "komisi"        => komisiPlatform((int) ($checkout["subtotal"] ?? 0)),
        "timeline"      => [
            [
                "waktu"   => date("d M Y H:i"),
                "status"  => $statusAwal,
                "catatan" => $catatanAwal,
            ],
        ],
    ];

    pesananSimpan($pesanan);
    return $pesanan;
}

function pesananUbahStatus(string $nomor, string $status, string $catatan = ""): void
{
    $pesanan = pesananAmbil($nomor);
    if ($pesanan === null) {
        return;
    }

    $pesanan["status"] = $status;
    $pesanan["timeline"][] = [
        "waktu"   => date("d M Y H:i"),
        "status"  => $status,
        "catatan" => $catatan,
    ];

    pesananSimpan($pesanan);
}

function pesananDanaPenjual(array $pesanan): int
{
    return max(0, (int) $pesanan["subtotal"] - (int) $pesanan["komisi"]);
}
