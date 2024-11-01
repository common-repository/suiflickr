var userid;
var perpage;
var currentpage = 1;
var pagecache = new Array();
var setscache = "";
var photoid;
var pages;

$(function(){
	userid = $("input#userid").val();
	perpage = $("input#perpage").val();
	if( "sets" == $("select#showmode").val() )
		setscache = '<div id="sf-photos">\n' + $("#sf-photos:first").html() + '</div>';
	else
		pagecache[1] = '<div id="sf-photos">\n' + $("#sf-photos:first").html() + '</div>';
	
	pages = $("input#pages").val();
	callFn();
});

function callFn(){
	setoptions();
	changeuser();
	insertPhoto();
	changeshowmode();
	previousPage();
	nextPage();
	refresh();
	backsetspage();
	clicksets();
	changeinsertas();
}

function changeinsertas(){
	$("input#thumbnail").unbind("click").bind("click", function(){
		$("div#op-thumbnail-size").show();
	});
	$("input#full").unbind("click").bind("click", function(){
		$("div#op-thumbnail-size").hide();
	});
}

function setoptions(){
	$(":button#btnOptions").unbind("click").bind("click", function(){
		$("#sf-photos").toggle();
		$("#sf-options").toggle();
		$("#navigation").toggle();
	});
}

function changeshowmode(){
	$("select#showmode").unbind("change").bind("change", function(){
		loading();
		$.ajax({
			type:"POST",
			dataType:"json",
			url:"suiFlickr-post.php",
			data:"userid=" + userid + "&showmode=" + $("select#showmode").val() + "&perpage=" + perpage,
			success:function(data){
				pagecache = new Array();
				currentpage = 1;
				if( "sets" == $("select#showmode").val() ){
					setscache = data.html;
					pages = 1;
				}else{
					$(":button#btnNext").removeAttr("disabled");
					pagecache[1] = data.html;
					photoid = "";
					pages = data.pages;
				}
				$("div#sf-photos").replaceWith(data.html);
				callFn();
				loading();
			}
		});
	});
}

function changeuser(){
	$(":button#btnShowuser").unbind("click").bind("click", function(){
		loading();
		$.ajax({
			type:"POST",
			dataType:"json",
			url:"suiFlickr-post.php",
			data:"changeuser=" + $("input#username").val() + "&showmode=" + $("select#showmode").val() + "&perpage" + perpage,
			success:function(data){
				currentpage = 1;
				userid = data.userid;
				if( "sets" == $("select#showmode").val() ){
					setscache = data.html;
					pages = 1;
				}else{
					$(":button#btnNext").removeAttr("disabled");
					pagecache[1] = data.html;
					pages = data.pages;
				}
				$("div#sf-photos").replaceWith(data.html);
				callFn();
				loading();
			}
		});
	});
}

function insertPhoto(){
	$(":button#btnInsert").unbind("click").bind("click", function(){
		var photos = $("div.sf-photo input:checked");
		var thumbnailsize = "";
		
		if( 1 == $("input#thumbnail:checked").length ){
			thumbnailsize = 'width="' + $("select#thumbnail-size option:selected")[0].value + '"';
			if( 1 == $("input#crop:checked").length )
				thumbnailsize += ' height="' + $("select#thumbnail-size option:selected")[0].value +'"';
		}
		
		var cssstyle = $("select#css-style option:selected")[0].value;
		if("none" == cssstyle)
			cssstyle = "";
		var outputhtml = "";
		
		for(i=0; i<photos.length; i++){
			outputhtml += ('<a href="' + photos[i].value + '" class="highslide" rel="highslide">');
			outputhtml += ('<img src="' + photos[i].value + '"' + thumbnailsize + ' class="sf-img ' + cssstyle + '" />');
		}
	
		var win = window.opener ? window.opener : window.dialogArguments;
	
		if(!win)
			win = top;
		tinyMCE = win.tinyMCE;
		if(typeof tinyMCE != 'undefined' && tinyMCE.getInstanceById('content')){
			tinyMCE.selectedInstance.getWin().focus();
			tinyMCE.execCommand('mceInsertContent', false, outputhtml);
		}else
			win.edInsertContent(win.edCanvas, outputhtml);
		return false;
		
		insertPhoto();
	});
}

function previousPage(){
	if(1 == currentpage){
		$(":button#btnPrevious").attr("disabled", "disabled");
	}
	$(":button#btnPrevious").unbind("click").bind("click", function(){
		var page = currentpage - 1;
		if(currentpage > 1){
			$("div#sf-photos").replaceWith(pagecache[page]);
			currentpage--;
			if( currentpage == (pages - 1) )
				$(":button#btnNext").removeAttr("disabled");
		}
		callFn();
	});
}

function nextPage(){
	if(currentpage == pages){
		$(":button#btnNext").attr("disabled", "disabled");
	}
	$(":button#btnNext").unbind("click").bind("click", function(){
		var page = currentpage + 1;
		if( "undefined" != typeof( pagecache[page] ) ){
			$("div#sf-photos").replaceWith(pagecache[page]);
			currentpage++;
			if(2 == currentpage)
				$(":button#btnPrevious").removeAttr("disabled");
			callFn();
		}else{
			loading();
			var photoiddata = "";
			if( "sets" == $("select#showmode").val() )
				photoiddata = "&photoid=" + photoid;
			$.ajax({
				type:"POST",
				dataType:"json",
				url:"suiFlickr-post.php",
				data:"userid=" + userid + "&showmode=" + $("select#showmode").val() + "&perpage=" + perpage + "&page=" + page + photoiddata,
				success:function(data){
					pagecache[page] = data.html;
					$("div#sf-photos").replaceWith(data.html);
					currentpage++;
					if(2 == currentpage)
						$(":button#btnPrevious").removeAttr("disabled");
					callFn();
					loading();
				}
			});
		}
	});
}

function backsetspage(){
	$(":button#btnBack").unbind("click").bind("click", function(){
		$("div#sf-photos").replaceWith(setscache);
		$(this).attr("disabled", "disabled");
		$(":button#btnNext").attr("disabled", "disabled");
		callFn();
	});
}

function refresh(){
	$(":button#btnRefresh").unbind("click").bind("click", function(){
		loading();
		pagecache = new Array();
		setscache = "";
		$.ajax({
			type:"POST",
			dataType:"json",
			url:"suiFlickr-post.php",
			data:"userid=" + userid + "&showmode=" + $("select#showmode").val() + "&perpage=" + perpage,
			success:function(data){
				currentpage = 1;
				if( "sets" == $("select#showmode").val() ){
					setscache = data.html;
					pages = 1;
				}else{
					$(":button#btnNext").removeAttr("disabled");
					pagecache[1] = data.html;
					pages = data.pages;
				}
				$("div#sf-photos").replaceWith(data.html);
				callFn();
				loading();
			}
		});
	});
}

function clicksets(){
	if( "sets" == $("select#showmode").val() ){
		$("div.sf-photoset").unbind("click").bind("click", function(){
			loading();
			pagecache = new Array();
			photoid = this.id;
			$.ajax({
				type:"POST",
				dataType:"json",
				url:"suiFlickr-post.php",
				data:"userid=" + userid + "&showmode=sets&perpage=" + perpage + "&photoid=" + photoid,
				success:function(data){
					currentpage = 1;
					pages = data.pages;
					pagecache[1] = data.html;
					$(":button#btnBack").removeAttr("disabled");
					$(":button#btnNext").removeAttr("disabled");
					$("div#sf-photos").replaceWith(data.html);
					callFn();
					loading();
				}
			});
		});
	}
}

function loading(){
	$("div#sf-loading").toggle("slow");
}