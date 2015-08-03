function copyItem(com_id, item_id){
	var copies = prompt(LANG_AD_HOW_MANY_COPY, 1);
	if (copies>0){
		window.location.href='/admin/index.php?view=components&do=config&id='+com_id+'&opt=copy_item&item_id='+item_id+'&copies='+copies;
	}
}

function copyCat(com_id, item_id){
	var copies = prompt(LANG_AD_HOW_MANY_COPY, 1);
	if (copies>0){
		window.location.href='/admin/index.php?view=components&do=config&id='+com_id+'&opt=copy_cat&item_id='+item_id+'&copies='+copies;
	}
}

function xlsEditRow(){
    var r = $('input#title_row').val();
    $('input.row').val(r);
}

function xlsEditCol(){
    var c = Number($('input#title_col').val());

    $("input.col").each(function (i) {
        $(this).val(i+c+1);
    });
}

function ignoreRow(row){
    var r_id = 'row_'+row;
    var c_id = 'ignore_'+row;
    var checked = $('input:checkbox[id='+c_id+']').prop('checked');
    if(checked){
        $('tr#'+r_id+' input:text[class!=other]').prop('disabled', true);
        $('tr#'+r_id+' input:text[class=other]').prop('disabled', false);
    } else {
        $('tr#'+r_id+' input:text[class!=other]').prop('disabled', false);
        $('tr#'+r_id+' input:text[class=other]').prop('disabled', true);
    }
}

function toggleDiscountLimit(){
    var sign = Number($('select#sign').val());

    if (sign==3){ $('tr.if_limit').show(); }
    else { $('tr.if_limit').hide(); }
}

function checkGroupList(){

	if(document.addform.is_public.checked){
		$('select#showin').prop('disabled', false);
        $('input#can_edit').prop('disabled', false);
	} else {
		$('select#showin').prop('disabled', true);
        $('input#can_edit').prop('disabled', true);
        $('input#can_edit').prop('checked', false);
	}

}

function toggleAdvert(){
    if ($('select#view_type').val() == 'shop') {
        $('.advert').show();
    } else {
        $('.advert').hide();
    }
}
