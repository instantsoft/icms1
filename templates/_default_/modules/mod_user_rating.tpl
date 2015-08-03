<table cellspacing="5" border="0" cellpadding="3" class="mod_user_rating">
{foreach key=tid item=usr from=$users}
    <tr>
        <td width="20" class="avatar"><a href="{profile_url login=$usr.login}"><img border="0" class="usr_img_small" src="{$usr.avatar}" /></a></td>
        <td width="">
                {$usr.user_link}

                {if $cfg.view_type == 'rating'}
                    <div class="rating">{$usr.rating|rating}</div>
                {else}
                    <div class="karma">{$usr.karma|rating}</div>
                {/if}
                {if $usr.microstatus}
                	<div class="microstatus">{$usr.microstatus}</div>
                {/if}
        </td>
    </tr>
{/foreach}
</table>