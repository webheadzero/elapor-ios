<?php
include "db.php";
include "date.php";

$username	= $_POST['username'];
$password	= $_POST['password'];
$device		= $_POST['device-id'];
$waktu		= strtotime(date('Y-m-d H:i:s'));
$hasil		= new \stdClass();
$q = mysqli_query($con,"select user.id, username, password_hash, user.nama as nama_lengkap, user_grup.nama as nama_grup, 
						tanggal_lahir,updated_at, file.nama as foto_user, ifnull(total_notifikasi, 0) as total_notifikasi, file.id as id_foto,
						device_id, password_reset_token,status
						from `user` 
						left join user_grup on user.id_grup = user_grup.id
						left join file on user.id_file = file.id
						left join (select count(id) as total_notifikasi,id_user from notifikasi where terbaca is null group by id_user)c on c.id_user=user.id
						where username='$username'");
						
while ($row=mysqli_fetch_object($q)){
	$row->tgl_lahir 	= popup_tgl($row->tanggal_lahir);
	$row->tanggal_lahir = js_to_mysql_date($row->tanggal_lahir);
	$data[] = $row;
}
$result = $data[0];

$result->foto_user	= $result->foto_user != null ? $link_get_pict.$result->id_foto.'/'.$result->foto_user : '';

$validate_password = file_get_contents($link_password.'validate_password?password='.$password.'&password_hash='.$result->password_hash);

if(($q->num_rows > 0) && $validate_password && $result->status == '1'){
	if($result->device_id != '' && $device != '' && $device != $result->device_id && $result->device_id != 'null'){
		$hasil = 'logged';
		echo json_encode($hasil);
	}else{
		if ($result->device_id == null || $result->device_id == ''){
			$u = mysqli_query($con,"update `user` set `updated_at`='".$waktu."', `device_id`='".$device."' where id='".$result->id."'");
		}
		echo json_encode($result);
	}
}elseif($result->status == '0' && $result->password_reset_token == ''){
	$hasil = 'blokir';
	echo json_encode($hasil);
}elseif($result->status == '0' && $result->password_reset_token != ''){
	$hasil = 'verifikasi';
	echo json_encode($hasil);
}else{
	echo "error";
}
?>