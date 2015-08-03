<input type="hidden" name="target_id" value="{$target_id}" />
<input type="hidden" name="component" value="{$component}" />

{if $total}

    {foreach key=id item=record from=$records}
        <div class="usr_wall_entry" id="wall_entry_{$record.id}">
            <div class="usr_wall_title"><a href="{profile_url login=$record.author_login}">{$record.author}</a>, {$record.fpubdate}{if $record.is_today} {$LANG.BACK}{/if}:</div>
            {if $my_profile || $record.author_id==$user_id || $is_admin}
                <div class="usr_wall_delete"><a class="ajaxlink" href="javascript:void(0)" onclick="deleteWallRecord('{$component}', '{$target_id}', '{$record.id}', '{csrf_token}');return false;">{$LANG.DELETE}</a></div>
            {/if}

            <table style="width:100%; margin-bottom:2px;" cellspacing="0" cellpadding="0">
            <tr>
                <td width="70" valign="top" align="center" style="text-align:center">
                    <div class="usr_wall_avatar">
                        <a href="{profile_url login=$record.author_login}"><img border="0" class="usr_img_small" src="{$record.avatar}" /></a>
                    </div>
                </td>
                <td width="" valign="top" class="usr_wall_text">{$record.content}</td>
            </tr>
            </table>
        </div>
    {/foreach}

	{$pagebar}

{else}
    <p>{$LANG.NOT_POSTS_ON_WALL_TEXT}</p>
{/if}