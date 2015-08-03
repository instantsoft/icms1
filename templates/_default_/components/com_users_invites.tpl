<h1 class="con_heading">{$LANG.MY_INVITES}</h1>

<p style="margin-bottom: 4px">{$LANG.YOU_CAN_SEND} {$invites_count|spellcount:$LANG.INVITE1:$LANG.INVITE2:$LANG.INVITE10}</p>

<p style="margin-bottom: 10px">{$LANG.INVITE_NOTICE}</p>

<p style="margin-bottom: 5px"><strong>{$LANG.INVITE_EMAIL}:</strong></p>

<form method="post" action="">
    <input type="hidden" name="csrf_token" value="{csrf_token}" />
    <input type="text" name="invite_email" class="text-input" value="" style="width:200px"/>

    <input type="submit" name="send_invite" value="{$LANG.SEND_INVITE}" />

</form>


