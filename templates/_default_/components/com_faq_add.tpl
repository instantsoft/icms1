<div class="con_heading">{$LANG.SET_QUESTION}</div>

<div style="margin-top:10px">{$LANG.SET_QUESTION_TEXT}</div>
<div style="margin-bottom:10px">{$LANG.CONTACTS_TEXT}</div>

{if $error}<p style="color:red">{$error}</p>{/if}

<form action="" method="POST" name="questform">
	<table cellpadding="0" cellspacing="0" class="faq_add_cat">
		<tr>
			<td width="150">
				<strong>{$LANG.CAT_QUESTIONS}: </strong>
			</td>
			<td>
				<select name="category_id" style="width:300px">
					{$catslist}
				</select>
			</td>
		</tr>
	</table>

	<textarea name="message" id="faq_message" style="">{$message}</textarea>

    {if !$user_id}
        <p style="margin-bottom:10px">{captcha}</p>
    {/if}

	<div>
		<input type="button" style="font-size:16px;margin-right:2px;margin-top:3px;" onclick="sendQuestion()" name="gosend" value="{$LANG.SEND}"/>
		<input type="button" style="font-size:16px;margin-top:3px;" name="cancel" onclick="window.history.go(-1)" value="{$LANG.CANCEL}"/>
	</div>
</form>
