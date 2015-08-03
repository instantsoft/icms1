<div class="con_rss_icon">
    <span class="blog_entry_date">{$blog.pubdate}</span>
    <span class="post_karma">{$blog.rating|rating}</span>
    <a href="/rss/{component}/{$blog.id}/feed.rss" title="{$LANG.RSS}">
        {$LANG.RSS} <img src="/templates/{template}/images/icons/rss.png" border="0" alt="{$LANG.RSS}"/>
    </a>
</div>
<h1 class="con_heading">{$blog.title}</h1>

{if !$myblog}
	{if $blog.ownertype == 'single'}
		<table cellspacing="0" cellpadding="5" class="blog_desc">
			<tr>
				<td width=""><strong>{$LANG.BLOG_AVTOR}:</strong></td>
				<td width="">{$blog.author}</td>
			</tr>
		</table>
	{else}
		<table cellspacing="0" cellpadding="2" class="blog_desc">
			<tr>
				<td width=""><strong>{$LANG.BLOG_ADMIN}:</strong></td>
				<td width="">{$blog.author}</td>
                {if $blog.forall}
                	<td width=""><span class="blog_authorsall">({$LANG.BLOG_OPENED_FOR_ALL})</span></td>
                {/if}
			</tr>
		</table>
	{/if}
{/if}

{if $myblog || $is_writer || $is_admin}
    <div class="blog_toolbar">
	{if $myblog || $is_admin}

		<table cellspacing="0" cellpadding="2">
			<tr>
				{if $on_moderate}
					<td width="16"><img src="/templates/{template}/images/icons/folder_table.png" border="0"/></td>
					<td width=""><a class="blog_moderate_link" href="{$blog.moderate_link}">{$LANG.MODERATING} ({$on_moderate})</a></td>
				{/if}
				<td width="16"><img src="/templates/{template}/images/icons/edit.png" border="0"/></td>
				<td width=""><a href="{$blog.add_post_link}">{$LANG.NEW_POST}</a></td>
                <td width="16"><img src="/templates/{template}/images/icons/addcat.png" border="0"/></td>
                <td width=""><a class="ajaxlink" href="javascript:void(0)" onclick="$('#opt_cat').toggle();">{$LANG.CATS}</a></td>
                {if $is_config}
                    <td width="16"><img src="/templates/{template}/images/icons/settings.png" border="0"/></td>
                    <td width=""><a class="ajaxlink" href="javascript:void(0)" onclick="{component}.editBlog({$blog.id});return false;">{$LANG.CONFIG}</a></td>
                {/if}
			</tr>
		</table>

        <table cellspacing="0" cellpadding="5" id="opt_cat" style="display:none; background-color:#E0EAEF;position: absolute;right: 54px;top: 32px;">
            <tr><td width="16"><img src="/templates/{template}/images/icons/addcat.png" border="0"/></td>
            <td width=""><a class="ajaxlink" href="javascript:void(0)" onclick="{component}.addBlogCat({$blog.id});return false;">{$LANG.NEW_CAT}</a></td><tr>
        {if $cat_id>0}
            <tr><td width="16"><img src="/templates/{template}/images/icons/editcat.png" border="0"/></td>
            <td width=""><a class="ajaxlink" href="javascript:void(0)" onclick="{component}.editBlogCat({$cat_id});return false;">{$LANG.RENAME_CAT}</a></td><tr>
            <tr><td width="16"><img src="/templates/{template}/images/icons/deletecat.png" border="0"/></td>
            <td width=""><a class="ajaxlink" href="javascript:void(0)" onclick="{component}.deleteCat({$cat_id}, '{csrf_token}');return false;">{$LANG.DEL_CAT}</a></td><tr>
        {/if}
        </table>

	{elseif $is_writer}
		<table cellspacing="0" cellpadding="5">
			<tr>
				<td width="16"><img src="/templates/{template}/images/icons/edit.png" border="0"/></td>
				<td width=""><a href="{$blog.add_post_link}">{$LANG.NEW_POST}</a></td>
			</tr>
		</table>
	{/if}
    </div>
{/if}

{if $blogcats}
<div class="blog_catlist">

	<div class="blog_cat">
		<table cellspacing="0" cellpadding="1">
			<tr>
				<td width="16"><img src="/templates/{template}/images/icons/folder.png" border="0" /></td>
				{if $cat_id}
					<td><a href="{$blog.blog_link}">{$LANG.ALL_CATS}</a> <span style="color:#666666">({$all_total})</span></td>
				{else}
					<td>{$LANG.ALL_CATS} <span style="color:#666666">({$total})</span></td>
				{/if}
			</tr>
		</table>
	</div>

	{foreach key=tid item=cat from=$blogcats}
		<div class="blog_cat">
			<table cellspacing="0" cellpadding="2">
				<tr>
					<td width="16"><img src="/templates/{template}/images/icons/folder.png" border="0" /></td>
					{if $cat_id!=$cat.id}
						<td><a href="{$blog.blog_link}/cat-{$cat.id}">{$cat.title}</a> <span style="color:#666666">({$cat.post_count})</span></td>
					{else}
						<td>{$cat.title} <span style="color:#666666">({$cat.post_count})</span></td>
                        {$cur_cat=$cat}
					{/if}
				</tr>
			</table>
		</div>
	{/foreach}

</div>
{if $cur_cat.description}
	<div class="usr_photos_notice">{$cur_cat.description|nl2br}</div>
{/if}
{/if}

{if $posts}
	<div class="blog_entries">
		{foreach key=tid item=post from=$posts}
			<div class="blog_entry">
				<table width="100%" cellspacing="0" cellpadding="0" class="blog_records">
					<tr>
						<td width="" class="blog_entry_title_td">
							<div class="blog_entry_title"><a href="{$post.url}">{$post.title}</a></div>
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
