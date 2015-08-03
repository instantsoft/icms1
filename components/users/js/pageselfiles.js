function orderPage(field){
	$("#orderby").val(field);
	document.orderform.submit();
}

function checkSelFiles(){
	var sel =false;
	for(i=0; i<25; i++){
	 if($("#fileid"+i).prop('checked')){
		sel = true;
	 }
	}
	return sel;
}

function delFiles(title){
	var sel = checkSelFiles();
	if (sel == false){
	 	core.alert(LANG_NO_SELECT_FILE, LANG_ERROR);
	} else {
		core.confirm(title, null, function(){
            $("#listform").attr('action', 'delfilelist.html');
            document.listform.submit();
		});
	}
}

function pubFiles(flag){
	var sel = false;
	for(i=0; i<25; i++){
	 if($("#fileid"+i).prop('checked')){
		sel = true;
	 }
	}
	if (sel == false){
	 	core.alert(LANG_NO_SELECT_FILE, LANG_ERROR);
	} else {
		if(flag==1){
		 $("#listform").attr('action', 'showfilelist.html');
		} else {
		 $("#listform").attr('action', 'hidefilelist.html');
		}
		document.listform.submit();
	}
}
