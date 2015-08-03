<table class="threads_table" width="100%" cellspacing="0" cellpadding="5" border="0">
    <tr>
        <td colspan="2" class="darkBlue-LightBlue">{$LANG.THREADS}</td>
        <td class="darkBlue-LightBlue">{$LANG.AUTHOR}</td>
        <td class="darkBlue-LightBlue">{$LANG.FORUM_ACT}</td>
        <td class="darkBlue-LightBlue">{$LANG.LAST_POST}</td>
    </tr>
{if $threads}
    {$row=1}
    {foreach key=id item=thread from=$threads}
        {if $row % 2}{$class='row1'}{else}{$class='row2'}{/if}
        <tr>
            {if $thread.pinned}
                <td width="30" class="{$class}" align="center" valign="middle"><img alt="{$LANG.ATTACHED_THREAD}" src="/templates/{template}/images/icons/forum/pinned.png" border="0" title="{$LANG.ATTACHED_THREAD}" /></td>
            {else}
                {if $thread.closed}
                    <td width="30" class="{$class}" align="center" valign="middle"><img alt="{$LANG.THREAD_CLOSE}" src="/templates/{template}/images/icons/forum/closed.png" border="0" title="{$LANG.THREAD_CLOSE}" /></td>
                {else}
                    {if $thread.is_new}
                        <td width="30" class="{$class}" align="center" valign="middle"><img alt="{$LANG.HAVE_NEW_MESS}" src="/templates/{template}/images/icons/forum/new.png" border="0" title="{$LANG.HAVE_NEW_MESS}" /></td>
                    {else}
                        <td width="30" class="{$class}" align="center" valign="middle"><img alt="{$LANG.NOT_NEW_MESS}" src="/templates/{template}/images/icons/forum/old.png" border="0" title="{$LANG.NOT_NEW_MESS}" /></td>
                    {/if}
                {/if}
            {/if}
            <td width="" class="{$class}" align="left">
                <div class="thread_link"><a href="/forum/thread{$thread.id}.html">{$thread.title}</a>
                    {if $thread.pages>1}
                        <span class="thread_pagination" title="{$LANG.PAGES}"> (
                            {section name=foo start=1 loop=$thread.pages+1 step=1}
                                {if $smarty.section.foo.index > 5 && $thread.pages > 6}
                                    ...<a href="/forum/thread{$thread.id}-{$thread.pages}.html" title="{$LANG.LAST}">{$thread.pages}</a>
                                    {break}
                                {else}
                                    <a href="/forum/thread{$thread.id}-{$smarty.section.foo.index}.html" title="{$LANG.PAGE} {$smarty.section.foo.index}">{$smarty.section.foo.index}</a>
                                    {if $smarty.section.foo.index < $thread.pages}, {/if}
                                {/if}
                            {/section}
                        ) </span>
                    {/if}
                </div>
                {if $thread.description}
                    <div class="thread_desc">{$thread.description}</div>
                {/if}
            </td>
            <td width="120" style="font-size:12px" class="{$class}"><a href="{profile_url login=$thread.login}">{$thread.nickname}</a></td>
            <td width="120" style="font-size:12px; color:#375E93" class="{$class}">
                <strong>{$LANG.HITS}:</strong> {$thread.hits}<br/>
                <strong>{$LANG.REPLIES}:</strong> {$thread.answers}
            </td>
            <td width="200" style="font-size:12px" class="{$class}">
                {if $thread.last_msg_array}
                    <a href="/forum/thread{$thread.last_msg_array.thread_id}-{$thread.last_msg_array.lastpage}.html#{$thread.last_msg_array.id}"><img class="last_post_img" title="{$LANG.GO_LAST_POST}" alt="{$LANG.GO_LAST_POST}" src="/templates/{template}/images/icons/anchor.png"></a>
                    {$LANG.FROM} {$thread.last_msg_array.user_link}<br/>
                    {$thread.last_msg_array.fpubdate}
                {else}
                    {$LANG.NOT_POSTS}
                {/if}
            </td>
        </tr>
        {$row=$row+1}
    {/foreach}

{else}
    <td colspan="7" align="center" valign="middle" class="row1">
        <p style="margin: 5px">{$LANG.NOT_THREADS_IN_FORUM}.</p>
    </td>

{/if}
</table>
{$pagination}

{if $show_panel}
<table class="threads_table" width="100%" cellspacing="0" cellpadding="5" border="0" style="margin: 10px 0 0 0; font-size: 12px">
    <tr>
        <td class="row1">{$LANG.OPTIONS_VIEW}</td>
        {if $moderators}
            <td class="row1">{$LANG.THIS_FORUM_MODERS}</td>
        {/if}
    </tr>
    <tr>
        <td>
            <form action="" method="post">
                <table cellspacing="1" cellpadding="5" border="0" style="color: #555">
                <tbody>
                    <tr valign="bottom">
                      <td>
                          <div>{$LANG.THREAD_ORDER}</div>
                            <select name="order_by">
                              <option value="title" {if $order_by == 'title'}selected="selected"{/if}>{$LANG.TITLE}</option>
                              <option value="pubdate" {if $order_by == 'pubdate'}selected="selected"{/if}>{$LANG.ORDER_DATE}</option>
                              <option value="post_count" {if $order_by == 'post_count'}selected="selected"{/if}>{$LANG.ANSWER_COUNT}</option>
                              <option value="hits" {if $order_by == 'hits'}selected="selected"{/if}>{$LANG.HITS_COUNT}</option>
                            </select>
                      </td>
                      <td>
                        <div>{$LANG.ORDER_TO}</div>
                        <select name="order_to">
                          <option value="asc" {if $order_to == 'asc'}selected="selected"{/if}>{$LANG.ORDER_ASC}</option>
                          <option value="desc" {if $order_to == 'desc'}selected="selected"{/if}>{$LANG.ORDER_DESC}</option>
                        </select>
                      </td>
                      <td>
                        <div>{$LANG.SHOW}</div>
                        <select name="daysprune">
                          <option value="1" {if $daysprune == 1}selected="selected"{/if}>{$LANG.SHOW_DAY}</option>
                          <option value="7" {if $daysprune == 7}selected="selected"{/if}>{$LANG.SHOW_W}</option>
                          <option value="30" {if $daysprune == 30}selected="selected"{/if}>{$LANG.SHOW_MONTH}</option>
                          <option value="365" {if $daysprune == 365}selected="selected"{/if}>{$LANG.SHOW_YEAR}</option>
                          <option value="all" {if !$daysprune}selected="selected"{/if}>{$LANG.SHOW_ALL}</option>
                        </select>
                      </td>
                      <td>
                        <div></div>
                        <input type="submit" value="{$LANG.SHOW_THREADS}">
                      </td>
                    </tr>
                </tbody>
            </table>
            </form>
        </td>
        {if $moderators}
            <td style="vertical-align: top">
            {foreach key=id item=moderator from=$moderators}
                {if $q}, {/if}<a href="{profile_url login=$moderator.login}">{$moderator.nickname}</a>
                {$q="1"}
            {/foreach}
        </td>
        {/if}
    </tr>
</table>
{/if}