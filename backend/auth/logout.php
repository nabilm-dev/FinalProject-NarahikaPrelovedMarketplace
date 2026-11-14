<?php
session_start();
$_SESSION = [];
session_destroy();
header("Location: ../frontend/pages/auth/form_masuk.html");
exit;