{if $friends || $is_admin}
    <div class="float_bar">
        <a href="javascript:void(0)" class="new_link" onclick="users.sendMess(0, 0, this);return false;" title="{$LANG.NEW_MESS}:"><span class="ajaxlink">{$LANG.WRITE}</span></a>
    </div>
{/if}
<div class="con_heading">{$LANG.MY_MESS}</div>

<div class="usr_msgmenu_tabs">
    {if $opt == 'in'}
        <span class="usr_msgmenu_active in_span">{$page_title} {if $new_messages.messages}({$new_messages.messages}){/if}</span>
        <a class="usr_msgmenu_link out_link" href="/users/{$id}/messages-sent.html">{$LANG.SENT}</a>
        <a class="usr_msgmenu_link notices_link" href="/users/{$id}/messages-notices.html">{$LANG.NOTICES} {if $new_messages.notices}({$new_messages.notices}){/if}</a>
        <a class="usr_msgmenu_link history_link" href="/users/{$id}/messages-history.html">{$LANG.DIALOGS}</a>
    {elseif $opt == 'out'}
        <a class="usr_msgmenu_link in_link" href="/users/{$id}/messages.html">{$LANG.INBOX} {if $new_messages.messages}({$new_messages.messages}){/if}</a>
        <span class="usr_msgmenu_active out_span">{$page_title}</span>
        <a class="usr_msgmenu_link notices_link" href="/users/{$id}/messages-notices.html">{$LANG.NOTICES} {if $new_messages.notices}({$new_messages.notices}){/if}</a>
        <a class="usr_msgmenu_link history_link" href="/users/{$id}/messages-history.html">{$LANG.DIALOGS}</a>
    {elseif $opt == 'notices'}
        <a class="usr_msgmenu_link in_link" href="/users/{$id}/messages.html">{$LANG.INBOX} {if $new_messages.messages}({$new_messages.messages}){/if}</a>
        <a class="usr_msgmenu_link out_link" href="/users/{$id}/messages-sent.html">{$LANG.SENT}</a>
        <span class="usr_msgmenu_active notices_span">{$page_title} {if $new_messages.notices}({$new_messages.notices}){/if}</span>
        <a class="usr_msgmenu_link history_link" href="/users/{$id}/messages-history.html">{$LANG.DIALOGS}</a>
    {elseif $opt == 'history'}
        <a class="usr_msgmenu_link in_link" href="/users/{$id}/messages.html">{$LANG.INBOX} {if $new_messages.messages}({$new_messages.messages}){/if}</a>
        <a class="usr_msgmenu_link out_link" href="/users/{$id}/messages-sent.html">{$LANG.SENT}</a>
        <a class="usr_msgmenu_link notices_link" href="/users/{$id}/messages-notices.html">{$LANG.NOTICES} {if $new_messages.notices}({$new_messages.notices}){/if}</a>
        <span class="usr_msgmenu_active history_span">{$page_title}</span>
    {/if}
</div>
<div class="usr_msgmenu_bar">
    <strong>{$LANG.MESS_INBOX}:</strong> <span id="msg_count">{$msg_count}</span>
{if ($opt!='history') && $msg_count>0}
    <div style="float: right;"><a href="javascript:void(0)" onclick="users.cleanCat('/users/{$id}/delmessages-{$opt}.html');return false;">{$LANG.CLEAN_CAT}</a></div>
{/if}
{if $opt=='history'}
    <div style="float: right;">
        <form action="" id="history" method="post">
            <select name="with_id" id="with_id" style="width:360px;" onchange="changeFriend();">
                <option value="0">{$LANG.FRIEND_FOR_DIALOGS}</option>
                {if $interlocutors}
                    {$interlocutors}
                {/if}
            </select>
        </form>
    </div>
{/if}
</div>

{if $records}
    {foreach key=tid item=record from=$records}
    <div class="usr_msg_entry" id="usr_msg_entry_id_{$record.id}">
        <table style="width:100%" cellspacing="0">
        <tr>
            <td class="usr_msg_title" width=""><strong>{$record.authorlink}</strong>, <span class="usr_msg_date">{$record.fpubdate}</span></td>
            {if $record.is_new}
                {if $opt=='in' || $opt == 'notices'}
                    <td class="usr_msg_title" width="90" align="right"><span class="msg_new">{$LANG.NEW}!</span></td>
                {else}
                    <td class="usr_msg_title" width="90" align="right"><a class="msg_delete" href="javascript:void(0)" onclick="users.deleteMessage('{$record.id}')"><span class="ajaxlink">{$LANG.CANCEL_MESS}</span></a></td>
                {/if}
            {else}
                <td class="usr_msg_title" width="14" align="right">&nbsp;</td>
                <td class="usr_msg_title" width="20" align="right">&nbsp;</td>
            {/if}
            {if $opt=='in'}
                {if $record.sender_id>0}
                    <td class="usr_msg_title" width="80" align="right"><a href="javascript:void(0)" class="msg_reply" onclick="users.sendMess('{$record.from_id}', '{$record.id}', this);return false;" title="{$LANG.NEW_MESS}: {$record.author|escape:'html'}"><span class="ajaxlink">{$LANG.REPLY}</span></a></td>
                    <td class="usr_msg_title" width="80" align="right"><a class="msg_history" href="/users/{$id}/messages-history{$record.from_id}.html">{$LANG.HISTORY}</a></td>
                {/if}
            {/if}
            {if $opt == 'in' || (in_array($opt, array('out','history','notices')) && !$record.is_new)}
                <td class="usr_msg_title" width="70" align="right"><a class="msg_delete" href="javascript:void(0)" onclick="users.deleteMessage('{$record.id}')"><span class="ajaxlink">{$LANG.DELETE}</span></a></td>
            {/if}
        </tr>
        </table>
        <table cellspacing="4">
        <tr>
            <td width="70" height="70" valign="middle" align="center" style="border:solid 1px #C3D6DF; padding: 4px">
                {if $record.sender_id > 0}
                    <a href="{profile_url login=$record.author_login}"><img border="0" class="usr_img_small" src="{$record.user_img}" /></a>
                {else}
                    <img border="0" class="usr_img_small" src="{$record.user_img}" />
                {/if}
                <div style="margin: 4px 0 0 0;">{$record.online_status}</div>
            </td>
            <td width="" valign="top"><div style="padding:6px">{$record.message}</div></td>
        </tr>
        </table>
    </div>
    {/foreach}
    {$pagebar}
{else}
    <p style="padding:20px 10px">{$LANG.NOT_MESS_IN_CAT}</p>
{/if}

<script type="text/javascript">
    function changeFriend(){
        fr_id = $("#with_id option:selected").val();
        if(fr_id != 0) {
            $("#history").attr("action", '/users/{$id}/messages-history'+fr_id+'.html');
            $('#history').submit();
        }
    }
</script>