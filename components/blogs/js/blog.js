$(function(){
  blogs = {
    editBlog: function(blog_id) {
      core.message(LANG_CONFIG_BLOG);
      $.post('/blogs/'+blog_id+'/editblog.html', {}, function(data){
		if(data.error == false){
			$('#popup_message').html(data.html);
			$('#popup_progress').hide();
			$('#popup_ok').val(LANG_SAVE).show();
			$('#popup_panel').prepend('<input id="delete_blog" type="button" class="button_yes" value="'+LANG_DEL_BLOG+'"/>');
			$('#popup_ok').click(function(){
				$('#popup_ok').prop('disabled', true);
				$('#authorslist option').prop("selected",true);
				$('.ajax-loader').show();
				var options = {
					success: blogs.doeditBlog
				};
				$('#cfgform').ajaxSubmit(options);
			});
			$('#delete_blog').click(function(){
				$('#delete_blog').prop('disabled', true);
				csrf_token = $('#csrf_token').val();
				core.confirm(LANG_YOU_REALY_DELETE_BLOG, null, function(){
					$.post('/blogs/'+blog_id+'/delblog.html', {csrf_token: csrf_token}, function(result){
						if(result.error == false){
							window.location.href = result.redirect;
						}
					}, 'json');
				});
			});
		} else {
			core.alert(data.text, LANG_ERROR);
		}
      }, 'json');
    },
    doeditBlog: function(result, statusText, xhr, $form){
		$('.ajax-loader').hide();
		$('.sess_messages').fadeOut();
		if(statusText == 'success'){
			if(result.error == false){
				window.location.href = result.redirect;
			} else {
				$('#error_mess').html(result.text);
				$('.sess_messages').fadeIn();
				$('#popup_ok').prop('disabled', false);
			}
		} else {
			core.alert(statusText, LANG_ERROR);
		}
    },
    addBlogCat: function(blog_id) {
      core.message(LANG_NEW_CAT);
      $.post('/blogs/'+blog_id+'/newcat.html', {}, function(data){
		if(data.error == false){
			$('#popup_message').html(data.html);
			$('#popup_progress').hide();
			$('#popup_ok').val(LANG_SAVE).show();
			$('#popup_ok').click(function(){
				$('#popup_ok').prop('disabled', true);
				$('.ajax-loader').show();
				var options = {
					success: blogs.doBlogCat
				};
				$('#addform').ajaxSubmit(options);
			});
		} else {
			core.alert(data.text, LANG_ERROR);
		}
      }, 'json');
    },
    editBlogCat: function(cat_id) {
      core.message(LANG_RENAME_CAT);
      $.post('/blogs/editcat'+cat_id+'.html', {}, function(data){
		if(data.error == false){
			$('#popup_message').html(data.html);
			$('#popup_progress').hide();
			$('#popup_ok').val(LANG_SAVE).show();
			$('#popup_ok').click(function(){
				$('#popup_ok').prop('disabled', true);
				$('.ajax-loader').show();
				var options = {
					success: blogs.doBlogCat
				};
				$('#addform').ajaxSubmit(options);
			});
		} else {
			core.alert(data.text, LANG_ERROR);
		}
      }, 'json');
    },
    doBlogCat: function(result, statusText, xhr, $form){
		$('.ajax-loader').hide();
		$('.sess_messages').fadeOut();
		if(statusText == 'success'){
			if(result.error == false){
				window.location.href = result.redirect;
			} else {
				$('#error_mess').html(result.text);
				$('.sess_messages').fadeIn();
				$('#popup_ok').prop('disabled', false);
			}
		} else {
			core.alert(statusText, LANG_ERROR);
		}
    },
    deleteCat: function(cat_id, csrf_token) {
		core.confirm(LANG_YOU_REALY_DELETE_CAT, null, function(){
			$.post('/blogs/delcat'+cat_id+'.html', {csrf_token: csrf_token}, function(result){
				if(result.error == false){
					window.location.href = result.redirect;
				}
			}, 'json');
		});
    },
    deletePost: function(post_id, csrf_token) {
		core.confirm(LANG_YOU_REALY_DELETE_POST, null, function(){
			$.post('/blogs/delpost'+post_id+'.html', {csrf_token: csrf_token}, function(result){
				if(result.error == false){
					window.location.href = result.redirect;
				}
			}, 'json');
		});
    },
    publishPost: function(post_id) {
      $.post('/blogs/publishpost'+post_id+'.html', {}, function(data){
		if(data == 'ok'){
			$('#pub_link').hide();
			$('#pub_wait').hide();
			$('#pub_date').fadeIn();
		} else {
			core.alert(LANG_NO_PUBLISHED, LANG_ERROR);
		}
      });
    }
  }
});