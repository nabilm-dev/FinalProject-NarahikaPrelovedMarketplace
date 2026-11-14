<?php
session_start();
require "../koneksi.php"; // Mundur 1 folder

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Mundur 2 folder ke frontend
    header("Location: ../../frontend/pages/auth/form_masuk.php");
    exit;
}

$email    = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user["password"])) {
    session_regenerate_id(true);
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["nama"]    = $user["nama"];
    // Kolom role belum tentu ada, default ke pembeli
    $_SESSION["role"]    = $user["role"] ?? "buyer";
    
    // Mundur 2 folder ke dashboard
    header("Location: ../../frontend/dashboard.php");
    exit;
}

echo "<script>alert('Email atau password salah!');history.back();</script>";
?>