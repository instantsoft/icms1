(function($) {
  core = {
    verticalOffset: -390,
    horizontalOffset: 0,
    repositionOnResize: true,
    draggable: true,
    dialogClass: null,
    alert: function(message, title, callback) {
      if( title == null ) title = LANG_ATTENTION;
      this._show_mess(title, message, null, 'alert', function(result) {
        if(callback) callback(result);
      });
    },
    confirm: function(message, title, callback) {
      if(title == null) title = LANG_CONFIRM;
        this._show_mess(title, message, null, 'confirm', function(result) {
          if(result) callback(result);
        });
    },
    prompt: function(message, value, title, callback) {
      if( title == null ) title = 'Prompt';
        this._show_mess(title, message, value, 'prompt', callback);
    },
    message: function(title){
      if(title == null) title = '';
      this._show_mess(title, '', '', 'message');
    },
    box_close: function() {
      $('#popup_container').fadeOut(200, function(){
        $('#popup_overlay, #popup_container').remove();
      });
    },
    _show_mess: function(title, msg, value, type, callback) {
      if ($('#popup_container').length > 0) {
        $('#popup_overlay, #popup_container').remove();
      }
      var html = "<div id='popup_container'>" +
            "<div class='popup_body'>" +
              "<div class='popup_title_wrap'><div class='popup_x_button'/><div id='popup_title'/></div>" +
              "<div id='popup_progress'><img src='/images/progress.gif' alt="+LANG_LOADING+"'...' /></div>" +
              "<div id='popup_content'/>" +
              "<div id='popup_message'/>" +
              "<div id='popup_panel'>" +
                "<span class='ajax-loader'>&nbsp;</span>" +
                "<div id='popup_info'/>" +
                "<input id='popup_ok' type='button' class='button_yes' value='" + LANG_CONTINUE + "'/>" +
                "<input id='popup_cancel' type='button' class='button_no' value='" + LANG_CANCEL + "'/>" +
                "<input id='popup_close' type='button' class='button_no' value='" + LANG_CLOSE + "'/>" +
              "</div>" +
      "</div>";

      this._overlay('show');
      $('body').append(html);
      $('#popup_panel input').hide();
      if(this.dialogClass) {
        $('#popup_container').addClass(this.dialogClass);
      }
      var browser = navigator.userAgent;
      var version = 0;
      var msie = false;
      if( browser.indexOf("MSIE") != -1 ) {
            msie = true;
            var re = /.+(MSIE)\s(\d\d?)(\.?\d?).+/i;
            version = browser.replace(re, "$2");
      }

      var pos = ((msie && version <= 6 )||(($(window).height()<480)||($(window).width()<700))) ? 'absolute' : 'fixed';
      $('#popup_container').css({ position: pos });
      $('#popup_title').text(title);
      $('#popup_content').addClass(type);
      $('#popup_message').text('<span class="box_message_content">' + msg + '</span>');
      $('#popup_message').html($('#popup_message').text().replace(/\n/g, '<br />'));
      $('#popup_container').css({
        minWidth: $('#popup_container').outerWidth(),
        maxWidth: $('#popup_container').outerWidth()
      });
      this._reposition();
      this._maintainPosition(true);

      switch(type) {
        case 'alert':
          $('#popup_close').show();
          $('#popup_overlay, #popup_close, .popup_x_button').click(function() {
            core.box_close();
            callback(true);
          });
          $('#popup_close').keypress(function(e) {
            if(e.keyCode == 27 || e.keyCode == 13) {
              $('#popup_close').trigger('click');
            }
          });
          $('#popup_close').focus().select();
        break;
        case 'confirm':
          $('#popup_ok, #popup_cancel').show();
          $('#popup_ok').click(function() {
            if( callback ) callback(true);
          });
          $('#popup_overlay, #popup_cancel ,#popup_close, .popup_x_button').click(function() {
            core.box_close();
            callback(false);
          });
          $('#popup_ok').click(function() {
            core.box_close();
          });
          $('#popup_cancel').focus().select();
        break;
        case 'prompt':
          $('#popup_message').append('<input id="popup_prompt" name="popup_prompt" value="' + value + '"/>');
          $('#popup_prompt').width($('#popup_message').width());
          $('#popup_ok').val(LANG_SEND);
          $('#popup_ok, #popup_cancel').show();
          $('#popup_ok').click(function() {
            var val = $('#popup_prompt').val();
            if (val){
              if(callback) callback(val);
            }
          });
          $('#popup_overlay, #popup_cancel, .popup_x_button').click(function() {
            core.box_close();
            if(callback) callback(null);
          });
          $('#popup_prompt, #popup_ok, #popup_close').keypress(function(e) {
            if(e.keyCode == 27) $('#popup_cancel').trigger('click');
          });
          $('#popup_prompt').focus().select();
        break;
        case 'message':
          $('#popup_cancel, #popup_progress').show();
          $('#popup_overlay, #popup_cancel ,#popup_close, .popup_x_button').focus().select().click(function(){
            core.box_close();
          });
        break;
       }
    },
    _overlay: function(status) {
      switch(status) {
        case 'show':
          this._overlay('hide');
          $('body').append('<div id="popup_overlay"></div>');
          $('#popup_overlay').css({
            height: $(document).height()
          });
        break;
        case 'hide':
          $('#popup_overlay').remove();
        break;
      }
    },
    _reposition: function() {
      var top = ((window.screen.availHeight / 2)) + this.verticalOffset;
      var left = (($(window).width() / 2) - ($('#popup_container').outerWidth() / 2)) + this.horizontalOffset;
      if(top < 0) top = 0;
      if(left < 0) left = 0;

      $('#popup_container').css({
        top: top,
        left: left
      });

      // IE6 fix
      var browser = navigator.userAgent;
      var version = 0;
      var msie = false;
      if( browser.indexOf("MSIE") != -1 ) {
            msie = true;
            var re = /.+(MSIE)\s(\d\d?)(\.?\d?).+/i;
            version = browser.replace(re, "$2");
      }

      if((msie && version <= 6 )||(($(window).height()<480)||($(window).width()<700))) top = top + $(window).scrollTop();
      $('#popup_overlay').height($(document).height());
    },
    _maintainPosition: function(status) {
      if(this.repositionOnResize) {
        switch(status) {
          case true:
            $(window).bind('resize', this._reposition);
          break;
          case false:
            $(window).unbind('resize', this._reposition);
          break;
        }
      }
    },
    show_popup_info: function(text, type) {
      this._popup_info(type);
      $('#popup_info').html(text).show().delay(4000).fadeOut('slow');
    },
    hide_popup_info: function() {
      this.show_popup_info('');
      $('#popup_info').text('').hide();
    },
    _popup_info: function(type) {
      $('#popup_info').hide();
      $('#popup_info').attr('class', type);
    },
    show_error_field: function(obj) {
      obj.animate({backgroundColor:"#e6a4a4"}, {duration:200});
      obj.animate({backgroundColor:"#ffffff"}, {duration:200});
      obj.css({border:'1px solid red'});
    },
    hide_error_field: function(obj) {
      obj.css({border:'1px solid #aaa'});
    }
  };

	jQuery.each(['backgroundColor', 'borderBottomColor', 'borderLeftColor', 'borderRightColor', 'borderTopColor', 'color', 'outlineColor'], function(i,attr){
		jQuery.fx.step[attr] = function(fx){
			if ( fx.state == 0 ) {
				fx.start = getColor( fx.elem, attr );
				fx.end = getRGB( fx.end );
			}

			fx.elem.style[attr] = "rgb(" + [
				Math.max(Math.min( parseInt((fx.pos * (fx.end[0] - fx.start[0])) + fx.start[0]), 255), 0),
				Math.max(Math.min( parseInt((fx.pos * (fx.end[1] - fx.start[1])) + fx.start[1]), 255), 0),
				Math.max(Math.min( parseInt((fx.pos * (fx.end[2] - fx.start[2])) + fx.start[2]), 255), 0)
			].join(",") + ")";
		}
	});
	function getRGB(color) {
		var result;
		if ( color && color.constructor == Array && color.length == 3 )
			return color;

		if (result = /rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(color))
			return [parseInt(result[1]), parseInt(result[2]), parseInt(result[3])];

		if (result = /rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(color))
			return [parseFloat(result[1])*2.55, parseFloat(result[2])*2.55, parseFloat(result[3])*2.55];

		if (result = /#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(color))
			return [parseInt(result[1],16), parseInt(result[2],16), parseInt(result[3],16)];

		if (result = /#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(color))
			return [parseInt(result[1]+result[1],16), parseInt(result[2]+result[2],16), parseInt(result[3]+result[3],16)];

		return colors[jQuery.trim(color).toLowerCase()];
	}
	function getColor(elem, attr) {
		var color;
		do {
			color = jQuery.curCSS(elem, attr);
			if ( color != '' && color != 'transparent' || jQuery.nodeName(elem, "body") )
				break;
			attr = "backgroundColor";
		} while ( elem = elem.parentNode );
		return getRGB(color);
	};
})(jQuery);

function centerLink(href){
	$('div.component').css({opacity:0.4, filter:'alpha(opacity=40)'});
	$.post(href, function(data){
		$('div.component').html(data);
		$('div.component').css({opacity:1.0, filter:'alpha(opacity=100)'});
	});
}
function deleteWallRecord(component, target_id, record_id, csrf_token){
    core.confirm(LANG_CONFIRM_DEL_POST_ON_WALL, null, function(){
        $('#wall_entry_'+record_id).css('background', '#FFAEAE').fadeOut();
        $.post('/core/ajax/wall.php', {target_id: target_id, component: component, do_wall: 'delete', csrf_token: csrf_token, record_id: record_id}, function(result){
            if(result){
                wallPage(1);
            }
        });
    });
}
function addWall(component, target_id){

	core.message(LANG_NEW_POST_ON_WALL);
	$.post('/core/ajax/wall.php', {target_id: target_id, component: component, do_wall: 'add'}, function(data){
		if(data.error == false){
			$('#popup_message').html(data.html);
			$('#popup_progress').hide();
			$('#popup_ok').val(LANG_ADD).show();
			$('#popup_ok').click(function(){
				$('#popup_ok').prop('disabled', true);
				$('#popup_panel .ajax-loader').show();
				var options = {
					success: doaddWall
				};
				$('#add_wall_form').ajaxSubmit(options);
			});
		} else {
			core.alert(data.text, LANG_ERROR);
		}
	}, 'json');

}

function doaddWall(result, statusText, xhr, $form){
	$('.ajax-loader').hide();
	if(statusText == 'success'){
		if(result.error == false){
			core.box_close();
			wallPage(1);
		} else {
            core.show_popup_info(result.text, 'error');
			$('#popup_ok').prop('disabled', false);
		}
	} else {
		core.alert(statusText, LANG_ERROR);
	}
}
function wallPage(page){

	var target_id = $('div.wall_body input[name=target_id]').val();
	var component = $('div.wall_body input[name=component]').val();

	$('div.wall_body').css({opacity:0.5, filter:'alpha(opacity=50)'});
	$.post('/core/ajax/wall.php', {'target_id': target_id, 'component': component, 'page':page}, function(data){
		$('div.wall_body').html(data);
		$('div.wall_body').css({opacity:1.0, filter:'alpha(opacity=100)'});
	});

}
function setLang(lang){
	$('body').append('<form id="lform" style="display:none" method="post" action="/set_lang.php"><input type="hidden" name="lang" value="'+lang+'"/></form>');
	$('#lform').submit();
}