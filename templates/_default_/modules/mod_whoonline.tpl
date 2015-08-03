{if $users}
    {", "|implode:$users}
{else}
    <div><strong>{$LANG.WHOONLINE_USERS}:</strong> 0</div>
{/if}
<div style="margin-top:10px"><strong>{$LANG.WHOONLINE_GUESTS}:</strong> {$guests}</div>

{if $cfg.show_today}
    <div style="margin-top:10px;margin-bottom:8px"><strong>{$LANG.WAS_TODAY}:</strong></div>
    {if $today_users}
        {", "|implode:$today_users}
    {else}
        <div>{$LANG.NOBODY_TODAY}</div>
    {/if}
{/if}