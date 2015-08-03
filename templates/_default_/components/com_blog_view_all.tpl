<h1 class="con_heading">{$LANG.BLOGS}</h1>

<div class="blog_type_menu">

    {if !$ownertype}
        <span class="blog_type_active">{$LANG.POSTS_RSS}</span>
    {else}
        <a class="blog_type_link" href="/blogs">{$LANG.POSTS_RSS}</a>
    {/if}

     {if $ownertype == 'all'}
        <span class="blog_type_active">{$LANG.ALL_BLOGS} ({$total})</span>
     {else}
        <a class="blog_type_link" href="/blogs/all.html">{$LANG.ALL_BLOGS}</a>
     {/if}

    {if $ownertype == 'single'}
        <span class="blog_type_active">{$LANG.PERSONALS} <span class="blog_type_num">({$total})</span></span>
    {else}
        <a class="blog_type_link" href="/blogs/single.html">{$LANG.PERSONALS}</a>
    {/if}

    {if $ownertype == 'multi'}
        <span class="blog_type_active">{$LANG.COLLECTIVES} <span class="blog_type_num">({$total})</span></span>
    {else}
        <a class="blog_type_link" href="/blogs/multi.html">{$LANG.COLLECTIVES}</a>
    {/if}

</div>
{if $blogs}
	<table width="100%" cellspacing="0" cellpadding="4" class="blog_full_list">
		{foreach key=tid item=blog from=$blogs}
            <tr>
                <td class="blog_title_td"><a class="blog_title" href="{$blog.url}">{$blog.title}</a></td>
                {if $blog.ownertype =='single'}
                    <td width="220"><a class="blog_user" href="{profile_url login=$blog.login}">{$blog.nickname}</a></td>
                {else}
                    <td width="220">&nbsp;</td>
                {/if}
                <td width="40"><span class="blog_posts">{$blog.records}</span></td>
                <td width="40"><span class="blog_comm">{$blog.comments_count}</span></td>
                {if $cfg.rss_one}
                    <td width="16">
                        <a class="blog_rss" href="/rss/blogs/{$blog.id}/feed.rss"></a>
                    </td>
                {/if}
                <td width="20" align="center" valign="middle">{$blog.rating|rating}</td>
            </tr>
		{/foreach}
	</table>

	{if $cfg.rss_all}
		<div class="blogs_full_rss">
			<a href="/rss/blogs/all/feed.rss">{$LANG.BLOGS_RSS}</a>
		</div>
	{/if}
	{$pagination}
{else}
	<p>{$LANG.NOT_ACTIVE_BLOGS}</p>
{/if}