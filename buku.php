<?php
include "auth.php";
include "connection.php";
$msg = "";

function getBookImageExtension($file)
{
	if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
		return null;
	}

	if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
		return false;
	}

	$image = getimagesize($file['tmp_name']);
	$extensions = [
		'image/jpeg' => 'jpg',
		'image/png' => 'png',
		'image/webp' => 'webp'
	];

	return $image ? ($extensions[$image['mime']] ?? false) : false;
}

function saveBookImage($id, $file, $extension)
{
	$directory = __DIR__ . '/uploads/buku';
	if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
		return false;
	}

	$destination = $directory . '/buku_' . (int)$id . '.' . $extension;
	if (!move_uploaded_file($file['tmp_name'], $destination)) {
		return false;
	}

	foreach (glob($directory . '/buku_' . (int)$id . '.*') ?: [] as $oldImage) {
		if ($oldImage !== $destination) {
			unlink($oldImage);
		}
	}

	return true;
}

function getBookImagePath($id)
{
	$images = glob(__DIR__ . '/uploads/buku/buku_' . (int)$id . '.*');
	return $images ? 'uploads/buku/' . basename($images[0]) : null;
}

if (isset($_POST['simpan'])) {
	$extension = getBookImageExtension($_FILES['gambar_buku'] ?? []);
	if ($extension === false) {
		$msg = "Gambar harus berformat JPG, PNG, atau WEBP dan maksimal 5 MB.";
	} else {
		$judul = mysqli_real_escape_string($conn, $_POST['judul_buku']);
		$penulis = mysqli_real_escape_string($conn, $_POST['penulis']);
		$penerbit = mysqli_real_escape_string($conn, $_POST['penerbit']);
		$tahun = (int)$_POST['tahun_terbit'];
		$stok = (int)$_POST['stok'];

		$query = mysqli_query($conn, "INSERT INTO buku(judul_buku,penulis,penerbit,tahun_terbit,stok) VALUES('$judul','$penulis','$penerbit',$tahun,$stok)");
		if ($query) {
			$id = mysqli_insert_id($conn);
			if ($extension !== null && !saveBookImage($id, $_FILES['gambar_buku'], $extension)) {
				$msg = "Data buku tersimpan, tetapi gambar gagal diunggah.";
			} else {
				header("Location: buku.php");
				exit;
			}
		} else {
			$msg = "Data buku gagal disimpan: " . mysqli_error($conn);
		}
	}
}

if (isset($_POST['update'])) {
	$extension = getBookImageExtension($_FILES['gambar_buku'] ?? []);
	if ($extension === false) {
		$msg = "Gambar harus berformat JPG, PNG, atau WEBP dan maksimal 5 MB.";
	} else {
		$id = (int)$_POST['id_buku'];
		$judul = mysqli_real_escape_string($conn, $_POST['judul_buku']);
		$penulis = mysqli_real_escape_string($conn, $_POST['penulis']);
		$penerbit = mysqli_real_escape_string($conn, $_POST['penerbit']);
		$tahun = (int)$_POST['tahun_terbit'];
		$stok = (int)$_POST['stok'];

		$query = mysqli_query($conn, "UPDATE buku SET judul_buku='$judul',penulis='$penulis',penerbit='$penerbit',tahun_terbit=$tahun,stok=$stok WHERE id_buku=$id");
		if ($query && ($extension === null || saveBookImage($id, $_FILES['gambar_buku'], $extension))) {
			header("Location: buku.php");
			exit;
		}
		$msg = $query
			? "Data buku tersimpan, tetapi gambar gagal diunggah."
			: "Data buku gagal diperbarui: " . mysqli_error($conn);
	}
}

if (isset($_GET['hapus'])) {
	$id = (int)$_GET['hapus'];
	mysqli_query($conn, "DELETE FROM buku WHERE id_buku=$id");
	foreach (glob(__DIR__ . '/uploads/buku/buku_' . $id . '.*') ?: [] as $image) {
		unlink($image);
	}
	header("Location: buku.php");
	exit;
}

$edit = null;
if (isset($_GET['edit'])) {
	$id = (int)$_GET['edit'];
	$q = mysqli_query($conn, "SELECT * FROM buku WHERE id_buku=$id");
	$edit = mysqli_fetch_assoc($q);
}

$data = mysqli_query($conn, "SELECT * FROM buku ORDER BY id_buku DESC");
?>
<!DOCTYPE html>
<html>
<head>
	<title>Data Buku</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="container">
		<a class="btn" href="index.php">Kembali</a>
		<a class="btn red" href="logout.php">Logout</a>
		<h2>Data Buku</h2>
		<?php if ($msg) { ?>
			<div class="alert"><?=htmlspecialchars($msg)?></div>
		<?php } ?>

		<form method="post" class="form" enctype="multipart/form-data">
			<?php if ($edit) { ?>
				<input type="hidden" name="id_buku" value="<?=$edit['id_buku']?>">
			<?php } ?>
			<input name="judul_buku" placeholder="Judul Buku" value="<?=$edit?htmlspecialchars($edit['judul_buku']):''?>" required>
			<input name="penulis" placeholder="Penulis" value="<?=$edit?htmlspecialchars($edit['penulis']):''?>" required>
			<input name="penerbit" placeholder="Penerbit" value="<?=$edit?htmlspecialchars($edit['penerbit']):''?>" required>
			<input type="number" name="tahun_terbit" placeholder="Tahun Terbit" value="<?=$edit?$edit['tahun_terbit']:''?>" required>
			<input type="number" name="stok" placeholder="Stok" min="0" value="<?=$edit?$edit['stok']:''?>" required>
			<label for="gambar_buku">Gambar Buku (JPG, PNG, WEBP, maksimal 5 MB)</label>
			<input type="file" id="gambar_buku" name="gambar_buku" accept="image/jpeg,image/png,image/webp">
			<?php if ($edit && ($gambarEdit = getBookImagePath($edit['id_buku']))) { ?>
				<img class="book-cover-preview" src="<?=htmlspecialchars($gambarEdit)?>" alt="Sampul <?=htmlspecialchars($edit['judul_buku'])?>">
			<?php } ?>
			<?php if ($edit) { ?>
				<button name="update">Simpan Perubahan</button>
				<a class="btn" href="buku.php">Batal</a>
			<?php } else { ?>
				<button name="simpan">Tambah Buku</button>
			<?php } ?>
		</form>

		<table>
			<tr>
				<th>ID</th>
				<th>Gambar</th>
				<th>Judul</th>
				<th>Penulis</th>
				<th>Penerbit</th>
				<th>Tahun</th>
				<th>Stok</th>
				<th>Aksi</th>
			</tr>
			<?php while ($r = mysqli_fetch_assoc($data)) { ?>
				<tr>
					<td><?=$r['id_buku']?></td>
					<td>
						<?php $gambar = getBookImagePath($r['id_buku']); ?>
						<?php if ($gambar) { ?>
							<img class="book-cover" src="<?=htmlspecialchars($gambar)?>" alt="Sampul <?=htmlspecialchars($r['judul_buku'])?>">
						<?php } else { ?>
							<div class="book-cover-placeholder">
								<span>Belum ada sampul</span>
							</div>
						<?php } ?>
					</td>
					<td><?=htmlspecialchars($r['judul_buku'])?></td>
					<td><?=htmlspecialchars($r['penulis'])?></td>
					<td><?=htmlspecialchars($r['penerbit'])?></td>
					<td><?=$r['tahun_terbit']?></td>
					<td><?=$r['stok']?></td>
					<td>
						<a class="btn" href="?edit=<?=$r['id_buku']?>">Edit</a>
						<a class="btn red" href="?hapus=<?=$r['id_buku']?>" onclick="return confirm('Hapus buku?')">Hapus</a>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
</body>
</html>