<div class="con_heading">{$LANG.SELECTING_AVATAR}</div>
<div class="con_text">{$LANG.CLICK_ON_AVATAR_TEXT}:</div>

<table class="" style="margin-top:15px;margin-bottom:15px;" cellpadding="5" width="100%" border="0">
    {$col="1"}
    {foreach key=avatar_id item=avatar from=$avatars}
        {if $col==1} <tr> {/if}
            {math equation="(x-1)*y + z" x=$page y=$perpage z=$avatar_id assign="avatar_id"}
            <td width="25%" valign="middle" align="center">
                    <a href="/users/{$userid}/select-avatar/{$avatar_id}" title="{$LANG.SELECT_AVATAR}">
                        <img src="{$avatars_dir}/{$avatar}" border="0" />
                    </a>
            </td>
        {if $col==4} </tr> {$col="1"} {else} {$col=$col+1} {/if}
    {/foreach}

    {if $col>1}
        <td colspan="{math equation="x - y + 1" x=$col y=4}">&nbsp;</td></tr>
    {/if}
</table>

{$pagebar}