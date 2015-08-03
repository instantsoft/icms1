<div class="con_heading">{$pagetitle}</div>

<div class="blog_type_menu">

        {if !$ownertype}
            <span class="blog_type_active">{$LANG.POSTS_RSS} ({$total})</span>
        {else}
            <a class="blog_type_link" href="/blogs">{$LANG.POSTS_RSS}</a>
        {/if}

         {if $ownertype == 'all'}
            <span class="blog_type_active">{$LANG.ALL_BLOGS}</span>
         {else}
            <a class="blog_type_link" href="/blogs/all.html">{$LANG.ALL_BLOGS}</a>
         {/if}

        {if $ownertype == 'single'}
            <span class="blog_type_active">{$LANG.PERSONALS}</span>
        {else}
            <a class="blog_type_link" href="/blogs/single.html">{$LANG.PERSONALS}</a>
        {/if}

        {if $ownertype == 'multi'}
            <span class="blog_type_active">{$LANG.COLLECTIVES}</span>
        {else}
            <a class="blog_type_link" href="/blogs/multi.html">{$LANG.COLLECTIVES}</a>
        {/if}

</div>

{if $posts}
	<div class="blog_entries">
		{foreach key=tid item=post from=$posts}
			<div class="blog_entry">
				<table width="100%" cellspacing="0" cellpadding="0" class="blog_records">
					<tr>
						<td width="" class="blog_entry_title_td">
							<div class="blog_entry_title">
                                {if $post.blog_url}
                                    <a href="{$post.blog_url}" style="color:gray">{$post.blog_title}</a> &rarr;
                                {/if}
                                <a href="{$post.url}">{$post.title}</a>
                            </div>
						</td>
					</tr>
					<tr>
						<td>
							<div class="blog_entry_text">{$post.content_html}</div>
							<div class="blog_comments">
                                <a class="blog_user" href="{profile_url login=$post.login}">{$post.author}</a>
                                <span class="blog_entry_date">{if !$post.published}<span style="color:#CC0000">{$LANG.ON_MODERATE}</span>{else}{$post.fpubdate}{/if}</span>
                                <span class="post_karma">{$post.rating|rating}</span>
                                <span class="post_hits">{$post.hits}</span>
								{if ($post.comments_count > 0)}
									<a class="blog_comments_link" href="{$post.url}#c">{$post.comments_count|spellcount:$LANG.COMMENT:$LANG.COMMENT2:$LANG.COMMENT10}</a>
								{else}
									<a class="blog_comments_link" href="{$post.url}#c">{$LANG.NOT_COMMENTS}</a>
								{/if}
							{if $post.tagline != false}
								 <span class="tagline">{$post.tagline}</span>
							{/if}
							</div>
						</td>
					</tr>
				</table>
			</div>
		{/foreach}
	</div>

	{$pagination}
{else}
	<p style="clear:both">{$LANG.NOT_POSTS}</p>
{/if}
