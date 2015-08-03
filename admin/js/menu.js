function checkAccesList(){

	if(document.addform.is_public.checked){
		$('select#allow_group').prop('disabled', true);
	} else {
		$('select#allow_group').prop('disabled', false);
	}

}

function selectIcon(file){
	document.addform.iconurl.value = file;
	hideIcons();
}

function showIcons(){
    $('#iconlink').hide();
    $('#icondiv').show();
}

function hideIcons(){
    $('#iconlink').show();
    $('#icondiv').hide();
}

function showMenuTarget(){

    $('.menu_target').hide();

    var target = $('select[name=mode]').val();

    $('div#t_'+target).fadeIn('fast');

}

function submitItem(){

    var linktype    = $('#addform #linktype').val();
    var link        = $('#addform #link').val();

    if (linktype=='link' && link==''){
        adminAlert(LANG_AD_SPECIFY_LINK_MENU); return;
    }

    $('#addform').submit();

}