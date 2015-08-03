<div class="float_bar">
{if $sub_do == 'threads'}
    <a class="ajaxlink" href="javascript:" onclick="forum.getUserActivity('threads', '{$link}', '1');"><strong>{$LANG.THREADS} ({$thread_count})</strong></a> | <a class="ajaxlink" href="javascript:" onclick="forum.getUserActivity('posts', '{$link}', '1');">{$LANG.MESSAGES1} ({$post_count})</a>
{else}
    {if $thread_count}<a class="ajaxlink" href="javascript:" onclick="forum.getUserActivity('threads', '{$link}', '1');">{$LANG.THREADS} ({$thread_count})</a> | {/if}<a class="ajaxlink" href="javascript:" onclick="forum.getUserActivity('posts', '{$link}', '1');"><strong>{$LANG.MESSAGES1} ({$post_count})</strong></a>
{/if}
{if ($is_admin || $is_moderator) && !$my_profile}
 | <a class="ajaxlink" href="javascript:" onclick="forum.clearAllPosts('{$user_id}', '{csrf_token}');">{$LANG.DELETE_ALL_USER_POSTS}</a>
{/if}
</div>

<h1 class="con_heading">{$pagetitle}</h1>

{if $sub_do == 'threads'}
    {include file='com_forum_view.tpl'}
{else}

    {if $post_count}

    <table class="posts_table" width="100%" cellspacing="2" cellpadding="5" border="0" bordercolor="#999999">
        {$last_thread_id=''}
        {foreach key=pid item=post from=$posts}
            {if $post.thread_id != $last_thread_id}
            <tr>
              <td colspan="2" class="darkBlue-LightBlue">{$LANG.THREAD}: <a  href="/forum/thread{$post.thread_id}.html" >{$post.thread_title}</a></td>
            </tr>
            {/if}
            {$last_thread_id=$post.thread_id}
            <tr class="posts_table_tr">
                <td class="post_usercell" width="140" align="center" valign="top" height="150">
                    <div>
                        <a href="{profile_url login=$post.login}" title="{$LANG.GOTO_PROFILE}">{$post.nickname|escape:html}</a>
                    </div>
                    <div class="post_userrank">
                        {if $post.userrank.group}
                            <span class="{$post.userrank.class}">{$post.userrank.group}</span>
                        {/if}
                        {if $post.userrank.rank}
                            <span class="{$post.userrank.class}">{$post.userrank.rank}</span>
                        {/if}
                    </div>
                    <div class="post_userimg">
                        <a href="{profile_url login=$post.login}" title="{$LANG.GOTO_PROFILE}"><img border="0" class="usr_img_small" src="{$post.avatar_url}" alt="{$post.nickname|escape:html}" /></a>
                        {if $post.user_awards}
                            <div class="post_userawards">
                                {foreach key=aid item=award from=$post.user_awards}
                                    <img src="/images/icons/award.gif" border="0" alt="{$award.title|escape:html}" title="{$award.title|escape:html}"/>
                                {/foreach}
                            </div>
                        {/if}
                    </div>
                    <div class="post_usermsgcnt">{$LANG.MESSAGES}: {$post.post_count}</div>
                    {if $post.city}
                        <div class="post_usermsgcnt">{$post.city}</div>
                    {/if}
                    <div>{$post.flogdate}</div>
                </td>
                <td width="" class="post_msgcell" align="left" valign="top">

                    <div class="post_user_date">
                        {$post.fpubdate}, {$post.wday}
                    </div>

                    <div class="post_content">{$post.content_html}</div>
                    {if $post.attached_files && $cfg.fa_on}
                        <div id="attached_files_{$post.id}">
                        {include file='com_forum_attached_files.tpl'}
                        </div>
                    {/if}
                    {if $post.edittimes}
                        <div class="post_editdate">{$LANG.EDITED}: {$post.edittimes|spellcount:$LANG.COUNT1:$LANG.COUNT2:$LANG.COUNT1} ({$LANG.LAST_EDIT}: {$post.peditdate})</div>
                    {/if}
                    {if $post.signature_html}
                        <div class="post_signature">{$post.signature_html}</div>
                    {/if}
                </td>
            </tr>
            {$num=$num+1}
        {/foreach}
    </table>
    {$pagination}

    {else}
        <p>{$LANG.NOT_POST_BY_USER}</p>
    {/if}

{/if}