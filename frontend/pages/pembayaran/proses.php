<?php
/**
 * Pemroses pembayaran.
 * Semua aksi POST diakhiri redirect supaya refresh tidak mengulang transaksi.
 */
session_start();

require_once __DIR__ . "/../../data/pesanan.php";
require_once __DIR__ . "/../../data/keranjang.php";

$checkout = $_SESSION["checkout"] ?? null;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: pembayaran.php");
    exit;
}

$aksi = $_POST["aksi"] ?? "";

// --- Batalkan pesanan, barang kembali ke keranjang ---
if ($aksi === "batal") {
    if ($checkout !== null) {
        $_SESSION["keranjang"] = [];
        foreach ($checkout["items"] as $baris) {
            $_SESSION["keranjang"][(int) $baris["produk"]["id"]] = (int) $baris["qty"];
        }
        unset($_SESSION["checkout"]);
    }
    header("Location: ../keranjang/keranjang.php");
    exit;
}

// --- Bayar (QRIS / e-wallet / transfer) ---
if ($aksi === "bayar") {
    if ($checkout === null) {
        header("Location: pembayaran.php");
        exit;
    }

    $metode = $_POST["metode"] ?? $checkout["metode_bayar"] ?? "qris";
    if (!in_array($metode, ["qris", "ewallet", "transfer", "cod"], true)) {
        $metode = "qris";
    }

    $daftarEwallet = [
        "gopay"     => "GoPay",
        "ovo"       => "OVO",
        "dana"      => "DANA",
        "shopeepay" => "ShopeePay",
    ];

    $detail = "";
    if ($metode === "qris") {
        $detail = "QRIS";
    } elseif ($metode === "ewallet") {
        $kode   = $_POST["ewallet"] ?? "gopay";
        $nama   = $daftarEwallet[$kode] ?? "GoPay";
        $noHp   = preg_replace('/[^0-9]/', '', (string) ($_POST["no_hp"] ?? ""));
        $masked = strlen($noHp) >= 7
            ? substr($noHp, 0, 4) . str_repeat("*", max(0, strlen($noHp) - 7)) . substr($noHp, -3)
            : $noHp;
        $detail = $nama . ($masked !== "" ? " (" . $masked . ")" : "");
    } elseif ($metode === "transfer") {
        $bank   = trim((string) ($_POST["bank_pengirim"] ?? "BCA"));
        $detail = "BCA Virtual Account" . ($bank !== "" ? " dari " . $bank : "");
    }

    $_SESSION["checkout"]["metode_bayar"]  = $metode;
    $_SESSION["checkout"]["metode_detail"] = $detail;

    $pesanan = pesananBuat($_SESSION["checkout"]);
    unset($_SESSION["checkout"]);

    header("Location: status_pesanan.php?nomor=" . urlencode($pesanan["nomor"]));
    exit;
}

header("Location: pembayaran.php");
exit;
