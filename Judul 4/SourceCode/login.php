<?php
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: contacts.php");
    exit();
}

$login_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');

    if ($u === "" || $p === "") {
        $login_error = "Username dan password wajib diisi.";
    } elseif ($u === "admin" && $p === "admin123") { 
        $_SESSION['logged_in']  = true;
        $_SESSION['username']   = $u;
        $_SESSION['login_time'] = date('Y-m-d H:i:s');

        header("Location: contacts.php");
        exit();
    } else {
        $login_error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-box">
    <h2>Login Sistem Kontak</h2>

    <?php if (!empty($login_error)): ?>
        <div class="error"><?= htmlspecialchars($login_error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username">

        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password">

        <button type="submit" style="width:100%;">Login</button>
    </form>

    <small>Demo: username <strong>admin</strong> – password <strong>admin123</strong></small>
</div>

</body>
</html>
