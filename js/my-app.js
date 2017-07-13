
// Initialize your app
var myApp = new Framework7({
    material: true,
    animateNavBackIcon:true,
    cache:true,
    hideNavbarOnPageScroll : true,
    precompileTemplates: true
});

// Export selectors engine
var $$ = Dom7;

// Add view
var mainView = myApp.addView('.view-main', {
    // Because we use fixed-through navbar we can enable dynamic navbar
    dynamicNavbar: false,
    domCache: true
});

var jsonUrl 	= "https://elapor.bantulkab.go.id/json/";
var ptrContent 	= $$('.pull-to-refresh-content');
var userdata 	= JSON.parse(window.localStorage.getItem('userdata'));
var indicatorMt = false;

function handleOpenURL(url)
{
	var strValue = url;
	strValue = strValue.replace('elaporbantul://','');
	mainView.router.loadPage(strValue);
}

function clearInput(){
	$$('input').val('');
	$$('select').val('');
	$$('textarea').val('');
}

function userData(){
	return JSON.parse(window.localStorage.getItem('userdata'));
}

function logout(id){
    window.plugins.googleplus.logout(
       function (msg) {
        }
    );
	$$.ajax({
		url: jsonUrl + "update.php?type=logout&id_user="+id,
		type: "post",
		async: true,
		dataType: "json",
		success: function(data) {
            
		},
		error: function (textStatus, errorThrown) {

		}
	});
}

function calendar()
{
	var userData = window.userData();
	if(userData){
		var tgl = userData.tgl_lahir ? (userData.tgl_lahir) : new Date();
	}else{
		var tgl = new Date();
	}

	var options = {
		date: tgl,
		mode: 'date'
	};

	function onSuccess(date) {
		var month 	= ('0' + (date.getMonth() + 1)).slice(-2);
		var day 	= ('0' + date.getDate()).slice(-2);
		var year 	= date .getFullYear();
		$$('#tanggal_lahir').val(day + "-" + month + "-" + year);
        var className = $$('#tanggal_lahir').attr('class');
        if (className == ''){
            $$('#tanggal_lahir').addClass('focus-state');
            $$('#tanggal_lahir').parent().addClass('focus-state');
            $$('#tanggal_lahir').parent().parent().addClass('focus-state');
        }
	}

	function onError(error) { // Android only
	}

	datePicker.show(options, onSuccess, onError);
}


function validateEmail(email) {
  var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}

function validateUsername(username) {
  var re = /^[a-z0-9_-]{2,20}/;
  return re.test(username);
}

if(localStorage.getItem("notifikasi") == 0){
	$$('#icon-notif i').text('bell');
	$$('#icon-notif span').remove();
}

if(userData() != null){
	$$('#myform').submit();
}
if(navigator.splashscreen)
	navigator.splashscreen.hide();

$$(document).on('ajaxStart', function (e) {
    myApp.showIndicator();
});

$$(document).on('ajaxError', function (e) {
    myApp.hideIndicator();
});

$$(document).on('ajaxComplete', function (e) {
    myApp.hideIndicator();
});

function AjaxPage(url,id_page){
    $$.ajax({
        url: url,
        type: "post",
        async: true,
        dataType: "json",
        success: function(data) {
            var context = data;
            loadView(id_page, context);
        },
        error: function (textStatus, errorThrown) {

        }
    });
}


function loadView(id_page, context){
	context = context ? context : '';
    var template = $$(id_page+'_template').html();
    var compiledTemplate = Template7.compile(template);
    var html = compiledTemplate(context);
    $$(id_page).html(html);
}

$$(document).on('DOMContentLoaded', function(){


	var userData = window.userData();

    if($$('#tab-home').hasClass('active')){
        $$('.add-laporan').addClass('in');
		if (userData.nama_grup != 'Masyarakat'){
			 $$('.add-laporan').removeClass('in');
		}
    } else {
        $$('.add-laporan').removeClass('in');
    }

    $$('.main-tabs .tab').on('tab:show', function () {
		$$('.pull-to-refresh-content').scrollTop(0);

        var url = $$(this).data('url');
        var title = $$(this).data('title');

        if($$('#tab-home').hasClass('active')){
            $$('.add-laporan').addClass('in');
			if (userData.nama_grup != 'Masyarakat'){
				 $$('.add-laporan').removeClass('in');
			}
        } else {
            $$('.add-laporan').removeClass('in');
        }

		if($$('#tab-notification').hasClass('active')){
            $$('#icon-notif i').text('bell_fill');
        } else {
            $$('#icon-notif i').text('bell');
        }

        $$.get(url, function (data) {
			if (url == 'home.html'){
				myApp.showIndicator();
				$$.getJSON(jsonUrl + "json.php?type=list&id_user="+userData.id+"&nama_grup="+userData.nama_grup, function(result) {
					var theTemplate = Handlebars.compile(data);
					$$(".main-tabs #tab-home .content").html(theTemplate({model: result.laporan, terespon: result.terespon[0], total: result.total, blm_dibalas: result.blm_dibalas}));
					myApp.hideIndicator();

				});
			}else if (url == 'category.html'){
				myApp.showIndicator();

				$$.getJSON(jsonUrl + "json.php?type=list_kategori", function(result) {
					var theTemplate = Handlebars.compile(data);
					$$(".main-tabs #tab-keluhan .content").html(theTemplate(result));
					myApp.hideIndicator();
				});

			}else if (url == 'notification.html'){
				myApp.showIndicator();

				$$.getJSON(jsonUrl + "json.php?type=notifikasi&id_user="+userData.id, function(result) {
					var theTemplate = Handlebars.compile(data);
					$$(".main-tabs #tab-notification .content").html(theTemplate({today: result.today, all: result.all}));
					myApp.hideIndicator();
				});

			}else if(url == 'more.html'){
				myApp.showIndicator();

				$$.getJSON(jsonUrl + "json.php?id_user="+userData.id, function(result) {
					var theTemplate = Handlebars.compile(data);
					var foto_profile = result.result[0].foto_user ? result.result[0].foto_user : window.localStorage.getItem('foto_profile');
					$$(".main-tabs #tab-more .content").html(theTemplate({result:result.result, foto_profile: foto_profile}));
					myApp.hideIndicator();
				});
			}else{
				$$('.main-tabs .tab.active .content').html(data);
			}
			$$('.navbar .logo').html(title);
        });
    });

    $$(document).on('click','.zoomImage', function () {
        var photo = $$(this).data('image');
        var myPhotoBrowserDark = myApp.photoBrowser({
            photos : [photo],
            theme: 'dark',
            toolbar: false
        });
        myPhotoBrowserDark.open();
    });


});


//login
$$('#login').on('click', function() {
	var url		 = jsonUrl + 'login.php'
	var username = $$('#username').val();
	var password = $$('#password').val();

	if(!username || !password) {
		myApp.alert('Field masih ada yang kosong, silahkan dilengkapi.!', 'Informasi');
	} else {

		$$.ajax({
			url: url,
			type: "post",
			async: true,
			data: {'username':username, 'password':password, 'device-id':localStorage.getItem("device-id")},
			dataType: "json",
			beforeSend: function(){
				myApp.showIndicator();
			},
			success: function(data) {
				clearInput();
				myApp.showIndicator();
				if (data == 'blokir'){
					myApp.alert('Akun diblokir!', 'Login Gagal');
				}else if (data == 'logged'){
					myApp.confirm('Akun telah login di device lain. Teruskan login?', 'Peringatan',
						 function () {

							$$.ajax({
								url: jsonUrl + "update.php?type=device&username="+username+"&device_id="+localStorage.getItem("device-id"),
								type: "post",
								async: true,
								dataType: "json",
								success: function(data) {
									window.localStorage.setItem('userdata', JSON.stringify(data));
									userdata = JSON.parse(window.localStorage.getItem('userdata'));
									localStorage.setItem("password", password);
									localStorage.setItem("notifikasi", userdata.total_notifikasi ? userdata.total_notifikasi : '0');
									if(localStorage.getItem("notifikasi") > 0){
										$$('#icon-notif i').text('bell_fill');
										$$('#icon-notif span').remove();
										$$('#icon-notif').append('<span class="badge bg-red" style="position: absolute;top:0;right:30%;">'+localStorage.getItem("notifikasi")+'</span>');
									}
									myApp.showIndicator();
									$$('#myform').submit();
								},
								error: function (textStatus, errorThrown) {
									myApp.alert('error'+textStatus+' '+errorThrown);
								}
							});

						 },
						 function () {
							window.localStorage.clear();
							return;
						 }
						);

				}else if(data == 'verifikasi'){
					myApp.alert('Akun belum diverifikasi!', 'Login Gagal');
				}else if(data == 'error'){
					myApp.alert('Username atau Password anda salah.!', 'Login Gagal');
				}else{
					$$.ajax({
								url: jsonUrl + "update.php?type=device&username="+username+"&device_id="+localStorage.getItem("device-id"),
								type: "post",
								async: true,
								dataType: "json",
								success: function(data) {
									myApp.showIndicator();
									window.localStorage.setItem('userdata', JSON.stringify(data));
									userdata = JSON.parse(window.localStorage.getItem('userdata'));
									localStorage.setItem("password", password);
									localStorage.setItem("notifikasi", userdata.total_notifikasi ? userdata.total_notifikasi : '0');
									if(localStorage.getItem("notifikasi") > 0){
										$$('#icon-notif i').text('bell_fill');
										$$('#icon-notif span').remove();
										$$('#icon-notif').append('<span class="badge bg-red" style="position: absolute;top:0;right:30%;">'+localStorage.getItem("notifikasi")+'</span>');
									}
									window.location = "main.html";
									myApp.showIndicator();
								},
								error: function (textStatus, errorThrown) {

								}
							});
				}
			},
			error: function(data) {
				clearInput();
				if(data.response){
					userdata = JSON.parse(window.localStorage.getItem('userdata'));
				}else{
					mainView.router.loadPage('index.html');
				}
				window.localStorage.clear();
				myApp.alert('Username atau Password anda salah.!', 'Login Gagal');
			},
		});
	}
});


//registrasi
myApp.onPageInit('registrasi', function (page) {

	openFB.init({appId: '208058936344546'});

	//registrasi via google
	$$('.google').on('click', function() {
		window.plugins.googleplus.login(
		  {
			scopes: 'profile email https://www.googleapis.com/auth/plus.profile.emails.read https://www.googleapis.com/auth/plus.login', // optional, space-separated(!) list of scopes, If not included or empty, defaults to 'profile email'.
			webClientId: '1073257436431-0i41j2n946d9762o2g69t8h8k91n1037.apps.googleusercontent.com', // optional clientId of your Web application from Credentials settings of your project - On Android, this MUST be included to get an idToken. On iOS, it is not required.
			offline: true // optional, but requires the webClientId - if set to true the plugin will also return a serverAuthCode, which can be used to grant offline access to a non-Google server
		  },
		  function (obj) {
			var dataString= [];
			dataString[0] = obj.email;
			dataString[1] = obj.email;
			dataString[2] = '';
			dataString[3] = obj.displayName;
			dataString[4] = obj.birthday;
			dataString[5] = '-';
			dataString[6] = obj.gender == 'female' ? 'p' : 'l';
			dataString[7] = '-';
            dataString[8] = 3;
            dataString[9] = localStorage.getItem("device-id") ? localStorage.getItem("device-id") : 'fb';
            dataString[10] = obj.imageUrl;
                                     
			$$.ajax({
				type: "POST",
				url: jsonUrl + "insert.php?type=registrasi&via=fb",
				data: { dataArray : dataString },
				crossDomain: true,
				cache: false,
				beforeSend: function() {
					myApp.showIndicator();
				},
				success: function(data) {
					myApp.showIndicator();
                    if(data == 'blokir'){
                    window.plugins.googleplus.logout(
                                                     function (msg) {
                                                     }
                                                     );
						myApp.alert('Akun diblokir!', 'Informasi');
                    }else if(data == 'username'){
                    window.plugins.googleplus.logout(
                                                     function (msg) {
                                                     }
                                                     );
						myApp.alert('Username sudah digunakan!', 'Informasi');
                    }else if(data == 'email'){
                    window.plugins.googleplus.logout(
                                                     function (msg) {
                                                     }
                                                     );
						myApp.alert('Email sudah digunakan!', 'Informasi');
					}else if(data == 'error'){
						myApp.alert('Registrasi via google gagal!', 'Registrasi Gagal');
						myApp.hideIndicator();
					}else{
						clearInput();
						window.localStorage.setItem('userdata', data);
						userdata = JSON.parse(window.localStorage.getItem('userdata'));
						window.localStorage.setItem("google", 'google');
						window.localStorage.setItem('foto_profile', obj.imageUrl);
						window.location = "main.html";
					}
				},
				error: function(data) {
					clearInput();
					if(data.response){
						userdata = JSON.parse(window.localStorage.getItem('userdata'));
					}else{
						mainView.router.loadPage('index.html');
					}
					window.localStorage.clear();
					myApp.alert('Registrasi via google gagal!', 'Registrasi Gagal');
					myApp.hideIndicator();
					window.plugins.googleplus.logout(
						function (msg) {

						}
					);
				},
			});

		  },
		  function (msg) {
			  myApp.alert('Registrasi via google gagal!', 'Registrasi Gagal');
			  myApp.hideIndicator();
			  window.plugins.googleplus.logout(
				function (msg) {

				}
			  );
		 }
		);
	});

	//register via facebook
	$$('.facebook').on('click', function() {
		myApp.showIndicator();
        openFB.login(
			function(response) {

				if(response.status === 'connected') {
					var dataString = [];

					openFB.api({
						path: '/v2.8/me?fields=id,first_name,name,email,gender,birthday',
						success: function(result) {
							dataString[0] = result.email;
							dataString[1] = result.email;
							dataString[2] = '';
							dataString[3] = result.name;
							dataString[4] = result.birthday;
							dataString[5] = '-';
							dataString[6] = result.gender == 'female' ? 'p' : 'l';
							dataString[7] = '-';
                            dataString[8] = '3';
                            dataString[9] = localStorage.getItem("device-id") ? localStorage.getItem("device-id") : 'fb';
                            dataString[10] = "http://graph.facebook.com/"+result.id+"/picture?width=34&height=34";
                               
							$$.ajax({
								type: "POST",
								url: jsonUrl + "insert.php?type=registrasi&via=fb",
								data: { dataArray : dataString },
								crossDomain: true,
								cache: false,
								beforeSend: function() {
									myApp.showIndicator();
								},
								success: function(data) {
									myApp.showIndicator();
									if(data == 'blokir'){
										myApp.alert('Akun diblokir!', 'Informasi');
									}else if(data == 'username'){
										myApp.alert('Username sudah digunakan!', 'Informasi');
									}else if(data == 'email'){
										myApp.alert('Email sudah digunakan!', 'Informasi');
									}else if(data == 'error'){
										myApp.alert('Registrasi via facebook gagal!', 'Registrasi Gagal');
										myApp.hideIndicator();
									}else{
										window.localStorage.setItem('userdata', data);
										window.localStorage.setItem('foto_profile', "http://graph.facebook.com/"+result.id+"/picture?width=34&height=34");
										userdata = JSON.parse(window.localStorage.getItem('userdata'));
										window.location = "main.html";
									}
								}
							});
						},
						error: function(data) {
							clearInput();
							myApp.alert('Registrasi via facebook gagal!', 'Registrasi Gagal');
							mainView.router.loadPage('index.html');
							myApp.hideIndicator();
						},
					});
				} else {
					myApp.alert('Registrasi via facebook gagal!', 'Registrasi Gagal');
					myApp.hideIndicator();
				}
			}, {scope: 'email,publish_actions, public_profile'});
    });

	//registrasi via form input
	$$('.registrasi').on('click', function() {

		var inputValues = $$('.inputs-list input');

		var dataString = [];

		for (var i = 2, len = inputValues.length; i < len; i++) {
			var item = inputValues[i];
			dataString[i-2] = item.value;
		}

		var jenis_kelamin = $$('.inputs-list select').val();
		var biodata = $$('.inputs-list textarea').val();

		dataString[inputValues.length - 2] = jenis_kelamin;
		dataString[inputValues.length - 1] = biodata;
		dataString[inputValues.length] = 3;

		if (!dataString[0] || !dataString[1] || !dataString[2] || !dataString[3]
			|| !dataString[4] || !dataString[5] || !dataString[6] || !dataString[7]){
			myApp.alert('Field masih ada yang kosong, silahkan dilengkapi!', 'Informasi');
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
				url: jsonUrl + "insert.php?type=registrasi",
				data: { dataArray : dataString },
				crossDomain: true,
				cache: false,
				beforeSend: function() {
					myApp.showIndicator();

				},
				success: function(data) {
					if (data === 'error') {
						myApp.alert("Registrasi gagal!", "Informasi");
					}else if (data === 'username'){
						myApp.alert("Username sudah digunakan!", "Informasi");
					}else if (data === 'email'){
						myApp.alert("Email sudah digunakan!", "Informasi");
					}else if(data != ''){
						mainView.router.back();
						myApp.alert('Silahkan cek email Anda untuk verifikasi.', 'Informasi');
					}
				},
				error: function(data){
				}
			});
		}

	});

});
