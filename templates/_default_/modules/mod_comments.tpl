{foreach key=aid item=comment from=$comments}
    <div class="mod_com_line">
    	<a class="mod_com_link" href="{$comment.target_link}#c{$comment.id}">{$comment.content|strip_tags|truncate:90}</a> {if $cfg.showtarg} {$comment.rating|rating}{/if}
    </div>
    <div class="mod_com_details">
		{if !$comment.is_profile}{$comment.author}{else}<a class="mod_com_userlink" href="{profile_url login=$comment.author.login}">{$comment.author.nickname}</a>{/if}
    	 {$comment.fpubdate}<br/>
		{if $cfg.showtarg}
			<a class="mod_com_targetlink" href="{$comment.target_link}">{$comment.target_title}</a>
        {/if}
    </div>
{/foreach}
{if $cfg.showrss}
	<div style="margin-top:15px"> <a href="/rss/comments/all/feed.rss" class="mod_latest_rss">{$LANG.COMMENTS_RSS}</a> </div>
{/if}
<div style="margin-top:5px"> <a href="/comments" class="mod_com_all">{$LANG.COMMENTS_ALL}</a> </div>
