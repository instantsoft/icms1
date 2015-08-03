{if $comments_count}
	{foreach key=cid item=comment from=$comments}
        {$next=$cid+1}
		<a name="c{$comment.id}"></a>
        {if $comment.level < $cfg.max_level-1}
            <div style="margin-left:{math equation="x*35" x=$comment.level}px;">
        {else}
            <div style="margin-left:{math equation="(x-1)*35" x=$cfg.max_level}px;">
        {/if}
        <table class="cmm_entry">
			<tr>
				<td class="cmm_title" valign="middle">
					{if !$comment.is_profile}
						<span class="cmm_author">{$comment.author} {if $is_admin && $comment.ip}({$comment.ip}){/if}</span>
					{else}
						<span class="cmm_author"><a href="{profile_url login=$comment.author.login}">{$comment.author.nickname}</a> {if $is_admin && $comment.ip}({$comment.ip}){/if}</span>
					{/if}
                    <a class="cmm_anchor" href="#c{$comment.id}" title="{$LANG.LINK_TO_COMMENT}">#</a>
                    <span class="cmm_date">{if $comment.published}{$comment.fpubdate}{else}<span style="color:#F00">{$LANG.WAIT_MODERING}</span>{/if}</span>
                    {if !$is_user || $comment.is_voted || !$comment.is_profile}
                        <span class="cmm_votes">
                        {if $comment.rating>0}
                            <span class="cmm_good">+{$comment.rating}</span>
                        {elseif $comment.rating<0}
                            <span class="cmm_bad">{$comment.rating}</span>
                        {else}
                            {$comment.rating}
                        {/if}
                        </span>
                    {else}
                        <span class="cmm_votes" id="votes{$comment.id}">
                            <table border="0" cellpadding="0" cellspacing="0"><tr>
                            <td>{$comment.rating|rating}</td>
                            <td><a href="javascript:void(0);" onclick="voteComment({$comment.id}, -1);" title="{$LANG.BAD_COMMENT}"><img border="0" alt="-" src="/templates/{template}/images/icons/comments/vote_down.gif" style="margin-left:8px"/></a></td>
                            <td><a href="javascript:void(0);" onclick="voteComment({$comment.id}, 1);" title="{$LANG.GOOD_COMMENT}"><img border="0" alt="+" src="/templates/{template}/images/icons/comments/vote_up.gif" style="margin-left:2px"/></a></td>
                            </tr></table>
                        </span>
                    {/if}
				</td>
			</tr>
			<tr>
				{if $comment.is_profile}
					<td valign="top">
						<table width="100%" cellpadding="1" cellspacing="0">
							<tr>
								<td width="70" height="70"  align="center" valign="top" class="cmm_avatar">
									<a href="{profile_url login=$comment.author.login}"><img border="0" class="usr_img_small" src="{$comment.user_image}" /></a>
								</td>
								<td class="cmm_content_av" valign="top">
				{else}
					<td class="cmm_content" valign="top">
				{/if}
                	<div id="cm_msg_{$comment.id}">
					{if $comment.show}
						{$comment.content}
					{else}
						<a href="javascript:void(0)" onclick="expandComment({$comment.id})" id="expandlink{$comment.id}">{$LANG.SHOW_COMMENT}</a>
						<div id="expandblock{$comment.id}" style="display:none">{$comment.content}</div>
					{/if}
                    </div>

                    <div style="margin-top:15px;">
                        <span id="cm_add_link{$comment.id}" class="cm_add_link"><a href="javascript:void(0)" onclick="addComment('{$target|escape:'html'}', '{$target_id}', {$comment.id})" class="ajaxlink">{$LANG.REPLY}</a></span>
                        {if $is_user}
                            {if $is_admin || ($comment.is_my && $comment.is_editable && $comment.content_bbcode) || ($user_can_moderate && $comment.content_bbcode)}
                                {if !$comment.content_bbcode}
                                    <span class="left_border"><a href="/admin/index.php?view=components&do=config&link=comments&opt=edit&item_id={$comment.id}">{$LANG.EDIT}</a></span>
                                {else}
                                   <span class="left_border"><a href="javascript:" onclick="editComment('{$comment.id}', '{csrf_token}')" class="ajaxlink">{$LANG.EDIT}</a></span>
                                {/if}
                            {/if}
                            {if $is_admin || ($comment.is_my && $user_can_delete) || $user_can_moderate || $target_author_can_delete}
                                <span class="left_border"><a href="javascript:" onclick="deleteComment({$comment.id}, '{csrf_token}'{if $comments[$next].level > $comment.level}, 1{/if});return false;" class="ajaxlink">{if $comments[$next].level > $comment.level}{$LANG.DELETE_BRANCH}{else}{$LANG.DELETE}{/if}</a></span>
                            {/if}
                        {/if}
                    </div>

                    {if $comment.is_profile}
                        </td></tr></table>
                    {/if}
					</td>
				</tr>
			</table>
            <div id="cm_addentry{$comment.id}" class="reply" style="display:none"></div>
        </div>
	{/foreach}

{else}
	<p>{$labels.not_comments}</p>
{/if}