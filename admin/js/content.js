function sendContentForm(opt, object_id){

    var link = 'index.php?view=content&do='+opt;

    if (object_id && object_id.length>0) { link = link + '&obj_id='+ object_id; }

    var sel  = checked();

    if (sel){
        if (opt!='delete' || confirm(LANG_AD_DELETE_SELECTED_ARTICLES+' ('+sel+' '+LANG_AD_PIECES+')?')){

            document.selform.action = link;
            document.selform.submit();

        }
    } else {
        adminAlert(LANG_AD_NO_SELECTED_ARTICLES);
    }

}

function moveItem(item_id, dir){

    var cat_id = $('#filter_form input[name=cat_id]').val();

	$.ajax({
		  type: "POST",
		  url: "/admin/index.php",
		  data: "view=content&do=move&id="+item_id+"&cat_id="+cat_id+"&dir="+dir,
		  success: function(msg){
            var trh = $('#listTable tr#'+item_id).html();
			var curr_ord = ~~$('#listTable tr#'+item_id+' .ordering').html();
            if (dir == -1){
                $('#listTable tr#'+item_id).prev('tr').before('<tr id="'+item_id+'">'+trh+'</tr>').next('tr').remove();
				$('#listTable tr#'+item_id+' .ordering').html(curr_ord-1);
				$('#listTable tr#'+item_id).next().find('.ordering').html(curr_ord);
            }
            if (dir == 1){
                $('#listTable tr#'+item_id).next('tr').after('<tr id="'+item_id+'">'+trh+'</tr>').prev('tr').remove();
				$('#listTable tr#'+item_id+' .ordering').html(curr_ord+1);
				$('#listTable tr#'+item_id).prev().find('.ordering').html(curr_ord);
            }
            $('#listTable tr').find('.move_item_up').show();
            $('#listTable tr').find('.move_item_down').show();
            $('#listTable tr').eq(1).find('.move_item_up').hide();
            $('#listTable tr').eq($('#listTable tr').length-1).find('.move_item_down').hide();
            $('#listTable tr#'+item_id).animate( { opacity:0.01 }, 200 ).animate( { opacity:1 }, 200 );
		  }
	});
}

function deleteCat(cat_name, cat_id){

    var sure = confirm(LANG_AD_CATEGORY_DELETE+' "'+cat_name+'" '+LANG_AD_AND_SUB_CATS);

    if (!sure){ return; }

    var is_with_content = confirm(LANG_AD_DELETE_SUB_ARTICLES);

    var link = '?view=cats&do=delete&id='+cat_id;

    if (!is_with_content){
        window.location.href = link;
    } else {
        window.location.href = link + '&content=1';
    }

}