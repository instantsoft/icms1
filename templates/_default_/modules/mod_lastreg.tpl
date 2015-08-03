{if $cfg.view_type == 'table'}
  {foreach key=aid item=usr from=$usrs}
    <div class="mod_new_user">
        <div class="mod_new_user_avatar"><a href="{profile_url login=$usr.login}"><img border="0" class="usr_img_small" src="{$usr.avatar}" /></a></div>
        <div class="mod_new_user_link"><a href="{profile_url login=$usr.login}">{$usr.nickname}</a></div>
    </div>
  {/foreach}
{/if}

{if $cfg.view_type == 'hr_table'}
    {$col="1"}
    <table cellspacing="5" border="0" width="100%">
          {foreach key=aid item=usr from=$usrs}
            {if $col==1} <tr> {/if}
                    <td width="" class="new_user_avatar" align="center" valign="middle"><a href="{profile_url login=$usr.login}" class="new_user_link" title="{$usr.nickname|escape:'html'}"><img border="0" class="usr_img_small" src="{$usr.avatar}" /></a><div class="mod_new_user_link"><a href="{profile_url login=$usr.login}">{$usr.nickname}</a></div>
                    </td>
            {if $col==$cfg.maxcool} </tr> {$col="1"} {else} {$col=$col+1} {/if}
          {/foreach}
    </table>
{/if}

{if $cfg.view_type == 'list'}
    {$now="0"}
        {foreach key=aid item=usr from=$usrs}
            <a href="{profile_url login=$usr.login}" class="new_user_link">{$usr.nickname}</a>
            {$now=$now+1}
            {if $now==$total}{else} ,{/if}
        {/foreach}
        <p><strong>{$LANG.LASTREG_TOTAL}:</strong> {$total_all}</p>
{/if}