{if $cfg.is_rss}
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td><h1 class="con_heading">{$title}</h1></td>
			<td valign="top" style="padding-left:6px">
                <div class="con_rss_icon">
                    <a href="/rss/catalog/all/feed.rss" title="{$LANG.RSS}"><img src="/images/markers/rssfeed.png" border="0" alt="{$LANG.RSS}"/></a>
                </div>
			</td>
		</tr>
	</table>
{else}
	<h1 class="con_heading">{$title}</h1>
{/if}

{if $cats_html}
    {$cats_html}
{else}
    {$LANG.NO_CAT_IN_CATALOG}
{/if}
