<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

include "connection.php";

$error = "";

if (isset($_POST['login'])) {

    $username = mysqli_real_escape_string(
        $conn,
        $_POST['username']
    );

    $password = mysqli_real_escape_string(
        $conn,
        $_POST['password']
    );

    $query = mysqli_query($conn, "
        SELECT *
        FROM users
        WHERE username = '$username'
        AND password = '$password'
        LIMIT 1
    ");

    $user = mysqli_fetch_assoc($query);

    if ($user) {

        $_SESSION['user_id'] = $user['id_users'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

$cariAnggota = mysqli_query($conn, "
    SELECT id_anggota, nama_anggota
    FROM anggota
    WHERE nama_anggota = '{$user['username']}'
    LIMIT 1
");

$anggotaLogin = mysqli_fetch_assoc($cariAnggota);

if ($anggotaLogin) {
    $_SESSION['id_anggota'] = $anggotaLogin['id_anggota'];
    $_SESSION['nama_anggota'] = $anggotaLogin['nama_anggota'];
} else {
    $_SESSION['id_anggota'] = null;
    $_SESSION['nama_anggota'] = $user['username'];
}
        header("Location: index.php");
        exit;

    } else {

        $error = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Login - Perpustakaan</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container">

    <h1>Sistem Perpustakaan</h1>

    <h2>Login</h2>


    <?php if ($error) { ?>

        <div class="alert">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php } ?>


    <form method="post" class="form">

        <label>Username</label>

        <input
            type="text"
            name="username"
            placeholder="Masukkan username"
            required
        >


        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Masukkan password"
            required
        >


        <button type="submit" name="login">
            Login
        </button>

    </form>


    <p>
        Belum punya akun?
        <a href="daftar.php">Daftar di sini</a>
    </p>

</div>

</body>

</html>