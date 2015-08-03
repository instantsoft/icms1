function addComment(target, target_id, parent_id){
    $('.cm_addentry').remove();
    $('.cm_add_link').show();
    $link_span  = $('#cm_add_link'+parent_id);
    old_html    = $link_span.html();
    loading_img = '<img src="/images/ajax-loader.gif"/>';
    $link_span.html(loading_img);
	$.post('/components/comments/addform.php', {target: target, target_id: target_id, parent_id: parent_id}, function(data) {
		if(data){
            $("#cm_addentry"+parent_id).html(data).fadeIn();
            $('#content').focus();
            $link_span.html(old_html).hide();
            $('#submit_cmm').click(function() {
                $('#submit_cmm').prop('disabled', true);
                $('#cancel_cmm').hide();
                $('.submit_cmm').append(loading_img);
                var options = {
                    success: showResponseAdd,
                    dataType: 'json'
                };
                $('#msgform').ajaxSubmit(options);
            });
		}
	});
}
function showResponseAdd(result, statusText, xhr, $form){

	$('.sess_messages').fadeOut();

	if(statusText == 'success'){
		if(result.error == true){
			$('#error_mess').html(result.text);
			$('.sess_messages').fadeIn();
			if(result.is_captcha){
				reloadCaptcha('kcaptcha1');
			}
			$('#submit_cmm').prop('disabled', false);
            $('.submit_cmm img').remove();
            $('#cancel_cmm').show();
		} else {
			if(result.is_premod){
				core.alert(result.is_premod);
                $('.cm_addentry').remove();
                $('.cm_add_link').show();
			} else {
                $('.cm_addentry').remove();
                $('.cm_add_link').show();
				loadComments(result.target, result.target_id, false);
				total_page = Number($('#comments_count').html());
				$('#comments_count').html(total_page+1);
			}
		}
	} else {
		core.alert(statusText, LANG_ERROR);
	}

}

function showResponseEdit(result, statusText, xhr, $form){
    $('.ajax-loader').hide();
	if(statusText == 'success'){
		if(result.error == true){
            core.show_popup_info(result.text, 'error');
			$('#popup_ok').prop('disabled', false);
		} else {
			core.box_close();
			$('#cm_msg_'+result.comment_id).html(result.text);
            afterLoad();
		}
	} else {
		core.alert(statusText, LANG_ERROR);
	}
}

function editComment(comment_id, csrf_token){
	core.message(LANG_EDIT_COMMENT);
	$.post('/components/comments/addform.php', {action: 'edit', id: comment_id, csrf_token: csrf_token}, function(data) {
		if(data) {
		  $('#popup_ok').show();
		  $('#popup_message').html(data);
		  $('#popup_progress').hide();
		}
	});
	$('#popup_ok').click(function(){
		$('#popup_ok').prop('disabled', true);
        $('.ajax-loader').show();
		var options = {
			success: showResponseEdit,
			dataType: 'json'
		};
		$('#msgform').ajaxSubmit(options);
	});
}

function deleteComment(comment_id, csrf_token, is_delete_tree) {
	core.confirm(LANG_CONFIRM_DEL_COMMENT, null, function() {
		$.post('/comments/delete/'+comment_id, {csrf_token: csrf_token}, function(result){
			if(result.error == false){
				if(is_delete_tree != 1){
					$('#cm_addentry'+comment_id).parent().css('background', '#FFAEAE').fadeOut();
					total_page = Number($('#comments_count').html());
					$('#comments_count').html(total_page-1);
				}
                loadComments(result.target, result.target_id, false);
			}
		}, 'json');
	});
}

function expandComment(id){
	$('a#expandlink'+id).hide();
	$('div#expandblock'+id).show();
}

function loadComments(target, target_id, anchor){

    $('div.component').css({opacity:0.4, filter:'alpha(opacity=40)'});

    $.ajax({
			type: "POST",
			url: "/components/comments/comments.php",
			data: "target="+target+"&target_id="+target_id+"&target_author_can_delete="+target_author_can_delete,
			success: function(data){
				$('div.cm_ajax_list').html(data);
                $('td.loading').html('');
                if (anchor){
                    window.location.hash = anchor.substr(1, 100);
                    $('a[href='+anchor+']').css('color', 'red').attr('title', LANG_COMMENT_IN_LINK);
                }
				$('div.component').css({opacity:1.0, filter:'alpha(opacity=100)'});
                afterLoad();
			}
    });

}

function afterLoad(){
    //для вставленых через бб-редактор
    $( '.bb_img img' ).each( function(){
        var link = $( this ).attr( 'src' );
        $( this ).wrap( '<a class="bb_photo" href="' + link + '" />' );
        $( '.bb_photo').colorbox({ transition: "none" });
    });

    //для бб-редактора вставленные с уменьшением
    $( '.forum_zoom a' ).each( function(){
        $( this ).colorbox({ transition: "none" });
    });
}

function goPage(dir, field, target, target_id){

	var p = Number($('#'+field).val()) + dir;
    loadComments(target, target_id, p);

}

function voteComment(comment_id, vote){

    $('span#votes'+comment_id).html('<img src="/images/ajax-loader.gif" border="0"/>');
    $.ajax({
			type: "POST",
			url: "/components/comments/vote.php",
			data: "comment_id="+comment_id+"&vote="+vote,
			success: function(data){
				$('span#votes'+comment_id).html(data);
			}
    });

}