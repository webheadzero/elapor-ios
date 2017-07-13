<?php
 header("Access-Control-Allow-Origin: *");
include "db.php";
include "date.php"; 
$data=array();
if(isset($_GET['id']))
{
	$q = mysqli_query($con,"select sub_kategori_laporan.nama as nama_kategori, user.nama as nama_lengkap, 
					file.nama as nama_file, isi, lokasi, file.id as id_file, f.id as id_foto_user,
					tgl_laporan, dayname(tgl_laporan) as hari, f.nama as foto_user, laporan.id, 
					ifnull(jml_komentar,0) as jml_komentar, sub_kategori_laporan_skpd.id_user, laporan.id_user as user_masyarakat
					from laporan 
					left join user on laporan.id_user=user.id
					left join file f on user.id_file=f.id 
					left join sub_kategori_laporan on sub_kategori_laporan.id=laporan.id_sub_kategori 
					left join sub_kategori_laporan_skpd on sub_kategori_laporan.id=sub_kategori_laporan_skpd.id_sub_kategori_laporan
					left join file on file.id=laporan.id_file
					left join (select count(id) as jml_komentar,id_laporan from komentar group by id_laporan)c on c.id_laporan=laporan.id 
					where laporan.id = ".$_GET['id']);
	$komentar = mysqli_query($con,"select k.id, user.nama as nama_lengkap, isi, user_grup.nama as grup, f.nama as foto_user, f.id as id_foto_user from komentar k 
									left join user on user.id=k.id_user 
									left join user_grup on user.id_grup = user_grup.id
									left join file f on user.id_file=f.id 
									where id_laporan= ".$_GET['id']
									." order by k.id asc");
	$comment = [];
	$i=0;
	while ($row_komentar=mysqli_fetch_object($komentar)){
		$row_komentar->foto_user = $row_komentar->foto_user != null ? $link_get_pict . $row_komentar->id_foto_user . '/' . $row_komentar->foto_user : '';
		$comment[$i] = $row_komentar;
		$i++;
	}
	$i=0;
	while ($row=mysqli_fetch_object($q)){
		$row->tgl_laporan = to_indo_day_name($row->hari).', '.format_tanggal($row->tgl_laporan);
		$row->nama_file		= $row->nama_file != null ? $link_get_pict.$row->id_file.'/'.$row->nama_file : '';
		$row->foto_user		= $row->foto_user != null ? $link_get_pict . $row->id_foto_user . '/' . $row->foto_user : '';
		$row->akses_komentar= ($row->id_user == $_GET['id_user'] || strtolower($_GET['nama_grup']) == 'admin' || $row->user_masyarakat == $_GET['id_user']) ? '1' : '';
		$data['laporan'][$i] = $row;
		$i++;
	}
	$data['komentar'] = $comment;
}elseif(isset($_GET['type']) && $_GET['type']== 'list_bantuan')
{
	$q = mysqli_query($con,"select * from `bantuan`");
	$i=0;
	while ($row=mysqli_fetch_object($q)){
		$row->isi = strip_tags($row->isi);
		$data['result'][$i] = $row;
		$i++;
	}
}elseif(isset($_GET['type']) && $_GET['type']== 'bantuan_detail')
{
	$q = mysqli_query($con,"select * from `bantuan` where judul_seo='".$_GET['judul_seo']."'");
	
	while ($row=mysqli_fetch_object($q)){
		$row->isi = strip_tags($row->isi);
		$data['result'][0]=$row;
	}
}elseif(isset($_GET['type']) && $_GET['type']== 'list_kategori')
{
	$q = mysqli_query($con,"select k.id, k.nama as nama_kategori, f.nama as nama_file, k.id_file from `kategori_laporan` k 
							left join `file` f on f.id=k.id_file");
	while ($row=mysqli_fetch_object($q)){
		$row->nama_file		= $row->nama_file != null ? $link_get_pict.$row->id_file.'/'.$row->nama_file : '';
		$data[]=$row;
	}
}elseif(isset($_GET['type']) && $_GET['type']== 'list_sub_kategori')
{
	$q = mysqli_query($con,"select k.id, k.nama as nama_kategori, f.nama as nama_file, k.id_file from `sub_kategori_laporan` k 
							left join `file` f on f.id=k.id_file
							". ($_GET['id_kategori'] ? " where k.id_kategori_laporan=".$_GET['id_kategori'] : ""));
							
	$i = 0;
	while ($row=mysqli_fetch_object($q)){
		$row->nama_file		= $row->nama_file != null ? $link_get_pict.$row->id_file.'/'.$row->nama_file : '';
		$data['result'][$i]=$row;
		$i++;
	}
}elseif(isset($_GET['type']) && $_GET['type']== 'notifikasi')
{
	$_GET['offset'] = $_GET['offset'] ? $_GET['offset'] : 0;
	$q = mysqli_query($con,"select terbaca, laporan.isi as laporan, laporan.tgl_laporan, 
							sub_kategori_laporan.nama as nama_kategori, notifikasi.id_laporan, 
							notifikasi.id, dayname(tgl_laporan) as hari, date(tgl_laporan) as tanggal, 
							notifikasi.komentar,
							laporan.id_user, user.nama as nama_masyarakat
							from `notifikasi` 
							left join laporan on notifikasi.id_laporan=laporan.id
							left join sub_kategori_laporan on laporan.id_sub_kategori=sub_kategori_laporan.id
							left join user on laporan.id_user=user.id
							where notifikasi.id_user = ".$_GET['id_user']." order by notifikasi.id desc
							 limit 10 offset ".$_GET['offset']);
							 
	while ($row=mysqli_fetch_object($q)){
		$row->tgl_laporan = to_indo_day_name($row->hari).', '.format_tanggal($row->tgl_laporan);
		
		$komentar = mysqli_query($con, "select user.nama, dayname(max(tanggal)) as hari, max(tanggal) as tanggal from komentar 
						left join user on user.id=komentar.id_user where id_laporan = ".$row->id_laporan."
						and komentar.id_user != ".$_GET['id_user']." group by id_user");
		$user_komentar = [];
		$i=0;
		while ($rows=mysqli_fetch_object($komentar)){
			$user_komentar[$i]=$rows->nama;
			$hari_komentar = $rows->hari;
			$tanggal_komentar = $rows->tanggal;
			$i++;
		}
		$list_user_komentar = implode (", ", $user_komentar);
		
		if ($row->komentar == '0'){
			$row->laporan = $row->nama_masyarakat . ' mengirim keluhan baru.';
		}elseif($_GET['id_user'] == $row->id_user){
			$row->laporan =  $list_user_komentar.' memberi komentar pada keluhan Anda.';
			$row->tgl_laporan = to_indo_day_name($hari_komentar).', '.format_tanggal($tanggal_komentar);
		}elseif($_GET['id_user'] != $row->id_user){
			if ($row->komentar == 1){
				if ($list_user_komentar == $row->nama_masyarakat){
					$row->laporan =  $list_user_komentar.' memberi komentar pada keluhannya.';
				}else{
					$row->laporan =  $list_user_komentar.' memberi komentar pada keluhan '.$row->nama_masyarakat;
				}
			}
			$row->tgl_laporan = to_indo_day_name($hari_komentar).', '.format_tanggal($tanggal_komentar);
		}
		
		if($row->tanggal == date('Y-m-d')){
			$today[]=$row;
		}else{
			$all[] =$row;
		}
		$data['today'] = $today;
		$data['all'] =$all;
	}
}elseif(isset($_GET['id_sub_kategori']))
{
	$_GET['offset'] = $_GET['offset'] ? $_GET['offset'] : 0;
	$q = mysqli_query($con,"select sub_kategori_laporan.nama as nama_kategori, user.nama as nama_lengkap, file.nama as nama_file, isi,
					tgl_laporan, dayname(tgl_laporan) as hari, f.nama as foto_user, laporan.id, laporan.id_user as pengirim,
					sub_kategori_laporan_skpd.id_user as skpd, lokasi, file.id as id_file, f.id as id_foto_user
					from laporan 
					left join user on laporan.id_user=user.id
					left join file f on user.id_file=f.id 
					left join sub_kategori_laporan on sub_kategori_laporan.id=laporan.id_sub_kategori 
					left join sub_kategori_laporan_skpd on sub_kategori_laporan.id=sub_kategori_laporan_skpd.id_sub_kategori_laporan
					left join file on file.id=laporan.id_file
					where id_sub_kategori = ".$_GET['id_sub_kategori']."
					order by laporan.id desc limit 10 offset ".$_GET['offset']);
					
	$user	= mysqli_query($con,"select id_grup from user where id='".$_GET['id_user']."'");
	
	$result_user = mysqli_fetch_row($user);	
	
	$i = 0;
	while ($row=mysqli_fetch_object($q)){
		$row->tgl_laporan = to_indo_day_name($row->hari).', '.format_tanggal($row->tgl_laporan);
		$row->nama_file		= $row->nama_file != null ? $link_get_pict.$row->id_file.'/'.$row->nama_file : '';
		$row->foto_user		= $row->foto_user != null ? $link_get_pict . $row->id_foto_user . '/' . $row->foto_user : '';
		$row->user_login 	= $_GET['id_user'];
		$row->pengirim		= $row->pengirim;
		$row->id_grup		= $result_user[0];
		
		if (($row->skpd == $row->user_login) || ($row->user_login == $row->pengirim) || ($row->id_grup == '1')){
			$row->komentar = 1;
		}
		
		$data['result'][$i] = $row;
		$i++;
	}
}elseif((isset($_GET['type']) && $_GET['type']== 'list'))
{
	$_GET['offset'] = $_GET['offset'] ? $_GET['offset'] : 0;
	$q=mysqli_query($con,"select sub_kategori_laporan.nama as nama_kategori, user.nama as nama_lengkap, file.id as id_file, file.nama as nama_file, 
					isi, tgl_laporan, ifnull(terespon,0) as terespon, f.id as id_foto_user, f.nama as foto_user,laporan.id, dayname(tgl_laporan) as hari,
					sub_kategori_laporan_skpd.id_user as skpd, laporan.id_user as pengirim, lokasi
					from laporan 
					left join user on laporan.id_user=user.id
					left join file f on user.id_file=f.id 
					left join sub_kategori_laporan on sub_kategori_laporan.id=laporan.id_sub_kategori 
					left join sub_kategori_laporan_skpd on sub_kategori_laporan.id=sub_kategori_laporan_skpd.id_sub_kategori_laporan
					left join file on file.id=laporan.id_file
					left join (select count(distinct(id_laporan)) as terespon,id_laporan from komentar group by id_laporan)c on c.id_laporan=laporan.id 
					order by laporan.id desc limit 10 offset ".$_GET['offset']); 
	$ttl 		= mysqli_query($con,"select count(laporan.id) as total from laporan".
								(strtolower($_GET['nama_grup']) == 'skpd' ? 
								" left join sub_kategori_laporan on sub_kategori_laporan.id=laporan.id_sub_kategori 
								left join sub_kategori_laporan_skpd on sub_kategori_laporan.id=sub_kategori_laporan_skpd.id_sub_kategori_laporan
								where sub_kategori_laporan_skpd.id_user=".$_GET['id_user'] : ""));				
	$terespon 	= mysqli_query($con,"select count(laporan.id) as terespon from laporan".
								(strtolower($_GET['nama_grup']) == 'skpd' ? 
								" left join sub_kategori_laporan on sub_kategori_laporan.id=laporan.id_sub_kategori 
								left join sub_kategori_laporan_skpd on sub_kategori_laporan.id=sub_kategori_laporan_skpd.id_sub_kategori_laporan
								where status=1 and sub_kategori_laporan_skpd.id_user=".$_GET['id_user'] : " where status=1"));			
	$notifikasi	= mysqli_query($con,"select ifnull(count(id),0) as total_notifikasi from notifikasi where terbaca is null and id_user=".$_GET['id_user']);
	
	$total 		= mysqli_fetch_row($ttl);	
	$jml_notif 	= mysqli_fetch_row($notifikasi);	
	
	$comment = [];
	$laporan = [];
	$notif 	 = [];
	while ($row_komentar=mysqli_fetch_object($terespon)){
		$comment[] = $row_komentar;
	}
	
	$user	= mysqli_query($con,"select id_grup from user where id='".$_GET['id_user']."'");
	
	$result_user = mysqli_fetch_row($user);	
		
	while ($row=mysqli_fetch_object($q)){
		$row->tgl_laporan 	= to_indo_day_name($row->hari).', '.format_tanggal($row->tgl_laporan);
		$row->nama_file		= $row->nama_file != null ? $link_get_pict.$row->id_file.'/'.$row->nama_file : '';
		$row->foto_user		= $row->foto_user != null ? $link_get_pict . $row->id_foto_user . '/' . $row->foto_user : '';
		$row->user_login 	= $_GET['id_user'];
		$row->id_grup		= $result_user[0];
		$row->pengirim		= $row->pengirim;
		
		if (($row->skpd == $row->user_login) || ($row->user_login == $row->pengirim) || ($row->id_grup == '1')){
			$row->komentar = 1;
		}
		$laporan[] = $row;
	}
	$data['laporan'] 	= $laporan;
	$data['terespon'] 	= $comment;
	$data['total'] 		= $total;
	$data['notif'] 		= $jml_notif;
	$data['blm_dibalas']= $total[0]- $comment[0]->terespon;
}
elseif(isset($_GET['type']) && $_GET['type']=='jml_notif'){
	$q = mysqli_query($con,"select count(id) from notifikasi where terbaca is null and id_user = ".$_GET['id_user']);
	$data = mysqli_fetch_row($q);	
}elseif(isset($_GET['id_user']))
{
	$q = mysqli_query($con,"select user.*, dayname(updated_at) as hari, file.nama as foto_user, user.nama as nama_lengkap,
							dayname(now()) as last_login, status,device_id from user
							left join file on file.id=user.id_file where user.id = ".$_GET['id_user']);
	while ($row=mysqli_fetch_object($q)){
		$row->tanggal_lahir = js_to_mysql_date($row->tanggal_lahir);
		$last_login 		= date('Y-m-d H:i:s', $row->updated_at);
		$row->foto_user		= $row->foto_user != null ? $link_get_pict.$row->id_file.'/'.$row->foto_user : '';
		$row->status_aktif 	= $row->status;
		$row->updated_at 	= to_indo_day_name($row->last_login).', '.format_tanggal($last_login);
		$data['result'][]	= $row;
	}
}
/*}elseif(isset($_GET['type']) && $_GET['type']=='notifikasi'){
	$q = mysqli_query($con,"SELECT notifikasi.id, user.nama_lengkap as nama_user, laporan.isi as isi_laporan, 
							user_laporan.nama_lengkap as nama_user_laporan, 
							user_komentar.nama_lengkap as nama_user_komentar, notifikasi.waktu, terbaca, dayname(notifikasi.waktu) as hari 
							FROM `notifikasi` 
							left join user on user.id=notifikasi.id_user 
							left join user user_laporan on user_laporan.id=notifikasi.id_user_laporan 
							left join user user_komentar on user_komentar.id=notifikasi.id_user_komentar 
							left join laporan on laporan.id=notifikasi.id_laporan 
							where id_user=".$_GET['id_user']);
	$total = mysqli_query($con,"SELECT count(id) from notifikasi 
							where id_user=".$_GET['id_user']." and terbaca=0");
	while ($row=mysqli_fetch_object($q)){
		$row->waktu = to_indo_day_name($row->hari).', '.format_tanggal($row->waktu);
		$data[]=$row;
	}	*/

echo json_encode($data);
?>