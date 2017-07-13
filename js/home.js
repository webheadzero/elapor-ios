function cardKeluhan(field){
	if (field.foto_user){
		var foto = field.foto_user;
	}else{
		var foto = 'img/avatar.png';
	}
	var html = '<div class="card keluhan-card">' +
				'<div class="card-header no-border">'+
					'<div class="keluhan-avatar"><img src="'+foto+'"></div>'+
					'<div class="keluhan-name">'+field.nama_lengkap+'</div>'+
					'<div class="keluhan-date">'+ field.tgl_laporan +'</div>'+
				'</div>'+
				'<div class="card-content">'+
					'<div class="post-image" style="min-height:20px;">'+
						(field.nama_file ?
							'<a href="#" data-image="'+field.nama_file +'" class="zoomImage"><img src="'+field.nama_file+'" width="100%"></a>'+
							'<div class="chip">'+
								'<div class="chip-label">'+field.nama_kategori+'</div>'+
							'</div>'
						:
							'<div class="chip" style="margin-top:-22px;">'+
								'<div class="chip-label">'+field.nama_kategori+'</div>'+
							'</div>'
						)
					+'</div>'+
					'<div class="card-content-inner">'+ field.isi +
						'<br/><br/>'+
							'<b>Lokasi: </b>'+field.lokasi+
					'</div>'+
				 ' </div>';
	html += '<div class="card-footer no-border">'+
		'<a href="laporan-detail.html?id='+  field.id +'" class="link comment" data-id="'+ field.id +'"><i class="material-icons">comment</i> Comment</a>'+
	  '</div>';

	html += '</div>';

	return html;
}

//pull to refresh
ptrContent.on('ptr:refresh', function (e) {
    var url = $$(this).parent().data('url');
    var userData = window.userData();

    // Emulate 2s loading
    setTimeout(function () {
        $$.get(url, function (data) {
          $$.getJSON(jsonUrl + "json.php?type=list&id_user="+userData.id, function(result) {
				var theTemplate = Handlebars.compile(data);
				var model = result.laporan;
				var length = result.total;
				var jml_laporan = $$('.laporan').text();
				jml_laporan = parseInt(jml_laporan);

				if (length > jml_laporan){
					for (i = 0; i < (length - jml_laporan); i++) {
						var html = cardKeluhan(model[i]);

						$$(html).insertAfter('div.home-counter');
					}
				}
			});
		  myApp.pullToRefreshDone();
        });
    }, 2000);
});

//infinite scroll home
$$('#tab-home .infinite-scroll').on('infinite', function () {

	if(indicatorMt) return;

	var lastRowTask = $$('.main-tabs #tab-home .content .keluhan-card').length;
	lastIndexTask = lastRowTask;

	indicatorMt = true;
	var userData = window.userData();
	var url = jsonUrl + "json.php?type=list&id_user="+userData.id+"&offset="+lastIndexTask;


	$$.ajax({
		url: url,
		type: "post",
		async: true,
		dataType: "json",
		beforeSend: function() {

		},
		success: function(data) {
			if (data.laporan.length === 0) {
				indicatorMt = true;
			}else{

				$$.each(data.laporan, function(i, field){
					var html = cardKeluhan(field);
					$$(html).insertAfter('.content > div.keluhan-card:last-child');
				});
				indicatorMt = false;
			}

		},
		error: function (textStatus, errorThrown) {
			myApp.detachInfiniteScroll($$('.infinite-scroll'));
			indicatorMt = true;
		}
	});

});


//infinite scroll notification
$$('#tab-notification .infinite-scroll').on('infinite', function () {

	if(indicatorMt) return;

	var lastRowTask = $$('.main-tabs #tab-notification .content li').length;
	lastIndexTask = lastRowTask;

	indicatorMt = true;
	var userData = window.userData();
	var url = jsonUrl + "json.php?type=notifikasi&id_user="+userData.id+"&offset="+lastIndexTask;


	$$.ajax({
		url: url,
		type: "post",
		async: true,
		dataType: "json",
		beforeSend: function() {
		},
		success: function(data) {
			if (data.all.length === 0) {
				indicatorMt = true;
			}else{

				$$.each(data.all, function(i, field){

					var notif = field.terbaca ? '<i class="material-icons color-gray">notifications_none</i>' : '<i class="material-icons color-blue">notifications</i>';

					var html = '<li>'+
							'<a href="pages/laporan-detail/laporan-detail.html?id='+field.id_laporan+'&id_notifikasi='+field.id+'" class="item-link item-content">'+
								'<div class="item-media" id="item-'+field.id_laporan+'">'+
									notif +
								'</div>'+
								'<div class="item-inner">'+
									'<div class="item-title-row">'+
										'<div class="item-title" style="white-space: normal !important;">'+
											field.laporan+
										'</div>'+
									'</div>'+
									'<div class="item-subtitle color-gray">'+field.tgl_laporan +'</div>'+
									'<div class="item-text">'+field.nama_kategori +'</div>'+
								'</div>'+
							'</a>'+
						'</li>';
					$$(html).insertAfter('.content .all > ul li:last-child');
				});

				indicatorMt = false;
			}

		},
		error: function (textStatus, errorThrown) {
			myApp.detachInfiniteScroll($$('.infinite-scroll'));
			indicatorMt = true;
		}
	});

});


myApp.onPageInit('home', function (page) {
	if($$(".main-tabs #tab-home .content").html() == '<span class="progressbar-infinite color-multi"></span>'){
		myApp.showIndicator();
	}
  //Do something here with home page
	if(localStorage.getItem("userdata") == null ){
		window.localStorage.clear();
		window.location = "index.html";
	}

	var userData = window.userData();

	$$.get('home.html', function (template) {
		$$.getJSON(jsonUrl + "json.php?type=list&id_user="+userData.id+"&nama_grup="+userData.nama_grup+"&device-id="+localStorage.getItem("device-id"), function(result) {

			var theTemplate = Handlebars.compile(template);

			localStorage.setItem("notifikasi", result.notif ? result.notif : '0');
			if(localStorage.getItem("notifikasi") > 0){
				$$('#icon-notif i').text('bell_fill');
				$$('#icon-notif span').remove();
				$$('#icon-notif').append('<span class="badge bg-red" style="position: absolute;top:0;right:30%;">'+localStorage.getItem("notifikasi")+'</span>');

			}
			$$(".main-tabs #tab-home .content").append(theTemplate({model: result.laporan, terespon: result.terespon[0], total: result.total, blm_dibalas: result.blm_dibalas}));
			myApp.hideIndicator();
		});
	});

}).trigger();

//laporan detail
myApp.onPageInit('detail-laporan', function (page) {
	window.setInterval(function() {
	  var elem = $$('.page-content');
	  elem.scrollTop = elem.scrollHeight;
	}, 5000);

	var id  = page.query.id;
	var id_notifikasi = page.query.id_notifikasi;
	var userData = window.userData();

	$$('#item-'+id).html('<i class="material-icons color-gray">notifications_none</i>');

	var dataString = "id_user=" + userData.id+"&id_laporan="+id;
	if (id){
		$$.ajax({
			type: "POST",
			url: jsonUrl + "update.php?type=notifikasi",
			data: dataString,
			dataType: "json",
			async: true,
			beforeSend: function() {
			},
			success: function(data) {
				var terbaca = JSON.parse(data);
				var notif = localStorage.getItem("notifikasi")-terbaca;
				localStorage.setItem("notifikasi", notif ? notif : '0');
				if(notif > 0){
					$$('#icon-notif i').text('bell_fill');
					$$('#icon-notif span').remove();
					$$('#icon-notif').append('<span class="badge bg-red" style="position: absolute;top:0;right:30%;">'+notif+'</span>');
				}
			}
		});
	}

	var url = jsonUrl + "json.php?id="+id+"&id_user="+userData.id+"&nama_grup="+userData.nama_grup;
	window.AjaxPage(url,'#laporan_detail');
	window.AjaxPage(url,'#laporan_komen');

	$$(document).on('click','.send-message', function (e) {
        var comment 	= $$('#komentar').val();
        var id_laporan 	= $$('#id_laporan').val();

		var dataString = "isi=" + comment + "&id_user="+userData.id+"&id_laporan="+
						id_laporan+'&grup='+userData.nama_grup+'&nama_lengkap='+userData.nama_lengkap;
		if (comment) {
			e.preventDefault();
			e.stopImmediatePropagation();
			$$.ajax({
				type: "POST",
				url: jsonUrl + "insert.php?type=komentar",
				data: dataString,
				crossDomain: true,
				cache: false,
				beforeSend: function() {

				},
				success: function(data) {

					if (data == "success") {
						var jml_komentar = $$('.jml_komentar').text();
						jml_komentar = parseInt(jml_komentar);
						$$('.jml_komentar').text((jml_komentar+1) + ' Komentar');

						if (userData.foto_user != null){
							var foto = '<div class="profilebox-avatar" style="min-width:34px;max-width:34px;margin-top:7px;">'+
										'<img src="'+userData.foto_user+'" width="100%" >'+
										'</div>';
						}else if(window.localStorage.getItem('foto_profile')){
							var foto = '<div class="profilebox-avatar" style="min-width:34px;max-width:34px;margin-top:7px;">'+
										'<img src="'+window.localStorage.getItem('foto_profile')+'" width="100%" >'+
										'</div>';
						}else{
								var foto = '<div class="profilebox-avatar"><img src="img/avatar.png" width="34" height="34"></div>';

						}
						$$('.comment-list ul').append('<li>' +
														'<div class="profilebox bg-white" style="display: -webkit-flex;">'
															+ foto +
															'<div class="item-inner" style="padding-top: 0px;margin-left:14px;">' +
																'<div class="item-title-row">' +
																	'<div class="item-title">'+ userData.nama_lengkap+'</div>' +
																'</div>' +
																'<div class="item-subtitle">'+userData.nama_grup+'</div>' +
																'<div class="item-comment">' + comment + '</div>' +
															'</div>' +
														'</div>' +
													'</li>');
						$$('#komentar').val('');
					} else if (data == "error") {
						myApp.alert("Komentar gagal dikirim.", "Informasi");
					}
				}
			});
			return false;
		}
    });
});



myApp.onPageInit('form-laporan', function (page) {
	$$.getJSON(jsonUrl + "json.php?type=list_kategori", function(result) {
		$$.each(result, function(i, field) {
			$$("select.kategori").append('<option value="'+field.id+'">'+field.nama_kategori+'</option>');
		});
	});

	 $$(document).on('change','select.kategori',function(){
		 var id = $$(this).val();
		 $$('select.sub-kategori').val('');

		 myApp.showIndicator();
		 if(id != 'Pilih Kategori'){
			$$.ajax({
				type: "post",
				async: true,
				dataType: "json",
				url: jsonUrl + "json.php?type=list_sub_kategori&id_kategori="+id,
				crossDomain: true,
				cache: false,
				beforeSend: function() {

				},
				success: function(data) {
					myApp.hideIndicator();
					$$('select.sub-kategori option').remove();
					$$('select.sub-kategori').parent('div').addClass("focus-state");
					$$('select.sub-kategori').parent().parent('div').addClass("focus-state");

					$$.each(data.result, function(i, field) {
						$$("select.sub-kategori").append('<option value="'+field.id+'">'+field.nama_kategori+'</option>');
					});
				}
			});
		 }else{
			 myApp.hideIndicator();
			 $$('select.sub-kategori').html('<option>Pilih Sub Kategori</option>');
			 $$('select.sub-kategori').parent('div').removeClass("focus-state");
			 $$('select.sub-kategori').parent().parent('div').removeClass("focus-state");
		 }
	 });

	 $$(document).on('click','#form-image-placeholder',function(){

		 if (!navigator.camera) {
			  myApp.alert("Camera API not supported", "Error");
			  return;
		  }

		  var buttons = [
			{
				text: 'Ambil Gambar',
				onClick : function(){
					navigator.camera.getPicture(onPhotoDataSuccess, onFail, { quality: 25,
					destinationType:Camera.DestinationType.DATA_URL,correctOrientation: true });
					myApp.closeModal();
				}
			},
			{
				text: 'Buka Galeri',
				onClick : function(){
					navigator.camera.getPicture(onPhotoDataSuccess, onFail, { quality: 25,
					destinationType: Camera.DestinationType.DATA_URL,correctOrientation: true,
					sourceType: Camera.PictureSourceType.PHOTOLIBRARY });
					myApp.closeModal();
				}
			},
		];

        myApp.actions(buttons);
    });

    function onPhotoDataSuccess(imageData) {
		var imgPlaceholder = document.getElementById('image-placeholder');
		var imgPlaceholderIcon = document.getElementById('image-placeholder-icon');

		imgPlaceholderIcon.style.display = 'none';
		imgPlaceholder.style.display = 'block';
		imgPlaceholder.src = "data:image/jpeg;base64," + imageData;
    }

	function onFail(message) {
		console.log('Failed because: ' + message);
    }

    $$('.save-laporan').on('click', function() {
        $$('.save-laporan').addClass('disabled');
		var lokasi 		= $$('.inputs-list input').val();
		var kategori 	= $$('.inputs-list select.sub-kategori').val();
		var isi 		= $$('.inputs-list textarea').val();

		var dataString = [];
		var userData = window.userData();

		dataString[0] = kategori;
		dataString[1] = userData.id;
		dataString[2] = lokasi;
		dataString[3] = isi;

		var base64image = $$('#image-placeholder').attr('src');
		//alert(base64image);
		dataString[4] = base64image;

		if (!dataString[0] || !dataString[2] || !dataString[3]){
			myApp.alert('Field masih ada yang kosong, silahkan dilengkapi!', 'Informasi');
			return;
		}

		var jsonString = JSON.stringify(dataString);
                           myApp.showIndicator();
		if (dataString[1]) {
			$$.ajax({
				type: "POST",
				url: jsonUrl + "insert.php?type=laporan",
				data: { dataArray : dataString },
				crossDomain: true,
				cache: false,
				beforeSend: function() {
                    myApp.showIndicator();
				},
				success: function(data) {
					if (data == "error") {
						myApp.alert("Keluhan gagal dikirim.", "Informasi");
					} else {

						var parsedData = JSON.parse(data);
						var userData = window.userData();

						var foto = userData.foto_user != 'null' ? userData.foto_user :
									(window.localStorage.getItem('foto_profile') ? window.localStorage.getItem('foto_profile') : 'img/avatar.png');
						if (parsedData[4] != ''){
							var image = '<a href="#" data-image="'+parsedData[4]+'" class="zoomImage"><img src="'+parsedData[4]+'" width="100%"></a>'+
										'<div class="chip">'+
											'<div class="chip-label">'+ parsedData[0] +'</div>'+
										'</div>';
						}else{
							var image = '<div class="chip" style="margin-top:-22px;">'+
											'<div class="chip-label">'+ parsedData[0] +'</div>'+
										'</div>';
						}

							$$('<div class="card keluhan-card">' +
									'<div class="card-header no-border">'+
										'<div class="keluhan-avatar"><img src="'+foto+'"></div>'+
										'<div class="keluhan-name">'+userData.nama_lengkap+'</div>'+
										'<div class="keluhan-date">'+ parsedData[1] +'</div>'+
									'</div>'+
									'<div class="card-content">'+
										'<div class="post-image" style="min-height: 20px">'+
											image +
										'</div>'+
										'<div class="card-content-inner">'+ isi +
											'<br/><br/>'+
												'<b>Lokasi: </b>'+lokasi+
										'</div>'+
									 ' </div>'+
									  '<div class="card-footer no-border">'+
										'<a href="laporan-detail.html?id='+ parsedData[2] +'" class="link comment" data-id="'+ parsedData[2] +'"><i class="material-icons">comment</i> Comment</a>'+
									  '</div>'+
									'</div>').insertAfter('div.home-counter');

						var jml_laporan = $$('.laporan').text();
						jml_laporan = parseInt(jml_laporan);
						$$('.laporan').text(jml_laporan+1);
                    $$('.save-laporan').removeClass('disabled');
						mainView.router.back({
							animatePages: false
						});
					}
                    
				}
			});
                           myApp.hideIndicator();
                           return false;
		}

	});
});
