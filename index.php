<?php
include "auth.php";
include "connection.php";

/* Mengambil jumlah data */

$q_buku = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku");
$data_buku = mysqli_fetch_assoc($q_buku);
$total_buku = $data_buku['total'];

$q_stok = mysqli_query($conn, "SELECT SUM(stok) AS total FROM buku");
$data_stok = mysqli_fetch_assoc($q_stok);
$total_stok = $data_stok['total'] ?? 0;

$q_anggota = mysqli_query($conn, "SELECT COUNT(*) AS total FROM anggota");
$data_anggota = mysqli_fetch_assoc($q_anggota);
$total_anggota = $data_anggota['total'];

$q_peminjaman = mysqli_query($conn, "SELECT COUNT(*) AS total FROM peminjaman");
$data_peminjaman = mysqli_fetch_assoc($q_peminjaman);
$total_peminjaman = $data_peminjaman['total'];

$q_pengembalian = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pengembalian");
$data_pengembalian = mysqli_fetch_assoc($q_pengembalian);
$total_pengembalian = $data_pengembalian['total'];
?>

<!DOCTYPE html>
<html>

<head>

    <title>Dashboard Perpustakaan</title>

    <link rel="stylesheet" href="style.css">

    <style>

        .dashboard {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 25px 0;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }

        .card h3 {
            margin: 0 0 10px;
            font-size: 17px;
        }

        .card p {
            margin: 0;
            font-size: 30px;
            font-weight: bold;
        }

        .menu {
            margin: 20px 0;
        }

        .menu a {
            display: inline-block;
            margin: 5px;
            padding: 10px 15px;
            text-decoration: none;
        }

        @media (max-width: 800px) {

            .dashboard {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 500px) {

            .dashboard {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <h1>Dashboard Perpustakaan</h1>

    <p>

        Login sebagai:

        <b>
            <?= htmlspecialchars($_SESSION['username']) ?>
        </b>

        (
        <?= htmlspecialchars($_SESSION['role']) ?>
        )

        -

        <a href="logout.php">
            Logout
        </a>

    </p>


    <div class="menu">

        <a href="index.php">
            Dashboard
        </a>

        <a href="buku.php">
            Data Buku
        </a>

        <a href="anggota.php">
            Data Anggota
        </a>

        <a href="users.php">
            Data User
        </a>

        <a href="peminjaman.php">
            Peminjaman
        </a>

        <a href="pengembalian.php">
            Pengembalian
        </a>

    </div>


    <div class="dashboard">

        <div class="card">

            <h3>
                Total Buku
            </h3>

            <p>
                <?= $total_buku ?>
            </p>

        </div>


        <div class="card">

            <h3>
                Total Stok
            </h3>

            <p>
                <?= $total_stok ?>
            </p>

        </div>


        <div class="card">

            <h3>
                Total Anggota
            </h3>

            <p>
                <?= $total_anggota ?>
            </p>

        </div>


        <div class="card">

            <h3>
                Total Peminjaman
            </h3>

            <p>
                <?= $total_peminjaman ?>
            </p>

        </div>


        <div class="card">

            <h3>
                Total Pengembalian
            </h3>

            <p>
                <?= $total_pengembalian ?>
            </p>

        </div>

    </div>

</div>

</body>

</html>