$(function(){
  clubs = {
    addBlogCat: function(blog_id) {
      core.message(LANG_NEW_CAT);
      $.post('/clubs/'+blog_id+'/newcat.html', {}, function(data){
		if(data.error == false){
			$('#popup_message').html(data.html);
			$('#popup_progress').hide();
			$('#popup_ok').val(LANG_SAVE).show();
			$('#popup_ok').click(function(){
				$('#popup_ok').prop('disabled', true);
				$('.ajax-loader').show();
				var options = {
					success: clubs.doBlogCat
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
      $.post('/clubs/editcat'+cat_id+'.html', {}, function(data){
		if(data.error == false){
			$('#popup_message').html(data.html);
			$('#popup_progress').hide();
			$('#popup_ok').val(LANG_SAVE).show();
			$('#popup_ok').click(function(){
				$('#popup_ok').prop('disabled', true);
				$('.ajax-loader').show();
				var options = {
					success: clubs.doBlogCat
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
		if(statusText == 'success'){
			if(result.error == false){
				window.location.href = result.redirect;
			} else {
				core.show_popup_info(result.text, 'error');
				$('#popup_ok').prop('disabled', false);
			}
		} else {
			core.alert(statusText, LANG_ERROR);
		}
    },
    deleteCat: function(cat_id, csrf_token) {
		core.confirm(LANG_YOU_REALY_DELETE_CAT, null, function(){
			$.post('/clubs/delcat'+cat_id+'.html', {csrf_token: csrf_token}, function(result){
				if(result.error == false){
					window.location.href = result.redirect;
				}
			}, 'json');
		});
    },
    publishPost: function(post_id) {
      $.post('/clubs/publishpost'+post_id+'.html', {}, function(data){
		if(data == 'ok'){
			$('#pub_link').hide();
			$('#pub_wait').hide();
			$('#pub_date').fadeIn();
		} else {
			core.alert(LANG_NO_PUBLISHED, LANG_ERROR);
		}
      });
    },
    deletePost: function(post_id, csrf_token) {
		core.confirm(LANG_YOU_REALY_DELETE_POST, null, function(){
			$.post('/clubs/delpost'+post_id+'.html', {csrf_token: csrf_token}, function(result){
				if(result.error == false){
					window.location.href = result.redirect;
				}
			}, 'json');
		});
    },
    publishPhoto: function(photo_id) {
      $.post('/clubs/publish'+photo_id+'.html', {}, function(data){
		if(data == 'ok'){
			$('#pub_photo_link').hide();
			$('#pub_photo_wait').hide();
			$('#pub_photo_date').fadeIn();
		} else {
			core.alert(LANG_NO_PUBLISH, LANG_ERROR);
		}
      });
    },
    editPhoto: function(photo_id) {
      core.message(LANG_EDIT_PHOTO);
      $.post('/clubs/editphoto'+photo_id+'.html', {}, function(data){
		if(data.error == false){
			$('#popup_message').html(data.html);
			$('#popup_progress').hide();
			$('#popup_ok').val(LANG_SAVE).show();
			$('#popup_ok').click(function(){
				$('#popup_ok').prop('disabled', true);
				$('#popup_progress').show();
				var options = {
					success: clubs.doeditPhoto
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
			$.post('/clubs/delphoto'+photo_id+'.html', {csrf_token: csrf_token}, function(result){
				if(result.error == false){
					window.location.href = result.redirect;
				}
			}, 'json');
		});
    },
    deleteAlbum: function(album_id, csrf_token) {
		core.confirm(LANG_YOU_REALLY_DELETE_ALBUM, null, function(){
			$.post('/clubs/delalbum'+album_id+'.html', {csrf_token: csrf_token}, function(result){
				if(result.error == false){
					window.location.href = result.redirect;
				}
			}, 'json');
		});
    },
    renameAlbum: function(album_id) {
		core.message(LANG_RENAME_ALBUM);
		$('#popup_ok').val(LANG_SAVE).show();
		$('#popup_message').html('<script type="text/javascript" src="/includes/jquery/jquery.form.js"></script><form action="/components/clubs/ajax/renamealbum.php" method="post" id="rename_album"><input type="hidden" name="album_id" value="'+album_id+'" /><table border="0" cellspacing="0" cellpadding="10" align="left"><tbody><tr><td width="180"><strong>'+LANG_ALBUM_TITLE+': </strong></td><td><input onkeydown="if(13==event.keyCode){return false;}" type="text" class="text-input" name="title" id="title" style="width:300px"/></td></tr></tbody></table></form>');
		$('#title').focus();
		$('#popup_progress').hide();
		$('#popup_ok').click(function(){
			$('#popup_ok').prop('disabled', true);
			$('.ajax-loader').show();
			var options = {
				success: clubs.dorenameAlbum,
				dataType: 'json'
			};
			$('#rename_album').ajaxSubmit(options);
		});
    },
    dorenameAlbum: function(result, statusText, xhr, $form){
        $('.ajax-loader').hide();
		if(statusText == 'success'){
			if(result.error == true){
                core.show_popup_info(result.text, 'error');
				$('#popup_ok').prop('disabled', false);
			} else {
				core.box_close();
				$('#album_title').html(result.text);
			}
		} else {
			core.alert(statusText, LANG_ERROR);
		}
    },
    leaveClub: function(club_id, csrf_token) {
		core.confirm(LANG_REALY_EXIT_FROM_CLUB, null, function(){
			$.post('/clubs/'+club_id+'/leave.html', {csrf_token: csrf_token, confirm: 1}, function(result){
				if(result.error == false){
					window.location.href = result.redirect;
				}
			}, 'json');
		});
    },
    joinClub: function(club_id) {
      core.message(LANG_JOINING_CLUB);
      $.post('/clubs/'+club_id+'/join.html', {}, function(data){
		if(data.error == false){
			$('#popup_message').html(data.text);
			$('#popup_progress').hide();
			$('#popup_ok').show();
			$('#popup_ok').click(function(){
				$('#popup_ok').prop('disabled', true);
				$('#popup_progress').show();
				$.post('/clubs/'+club_id+'/join.html', {confirm: 1}, function(data){
					if(data.error == false){
						window.location.href = data.redirect;
					}
				});
			});
		} else {
			core.alert(data.text, LANG_ERROR);
		}
      }, 'json');
    },
    sendMessages: function(club_id) {
      core.message(LANG_SEND_MESSAGE);
      $.post('/clubs/'+club_id+'/message-members.html', {}, function(data){
		if(data.error == false){
			$('#popup_message').html(data.html);
			$('#popup_progress').hide();
			$('#popup_ok').show();
			$('#popup_ok').click(function(){
				$('#popup_ok').prop('disabled', true);
				$('.ajax-loader').show();
				var options = {
					success: clubs.dosendMessages,
					dataType: 'json'
				};
				$('#send_messages').ajaxSubmit(options);
			});
		} else {
			core.alert(data.text, LANG_ERROR);
		}
      }, 'json');
    },
    dosendMessages: function(result, statusText, xhr, $form){
        $('.ajax-loader').hide();
		if(statusText == 'success'){
			if(result.error == true){
                core.show_popup_info(result.text, 'error');
				$('#popup_ok').prop('disabled', false);
			} else {
				core.alert(result.text);
			}
		} else {
			core.alert(statusText, LANG_ERROR);
		}
    },
    addAlbum: function(club_id) {
		core.message(LANG_ADD_PHOTOALBUM);
		$('#popup_ok').val(LANG_SAVE).show();
		$('#popup_message').html('<script type="text/javascript" src="/includes/jquery/jquery.form.js"></script><form action="/components/clubs/ajax/createalbum.php" method="post" id="create_album"><input type="hidden" name="club_id" value="'+club_id+'" /><table border="0" cellspacing="0" cellpadding="10" align="left"><tbody><tr><td width="140"><strong>'+LANG_ALBUM_TITLE+': </strong></td><td><input onkeydown="if(13==event.keyCode){return false;}" type="text" class="text-input" name="title" id="title" style="width:300px"/></td></tr></tbody></table></form>');
		$('#title').focus();
		$('#popup_progress').hide();
		$('#popup_ok').click(function(){
			$('#popup_ok').prop('disabled', true);
			$('.ajax-loader').show();
			var options = {
				success: clubs.doaddAlbum,
				dataType: 'json'
			};
			$('#create_album').ajaxSubmit(options);
		});
    },
    doaddAlbum: function(result, statusText, xhr, $form){
        $('.ajax-loader').hide();
		if(statusText == 'success'){
			if(result.error == true){
                core.show_popup_info(result.text, 'error');
				$('#popup_ok').prop('disabled', false);
			} else {
				window.location.href = '/clubs/photoalbum'+result.album_id;
			}
		} else {
			core.alert(statusText, LANG_ERROR);
		}
    },
    create: function() {
      core.message(LANG_CREATE_CLUB);
      $.post('/clubs/create.html', {}, function(data){
		if(data.error == false){
			if(data.can_create == true){
				$('#popup_ok').val(LANG_CREATE).show();
			}
			$('#popup_message').html(data.html);
			$('#popup_progress').hide();
			$('#popup_ok').click(function(){
				$('#popup_ok').prop('disabled', true);
				$('.ajax-loader').show();
				var options = {
					success: clubs.doCreate,
					dataType: 'json'
				};
				$('#create_club').ajaxSubmit(options);
			});
		} else {
			core.alert(data.text, LANG_ERROR);
		}
      }, 'json');
    },
    doCreate: function(result, statusText, xhr, $form){
		$('.ajax-loader').hide();
		if(statusText == 'success'){
			if(result.error == true){
                core.show_popup_info(result.text, 'error');
				$('#popup_ok').prop('disabled', false);
			} else {
				window.location.href = '/clubs/'+result.club_id;
			}
		} else {
			core.alert(statusText, LANG_ERROR);
		}
    },
    intive: function(club_id) {
      core.message(LANG_SEND_INVITE_CLUB);
      $.post('/clubs/'+club_id+'/join_member.html', {}, function(data) {
        if(data.error == false) {
          $('#popup_ok').show();
          $('#popup_message').html(data.html);
          $('#popup_progress').hide();
          clubs.intive_init_tab();
          $('#popup_ok').click(function() {
            clubs.intive_send('/clubs/'+club_id+'/join_member.html');
          });
        } else {
          core.alert(data.text);
        }
      }, 'json');
    },
    intive_init_tab: function() {
      $('div#list_tab').click(function() {
        if($(this).attr('class')=='t_filter_off') {
          $(this).attr('class','t_filter_selected');
          $('div#list_selected_tab').attr('class','t_filter_off');
        }
      });
      $('div#list_selected_tab').click(function() {
        if($(this).attr('class')=='t_filter_off') {
          $(this).attr('class','t_filter_selected');
          $('div#list_tab').attr('class','t_filter_off');
        }
      });
    },
    intive_click: function(user_id) {
      var count_friends = $('span#count_friends').html();
      $('#flist_data #flist' + user_id).toggleClass('flist_cell').toggleClass('flist_cell_on');
      if($('#flist_data #flist' + user_id).attr('class') == 'flist_cell_on') {
        var new_count = Number(count_friends) + 1;
      } else {
        var new_count = Number(count_friends) - 1;
        if($('#list_selected_tab').attr('class') == 't_filter_selected'){
          $('#flist_data #flist' + user_id).fadeOut();
        }
      }
      $('#count_friends').html(new_count);
    },
    intive_filter: function(type) {
      if(type == 'all') {
        $('#flist_data div.flist_cell, #friend_list_lookup').show();
      } else {
        $('#flist_data div.flist_cell, #friend_list_lookup').hide();
      }
    },
    intive_send: function(href) {
      var users = {};
      var error = true;
      $('div#flist_data div.flist_cell_on').each(function(i, el) {
        users[i] = $(this).attr('value');
        error = false;
      });
      core.hide_popup_info();
      if (!error) {
        $('#popup_panel span.ajax-loader').show();
        $.post(href, { 'join': 1, 'users': users }, function(data) {
          $('#popup_panel span.ajax-loader').hide();
          if(data.error == false){
            core.show_popup_info(data.text, 'info');
            $('#popup_cancel').hide();
            $('#popup_close').show();
            $.each(users, function() {
              $('#flist' + this).fadeOut(200, function() {
                $(this).remove();
              });
            });
            $('#count_friends').html(0);
          } else {
            core.show_popup_info(data.text, 'error');
          }
        }, 'json');
      } else {
        core.show_popup_info(LANG_YOU_NO_SELECT_USER, 'error');
      }
    },
    intive_search: function() {
      var str = $('#friend_list_lookup').val();
      $('#flist_data .flist_name').each(function() {
        var name = clubs.strip_html($(this).html());
        var name_new = clubs.intive_highlight(name, str);
        $(this).html(name_new);
      });
    },
    intive_highlight: function(s, sub) {
      if (typeof s != 'string') return '';
      if (!sub || !sub.length) return s;
      sub = this.escapeRE(sub);
      var translate = this.parseLatin(sub),
      subs = translate == null ? [sub] : [sub, translate];
      $.each(subs, function() {
        var re = new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + this + ")(?![^<>]*>)(?![^&;]+;)", "gi");
        s = s.replace(re, "<em>$1</em>");
      });
      return s;
    },
    strip_html: function(text) {
      return text ? text.replace(/<(?:.|\s)*?>/g, '') : '';
    },
    escapeRE: function(s) {
      return s ? s.replace(/([.*+?^${}()|[\]\/\\])/g, '\\$1') : '';
    },
    parseLatin: function(text) {
      var outtext = text;
      var lat1 = ['yo','zh','kh','ts','ch','sch','shch','sh','eh','yu','ya','YO','ZH','KH','TS','CH','SCH','SHCH','SH','EH','YU','YA',"'"];
      var rus1 = ['ё', 'ж', 'х', 'ц', 'ч', 'щ',  'щ',   'ш', 'э', 'ю', 'я', 'Ё', 'Ж', 'Х', 'Ц', 'Ч', 'Щ',  'Щ',   'Ш', 'Э', 'Ю', 'Я', 'ь'];
      for (var i = 0, l = lat1.length; i < l; i++) {
        outtext = outtext.split(lat1[i]).join(rus1[i]);
      }
      var lat2 = 'abvgdezijklmnoprstufhcyABVGDEZIJKLMNOPRSTUFHCYёЁ';
      var rus2 = 'абвгдезийклмнопрстуфхцыАБВГДЕЗИЙКЛМНОПРСТУФХЦЫеЕ';
      for (var i = 0, l = lat2.length; i < l; i++) {
        outtext = outtext.split(lat2.charAt(i)).join(rus2.charAt(i));
      }
      return (outtext == text) ? null : outtext;
    }
  }
});
