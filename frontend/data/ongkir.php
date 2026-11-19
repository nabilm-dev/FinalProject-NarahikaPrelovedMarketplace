<?php
/**
 * Hitung ongkos kirim sederhana (simulasi).
 *
 * Bukan API kurir asli. Rumusnya:
 *   biaya = tarif_dasar_kurir × berat_kg × faktor_zona
 * Berat dibulatkan ke atas per 1 kg, hasil dibulatkan ke atas per Rp500.
 *
 * Nanti kalau mau pakai API RajaOngkir, cukup ganti isi hitungOngkir().
 */

function daftarKurir(): array
{
    return [
        "JNE"      => ["nama" => "JNE Reguler", "dasar" => 9000],
        "JNT"      => ["nama" => "J&T Express", "dasar" => 8000],
        "SICEPAT"  => ["nama" => "SiCepat REG", "dasar" => 7000],
        "ANTERAJA" => ["nama" => "Anteraja Reguler", "dasar" => 8500],
        "POS"      => ["nama" => "POS Indonesia", "dasar" => 6500],
    ];
}

function daftarProvinsi(): array
{
    return [
        "Aceh", "Sumatera Utara", "Sumatera Barat", "Riau", "Kepulauan Riau",
        "Jambi", "Bengkulu", "Sumatera Selatan", "Bangka Belitung", "Lampung",
        "Banten", "DKI Jakarta", "Jawa Barat", "Jawa Tengah", "DI Yogyakarta",
        "Jawa Timur", "Bali", "Nusa Tenggara Barat", "Nusa Tenggara Timur",
        "Kalimantan Barat", "Kalimantan Tengah", "Kalimantan Selatan",
        "Kalimantan Timur", "Kalimantan Utara", "Sulawesi Utara", "Gorontalo",
        "Sulawesi Tengah", "Sulawesi Barat", "Sulawesi Selatan", "Sulawesi Tenggara",
        "Maluku", "Maluku Utara", "Papua", "Papua Barat",
    ];
}

function pulauProvinsi(string $provinsi): string
{
    $peta = [
        "Aceh" => "Sumatera", "Sumatera Utara" => "Sumatera", "Sumatera Barat" => "Sumatera",
        "Riau" => "Sumatera", "Kepulauan Riau" => "Sumatera", "Jambi" => "Sumatera",
        "Bengkulu" => "Sumatera", "Sumatera Selatan" => "Sumatera",
        "Bangka Belitung" => "Sumatera", "Lampung" => "Sumatera",

        "Banten" => "Jawa", "DKI Jakarta" => "Jawa", "Jawa Barat" => "Jawa",
        "Jawa Tengah" => "Jawa", "DI Yogyakarta" => "Jawa", "Jawa Timur" => "Jawa",

        "Bali" => "Bali & Nusa Tenggara", "Nusa Tenggara Barat" => "Bali & Nusa Tenggara",
        "Nusa Tenggara Timur" => "Bali & Nusa Tenggara",

        "Kalimantan Barat" => "Kalimantan", "Kalimantan Tengah" => "Kalimantan",
        "Kalimantan Selatan" => "Kalimantan", "Kalimantan Timur" => "Kalimantan",
        "Kalimantan Utara" => "Kalimantan",

        "Sulawesi Utara" => "Sulawesi", "Gorontalo" => "Sulawesi",
        "Sulawesi Tengah" => "Sulawesi", "Sulawesi Barat" => "Sulawesi",
        "Sulawesi Selatan" => "Sulawesi", "Sulawesi Tenggara" => "Sulawesi",

        "Maluku" => "Maluku", "Maluku Utara" => "Maluku",
        "Papua" => "Papua", "Papua Barat" => "Papua",
    ];

    return $peta[$provinsi] ?? "";
}

function zonaOngkir(string $asal, string $tujuan): array
{
    if ($asal !== "" && $asal === $tujuan) {
        return ["faktor" => 1.0, "label" => "Dalam provinsi"];
    }

    $pulauAsal   = pulauProvinsi($asal);
    $pulauTujuan = pulauProvinsi($tujuan);

    if ($pulauAsal !== "" && $pulauAsal === $pulauTujuan) {
        return ["faktor" => 1.3, "label" => "Lintas provinsi (satu pulau)"];
    }

    if (in_array($pulauTujuan, ["Maluku", "Papua"], true)) {
        return ["faktor" => 2.2, "label" => "Antar pulau (Papua/Maluku)"];
    }

    return ["faktor" => 1.8, "label" => "Antar pulau"];
}

function hitungOngkir(string $asal, string $tujuan, int $beratGram, string $kurir): int
{
    $daftar = daftarKurir();
    if (!isset($daftar[$kurir])) {
        $kurir = "JNE";
    }

    $beratGram = max(1, $beratGram);
    $kilogram  = (int) ceil($beratGram / 1000);   // 900 g tetap dihitung 1 kg
    $zona      = zonaOngkir($asal, $tujuan);

    $biaya = $daftar[$kurir]["dasar"] * $kilogram * $zona["faktor"];
    $biaya = (int) (ceil($biaya / 500) * 500);

    return max(6000, $biaya);
}

function estimasiKirim(string $asal, string $tujuan, string $kurir): string
{
    $pulauAsal   = pulauProvinsi($asal);
    $pulauTujuan = pulauProvinsi($tujuan);

    if ($asal !== "" && $asal === $tujuan) {
        $hari = [1, 2];
    } elseif ($pulauAsal !== "" && $pulauAsal === $pulauTujuan) {
        $hari = [2, 4];
    } elseif (in_array($pulauTujuan, ["Maluku", "Papua"], true)) {
        $hari = [5, 9];
    } else {
        $hari = [4, 7];
    }

    // Kurir murah biasanya sedikit lebih lama
    if (in_array($kurir, ["POS", "SICEPAT"], true)) {
        $hari[1] += 1;
    }

    return $hari[0] . "&ndash;" . $hari[1] . " hari";
}

function ongkirSemuaKurir(string $asal, string $tujuan, int $beratGram): array
{
    $hasil = [];
    foreach (daftarKurir() as $kode => $info) {
        $hasil[$kode] = [
            "nama"      => $info["nama"],
            "biaya"     => hitungOngkir($asal, $tujuan, $beratGram, $kode),
            "estimasi"  => estimasiKirim($asal, $tujuan, $kode),
        ];
    }
    return $hasil;
}
