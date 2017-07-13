//ganti foto profil,
$$(document).on('DOMContentLoaded', function(){
	$$(document).on('click','.logout', function () {
		var userData = window.userData();
		logout(userData.id);

		window.localStorage.clear();

		window.plugins.googleplus.logout(
				function (msg) {
				  //alert(msg); // do something useful instead of alerting
				}
			);
		mainView.router.loadPage('index.html');
	});

	$$(document).on('click','#image-avatar',function(){

		 if (!navigator.camera) {
			  myApp.alert("Camera API not supported", "Error");
			  return;
		  }

		  var buttons = [
			{
				text: 'Ambil Gambar',
				onClick : function(){
					navigator.camera.getPicture(onSuccess, onFail, { quality: 25,
					destinationType:Camera.DestinationType.DATA_URL,correctOrientation: true });
				}
			},
			{
				text: 'Buka Galeri',
				onClick : function(){
					navigator.camera.getPicture(onSuccess, onFail, { quality: 25,
					destinationType: Camera.DestinationType.DATA_URL,correctOrientation: true,
					sourceType: Camera.PictureSourceType.PHOTOLIBRARY });
				}
			},
		];

        myApp.actions(buttons);
    });

	function onSuccess(imageData) {
      var imgPlaceholder = document.getElementById('image-avatar');

      imgPlaceholder.style.display = 'block';
      imgPlaceholder.src = "data:image/jpeg;base64," + imageData;

	  var dataString = [];
	  var userData = window.userData();

	  dataString[0] = userData.id;
	  dataString[1] = "data:image/jpeg;base64," + imageData;
	  dataString[2] = userData.nama_lengkap;


		  $$.ajax({
				type: "POST",
				url: jsonUrl + "update.php?type=foto-profil",
				data: { dataArray : dataString },
				crossDomain: true,
				cache: false,
				beforeSend: function() {

				},
				success: function(data) {
					if (data == "success") {
						window.localStorage.setItem('userdata', JSON.stringify(data));
						userdata = JSON.parse(window.localStorage.getItem('userdata'));
					
						myApp.alert('Photo berhasil diubah.', 'Informasi');
					} else if (data == "error") {
						myApp.alert("Photo gagal diubah.", 'Informasi');
					}
				}
			});

    }

    function onFail(message) {
      console.log('Failed because: ' + message);
    }

	//popup share
	$$(document).on('click','.share', function () {
		myApp.popup('.popup-share');
	});
});



//edit profil
myApp.onPageInit('edit-profil', function (page) {
	var userData = window.userData();
	var url = jsonUrl + "json.php?id_user="+userData.id;

	window.AjaxPage(url,'#edit_profil');

	$$(document).on('click','.save-profil', function () {
		var inputValues = $$('.inputs-list input');

		var dataString = [];
		var userData = window.userData();

		for (var i = 0, len = inputValues.length; i < len; i++) {
			var item = inputValues[i];
			dataString[i] = item.value;
		}
		var jenis_kelamin = $$('.inputs-list select').val();
		var biodata = $$('.inputs-list textarea').val();

		dataString[inputValues.length] = jenis_kelamin;
		dataString[inputValues.length + 1] = biodata;
		dataString[inputValues.length + 2] = userData.id;

		if (!dataString[0] || !dataString[1]){
			myApp.alert('Silahkan lengkapi data!' ,'Informasi');
			return;
		}else if(!validateEmail(dataString[0])){
			myApp.alert('Format email tidak valid!', 'Informasi');
			return;
		}else if(!validateUsername(dataString[1])){
        myApp.alert('Username hanya boleh diisi dengan huruf kecil, angka dan underscore. Maksimal 20 karakter.', 'Informasi');
        return;
    }

		if (dataString[1]) {
			$$.ajax({
				type: "POST",
				url: jsonUrl + "update.php?type=edit-profil",
				data: { dataArray : dataString },
				crossDomain: true,
				cache: false,
				async: true,
				dataType: "json",
				beforeSend: function() {

				},
				success: function(data) {
					if (data == "username"){
              myApp.alert("Username sudah digunakan!", "Informasi");
              return;
          }else if (data == "email"){
              myApp.alert("Email sudah digunakan!", "Informasi");
              return;
          }else if (data == "error") {
              myApp.alert('Profil gagal diubah!', 'Informasi');
              return;
          }else if (data != "error") {
						mainView.router.back({
							animatePages: false
						});
						$$('.profilebox-name').text(dataString[3]);
						window.localStorage.setItem('userdata', JSON.stringify(data));
						userdata = JSON.parse(window.localStorage.getItem('userdata'));

						if (dataString[2] != ''){
							localStorage.setItem("password", password);
						}
						myApp.alert('Profil berhasil diubah!', 'Informasi');
					}
				}
			});
			return false;
		}

	});
});


//ganti password
myApp.onPageInit('ganti-password', function (page) {
	 $$(document).on('click','.save-password', function () {
		var inputValues = $$('.inputs-list input');

		var dataString = [];
		var userData = window.userData();

		for (var i = 0, len = inputValues.length; i < len; i++) {
			var item = inputValues[i];
			dataString[i] = item.value;
		}
		dataString[inputValues.length] = userData.id;

		if (!dataString[1] || !dataString[2]){
			myApp.alert('Field masih ada yang kosong, silahkan dilengkapi!', 'Peringatan');
			return;
		}else if (localStorage.getItem("password") != null && !dataString[0]){
			myApp.alert('Field masih ada yang kosong, silahkan dilengkapi!', 'Peringatan');
			return;
		}else if(ocalStorage.getItem("password") != null && dataString[0] != localStorage.getItem("password")){
			myApp.alert('Password lama salah.', 'Peringatan');
			return;
		}else if(dataString[1] != dataString[2]){
			myApp.alert('Password baru tidak sesuai.', 'Informasi');
			return;
		}

		var jsonString = JSON.stringify(dataString);

		if (dataString[1]) {
			$$.ajax({
				type: "POST",
				url: jsonUrl + "update.php?type=ganti-password",
				data: { dataArray : dataString },
				crossDomain: true,
				cache: false,
				beforeSend: function() {

				},
				success: function(data) {
					if (data == "success") {
						mainView.router.back({
							animatePages: false
						});
						myApp.alert('Password berhasil diubah!', 'Informasi');
					} else if (data == "error") {
						myApp.alert('Password gagal diubah!', 'Informasi');
					}
				}
			});
			return false;
		}

	});
});


//bantuan
myApp.onPageInit('bantuan', function (page) {
	var judul_seo = page.query.judul_seo;
	var userData = window.userData();

	var url = jsonUrl + "json.php?type=list_bantuan";
	window.AjaxPage(url,'#list_bantuan');

});

myApp.onPageInit('bantuan-detail', function (page) {
	var judul_seo = page.query.judul_seo;

	var url = jsonUrl + "json.php?type=bantuan_detail&judul_seo="+judul_seo;
	window.AjaxPage(url,'#bantuan_detail');

});
