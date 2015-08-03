<div class="cm_addentry">
{if $user_can_add}
    {if $can_by_karma || !$cfg.min_karma}
	<form action="/comments/{$do}" id="msgform" method="POST">
        <input type="hidden" name="parent_id" value="{$parent_id}" />
        <input type="hidden" name="comment_id" value="{$comment.id}" />
        <input type="hidden" name="csrf_token" value="{csrf_token}" />
        <input type="hidden" name="target" value="{$target}"/>
        <input type="hidden" name="target_id" value="{$target_id}"/>
        {if !$is_user}
            <div class="cm_guest_name"><label>{$LANG.YOUR_NAME}: <input type="text" maxchars="20" size="30" name="guestname" class="text-input" value="" id="guestname" /></label></div>
            <script type="text/javascript">$(document).ready(function(){ $('#guestname').focus(); });</script>
        {/if}
        {if $is_can_bbcode}
            <div class="usr_msg_bbcodebox">{$bb_toolbar}</div>
            <div class="cm_smiles">{$smilies}</div>
        {/if}
        <div class="cm_editor">
            <textarea id="content" name="content" class="ajax_autogrowarea" style="height:150px;min-height: 150px;">{$comment.content_bbcode|escape:'html'}</textarea>
        </div>
        {if $do=='add'}
            {if $need_captcha}
                <div class="cm_codebar">{captcha}</div>
            {/if}
            <div class="submit_cmm">
                <input id="submit_cmm" type="button" value="{$LANG.SEND}"/>
                <input id="cancel_cmm"type="button" onclick="$('.cm_addentry').remove();$('.cm_add_link').show();" value="{$LANG.CANCEL}"/>
            </div>
        {/if}
        {if $is_user && $do=='add'}
            {if !$user_subscribed}
                <div style="margin:9px 0; float:right; font-size:12px; vertical-align:middle">
                    <label style="padding:5px"><input name="subscribe" type="checkbox" value="1" style="margin:0; vertical-align:middle" /> {$LANG.NOTIFY_NEW_COMM}</label>
                </div>
            {/if}
        {/if}
	</form>
    <div class="sess_messages" {if !$notice}style="display:none"{/if}>
      <div class="message_info" id="error_mess">{$notice}</div>
    </div>
    {else}
        {if $is_user}
            <p>{$LANG.YOU_NEED} <a href="/users/{$is_user}/karma.html">{$LANG.KARMS}</a> {$LANG.TO_ADD_COMM}.<br> {$LANG.NEED} &mdash; {$karma_need}, {$LANG.HAS} &mdash; {$karma_has}.</p>
        {else}
            <p>{$LANG.COMMENTS_CAN_ADD_ONLY} <a href="/registration" />{$LANG.REGISTERED}</a> {$LANG.USERS}.</p>
        {/if}
    {/if}
{else}
    <p>{$LANG.YOU_HAVENT_ACCESS_TEXT}</p>
{/if}
</div>