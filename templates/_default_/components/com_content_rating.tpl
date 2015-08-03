<h1 class="con_heading">{$LANG.ARTICLES_RATING}</h1>
{if $articles}

<table class="contentlist" cellspacing="2" border="0" width="">
    {foreach key=tid item=article from=$articles}
        <tr>
            <td width="20" valign="top" style="font-size:20px">{$article.rating|rating}</td>
            <td width="" valign="top">
                <h2 class="con_title">
                    <a href="{$article.url}" class="con_titlelink">{$article.title}</a>
                </h2>
                {if $article.showdesc}
                    <div class="con_desc">
                        {if $article.image}
                            <div class="con_image">
                                <img src="/images/photos/small/{$article.image}" border="0" alt="{$article.title|escape:'html'}"/>
                            </div>
                        {/if}
                        {$article.description}
                    </div>
                {/if}

                {if $article.showcomm || $article.showdate || $article.tagline}
                    <div class="con_details">
                        {if $article.showdate}
                            {$article.fpubdate} - <a href="{profile_url login=$article.user_login}" style="color:#666">{$article.author}</a>
                        {/if}
                        {if $article.showcomm}
                            {if $article.showdate} | {/if}
                            <a href="{$article.url}#c" title="{$LANG.COMMENTS}">{$article.comments|spellcount:$LANG.COMMENT:$LANG.COMMENT2:$LANG.COMMENT10}</a>
                        {/if}
                         | {$article.hits|spellcount:$LANG.HIT:$LANG.HIT2:$LANG.HIT10}
                        {if $article.tagline}
                             | <strong>{$LANG.TAGS}:</strong> {$article.tagline}
                        {/if}
                        	 | <strong>{$LANG.CAT}:</strong> <a href="{$article.cat_url}">{$article.cat_title}</a>
                    </div>
                {/if}
            </td>
         </tr>
    {/foreach}
</table>

{else}
	<p>{$LANG.NO_ARTICLES_PUBL_ON_SITE}</p>
{/if}