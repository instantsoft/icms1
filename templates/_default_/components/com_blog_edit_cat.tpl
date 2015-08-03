<form action="{$form_action}" method="post" name="addform" id="addform">
    <input type="hidden" name="goadd" value="1" />
    <input type="hidden" name="csrf_token" value="{csrf_token}" />
	<table border="0" cellspacing="0" cellpadding="6" width="100%">
		<tr>
			<td width="170"><strong>{$LANG.CAT_NAME}: </strong></td>
			<td><input name="title" type="text" id="title" class="text-input" style="width:350px" value="{$mod.title|escape:'html'}"/></td>
		</tr>
		<tr>
			<td valign="top"><strong>{$LANG.CAT_DESCRIPTION}: </strong></td>
			<td><textarea class="text-input" name="description" cols="1" rows="10" style="width:350px" >{$mod.description|escape:'html'}</textarea></td>
		</tr>
	</table>
</form>
<script type="text/javascript" src="/includes/jquery/jquery.form.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('#title').focus();
    });
</script>
<div class="sess_messages" style="display:none">
  <div class="message_info" id="error_mess"></div>
</div>