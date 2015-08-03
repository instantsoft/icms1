{foreach key=tid item=post from=$posts}
    <div class="mod_latest_entry">

        <div class="mod_latest_image">
            {if !$post.fileurl}
                <a href="{profile_url login=$post.login}" title="{$post.author|escape:'html'}"><img class="usr_img_small img_64" src="{$post.author_avatar}" alt="{$post.author|escape:'html'}" /></a>
            {else}
                <a href="{$post.url}"><img class="usr_img_small img_64" src="{$post.fileurl}" alt="{$post.title|escape:'html'}" /></a>
            {/if}
        </div>

        <a class="mod_latest_blog_title" href="{$post.url}" title="{$post.title|escape:'html'}">{$post.title|truncate:70}</a>

        <div class="mod_latest_date">
            {$post.fpubdate} - <a href="{$post.blog_url}">{$post.blog_title}</a> - <a href="{$post.url}#c" title="{$post.comments_count|spellcount:$LANG.COMMENT1:$LANG.COMMENT2:$LANG.COMMENT10}" class="mod_latest_comments">{$post.comments_count}</a> - <span class="mod_latest_rating">{$post.rating|rating}</span>
        </div>

    </div>
{/foreach}

{if $cfg.showrss}
    <div class="mod_latest_rss">
        <a href="/rss/blogs/all/feed.rss">{$LANG.RSS}</a>
    </div>
{/if}