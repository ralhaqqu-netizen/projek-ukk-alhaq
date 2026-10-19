<?php
include "auth.php";
include "connection.php";
if(isset($_POST['simpan'])){
 $u=mysqli_real_escape_string($conn,$_POST['username']); $p=mysqli_real_escape_string($conn,$_POST['password']); $r=mysqli_real_escape_string($conn,$_POST['role']);
 $r = in_array($r, ['admin','petugas','user']) ? $r : 'user';
 mysqli_query($conn,"INSERT INTO users(username,password,role) VALUES('$u','$p','$r')");
 header("Location: users.php"); exit;
}
if(isset($_POST['update'])){
 $id=(int)$_POST['id_users'];
 $u=mysqli_real_escape_string($conn,$_POST['username']); $p=mysqli_real_escape_string($conn,$_POST['password']); $r=mysqli_real_escape_string($conn,$_POST['role']);
 $r = in_array($r, ['admin','petugas','user']) ? $r : 'user';
 mysqli_query($conn,"UPDATE users SET username='$u',password='$p',role='$r' WHERE id_users=$id");
 header("Location: users.php"); exit;
}
if(isset($_GET['hapus'])){ $id=(int)$_GET['hapus'];
 $cek = mysqli_query($conn, "SELECT role FROM users WHERE id_users=$id LIMIT 1");
 $row = mysqli_fetch_assoc($cek);
 if($row && !in_array($row['role'], ['admin','user'])) { header("Location: users.php"); exit; }
 mysqli_query($conn,"DELETE FROM users WHERE id_users=$id"); header("Location: users.php"); exit; }
$edit=null;
if(isset($_GET['edit'])){ $id=(int)$_GET['edit']; $q=mysqli_query($conn,"SELECT * FROM users WHERE id_users=$id"); $edit=mysqli_fetch_assoc($q); }
$data=mysqli_query($conn,"SELECT * FROM users ORDER BY id_users DESC");
?>
<!DOCTYPE html><html><head><title>Data User</title><link rel="stylesheet" href="style.css"></head><body><div class="container">
<a class="btn" href="index.php">Kembali</a> <a class="btn red" href="logout.php">Logout</a><h2>Data User</h2>
<form method="post" class="form">
<?php if($edit){ ?><input type="hidden" name="id_users" value="<?=$edit['id_users']?>"><?php } ?>
<input name="username" placeholder="Username" value="<?=$edit?htmlspecialchars($edit['username']):''?>" required>
<input name="password" placeholder="Password" value="<?=$edit?htmlspecialchars($edit['password']):''?>" required>
<select name="role">  
<option value="user" <?=($edit && $edit['role']=='user')?'selected':''?>>User</option>  
<option value="petugas" <?=($edit && $edit['role']=='petugas')?'selected':''?>>Petugas</option>  
<option value="admin" <?=($edit && $edit['role']=='admin')?'selected':''?>>Admin</option>  
</select>
<?php if($edit){ ?><button name="update">Simpan Perubahan</button> <a class="btn" href="users.php">Batal</a><?php } else { ?><button name="simpan">Tambah User</button><?php } ?>
</form>
<table><tr><th>ID</th><th>Username</th><th>Password</th><th>Role</th><th>Aksi</th></tr>
<?php while($r=mysqli_fetch_assoc($data)){ ?><tr><td><?=$r['id_users']?></td><td><?=htmlspecialchars($r['username'])?></td><td><?=htmlspecialchars($r['password'])?></td><td><?=$r['role']?></td><td><a class="btn" href="?edit=<?=$r['id_users']?>">Edit</a> <a class="btn red" href="?hapus=<?=$r['id_users']?>" onclick="return confirm('Hapus user?')">Hapus</a></td></tr><?php } ?></table>
</div></body></html>