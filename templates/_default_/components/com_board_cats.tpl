{if $category.is_can_add || $root_id==$category.id}
<div class="float_bar">
	<table cellpadding="2" cellspacing="0">
		<tr><td><img src="/components/board/images/add.gif" border="0"/></td>
		<td><a href="/board/{if $root_id!=$category.id}{$category.id}/{/if}add.html">{$LANG.ADD_ADV}</a></td></tr>
	</table>
</div>
{/if}

<h1 class="con_heading">{$category.title} <a href="/rss/board/{if $root_id==$category.id}all{else}{$category.id}{/if}/feed.rss" title="{$LANG.RSS}"><img src="/images/markers/rssfeed.png" border="0" alt="{$LANG.RSS}"/></a></h1>

{if $cats}
	<table class="board_categorylist" cellspacing="3" width="100%" border="0">
		{$col="1"}
		{foreach key=tid item=cat from=$cats}
			{if $col==1} <tr> {/if}
				<td width="30" valign="top">
                    <img class="bd_cat_main_icon" src="/upload/board/cat_icons/{$cat.icon}" border="0" />
                </td>
				<td valign="top" class="bd_cat_cell">
					<div class="bd_cat_main_title"><a href="/board/{$cat.id}">{$cat.title}</a> ({$cat.content_count})</div>
					{if $cat.description}
						<div class="bd_cat_main_desc">{$cat.description}</div>
					{/if}
					<div class="bd_cat_main_obtypes">{$cat.ob_links}</div>
				</td>
			{if $col==$maxcols} </tr> {$col="1"} {else} {$col=$col+1} {/if}
		{/foreach}

		{if $col>1}
			<td colspan="{math equation="x - y + 1" x=$col y=$maxcols}">&nbsp;</td></tr>
		{/if}
	</table>
{/if}
{if $category.description}
    <div class="board_description">{$category.description}</div>
{/if}