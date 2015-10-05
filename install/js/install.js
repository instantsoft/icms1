$(function(){
	$("form.wizard").wizard({
        show: function(element) {
            if($(element).is("#install")){
                $('.wizardnext').hide();
                $('input[name=install]').remove();
                $('.wizardcontrols').append('<input class="wizardnext" type="submit" name="install" value="'+LANG_INS_DO_INSTALL+'">');
            } else {
                $('input[name=install]').remove();
                $('.wizardnext').show();
            }
            if($(element).is("#start")){
                setTimeout("checkAgree()", 100);
            }
        }
    });
    $('#langs').on('click', function (){
        $('#langs-select').toggle().toggleClass('active_lang');
        $(this).toggleClass('active_lang');
        return false;
    });
    $('#langs-select li').on('click', function (){
        $('body').append('<form id="lform" style="display:none" method="post" action="/install/"><input type="hidden" name="lang" value="'+$(this).data('lang')+'"/></form>');
        $('#lform').submit();
    });
});
function checkAgree(){
    var agree = $('#license_agree').prop('checked');
    if (agree) { $('.wizardnext').fadeIn(); } else {
        $('.wizardnext').hide();
    }
}