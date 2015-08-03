<div class="con_heading">{$LANG.QUESTION_VIEW} {if $is_admin}<a href="/faq/delquest{$quest.id}.html">X</a>{/if}</div>

<table cellspacing="5" cellpadding="0" border="0" width="100%">
	<tr>
		<td width="35" valign="top"><img src="/templates/{template}/images/icons/big/faq_quest.png" border="0" /></td>
		<td width="" valign="top">
			<div class="faq_questtext">{$quest.quest}</div>
			{if $cfg.user_link}
            <div class="faq_questuser">{if $quest.nickname}<a href="{profile_url login=$quest.login}">{$quest.nickname}</a>{else}{$LANG.QUESTION_GUEST}{/if}</div>
			{/if}
			<div class="faq_questdate">{$quest.pubdate}</div>
		</td>
	</tr>
</table>

{if $quest.answer}
<table cellspacing="5" cellpadding="0" border="0" width="100%" style="margin:15px 0px;">
	<tr>
		<td width="35" valign="top">
			<img src="/templates/{template}/images/icons/big/faq_answer.png" border="0" />
		</td>
		<td width="" valign="top">
			<div class="faq_answertext">{$quest.answer}</div>
			<div class="faq_questdate">{$quest.answerdate}</div>
		</td>
	</tr>
</table>
{/if}

{if $cfg.is_comment}
{comments target='faq' target_id=$quest.id labels=$labels}
{/if}