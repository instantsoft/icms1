<script type="text/javascript" src="/includes/jquery/jquery.form.js"></script>
{if $is_reply_user}
  <div class="usr_msgreply_source">
    <div class="usr_msgreply_sourcetext">{$msg.message}</div>
    <div class="usr_msgreply_author">{$LANG.ORIGINAL_MESS}: <a href="{profile_url login=$msg.login}">{$msg.nickname}</a>, {$msg.senddate}</div>
  </div>
{/if}
<form action="" method="POST" name="msgform" id="send_msgform">
    <input type="hidden" name="gosend" value="1" />
    <input type="hidden" name="csrf_token" value="{csrf_token}" />
    <div class="usr_msg_bbcodebox">{$bbcodetoolbar}</div>
    {$smilestoolbar}
    <div class="cm_editor">
        <textarea class="ajax_autogrowarea" name="message" id="message"></textarea>
    </div>
    <div style="margin-top:6px; display:block">
    {if !$id && $friends}
        <strong>{$LANG.SEND_TO}: </strong>
        <select name="user_id" id="user_id" class="s_usr" style="width:160px;" onchange="changeFriendTo();">
            <option value="0"></option>
            {foreach key=gid item=friend from=$friends}
                <option value="{$friend.id}" {if $id == $friend.id} selected="selected"{/if}>{$friend.nickname}</option>
            {/foreach}
        </select>
    {else}
        <select name="user_id" id="user_id" style="display: none;">
            <option value="{$id}" selected="selected"></option>
        </select>
    {/if}
    {if $id_admin && !$is_reply_user}
        {if !$id}
        <select name="group_id" class="s_grp" id="group_id" style="width:160px; display:none">
            {foreach key=gid item=group from=$groups}
                <option value="{$group.id}">{$group.title}</option>
            {/foreach}
        </select>
        <input type="hidden" name="send_to_group" id="send_to_group" value="0" />
        <a href="javascript:" class="s_usr ajaxlink" onclick="$('.s_grp').fadeIn();$('.s_usr').hide();$('#send_to_group').val(1);">
            {$LANG.SEND_TO_GROUP}
        </a>
        <a href="javascript:" class="s_grp ajaxlink" onclick="$('.s_grp').hide();$('.s_usr').fadeIn();$('#send_to_group').val(0);" style="display:none">
            {$LANG.SEND_TO_FRIEND}
        </a>
        {/if}
        <label>
            <input name="massmail" type="checkbox" value="1"  />
            {$LANG.SEND_TO_ALL}
        </label>
    {/if}
    </div>
</form>
<script type="text/javascript">
$(document).ready(function(){
	$('.ajax_autogrowarea').focus();
});
function changeFriendTo(){
    fr_to = $("#user_id option:selected").html();
    $('#popup_title').html('{$LANG.WRITE_MESS}: '+fr_to);
}
</script>