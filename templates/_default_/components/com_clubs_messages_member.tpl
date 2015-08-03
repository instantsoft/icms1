<script type="text/javascript" src="/includes/jquery/jquery.form.js"></script>
<p style="margin:6px 0;" id="text_mes">{$LANG.SEND_MESSAGE_TEXT} "{$club.title}".</p>
<form action="/clubs/{$club.id}/message-members.html" method="POST" name="msgform" id="send_messages">
<input type="hidden" name="gosend" value="1" />
<input type="hidden" name="csrf_token" value="{csrf_token}" />
<div class="usr_msg_bbcodebox">{$bbcodetoolbar}</div>
{$smilestoolbar}
<div class="cm_editor"><textarea class="ajax_autogrowarea" name="content" id="message"></textarea></div>
<div style="margin:0 0 4px;">
	<label><input id="only_mod" name="only_mod" type="checkbox" value="1" onclick="mod_text()" /> {$LANG.MESSAGE_ONLY_MODERS}</label>
</div>
</form>
<script type="text/javascript">
function mod_text(){
	if ($('#only_mod').prop('checked')){
		$('#text_mes').html('{$LANG.SEND_MESSAGE_TEXT_MOD} "{$club.title|escape:'html'}".');
	} else {
		$('#text_mes').html('{$LANG.SEND_MESSAGE_TEXT} "{$club.title|escape:'html'}".');
	}
}
$(document).ready(function(){
	$('.ajax_autogrowarea').focus();
});
</script>