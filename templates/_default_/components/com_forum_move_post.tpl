<form action="/forum/movepost.html" method="POST" id="movethread_form">
    <input type="hidden" name="gomove" value="1" />
    <input type="hidden" name="id" value="{$thread.id}" />
    <input type="hidden" name="post_id" value="{$post_id}" />
    <table border="0" cellpadding="5" width="100%">
        <tr>
            <td width="240px">{$LANG.MOVE_POST}:</td>
            <td>
                <select name="new_thread_id" style="width:240px;">
                    {$threads}
                </select>
            </td>
        </tr>
    </table>
</form>