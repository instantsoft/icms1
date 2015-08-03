<h1 class="con_heading">{$LANG.CLUB_MEMBERS} - {$club.title} ({$total_members+1})</h1>
<div class="users_list">
<table width="100%" cellspacing="0" cellpadding="0" class="users_list">
{if $page==1}
  <tr>
    <td width="80" valign="top"><div class="avatar"><a href="{profile_url login=$club.login}"><img border="0" class="usr_img_small" src="{$club.admin_avatar}" /></a></div></td>
    <td valign="top">
      <div title="{$LANG.KARMA}" class="karma{if $club.karma > 0} pos{/if}{if $club.karma < 0} neg{/if}">{if $club.karma > 0}+{/if}{$club.karma}</div>
      <div class="status">
        {if $club.is_online}
            <span class="online">{$LANG.ONLINE}</span>
        {else}
            <span class="offline">{$club.logdate}</span>
        {/if}
      </div>
      <div class="nickname"><a href="{profile_url login=$club.login}" style="color:#F00" title="{$LANG.CLUB_ADMIN}">{$club.nickname}</a></div>
      {if $club.status}
      <div class="microstatus">{$club.status}</div>
      {/if} </td>
  </tr>
{/if}
  {foreach key=tid item=usr from=$members}
  <tr>
    <td width="80" valign="top"><div class="avatar"><a href="{profile_url login=$usr.login}"><img border="0" class="usr_img_small" src="{$usr.admin_avatar}" /></a></div></td>
    <td valign="top">
      <div title="{$LANG.KARMA}" class="karma{if $usr.karma > 0} pos{/if}{if $usr.karma < 0} neg{/if}">{if $usr.karma > 0}+{/if}{$usr.karma}</div>
      <div class="status">
        {if $usr.is_online}
            <span class="online">{$LANG.ONLINE}</span>
        {else}
            <span class="offline">{$usr.logdate}</span>
        {/if}
      </div>
      <div class="nickname"><a href="{profile_url login=$usr.login}" {if $usr.role=='moderator'}style="color:#090;" title="{$LANG.MODERATOR}"{/if}>{$usr.nickname}</a></div>
      {if $usr.status}
      <div class="microstatus">{$usr.status}</div>
      {/if} </td>
  </tr>
  {/foreach}		
</table>
</div>
{$pagebar}