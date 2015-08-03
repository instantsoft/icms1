<form action="" method="post">
    <input type="hidden" name="csrf_token" value="{csrf_token}" />
    {if !$user_id}
    <div style="margin-bottom:10px">
        <input type="text" class="text-input" style="width:98%;color:#666" name="username" value="" placeholder="{$LANG.YOUR_NAME}" />
    </div>
    {/if}

    <div>
        <input type="text" class="text-input" style="width:98%;color:#666" name="friend_email" value="" placeholder="{$LANG.FRIEND_EMAIL}" />
    </div>

    <p style="margin-top:10px">
        <input type="submit" name="send_invite_email" value="{$LANG.DO_INVITE}" />
    </p>

</form>
{if $is_redirect}
<script type="text/javascript">
    $(document).ready(function(){
        location.href='{$smarty.server.REQUEST_URI}';
    });
</script>
{/if}