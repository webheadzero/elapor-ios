<?php
 header("Access-Control-Allow-Origin: *");
function format_tanggal($tanggal, $tipe='long'){
	$array 		= explode(' ', $tanggal);
	$arraytime	= explode(':', $array[1]);
	$arraydate 	= explode('-', $array[0]);

	if(count($array)==1)
	{
		$array[1] = '';
	}
	else
	{
		$array[1] = ' - ' . substr($array[1], 0, 8);
	}

	if(count($arraydate) == 3)
	{
		if($tipe == 'short')
		{
			$nama_bulan = nama_bulan_pendek($arraydate[1]);
		}
		else
		{
			$nama_bulan = nama_bulan($arraydate[1]);
		}

		$output = $arraydate[2] . ' ' . $nama_bulan . ' ' . $arraydate[0] . ' '. $arraytime[0]. ':'. $arraytime[1];
		return $output;
	}
	else
	{
		return null;
	}

}

function nama_bulan($bulan)
{
	$nama = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 
							'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
	return($nama[$bulan-1]);
}

function nama_bulan_pendek($bulan)
{
	$nama = array('Jan', 'Feb', 'Mar', 'Apr', 'Mei', 
							'Jun', 'Jul', 'Agust', 'Sept', 'Okt', 'Nov', 'Des');
	return($nama[$bulan-1]);
}

function to_indo_day_name($dayname){
	$nama = array('Sunday'=>'Minggu', 
					'Monday' => 'Senin', 
					'Tuesday' => 'Selasa', 
					'Wednesday' => 'Rabu', 
					'Thursday' => 'Kamis', 
					'Friday' => 'Jumat', 
					'Saturday' => 'Sabtu');
	return($nama[$dayname]);
}

function js_to_mysql_date($tgl){
	preg_match("/^([0-9]+)-([0-9]+)-([0-9]+)/", $tgl, $matches);
	$hasil = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
	return($matches[3] ? $hasil : $tgl);
}

function popup_tgl($tgl){
	if($tgl){
		preg_match("/^([0-9]+)-([0-9]+)-([0-9]+)/", $tgl, $matches);
		$tgl = $matches[2] . '-' . $matches[3] . '-' . $matches[1];
	}
	return($tgl);
}
?>