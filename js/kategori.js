//list sub kategori
myApp.onPageInit('list-sub-category', function (page) {
	var id_kategori = page.query.id;
	var url = jsonUrl + "json.php?type=list_sub_kategori&id_kategori="+id_kategori;
	window.AjaxPage(url,'#list-sub-category');

});


//list-keluhan
myApp.onPageInit('list-keluhan', function (page) {
	$$('.content').scrollTop(0);
	var id_sub_kategori = page.query.id;
	var userData = window.userData();

                 var url = jsonUrl + "json.php?id_sub_kategori="+id_sub_kategori+"&id_user="+userData.id;
	window.AjaxPage(url,'#list_keluhan');

	$$(document).on('infinite', '.list-keluhan-kategori.infinite-scroll', function () {

		if(indicatorMt) return;

		var lastRowTask = $$('.list-keluhan-kategori .keluhan-card').length;
		lastIndexTask = lastRowTask;

		indicatorMt = true;

		var url = jsonUrl + "json.php?id_sub_kategori="+id_sub_kategori+"&id_user="+userData.id+"&offset="+lastIndexTask;

		$$.ajax({
			url: url,
			type: "post",
			async: true,
			dataType: "json",
			beforeSend: function() {

			},
			success: function(data) {
				if (data.result.length === 0) {
					indicatorMt = true;
				}else{
					$$.each(data.result, function(i, field){
						var html = cardKeluhan(field);
						$$(html).insertAfter('div.keluhan-card:last-child');
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

});
