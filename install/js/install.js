$().ready(function(){
	$("form.wizard").wizard({
			show: function(element) {

				if($(element).is("#install")){
					$('input[name=install]').remove();
					$('.wizardcontrols').append('<input class="wizardnext" type="submit" name="install" style="width:150px" value="'+LANG_INS_DO_INSTALL+'">');
				} else {
					$('input[name=install]').remove();
				}

                if($(element).is("#start")){
                    setTimeout("checkAgree()", 100);
                }

			}
		});
});

function checkAgree(){

    var agree = $('#license_agree').prop('checked');

    if (agree) { $('.wizardnext').prop('disabled', false); } else {
        $('.wizardnext').prop('disabled', true);
    }

}
function setLang(lang){
	$('body').append('<form id="lform" style="display:none" method="post" action="/install/"><input type="hidden" name="lang" value="'+lang+'"/></form>');
	$('#lform').submit();
}