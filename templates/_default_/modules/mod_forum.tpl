<table width="100%" cellspacing="0" cellpadding="5" border="0" >
    {foreach key=tid item=thread from=$threads}
        <tr>
            <td align="left" width="100"><div class="{if $thread.is_new}mod_fweb2_date_new{/if} mod_fweb2_date" {if $thread.is_new}title="{$LANG.HAVE_NEW_MESS}"{/if}>{$thread.last_msg_array.fpubdate}</div></td>
            <td width="13">
                <img src="/templates/{template}/images/icons/user_comment.png" border="0" />
            </td>
            <td style="padding-left:0px">{$thread.last_msg_array.user_link} {if $thread.last_msg_array.post_count == 1}{$LANG.FORUM_START_THREAD}{else}{$LANG.FORUM_REPLY_THREAD}{/if} &laquo;{$thread.last_msg_array.thread_link}&raquo;
            {if $cfg.showforum} {$LANG.FORUM_ON_FORUM} &laquo;<a href="/forum/{$thread.forum_id}">{$thread.forum_title}</a>&raquo;{/if}</td>
        </tr>

        {if $cfg.showtext}
        <tr>
            <td colspan="3"><div class="mod_fweb2_shorttext">{$thread.last_msg_array.content_html|strip_tags|truncate:200}</div></td>
        </tr>
        {/if}
    {/foreach}
</table>