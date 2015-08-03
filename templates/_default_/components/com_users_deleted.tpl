<div id="usertitle">
	<div class="con_heading" id="nickname" style="float:left;">
		{$usr.nickname}
	</div>
</div>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="200" valign="top">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td align="center" valign="middle" style="padding:10px; border:solid 1px gray; background-color:#FFFFFF">
						<img border="0" class="usr_img" src="{$usr.avatar}" />
					</td>
				</tr>
			</table>

	    </td>
    	<td valign="top" style="padding-left:10px">
				<div class="usr_deleted">{$LANG.USER_PROFILE_DELETED}</div>
				{if $is_admin}
                    {if !$others_active}
                        <div class="usr_restore">{$LANG.YOU_CAN} <a href="/users/restoreprofile{$usr.id}.html">{$LANG.RESTORE_PROFILE}</a></div>
                    {else}
                        <div class="usr_restore">{$LANG.CANT_RESTORE_PROFILE_TEXT} ({$usr.login}).</div>
                    {/if}
				{/if}
		</td>
  </tr>
</table>
