<?php
include "auth.php";
include "connection.php";
if (isset($_POST['simpan'])) {
	$nama = mysqli_real_escape_string($conn, $_POST['nama_anggota']);
	$kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
	$alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
	$telp = mysqli_real_escape_string($conn, $_POST['no_telp']);

	mysqli_query($conn, "INSERT INTO anggota(nama_anggota,kelas,alamat,no_telp) VALUES('$nama','$kelas','$alamat','$telp')");
	header("Location: anggota.php");
	exit;
}

if (isset($_POST['update'])) {
	$id = (int)$_POST['id_anggota'];
	$nama = mysqli_real_escape_string($conn, $_POST['nama_anggota']);
	$kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
	$alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
	$telp = mysqli_real_escape_string($conn, $_POST['no_telp']);

	mysqli_query($conn, "UPDATE anggota SET nama_anggota='$nama',kelas='$kelas',alamat='$alamat',no_telp='$telp' WHERE id_anggota=$id");
	header("Location: anggota.php");
	exit;
}

if (isset($_GET['hapus'])) {
	$id = (int)$_GET['hapus'];
	mysqli_query($conn, "DELETE FROM anggota WHERE id_anggota=$id");
	header("Location: anggota.php");
	exit;
}

$edit = null;
if (isset($_GET['edit'])) {
	$id = (int)$_GET['edit'];
	$q = mysqli_query($conn, "SELECT * FROM anggota WHERE id_anggota=$id");
	$edit = mysqli_fetch_assoc($q);
}

$data = mysqli_query($conn, "SELECT * FROM anggota ORDER BY id_anggota DESC");
?>
<!DOCTYPE html>
<html>
<head>
	<title>Data Anggota</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="container">
		<a class="btn" href="index.php">Kembali</a>
		<a class="btn red" href="logout.php">Logout</a>
		<h2>Data Anggota</h2>

		<form method="post" class="form">
			<?php if ($edit) { ?>
				<input type="hidden" name="id_anggota" value="<?=$edit['id_anggota']?>">
			<?php } ?>
			<input name="nama_anggota" placeholder="Nama Anggota" value="<?=$edit?htmlspecialchars($edit['nama_anggota']):''?>" required>
			<input name="kelas" placeholder="Kelas" value="<?=$edit?htmlspecialchars($edit['kelas']):''?>" required>
			<input name="alamat" placeholder="Alamat" value="<?=$edit?htmlspecialchars($edit['alamat']):''?>" required>
			<input name="no_telp" placeholder="No Telp" value="<?=$edit?htmlspecialchars($edit['no_telp']):''?>" required>
			<?php if ($edit) { ?>
				<button name="update">Simpan Perubahan</button>
				<a class="btn" href="anggota.php">Batal</a>
			<?php } else { ?>
				<button name="simpan">Tambah Anggota</button>
			<?php } ?>
		</form>

		<table>
			<tr>
				<th>ID</th>
				<th>Nama</th>
				<th>Kelas</th>
				<th>Alamat</th>
				<th>No Telp</th>
				<th>Aksi</th>
			</tr>
			<?php while ($r = mysqli_fetch_assoc($data)) { ?>
				<tr>
					<td><?=$r['id_anggota']?></td>
					<td><?=htmlspecialchars($r['nama_anggota'])?></td>
					<td><?=htmlspecialchars($r['kelas'])?></td>
					<td><?=htmlspecialchars($r['alamat'])?></td>
					<td><?=$r['no_telp']?></td>
					<td>
						<a class="btn" href="?edit=<?=$r['id_anggota']?>">Edit</a>
						<a class="btn red" href="?hapus=<?=$r['id_anggota']?>" onclick="return confirm('Hapus anggota?')">Hapus</a>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
</body>
</html>