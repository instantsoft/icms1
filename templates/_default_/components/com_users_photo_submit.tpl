<h1 class="con_heading">{$LANG.PHOTOS_CONFIG}</h1>

<script type="text/javascript">
    function togglePhoto(id){
        if ($('#delete'+id).prop('checked')){
            $('#photo'+id+' .text-input').prop('disabled', true);
            $('#photo'+id+' select').prop('disabled', true);
        } else {
            $('#photo'+id+' .text-input').prop('disabled', false);
            $('#photo'+id+' select').prop('disabled', false);
        }
    }
</script>

<form action="" method="post">

    <div id="usr_photos_upload_album">
        <table border="0" cellpadding="0" cellspacing="0">
            {if $albums}
            <tr>
                <td width="23" height="30"><input type="radio" name="new_album" id="new_album_0" value="0" checked="checked" onclick="$('#description').hide();" /></td>
                <td><label for="new_album_0">{$LANG.SAVE_TO_ALBUM}:</label></td>
                <td style="padding-left: 10px" colspan="3">
                    <select name="album_id" class="select-input">
                        {foreach key=ak item=album from=$albums}
                            <option value="{$album.id}" {if $album_id == $album.id} selected="selected"{/if}>{$album.title}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            {/if}
            <tr>
                <td width="23" height="30"><input type="radio" name="new_album" id="new_album_1" value="1" {if !$albums}checked="checked"{/if} onclick="$('#description').show();" /></td>
                <td><label for="new_album_1">{$LANG.CREATE_NEW_ALBUM}:</label></td>
                <td style="padding:0px 10px">
                    <input type="text" class="text-input" name="album_title" onclick="$('#description').show();$('#new_album_1').prop('checked', true);" />
                </td>
                <td width="80">{$LANG.SHOW}:</td>
                <td>
                    <select name="album_allow_who" id="album_allow_who">
                        <option value="all">{$LANG.TO_ALL}</option>
                        <option value="registered">{$LANG.TO_REGISTERED}</option>
                        <option value="friends">{$LANG.TO_MY_FRIEND}</option>
                    </select>
                </td>
            </tr>
            <tr id="description" {if $albums}style="display:none;"{/if} >
                <td width="23" height="30"></td>
                <td><label for="description">{$LANG.ALBUM_DESCRIPTION}:</label></td>
                <td style="padding-left: 10px" colspan="3">
					<textarea name="description" class="text-input" style="width:488px; height:45px;"></textarea>
                </td>
            </tr>
        </table>
    </div>

    <div class="usr_photos_submit_list">
        {foreach key=pk item=photo from=$photos}
        <div id="photo{$photo.id}" class="usr_photos_submit_one">
            <div class="float_bar">
                <table>
                    <tr>
                        <td width="20"><input type="checkbox" name="delete[]" value="{$photo.id}" id="delete{$photo.id}" onclick="togglePhoto({$photo.id})"/></td>
                        <td><label for="delete{$photo.id}">{$LANG.DELETE}</label></td>
                    </tr>
                </table>
            </div>
            <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="120" height="110">
                        <div class="ph_thumb"><img src="/images/users/photos/small/{$photo.imageurl}" /></div>
                    </td>
                    <td>

                        <table border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="100" height="30">{$LANG.TITLE}:</td>
                                <td><input type="text" name="title[{$photo.id}]" value="{$photo.title|escape:'html'}" class="text-input" /></td>
                            </tr>
                            <tr>
                                <td height="30">{$LANG.DESCRIPTION}:</td>
                                <td><input type="text" name="desc[{$photo.id}]" value="{$photo.description|escape:'html'}" class="text-input" /></td>
                            </tr>
                            <tr>
                                <td height="30">{$LANG.SHOW}:</td>
                                <td>
                                    <select name="allow[{$photo.id}]">
                                        <option value="all" {if $photo.allow_who=='all'}selected="selected"{/if}>{$LANG.TO_ALL}</option>
                                        <option value="registered" {if $photo.allow_who=='registered'}selected="selected"{/if}>{$LANG.TO_REGISTERED}</option>
                                        <option value="friends" {if $photo.allow_who=='friends'}selected="selected"{/if}>{$LANG.TO_MY_FRIEND}</option>
                                    </select>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>
        </div>
        {/foreach}
    </div>
    <div id="usr_photos_submit_btn">
    	<input type="hidden" name="is_edit" value="{$is_edit}" />
        <input type="submit" name="submit" value="{$LANG.SAVE}" /> {$LANG.AND_GO_TO_ALBUM}
    </div>
</form>
