{if !$is_homepage}
    {if $cat.showrss}
        <table cellpadding="0" cellspacing="0" border="0">
            <tr>
			<td><h1 class="con_heading">{$pagetitle}</h1></td>
			<td valign="top" style="padding-left:6px">
                <div class="con_rss_icon">
                    <a href="/rss/content/{$cat.id}/feed.rss" title="{$LANG.RSS}"><img src="/templates/{template}/images/icons/rss.png" alt="{$LANG.RSS}"/></a>
                </div>
            </td>
        </table>
    {else}
        <h1 class="con_heading">{$pagetitle}</h1>
    {/if}

    {if $cat.description}
        <div class="con_description">{$cat.description}</div>
    {/if}
{/if}

{if $subcats}
	<div class="categorylist">
		{foreach key=tid item=subcat from=$subcats}
            <div class="subcat">
                <a href="{$subcat.url}" class="con_subcat">{$subcat.title}</a> ({$subcat.content_count})
                <div class="con_description">{$subcat.description}</div>
            </div>
		{/foreach}
	</div>
{/if}

{if $cat_photos}
    {if $cat_photos.album.title}
        <h3>{$cat_photos.album.title}</h3>
    {/if}
    {$fcol="1"}
    <table cellpadding="0" cellspacing="0" border="0">
        {foreach key=tid item=con from=$cat_photos.photos}
            {if $fcol==1} <tr> {/if}
            <td align="center" valign="middle" width="{math equation="100/x" x=$cat_photos.album.maxcols}%">
                <div class="photo_thumb" align="center">
                    <a class="lightbox-enabled" rel="lightbox-galery" href="/images/photos/medium/{$con.file}" title="{$con.title|escape:'html'}">
                        <img class="photo_thumb_img" src="/images/photos/small/{$con.file}" alt="{$con.title|escape:'html'}" />
                    </a><br />
                    <a href="/photos/photo{$con.id}.html" title="{$con.title|escape:'html'}">{$con.title|truncate:15}</a>
                </div>
            </td>
        {if $fcol==$cat_photos.album.maxcols} </tr> {$fcol="1"} {else} {$fcol=$fcol+1} {/if}
        {/foreach}
        {if $fcol>1}
            <td colspan="{math equation="y - x + 1" x=$fcol y=$cat_photos.album.maxcols}">&nbsp;</td></tr>
        {/if}
   </table>
{/if}

{if $articles}
	{$col="1"}

	<table class="contentlist" cellspacing="2" border="0" width="100%">
		{foreach key=tid item=article from=$articles}
            {if $col==1} <tr> {/if}
                <td width="" valign="top">
                    <div class="con_title">
                        <a href="{$article.url}" class="con_titlelink">{$article.title}</a>
                    </div>
                    {if $cat.showdesc}
                        <div class="con_desc">
                            {if $article.image}
                                <div class="con_image">
                                    <img src="/images/photos/small/{$article.image}" alt="{$article.title|escape:'html'}"/>
                                </div>
                            {/if}
                            {$article.description}
                        </div>
                    {/if}

                    {if $cat.showcomm || $showdate || ($cat.showtags && $article.tagline)}
                        <div class="con_details">
                            {if $showdate}
                                {$article.fpubdate} - <a href="{profile_url login=$article.user_login}" style="color:#666">{$article.author}</a>
                            {/if}
                            {if $cat.showcomm}
                                {if $showdate} | {/if}
                                <a href="{$article.url}" title="{$LANG.DETAIL}">{$LANG.DETAIL}</a>
                                | <a href="{$article.url}#c" title="{$LANG.COMMENTS}">{$article.comments|spellcount:$LANG.COMMENT1:$LANG.COMMENT2:$LANG.COMMENT10}</a>
                            {/if}
                             | {$article.hits|spellcount:$LANG.HIT:$LANG.HIT2:$LANG.HIT10}
                            {if $cat.showtags && $article.tagline}
                                {if $showdate || $cat.showcomm} <br/> {/if}
                                {if $article.tagline} <strong>{$LANG.TAGS}:</strong> {$article.tagline} {/if}
                            {/if}
                        </div>
                    {/if}
                </td>
                {if $col==$cat.maxcols} </tr> {$col="1"} {else} {$col=$col+1} {/if}
		{/foreach}
		{if $col>1}
			<td colspan="{math equation="y - x + 1" x=$col y=$cat.maxcols}">&nbsp;</td></tr>
		{/if}
	</table>
	{$pagebar}
{/if}