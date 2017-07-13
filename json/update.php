<?php
 include "db.php";
 include "date.php";

 //edit profil
	if(isset($_GET['type']) && $_GET['type']=='edit-profil')
	{
		$value = $_REQUEST['dataArray'];
		$waktu = date('Y-m-d H:i:s');
		if($value[2]){
			$password_hash = file_get_contents($link_password.'set_password?password='.$value[2]);
		}else{
			$password_hash = '';
		}
		$value[4] = $value[4] ? js_to_mysql_date($value[4]) : null;

		$u 		= mysqli_query($con,"update `user` set `email`='$value[0]',`username`='$value[1]',".
								($password_hash ? "`password_hash`='$password_hash'," : "") .
								"`nama`='$value[3]', `jenis_kelamin`='$value[6]',
								`tanggal_lahir`='$value[4]', `alamat`='$value[5]', `biodata`='$value[7]' where id='$value[8]'");
		$q 		= mysqli_query($con,"select user.id, username, password_hash, user.nama as nama_lengkap, user_grup.nama as nama_grup,
						tanggal_lahir,updated_at, file.nama as foto_user, ifnull(total_notifikasi, 0) as total_notifikasi, file.id as id_foto,
						device_id
						from `user`
						left join user_grup on user.id_grup = user_grup.id
						left join file on user.id_file = file.id
						left join (select count(id) as total_notifikasi,id_user from notifikasi where terbaca is null group by id_user)c on c.id_user=user.id
						where username='".$value[1]."'");
		while ($row=mysqli_fetch_object($q)){
			$row->tgl_lahir 	= popup_tgl($row->tanggal_lahir);
			$row->tanggal_lahir = js_to_mysql_date($row->tanggal_lahir);
			$data[] = $row;
		}
		$result = $data[0];

		$result->foto_user	= $result->foto_user != null ? $link_get_pict.$result->id_foto.'/'.$result->foto_user : '';

		if($u)
			echo json_encode($result);
		else
			echo "error";
	}
//ganti password
	elseif(isset($_GET['type']) && $_GET['type']=='ganti-password')
	{
		$value = $_REQUEST['dataArray'];
		$password_hash = file_get_contents($link_password.'set_password?password='.$value[1]);

		$q 		= mysqli_query($con,"update `user` set `password_hash`='$password_hash' where id='$value[3]'");

		if($q)
			echo "success";
		else
			echo "error";
	}
//update notifikasi yang sudah terbaca
	elseif(isset($_GET['type']) && $_GET['type']== 'notifikasi')
	{
		$u	= mysqli_query($con,"select count(id) from notifikasi where id_laporan=".$_POST['id_laporan'].
								" and id_user=".$_POST['id_user']." and terbaca is null");
		$result	= mysqli_fetch_row($u);	
		$q	= mysqli_query($con,"UPDATE `notifikasi` SET `terbaca` = '1' WHERE id_laporan=".$_POST['id_laporan']);
		if($q)
			echo json_encode($result);
		 else
			echo "error";
	}
//ganti foto profil
	elseif(isset($_GET['type']) && $_GET['type']== 'foto-profil')
	{
		$value = $_REQUEST['dataArray'];

		//gambar
		if ($value[1]){
			$img = $value[1];
			$img = str_replace('data:image/jpeg;base64,', '', $img);
			$data = base64_decode($img);

			//login ke ftp
			$conn_id = ftp_connect('produksi.jmc.co.id') or die("Could not connect to produksi");
			$login_result = ftp_login($conn_id, 'jmc', 'sp1r1t2016');

			//ambil id_file user
			$user_file		= mysqli_query($con,"select id_file,file.nama as nama_file from user
										left join file on file.id=user.id_file where user.id = '$value[0]'");
			$result_user 	= mysqli_fetch_row($user_file);
			//delete file lama user
			$delete_file 	= mysqli_query($con, "delete from file where id=$result_user[0]");
			@unlink($link_upload.$result_user[0].'.jmc');

			//insert file baru
			$file 			= mysqli_query($con,"INSERT INTO `file` (`nama`) VALUES ('".($result_file[0]).'-'.substr($value[2], 0, 96).'.png'."')");
			$id_file		= mysqli_query($con,"select max(id) as id from file");
			$result_file 	= mysqli_fetch_row($id_file);

			//pindah gambar ke server
			file_put_contents($link_upload.$result_file[0].'.jmc', $data);
			//update file ke user
			$q 			= mysqli_query($con,"update `user` set `id_file`='$result_file[0]' where id='$value[0]'");

			if($q)
				echo "success";
			else
				echo "error";
		}
	}
//update data ketika logout
	elseif(isset($_GET['type']) && $_GET['type']== 'logout')
	{
		$q 			= mysqli_query($con,"update `user` set device_id = NULL where id=".$_GET['id_user']);
		if($q)
			echo "success";
		else
			echo "error";
	}
	elseif(isset($_GET['type']) && $_GET['type']== 'device')
	{
		$waktu	= strtotime(date('Y-m-d H:i:s'));
		$u		= mysqli_query($con,"update `user` set device_id = '".$_GET['device_id']."',
								`updated_at`='".$waktu."' where username='".$_GET['username']."' or email='".$_GET['username']."'");

		$q 		= mysqli_query($con,"select user.id, username, password_hash, user.nama as nama_lengkap, user_grup.nama as nama_grup,
						tanggal_lahir,updated_at, file.nama as foto_user, ifnull(total_notifikasi, 0) as total_notifikasi, file.id as id_foto,
						device_id
						from `user`
						left join user_grup on user.id_grup = user_grup.id
						left join file on user.id_file = file.id
						left join (select count(id) as total_notifikasi,id_user from notifikasi where terbaca is null group by id_user)c on c.id_user=user.id
						where username='".$_GET['username']."' or email='".$_GET['username']."'");
		while ($row=mysqli_fetch_object($q)){
			$row->tgl_lahir 	= popup_tgl($row->tanggal_lahir);
			$row->tanggal_lahir = js_to_mysql_date($row->tanggal_lahir);
			$data[] = $row;
		}
		$result = $data[0];

		$result->foto_user	= $result->foto_user != null ? $link_get_pict.$result->id_foto.'/'.$result->foto_user : '';


		if($u)
			echo json_encode($result);
		else
			echo "error";
	}
 ?>
