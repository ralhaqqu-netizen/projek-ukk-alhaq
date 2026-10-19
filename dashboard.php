<?php
include "../auth.php";
include "../connection.php";

$buku = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku")
)['total'];

$anggota = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM anggota")
)['total'];

$peminjaman = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM peminjaman")
)['total'];

$pengembalian = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM pengembalian")
)['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Perpustakaan</title>

    <style>
        body {
            font-family: Arial;
            background: #f2f2f2;
            margin: 0;
        }

        .header {
            background: #222;
            color: white;
            padding: 20px;
        }

        .container {
            padding: 25px;
        }

        .cards {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .card {
            background: white;
            width: 200px;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px #ccc;
        }

        .card h2 {
            font-size: 35px;
            margin: 10px;
            color: #4CAF50;
        }

        .menu {
            margin-top: 30px;
        }

        .menu a {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 12px 18px;
            margin: 5px;
            text-decoration: none;
            border-radius: 5px;
        }

        .menu a:hover {
            background: #388E3C;
        }
    </style>
</head>

<body>

<div class="header">
    <h1>Dashboard Perpustakaan</h1>
</div>

<div class="container">

    <h2>
        Selamat datang,
        <?php echo $_SESSION['username']; ?>
    </h2>

    <div class="cards">

        <div class="card">
            <h3>Total Buku</h3>
            <h2><?php echo $buku; ?></h2>
        </div>

        <div class="card">
            <h3>Total Anggota</h3>
            <h2><?php echo $anggota; ?></h2>
        </div>

        <div class="card">
            <h3>Peminjaman</h3>
            <h2><?php echo $peminjaman; ?></h2>
        </div>

        <div class="card">
            <h3>Pengembalian</h3>
            <h2><?php echo $pengembalian; ?></h2>
        </div>

    </div>

    <div class="menu">

        <a href="../buku/tampil.php">
            Data Buku
        </a>

        <a href="../anggota/tampil.php">
            Data Anggota
        </a>

        <a href="../peminjaman/tampil.php">
            Peminjaman
        </a>

        <a href="../pengembalian/tampil.php">
            Pengembalian
        </a>

        <a href="../user/tampil.php">
            Data User
        </a>

        <a href="../logout.php">
            Logout
        </a>

    </div>

</div>

</body>
</html>