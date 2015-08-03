<div class="con_heading">{$LANG.KARMA_HISTORY} - {$usr.nickname}</div>
{if $karma}
<table width="">
		{foreach key=id item=karm from=$karma}
			<tr>
				<td style="border-bottom:solid 1px silver" width="150" valign="middle">{$karm.fsenddate}</td>
				<td style="border-bottom:solid 1px silver" width="200" valign="middle"><a href="{profile_url login=$karm.login}">{$karm.nickname}</a></td>
				<td style="border-bottom:solid 1px silver" width="100" valign="middle" align="center">
                	{if $karm.kpoints>0}
                		<span style="font-size:24px;color:green">+{$karm.kpoints}</span>
                    {else}
                    	<span style="font-size:24px;color:red">{$karm.kpoints}</span>
                    {/if}
                </td>
			</tr>
		{/foreach}
</table>
{else}
<p>{$LANG.KARMA_NOT_MODIFY}</p>
<p>{$LANG.KARMA_NOT_MODIFY_TEXT}</p>
<p>{$LANG.KARMA_DESCRIPTION}</p>
{/if}