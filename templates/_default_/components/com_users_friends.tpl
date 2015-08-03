<div class="con_heading"><a href="{profile_url login=$usr.login}">{$usr.nickname}</a> &rarr; {$LANG.FRIENDS} ({$total})</div>
<div class="users_list">
  <table width="100%" cellspacing="0" cellpadding="0" class="users_list">
    {if $friends}
    {foreach key=tid item=friend from=$friends}
    <tr id="friend_id_{$friend.id}">
      <td width="80" valign="top"><div class="avatar"><a href="{profile_url login=$friend.login}"><img border="0" class="usr_img_small" src="{$friend.avatar}" /></a></div></td>
      <td valign="top"><div class="status">{$friend.flogdate}<br />
          <a href="javascript:void(0)" class="ajaxlink" onclick="users.sendMess('{$friend.id}', 0, this);return false;" title="{$LANG.WRITE_MESS}: {$friend.nickname|escape:'html'}">{$LANG.WRITE_MESS}</a> {if $myprofile}<br />
          <a href="javascript:void(0)" title="{$friend.nickname|escape:'html'}" onclick="users.delFriend('{$friend.id}', this);return false;" class="ajaxlink">{$LANG.STOP_FRIENDLY}</a>{/if} </div>
        <div class="nickname"> <a class="friend_link" href="{profile_url login=$friend.login}">{$friend.nickname}</a><br />
          {if $friend.status} <span class="microstatus">{$friend.status}</span> {/if} </div></td>
    </tr>
    {/foreach}
    {/if}
  </table>
</div>
{$pagebar}