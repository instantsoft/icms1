function hideAll(){
    $('#kind_text').hide();
    $('#kind_link').hide();
    $('#kind_textarea').hide();
    $('#kind_checkbox').hide();
    $('#kind_radiogroup').hide();
    $('#kind_list').hide();
    $('#kind_menu').hide();
    $('#kind_file').hide();
    $('.text_field').hide();
}

function show(){
	hideAll();
    needDiv = 'kind_' + $('#kind').val();
    $('#'+needDiv).show();
    if(needDiv != 'kind_file'){
        $('.text_field').show();
    }
}

function toggleSendTo(){
	var sendto = $('#sendto').val();
	if (sendto=='mail'){
		$('#sendto_mail').show();
		$('#sendto_user').hide();
	} else {
		$('#sendto_mail').hide();
		$('#sendto_user').show();
	}
}