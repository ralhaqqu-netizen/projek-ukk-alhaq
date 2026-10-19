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
$sukses = "";

if (isset($_POST['daftar'])) {

    $username = mysqli_real_escape_string(
        $conn,
        trim($_POST['username'])
    );

    $password = mysqli_real_escape_string(
        $conn,
        $_POST['password']
    );

    $password2 = mysqli_real_escape_string(
        $conn,
        $_POST['password2']
    );


    if ($password != $password2) {

        $error = "Konfirmasi password tidak sama.";

    } else {

        $cek = mysqli_query($conn, "
            SELECT id_users
            FROM users
            WHERE username = '$username'
            LIMIT 1
        ");

        if (mysqli_num_rows($cek) > 0) {

            $error = "Username sudah digunakan.";

        } else {

            /*
             * Akun yang daftar dari halaman ini
             * otomatis menjadi user biasa.
             */
            $role = "user";

            $query = mysqli_query($conn, "
                INSERT INTO users
                (username, password, role)
                VALUES
                ('$username', '$password', '$role')
            ");

            if ($query) {

                $sukses = "Pendaftaran berhasil. Silakan login.";

            } else {

                $error = "Pendaftaran gagal: "
                    . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Daftar - Perpustakaan</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container">

    <h1>Sistem Perpustakaan</h1>

    <h2>Daftar Akun</h2>


    <?php if ($error) { ?>

        <div class="alert">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php } ?>


    <?php if ($sukses) { ?>

        <div class="alert">
            <?= htmlspecialchars($sukses) ?>
        </div>

        <p>
            <a href="login.php">
                Kembali ke Login
            </a>
        </p>

    <?php } else { ?>


        <form method="post" class="form">

            <label>Username</label>

            <input
                type="text"
                name="username"
                placeholder="Buat username"
                required
            >


            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Buat password"
                required
            >


            <label>Konfirmasi Password</label>

            <input
                type="password"
                name="password2"
                placeholder="Ulangi password"
                required
            >


            <button type="submit" name="daftar">
                Daftar
            </button>

        </form>


        <p>
            Sudah punya akun?
            <a href="login.php">Login di sini</a>
        </p>


    <?php } ?>

</div>

</body>

</html>