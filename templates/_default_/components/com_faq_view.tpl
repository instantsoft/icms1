{if $is_user || $cfg.guest_enabled}
<div class="faq_send_quest">
    <a href="/faq/sendquest{if $id>0}{$id}{/if}.html">{$LANG.SET_QUESTION}</a>
</div>
{/if}

<div class="con_heading">{$pagetitle}</div>

{if $is_subcats}
	{if $id>0}
		<div class="faq_subcats">
	{else}
		<div class="faq_cats">
	{/if}
		<table width="100%">
			{foreach key=tid item=subcat from=$subcats}
				<tr>
					<td width="40" valign="top"><img src="/templates/{template}/images/icons/big/folder.png" border="0" /></td>
					<td valign="top">
						<div class="faq_cat_link"><a href="/faq/{$subcat.id}">{$subcat.title}</a></div>
						{if $subcat.description}
							<div class="faq_cat_desc">{$subcat.description}</div>
						{/if}
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
{/if}

{if $is_quests}
    {if $id==0}
        <h1 class="con_heading">{$LANG.LAST_QUESTIONS}</h1>
    {/if}
	{foreach key=tid item=quest from=$quests}
		<div class="faq_quest">
			<table cellspacing="5" cellpadding="0" border="0" width="100%">
				<tr>
					<td width="30" valign="top"><img src="/templates/{template}/images/icons/big/faq_quest.png" border="0" /></td>
					<td width="" valign="middle">
						<div class="faq_quest_link"><a href="/faq/quest{$quest.id}.html">{$quest.quest}</a></div>
                        {if $id==0}
                        <div class="faq_questcat">&rarr;  <a href="/faq/{$quest.cid}">{$quest.cat_title}</a></div>
                        {/if}
						<div class="faq_questdate">{$quest.pubdate}</div>
                        {if $cfg.user_link}
                        <div class="faq_questuser">{if $quest.nickname}<a href="{profile_url login=$quest.login}">{$quest.nickname}</a>{else}{$LANG.QUESTION_GUEST}{/if}</div>
                        {/if}

					</td>
				</tr>
			</table>
		</div>
	{/foreach}
	{if $id > 0} {$pagebar} {/if}
{/if}
