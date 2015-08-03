$(function(){
  photos = {
    publishPhoto: function(photo_id) {
      $.post('/photos/publish'+photo_id+'.html', {}, function(data){
		if(data == 'ok'){
			$('#pub_photo_link').hide();
			$('#pub_photo_wait').hide();
			$('#pub_photo_date').fadeIn();
		} else {
			core.alert(LANG_NO_PUBLISH, LANG_ERROR);
		}
      });
    },
    movePhoto: function(photo_id) {
      core.message(LANG_MOVE_PHOTO);
      $.post('/photos/movephoto'+photo_id+'.html', {}, function(data){
		if(data.error == false){
			$('#popup_message').html(data.html);
			$('#popup_progress').hide();
			$('#popup_ok').show();
			$('#popup_ok').click(function(){
				$('#popup_ok').prop('disabled', true);
				$('#popup_progress').show();
				var options = {
					success: photos.domovePhoto
				};
				$('#move_photo_form').ajaxSubmit(options);
			});
		} else {
			core.alert(data.text, LANG_ERROR);
		}
      }, 'json');
    },
    domovePhoto: function(result, statusText, xhr, $form){
		$('#popup_progress').hide();
		if(statusText == 'success'){
			if(result.error == false){
				window.location.href = result.redirect;
			} else {
				core.alert(result.text, LANG_ERROR);
			}
		} else {
			core.alert(statusText, LANG_ERROR);
		}
    },
    editPhoto: function(photo_id) {
      core.message(LANG_EDIT_PHOTO);
      $.post('/photos/editphoto'+photo_id+'.html', {}, function(data){
		if(data.error == false){
			$('#popup_message').html(data.html);
			$('#popup_progress').hide();
			$('#popup_ok').val(LANG_SAVE).show();
			$('#popup_ok').click(function(){
				$('#popup_ok').prop('disabled', true);
				$('#popup_progress').show();
				var options = {
					success: photos.doeditPhoto
				};
				$('#edit_photo_form').ajaxSubmit(options);
			});
		} else {
			core.alert(data.text, LANG_ERROR);
		}
      }, 'json');
    },
    doeditPhoto: function(result, statusText, xhr, $form){
		$('#popup_progress').hide();
		if(statusText == 'success'){
			if(result.error == false){
				window.location.href = result.redirect;
			}
		} else {
			core.alert(statusText, LANG_ERROR);
		}
    },
    deletePhoto: function(photo_id, csrf_token) {
		core.confirm(LANG_YOU_REALLY_DELETE_PHOTO, null, function(){
			$.post('/photos/delphoto'+photo_id+'.html', {csrf_token: csrf_token}, function(result){
				if(result.error == false){
					window.location.href = result.redirect;
				}
			}, 'json');
		});
    }
  }
});
