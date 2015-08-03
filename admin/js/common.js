function jsmsg(msg, link){
    $('body').append('<div id="dialog-confirm" title="'+msg+'"></div>');
    $( "#dialog-confirm" ).dialog({
      closeText: LANG_CLOSE,
      close: function( event, ui ) { deleteDialog(); },
      resizable: false,
      height:0,
      modal: true,
      buttons: [ { text: LANG_CONTINUE, click: function() { window.location.href = link; } }, { text: LANG_CANCEL, click: function() { $( this ).dialog( "destroy" ); deleteDialog(); } } ]
    });
}
function adminAlert(msg){
    $('body').append('<div id="dialog-confirm" title="'+LANG_ATTENTION+'"><p>'+msg+'</p></div>');
    $( "#dialog-confirm" ).dialog({
      closeText: LANG_CLOSE,
      close: function( event, ui ) { deleteDialog(); },
      resizable: false,
      minHeight: 0,
      modal: true
    });
}
function deleteDialog(){
    $( "#dialog-confirm" ).remove();
}

function checked(){
	var c = 0;
	for (var i=0; i<document.selform.length; i++){
		if(document.selform.elements[i].name == 'item[]'){
			if(document.selform.elements[i].checked){
				c = c + 1;
			}
		}
	}
	return c;
}

function checkSel(link){
	var ch = 0;
	for (var i=0; i<document.selform.length; i++){
		if(document.selform.elements[i].name == 'item[]'){
			if(document.selform.elements[i].checked){
				ch++;
			}
		}
	}

	if (ch>0){
		document.selform.action = link;
		document.selform.submit();
	} else { adminAlert(LANG_AD_NO_SELECT_OBJECTS); }

}

function sendForm(link){
	document.selform.action = link;
	document.selform.submit();
}

function invert(){
	for (var i=0; i<document.selform.length; i++){
		if(document.selform.elements[i].name == 'item[]'){
			document.selform.elements[i].checked = !document.selform.elements[i].checked;
		}
	}
}

function install(href){
	$('div.update_process').show();
	$('div.update_go').hide();
	window.location.href=href;
}

function activateListTable(){
	$('table.tablesorter').tablesorter({headers: {0: {sorter: false}}});

    var browser = navigator.userAgent;
    var msie = false;
    if( browser.indexOf("MSIE") != -1 ) {
            msie = true;
            var re = /.+(MSIE)\s(\d\d?)(\.?\d?).+/i;
            version = browser.replace(re, "$2");
    }
    if (!msie || $version != '6'){
		$('table.tablesorter').columnFilters();
	}
}

function pub(id, qs, qs2, action, action2){
	old_img = $('img#pub'+id).attr('src');
	$('img#pub'+id).attr('src', 'images/actions/loader.gif');
    $('a#publink'+id).attr('href', '');
	$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: qs,
		  success: function(msg){
			  if(msg){
				$('img#pub'+id).attr('src', 'images/actions/'+action+'.gif');
				$('a#publink'+id).attr('href', 'javascript:pub('+id+', "'+qs2+'", "'+qs+'", "'+action2+'", "'+action+'");');
			  } else {
				$('img#pub'+id).attr('src', old_img);
			  }
		  }
	});
}

function showIns(){

	document.getElementById('frm').style.display = 'none';
	document.getElementById('filelink').style.display = 'none';
	document.getElementById('include').style.display = 'none';
	document.getElementById('banpos').style.display = 'none';
	document.getElementById('pagebreak').style.display = 'none';
	document.getElementById('pagetitle').style.display = 'none';

	needDiv = document.addform.ins.options[document.addform.ins.selectedIndex].value;

	document.getElementById(needDiv).style.display = "table-row";

}

function insertTag(kind){

    text = '';

    if (kind=='material'){
        text = '{МАТЕРИАЛ=' + document.addform.m.options[document.addform.m.selectedIndex].text + '}';
    }
    if (kind=='photo'){
        text = '{ФОТО=' + document.addform.f.options[document.addform.f.selectedIndex].text + '}';
    }
    if (kind=='album'){
        text = '{АЛЬБОМ=' + document.addform.a.options[document.addform.a.selectedIndex].text + '}';
    }
    if (kind=='frm'){
        text = '{ФОРМА=' + document.addform.fm.options[document.addform.fm.selectedIndex].text + '}';
    }
    if (kind=='blank'){
        text = '{БЛАНК=' + document.addform.b.options[document.addform.b.selectedIndex].text + '}';
    }
    if (kind=='include'){
        text = '{ФАЙЛ=' + document.addform.i.value + '}';
    }
    if (kind=='filelink'){
        text = '{СКАЧАТЬ=' + document.addform.fl.value + '}';
    }
    if (kind=='banpos'){
        text = '{БАННЕР=' + document.addform.ban.value + '}';
    }
    if (kind=='pagebreak'){
        text = '{pagebreak}';
    }
    if (kind=='pagetitle'){
        text = '{СТРАНИЦА=' + document.addform.ptitle.value + '}';
    }

    if(CKEDITOR.instances.content.mode == "wysiwyg"){
        CKEDITOR.instances.content.insertHtml(text);
    } else {
        adminAlert(LANG_AD_SWITCH_EDITOR);
    }

}

function InsertPagebreak() {

    if(CKEDITOR.instances.content.mode == "wysiwyg"){
        CKEDITOR.instances.content.insertHtml('{pagebreak}');
    } else {
        adminAlert(LANG_AD_SWITCH_EDITOR);
    }

}
function checkGroupList(){

	if ($('#is_public').prop('checked')){
		$('select#showin').prop('disabled', true);
	} else {
		$('select#showin').prop('disabled', false);
	}

}
function editFieldLang(lang,target,target_id,field,linkObj){
    $.colorbox({
        href:'/admin/ajax/translations.php?lang='+lang+'&target='+target+'&target_id='+target_id+'&field='+field,
        transition: 'none',
        width: '900px',
        maxHeight: '99%',
        title: $(linkObj).parent().find('strong').first().text()
    });
    return false;
}