<form action="/forum/movethread{$thread.id}.html" method="POST" id="movethread_form">
    <input type="hidden" name="gomove" value="1" />
    <table border="0" cellpadding="5" width="100%">
        <tr>
            <td width="170px">{$LANG.MOVE_THREAD_IN_FORUM}:</td>
            <td>
                <select name="forum_id" style="width:220px;">
                    {foreach key=fid item=item from=$forums}
                        {if $item.cat_title != $last_cat_title}
                        {if $last_cat_title}</optgroup>{/if}
                        <optgroup label="{$item.cat_title|escape:html}">
                        {/if}
                        <option value="{$item.id}" {if $item.id == $thread.forum_id} selected="selected" {/if}>{$item.title}</option>
                        {if $item.sub_forums}
                            {foreach key=sid item=sub_forum from=$item.sub_forums}
                                <option value="{$sub_forum.id}" {if $sub_forum.id == $thread.forum_id} selected="selected" {/if}>--- {$sub_forum.title}</option>
                            {/foreach}
                        {/if}
                        {$last_cat_title=$item.cat_title}
                    {/foreach}
                    </optgroup>
                </select>
            </td>
        </tr>
    </table>
</form>