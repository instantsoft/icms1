<div class="float_bar">
    {if $user_id}{if $forum.id}<a href="/forum/{$forum.id}/newthread.html"><strong>{$LANG.NEW_THREAD}</strong></a> | {/if}<a href="/forum/my_activity.html">{$LANG.MY_ACTIVITY}</a> | {/if}<a href="/forum/latest_posts">{$LANG.LATEST_POSTS}</a> | <a href="/forum/latest_thread">{$LANG.NEW_THREADS}</a>
</div>

<h1 class="con_heading">{$pagetitle}{if $cfg.is_rss} <a href="/rss/forum/{if $forum}{$forum.id}{else}all{/if}/feed.rss" title="{$LANG.RSS}"><img src="/images/markers/rssfeed.png" border="0" alt="{$LANG.RSS}"/></a>{/if}</h1>

{if $forums}
<table class="forums_table" width="100%" cellspacing="0" cellpadding="8" border="0" bordercolor="#999999" >
    {$row=1}
    {foreach key=fid item=forum from=$forums}
        {if $forum.cat_title != $last_cat_title}
            <tr>
              <td colspan="2" width="" class="darkBlue-LightBlue"><a href="/forum/{$forum.cat_seolink}">{$forum.cat_title}</a></td>
              <td width="120" class="darkBlue-LightBlue">{$LANG.FORUM_ACT}</td>
              <td width="250" class="darkBlue-LightBlue">{$LANG.LAST_POST}</td>
            </tr>
        {/if}
        {if $row % 2}{$class='row11'}{else}{$class='row2'}{/if}
        <tr>
            <td width="32" class="{$class}" align="center" valign="top"><img src="{$forum.icon_url}" border="0" /></td>
            <td width="" class="{$class}" align="left" valign="top">
                <div class="forum_link"><a href="/forum/{$forum.id}">{$forum.title}</a></div>
                <div class="forum_desc">{$forum.description}</div>
                {if $forum.sub_forums}
                    <div class="forum_subs"><span class="forum_subs_title">{$LANG.SUBFORUMS}: </span>
                        {foreach key=sid item=sub_forum from=$forum.sub_forums}
                            {if $comma}, {/if}
                            <a href="/forum/{$sub_forum.id}" title="{$sub_forum.description|escape:'html'}">{$sub_forum.title}</a>
                            {$comma=1}
                        {/foreach}
                        {$comma=0}
                    </div>
                {/if}
            </td>
            <td class="{$class}" style="font-size:11px" valign="top">
                {if $forum.thread_count}
                    <strong>{$LANG.THREADS}:</strong> {$forum.thread_count}
                {else}
                    {$LANG.NOT_THREADS}
                {/if}
                <br/><strong>{$LANG.MESSAGES}:</strong> {$forum.post_count}

            </td>
            <td style="font-size:11px" class="{$class}" valign="top">
                {if $forum.last_msg_array}
                    <strong>{$LANG.IN_THREAD}: {$forum.last_msg_array.thread_link}</strong><br/>
                    {$forum.last_msg_array.fpubdate} {$LANG.FROM} {$forum.last_msg_array.user_link}
                {else}
                    {$LANG.NOT_POSTS}
                {/if}

            </td>
        </tr>
        {$last_cat_title=$forum.cat_title}
        {$row=$row+1}
    {/foreach}
</table>
{/if}