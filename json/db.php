<?php
 header("Access-Control-Allow-Origin: *");
 $con = mysqli_connect("produksi.jmc.co.id","jmc","sp1r1t2016","testing_2017_elapor_bantul") or die ("could not connect database");
 //$con = mysqli_connect("localhost","root","","dev_2017_elapor_bantul") or die ("could not connect database");
 
 $link_get_pict = 'http://produksi.jmc.co.id/2017/elapor_bantul/website/view_image/view/';
 $link_upload	= '../../../website/upload/';
 $link_password = 'http://produksi.jmc.co.id/2017/elapor_bantul/website/site/';

?>