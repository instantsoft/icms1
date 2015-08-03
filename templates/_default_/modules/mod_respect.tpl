<table width="100%" border="0" cellpadding="2" cellspacing="1">
    {foreach key=id item=user from=$users}
        <tr>
            <td width="30">
                <a href="{profile_url login=$user.login}#upr_awards" title="{$user.nickname|escape:'html'}"><img src="{$user.avatar}" border="0" /></a>
            </td>
            <td valign="top">
                <div style="margin-left:15px;">
                    <a style="font-size:16px;font-weight:bold;" href="{profile_url login=$user.login}#upr_awards" title="{$user.nickname|escape:'html'}">{$user.nickname}</a>
                    {if $cfg.show_awards}
                        <div style="margin-top:6px">
                            {foreach key=id item=award from=$user.awards}
                                <img src="/images/icons/award.gif" border="0" title="{$award.title|escape:'html'}" />
                            {/foreach}
                        </div>
                    {/if}
                </div>
            </td>
        </tr>
    {/foreach}
</table>