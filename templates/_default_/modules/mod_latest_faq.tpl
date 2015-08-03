{if $faq}
<table cellspacing="4" border="0" width="100%">
{foreach key=aid item=quest from=$faq}
	<tr>
		<td width="20" valign="top"><img src="/images/markers/faq.png" border="0" /></td>
		<td>
			<div class="mod_faq_quest">{$quest.quest|truncate:$cfg.maxlen}</div>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><span class="mod_faq_date">{$quest.date}</span> &mdash; <a href="{$quest.href}">{$LANG.LATEST_FAQ_DETAIL}...</a></td>
	</tr>
{/foreach}
</table>
{else}
    <p>{$LANG.LATEST_FAQ_NOT_QUES}</p>
{/if}
