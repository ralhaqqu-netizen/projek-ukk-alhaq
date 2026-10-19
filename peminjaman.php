<?php
include "auth.php";
include "connection.php";
$msg = "";
$sessionAnggotaId = isset($_SESSION['id_anggota']) ? (int)$_SESSION['id_anggota'] : 0;
$sessionAnggotaNama = $_SESSION['nama_anggota'] ?? null;
$userRole = $_SESSION['role'] ?? 'user';
$filterAnggota = $userRole === 'user' ? " AND p.id_anggota = $sessionAnggotaId" : "";

if (isset($_POST['approve_peminjaman'])) {
    if (!in_array($userRole, ['admin', 'petugas'])) {
        $msg = "Hanya admin atau petugas yang bisa menyetujui pinjaman.";
    } else {
        $approveId = (int)$_POST['approve_peminjaman'];
        $ok = mysqli_query($conn, "UPDATE peminjaman SET status='Dipinjam' WHERE id_peminjaman=$approveId AND status='Menunggu Persetujuan'");
        if ($ok) {
            header("Location: peminjaman.php");
            exit;
        } else {
            $msg = "Persetujuan gagal: " . mysqli_error($conn);
        }
    }
}

if (isset($_POST['hapus_peminjaman'])) {
    $hapusId = (int)$_POST['hapus_peminjaman'];
    $cek = mysqli_query($conn, "SELECT p.status, p.id_anggota FROM peminjaman p WHERE p.id_peminjaman=$hapusId LIMIT 1");
    $statusRow = mysqli_fetch_assoc($cek);
    if (!$statusRow) {
        $msg = "Data peminjaman tidak ditemukan.";
    } elseif ($statusRow['status'] === 'Dipinjam') {
        $msg = "Pinjaman yang masih aktif tidak bisa dihapus.";
    } else {
        $bolehHapus = ($userRole === 'admin') || ($userRole === 'user' && (int)$statusRow['id_anggota'] === $sessionAnggotaId);
        if (!$bolehHapus) {
            $msg = "Anda tidak punya izin untuk menghapus riwayat ini.";
        } else {
            mysqli_begin_transaction($conn);
            $ok = mysqli_query($conn, "DELETE FROM pengembalian WHERE id_peminjaman=$hapusId");
            $ok = $ok && mysqli_query($conn, "DELETE FROM peminjaman_detail WHERE id_peminjaman=$hapusId");
            $ok = $ok && mysqli_query($conn, "DELETE FROM peminjaman WHERE id_peminjaman=$hapusId");
            if ($ok) {
                mysqli_commit($conn);
                header("Location: peminjaman.php");
                exit;
            } else {
                mysqli_rollback($conn);
                $msg = "Gagal menghapus riwayat: " . mysqli_error($conn);
            }
        }
    }
}

if (isset($_POST['pinjam'])) {
    $anggota = $sessionAnggotaId;
    $buku = (int)$_POST['id_buku'];
    $jumlah = (int)$_POST['jumlah'];
    $tgl = mysqli_real_escape_string($conn, $_POST['tanggal_pinjam']);
    $kembali = mysqli_real_escape_string($conn, $_POST['tanggal_kembali']);

    if (!$anggota) {
        $msg = "Data anggota untuk akun login belum ditemukan. Hubungi admin.";
    } else {
        $q = mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku=$buku");
        $b = mysqli_fetch_assoc($q);
        if (!$b || $b['stok'] < $jumlah || $jumlah < 1) {
            $msg = "Stok buku tidak cukup.";
        } else {
            mysqli_begin_transaction($conn);
            $ok = mysqli_query($conn, "INSERT INTO peminjaman(id_anggota,tanggal_pinjam,tanggal_kembali,status) VALUES($anggota,'$tgl','$kembali','Menunggu Persetujuan')");
            $pid = mysqli_insert_id($conn);
            $ok = $ok && mysqli_query($conn, "INSERT INTO peminjaman_detail(id_peminjaman,id_buku,jumlah) VALUES($pid,$buku,$jumlah)");
            if ($ok) {
                mysqli_commit($conn);
                header("Location: peminjaman.php");
                exit;
            } else {
                mysqli_rollback($conn);
                $msg = "Peminjaman gagal: " . mysqli_error($conn);
            }
        }
    }
}

$anggota = mysqli_query($conn, "SELECT * FROM anggota ORDER BY nama_anggota");
$buku = mysqli_query($conn, "SELECT * FROM buku WHERE stok>0 ORDER BY judul_buku");
$data = mysqli_query($conn, "SELECT p.*,a.nama_anggota,b.judul_buku,d.jumlah FROM peminjaman p JOIN anggota a ON p.id_anggota=a.id_anggota JOIN peminjaman_detail d ON p.id_peminjaman=d.id_peminjaman JOIN buku b ON d.id_buku=b.id_buku WHERE 1=1$filterAnggota ORDER BY p.id_peminjaman DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Peminjaman</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a class="btn" href="index.php">Kembali</a>
        <a class="btn red" href="logout.php">Logout</a>
        <h2>Peminjaman Buku</h2>
        <?php if ($msg) echo "<div class='alert'>$msg</div>"; ?>

        <form method="post" class="form">
            <label>Anggota</label>
            <input type="hidden" name="id_anggota" value="<?=$sessionAnggotaId?>">
            <div class="selected-anggota"><?=htmlspecialchars($sessionAnggotaNama)?></div>
            <label>Buku</label>
            <select name="id_buku" required>
                <?php while ($b = mysqli_fetch_assoc($buku)) { ?>
                    <option value="<?=$b['id_buku']?>"><?=htmlspecialchars($b['judul_buku'])?> (stok <?=$b['stok']?>)</option>
                <?php } ?>
            </select>
            <input type="number" name="jumlah" value="1" min="1" required>
            <label>Tanggal Pinjam</label>
            <input type="date" name="tanggal_pinjam" value="<?=date('Y-m-d')?>" required>
            <label>Tanggal Kembali</label>
            <input type="date" name="tanggal_kembali" required>
            <button name="pinjam">Ajukan Pinjam</button>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Anggota</th>
                <th>Buku</th>
                <th>Jumlah</th>
                <th>Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php while ($r = mysqli_fetch_assoc($data)) {
                $bolehHapus = ($userRole === 'admin') || ($userRole === 'user' && (int)$r['id_anggota'] === $sessionAnggotaId);
            ?>
                <tr>
                    <td><?=$r['id_peminjaman']?></td>
                    <td><?=htmlspecialchars($r['nama_anggota'])?></td>
                    <td><?=htmlspecialchars($r['judul_buku'])?></td>
                    <td><?=$r['jumlah']?></td>
                    <td><?=$r['tanggal_pinjam']?></td>
                    <td><?=$r['tanggal_kembali']?></td>
                    <td><?=$r['status']?></td>
                    <td>
                        <?php if (in_array($userRole, ['admin', 'petugas']) && $r['status'] == 'Menunggu Persetujuan') { ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="approve_peminjaman" value="<?=$r['id_peminjaman']?>">
                                <button type="submit">Setujui</button>
                            </form>
                        <?php }
                        if ($bolehHapus && $r['status'] != 'Dipinjam') { ?>
                            <form method="post" style="display:inline; margin-left:5px;" onsubmit="return confirm('Hapus riwayat peminjaman ini?')">
                                <input type="hidden" name="hapus_peminjaman" value="<?=$r['id_peminjaman']?>">
                                <button type="submit" class="btn red">Hapus</button>
                            </form>
                        <?php } elseif (!$bolehHapus && $r['status'] != 'Dipinjam') {
                            echo '-';
                        } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>