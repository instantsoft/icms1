{$article.plugins_output_before}

{if $article.showtitle}
    <h1 class="con_heading">{$article.title}</h1>
{/if}

{if $article.showdate}
	<div class="con_pubdate">
		{if !$article.published}<span style="color:#CC0000">{$LANG.NO_PUBLISHED}</span>{else}{$article.pubdate}{/if} - <a href="{profile_url login=$article.user_login}">{$article.author}</a>
	</div>
{/if}

{if $is_pages}
	<div class="con_pt" id="pt">
		<span class="con_pt_heading">
			<a class="con_pt_hidelink" href="javascript:void;" onClick="$('#pt_list').toggle();">{$LANG.CONTENT}</a>
			{if $cfg.pt_hide} [<a href="javascript:void(0);" onclick="$('#pt').hide();">{$LANG.HIDE}</a>] {/if}
		</span>
		<div id="pt_list" style="{if $cfg.pt_disp}display: block;{else}display: none;{/if} width:100%">
			<div>
				<ul id="con_pt_list">
				{foreach key=tid item=pages from=$pt_pages}
					{if ($tid+1 != $page)}
						<li><a href="{$pages.url}">{$pages.title}</a></li>
					{else}
						<li>{$pages.title}</li>
					{/if}
				{/foreach}
				<ul>
			</div>
		</div>
	</div>
{/if}

<div class="con_text" style="overflow:hidden">
    {if $article.image}
        <div class="con_image" style="float:left;margin-top:10px;margin-right:20px;margin-bottom:20px">
            <img src="/images/photos/medium/{$article.image}" alt="{$article.title|escape:html}"/>
        </div>
    {/if}
    {$article.content}
</div>

{if $is_admin || $is_editor || $is_author}
    <div class="blog_comments">
        {if !$article.published && ($is_admin || $is_editor)}
        	<a class="blog_moderate_yes" href="/content/publish{$article.id}.html">{$LANG.ARTICLE_ALLOW}</a> |
        {/if}
        {if $is_admin || $is_editor || $is_author_del}
        	<a class="blog_moderate_no" href="/content/delete{$article.id}.html">{$LANG.DELETE}</a> |
        {/if}
        {if $is_admin || $is_editor || $is_author}
        	<a href="/content/edit{$article.id}.html" class="blog_entry_edit">{$LANG.EDIT}</a>
        {/if}
    </div>
{/if}

{if $article.showtags}
	{$tagbar}
{/if}

{if $cfg.rating && $article.canrate}
	<div id="con_rating_block">
		<div>
			<strong>{$LANG.RATING}: </strong><span id="karmapoints">{$karma_points}</span>
			<span style="padding-left:10px;color:#999"><strong>{$LANG.VOTES}:</strong> {$karma_votes}</span>
            <span style="padding-left:10px;color:#999">{$article.hits|spellcount:$LANG.HIT:$LANG.HIT2:$LANG.HIT10}</span>
		</div>
		{if $karma_buttons}
            <div id="karmactrl"><strong>{$LANG.RAT_ARTICLE}:</strong> {$karma_buttons}</div>
		{/if}
	</div>
{/if}

{$article.plugins_output_after}