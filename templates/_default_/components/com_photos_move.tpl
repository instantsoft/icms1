<div style="margin:10px">
    <form action="{$form_action}" method="POST" id="move_photo_form">
    	<input type="hidden" value="1" name="move_photo" />
        <table border="0" cellspacing="5" width="100%">
            <tr>
                <td width="175"><strong>{$LANG.MOVE_INTO_ALBUM}:</strong></td>
                <td><select name="album_id">{$html}</select></td>
            </tr>
        </table>
    </form>
</div>
<script type="text/javascript" src="/includes/jquery/jquery.form.js"></script>