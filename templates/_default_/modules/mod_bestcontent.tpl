{if $articles}
<table cellspacing="2" cellpadding="4" border="0" width="100%">
{foreach key=tid item=article from=$articles}
	<tr>
		<td class="mod_blog_karma" valign="top" width="30">{$article.rating|rating}</td>
		<td valign="top">
			<div>
				<a class="mod_bcon_content" style="font-size:16px" href="{$article.url}">{$article.title|truncate:60}</a> &mdash;
				<span class="mod_bcon_date">{$article.fpubdate}</span> (<a class="mod_bcon_author" href="{profile_url login=$article.user_login}">{$article.author}</a>)
			</div>
		{if $cfg.showdesc neq 0}
            {if $article.image}
                <div class="mod_latest_image">
                    <img src="/images/photos/small/{$article.image}" border="0" width="32" height="32" alt="{$article.title|escape:'html'}"/>
                </div>
            {/if}
			<div>{$article.description}</div>
		{/if}
		</td>
	</tr>
{/foreach}
{if $cfg.showlink neq 0}
	<tr><td colspan="2">
		<div style="text-align:right">
			<a href="/content/top.html">{$LANG.BESTCONTENT_FULL_RATING}</a> &rarr;
		</div>
	</td></tr>
{/if}
</table>
{else}
<p>{$LANG.BESTCONTENT_NOT_ARTICLES}</p>
{/if}