<?php
	include "db.php";
	include "date.php";
	include "main.php";

//registrasi
	if(isset($_GET['type']) && $_GET['type']=='registrasi')
	{
		$value 		= $_REQUEST['dataArray'];
		$waktu 		= date('Y-m-d H:i:s');
		$created_at = strtotime($waktu);
		$username 	= substr($value[0],0,20);
		$data_user	= str_replace('@','_',$username);

		if(isset($_GET['via']) && $_GET['via']=='fb'){
			$username	= str_replace('.','_',$data_user);
			$u			= mysqli_query($con,"update `user` set device_id = '".$value[9]."' 
									where username='".$username."' or email='".$value[0]."'");

		}else{
			$username	= $value[1];
		}

		$q	= mysqli_query($con,"select user.id,email,username,user.nama as nama_lengkap,status,password_reset_token, 
								tanggal_lahir, ifnull(total_notifikasi, 0) as total_notifikasi, user_grup.nama as nama_grup, device_id from user 
								left join (select count(id) as total_notifikasi,id_user from notifikasi 
									where terbaca is null)c on c.id_user=user.id
								left join user_grup on user.id_grup = user_grup.id
								 where username='$username' or email='$value[0]'");

		while ($row=mysqli_fetch_object($q)){
			$row->tanggal_lahir = js_to_mysql_date($row->tanggal_lahir);
			$data[] = $row;
		}

		$result = $data[0];
		//jika register dengan fb/google
		if(($result->device_id != '') && ($value[9] != '') && ($value[9] != $result->device_id) && ($result->device_id != 'null') && $result->status == '1'){
			echo "logged";
		}elseif ($result->email && (isset($_GET['via']) && $_GET['via']=='fb') && $result->status == '1'){
			echo json_encode($result);
		}elseif($result->email && (isset($_GET['via']) && $_GET['via']=='fb') && $result->status == '0'){
			echo "blokir";
		//username atau email sudah digunakan
		}elseif(strtolower($result->username) == strtolower($username)){
			echo "username";
		}elseif(strtolower($result->email) == strtolower($value[1])){
			echo "email";
		}else{
			if ($value[2] != ""){
				$password_hash = file_get_contents($link_password.'set_password?password='.$value[2]);
			}else{
				$password_hash = null;
			}
			$value[4] = $value[4] == '' ? null : ((isset($_GET['via']) && $_GET['via']=='fb') ? $value[4]: js_to_mysql_date($value[4]));
			$status = (isset($_GET['via']) && $_GET['via']=='fb') ? '1' : '0';

			$q 		= mysqli_query($con,"INSERT INTO `user` (`email`,`username`,`password_hash`, `nama`, `jenis_kelamin`,
				`alamat`, `biodata`, `updated_at`, `id_grup`, `status`, `created_at`, `tanggal_lahir`, `device_id`) VALUES
				('$value[0]', '$username', '$password_hash', '$value[3]', '$value[6]','$value[5]', '$value[7]',
				'$created_at', '$value[8]', '$status', '$created_at', '$value[4]', '$value[9]')");

			//ambil data user
			$u	= mysqli_query($con,"select user.id,email,username,user_grup.nama as nama_lengkap,tanggal_lahir,
								ifnull(total_notifikasi, 0) as total_notifikasi, user_grup.nama as nama_grup from user
								left join (select count(id) as total_notifikasi,id_user from notifikasi where terbaca is null)c on c.id_user=user.id
								 left join user_grup on user.id_grup = user_grup.id
								 where username='$username'");

			while ($row=mysqli_fetch_object($u)){
				$row->tanggal_lahir = js_to_mysql_date($row->tanggal_lahir);
				$data[] = $row;
			}
			$result = $data[0];
			if($result->id && (!isset($_GET['via']) && $_GET['via'] !='fb')){
				file_get_contents($link_password.'verifikasi_akun/'.$result->username.'/'.$value[2]);
			}
			if($q)
				echo json_encode($result);
			else
				echo "error";
		}
	}
//insert komentar
	elseif(isset($_GET['type']) && $_GET['type']=='komentar')
	{
		$isi 		= $_POST['isi'];
		$grup 		= $_POST['grup'];
		$id_user 	= $_POST['id_user'];
		$id_laporan = $_POST['id_laporan'];
		$waktu 		= date('Y-m-d H:i:s');
		$nama_lengkap = $_POST['nama_lengkap'];
		
		$q 			= mysqli_query($con,"INSERT INTO `komentar` (`id_laporan`,`id_user`,`isi`, `tanggal`) VALUES
						('$id_laporan', '$id_user','$isi','$waktu')");

		//ambil data laporan yang akan diupdate dan skpd
		$laporan	= mysqli_query($con,"SELECT TIMESTAMPDIFF(hour,tgl_laporan,now()) as waktu_keterlambatan,
						laporan.id_user, sub_kategori_laporan_skpd.id_user
						from laporan
						left join sub_kategori_laporan on sub_kategori_laporan.id=laporan.id_sub_kategori
						left join sub_kategori_laporan_skpd on sub_kategori_laporan.id=sub_kategori_laporan_skpd.id_sub_kategori_laporan
						where laporan.id='$id_laporan'");
		$result_laporan = mysqli_fetch_row($laporan);

		if($grup == 'SKPD'){
			$waktu_notif		= mysqli_query($con,"SELECT value from konfigurasi where nama='waktu_notifikasi' ");
			$waktu_notifikasi 	= mysqli_fetch_row($waktu_notif);
			if ($result_laporan[0] <= $waktu_notifikasi[0]){
				$is_late = 0;
			}else{
				$is_late = 1;
			}

			$komentar	= mysqli_query($con,"select * from komentar where id_laporan='$id_laporan' and id_user=".$id_user);

			//update status pada laporan
			if ($komentar->num_rows == 1){
				$laporan_update 	= mysqli_query($con,"UPDATE `laporan` SET `status` = '1', `is_late`='$is_late',
											tgl_balas='$waktu' WHERE `id` = '$id_laporan'");
			}
		}


		//input & send notifikasi
		$u = mysqli_query($con,"select distinct(id_user) as id_user from komentar where
					id_laporan='$id_laporan' and id_user!='$id_user'
					union select id_user from laporan where laporan.id='$id_laporan' and id_user!='$id_user'
					union select sub_kategori_laporan_skpd.id_user from laporan
					left join sub_kategori_laporan on sub_kategori_laporan.id=laporan.id_sub_kategori
					left join sub_kategori_laporan_skpd on sub_kategori_laporan.id=sub_kategori_laporan_skpd.id_sub_kategori_laporan
					where laporan.id='$id_laporan' and sub_kategori_laporan_skpd.id_user!='$id_user'");
		if($u->num_rows > 0){
			 foreach( $u as $row ) {
				$user 		= $row['id_user'];
				$penerima	= mysqli_query($con,"SELECT device_id from user where id='$user'");
				$device 	= mysqli_fetch_row($penerima);

				if($device[0]){
					$notif = new \stdClass();
					$notif->ids 	= $device[0];
					$notif->title 	= $nama_lengkap;
					$notif->message = $isi;
					$notif->url 	= "elaporbantul://laporan-detail.html?id=".$id_laporan;
					sendNotificationById($notif);
				}

				$komentar_baru	= mysqli_query($con,"select * from komentar where id_laporan='$id_laporan' and terbaca is null and id_user=".$id_user);
			
			
				if ($komentar_baru->num_rows > 0){
					$p	= mysqli_query($con,"INSERT INTO `notifikasi` (`id_user`,`id_laporan`,`terbaca`,`komentar`) VALUES 
							('$user', '$id_laporan',null,'1')");
				}else{
					$d  = mysqli_query($con, "delete from notifikasi where id_user=$user and id_laporan= $id_laporan and komentar='1'");
					$p	= mysqli_query($con,"INSERT INTO `notifikasi` (`id_user`,`id_laporan`,`terbaca`,`komentar`) VALUES 
							('$user', '$id_laporan',null,'1')");
					/*$u	= mysqli_query($con,"Update notifikasi set terbaca = null where id_laporan='$id_laporan'
										and id_user='$user' order by id desc limit 1");*/
				}
			}
		}

		if($q)
			echo "success";
		else
			echo "error";
	}
//insert laporan
	elseif(isset($_GET['type']) && $_GET['type']=='laporan')
	{
		$value = $_REQUEST['dataArray'];
		$waktu = date('Y-m-d H:i:s');
		
		//jika ada gambar
		if ($value[4]){
			$img = $value[4];
			$img = str_replace('data:image/jpeg;base64,', '', $img);
			$img = str_replace(' ', '+', $img);
			$data = base64_decode($img);

			$conn_id 		= ftp_connect('produksi.jmc.co.id') or die("Could not connect to produksi");
			$login_result 	= ftp_login($conn_id, 'jmc', 'sp1r1t2016');
			@move_uploaded_file($value[4], $link_upload.$result_file[0].'.jmc');
			file_put_contents($link_upload.$result_file[0].'.jmc',$data);

			$id_file		= mysqli_query($con,"SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'dev_2017_elapor_bantul' AND TABLE_NAME = 'file' ");
			$result_file 	= mysqli_fetch_row($id_file);
			$file 			= mysqli_query($con,"INSERT INTO `file` (`nama`) VALUES ('".($result_file[0]).'-'.substr($value[3], 0, 96).'.png'."')");
			$id_file		= mysqli_query($con,"select max(id) as id from file");
			$result_file 	= mysqli_fetch_row($id_file);

		}
		if ($result_file[0]){
			$id_file = $result_file[0];
			file_put_contents($link_upload.$result_file[0].'.jmc',$data);
		}else{
			$id_file = 'null';
		}

		$q 			= mysqli_query($con,"INSERT INTO `laporan` (`id_sub_kategori`,`id_user`,`lokasi`, `isi`, `tgl_laporan`, `id_file`) VALUES
						('$value[0]', '$value[1]', '$value[2]', '$value[3]','$waktu', $id_file)");

		//ambil data laporan
		$komentar	= mysqli_query($con,"select sub_kategori_laporan.nama as nama_kategori, tgl_laporan, laporan.id,
							sub_kategori_laporan_skpd.id_user, file.nama as nama_file, file.id as id_file,
							dayname(tgl_laporan) as hari, user.device_id as device, laporan.isi, masyarakat.nama as nama_masyarakat
							from laporan
							left join sub_kategori_laporan on sub_kategori_laporan.id=laporan.id_sub_kategori
							left join sub_kategori_laporan_skpd on sub_kategori_laporan.id=sub_kategori_laporan_skpd.id_sub_kategori_laporan
							left join user on user.id=sub_kategori_laporan_skpd.id_user
							left join user masyarakat on masyarakat.id=laporan.id_user
							left join file on file.id=laporan.id_file
							where tgl_laporan = '$waktu' and laporan.id_user='$value[1]'");

		$laporan = mysqli_fetch_row($komentar);

		//send notif ke skpd yang bersangkutan
		if($laporan[7]){
			$notif = new stdClass();
			$notif->ids 	= $laporan[7];
			$notif->title 	= $laporan[9];
			$notif->message = $laporan[8];
			$notif->url 	= "elaporbantul://laporan-detail.html?id=".$laporan[2];
			sendNotificationById($notif);
		}

		$laporan[1] = to_indo_day_name($laporan[6]).', '.format_tanggal($laporan[1]);
		$laporan[4]	= $laporan[4] != null ? $link_get_pict.$laporan[5].'/'.$laporan[4] : '';

		$u	= mysqli_query($con,"INSERT INTO `notifikasi` (`id_user`,`id_laporan`,`terbaca`) VALUES
						('$laporan[3]', '$laporan[2]',null)");

		if($q)
			echo json_encode($laporan);
		else
			echo "error";
	}else{
		echo "failed";
	}
 ?>
