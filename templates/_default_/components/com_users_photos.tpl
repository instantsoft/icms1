{if ($my_profile || $is_admin) && $album_type == 'private'}
    <div class="float_bar">
        {if $my_profile}
            <a href="/users/addphoto.html" class="usr_photo_add">{$LANG.ADD_PHOTO}</a>
        {/if}
        <a href="javascript:void(0)" onclick="$('#usr_photos_upload_album').show();" class="usr_edit_album"><span class="ajaxlink">{$LANG.EDIT_ALBUM}</span></a>
        <a href="/users/{$user_id}/delalbum{$album.id}.html" onclick="if(!confirm('{$LANG.DELETE_ALBUM_CONFIRM}')){ return false; }" class="usr_del_album"><span class="ajaxlink">{$LANG.DELETE_ALBUM}</span></a>
    </div>
{/if}

<div class="con_heading">
    <a href="{profile_url login=$usr.login}">{$usr.nickname}</a> &rarr; {$page_title}
</div>
{if ($my_profile || $is_admin) && $album_type == 'private'}
    <div id="usr_photos_upload_album" style="display:none;">
	<form action="/users/{$usr.id}/editalbum{$album.id}.html" method="post">
        <table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td><label for="album_title">{$LANG.ALBUM_TITLE}:</label></td>
            <td><input type="text" class="text-input" name="album_title" value="{$album.title|escape:'html'}" /></td>
            <td>{$LANG.SHOW}:
                    <select name="album_allow_who" id="album_allow_who">
                       <option value="all" {if $album.allow_who=='all'}selected="selected"{/if}>{$LANG.EVERYBODY}</option>
                       <option value="registered" {if $album.allow_who=='registered'}selected="selected"{/if}>{$LANG.REGISTERED}</option>
                       <option value="friends" {if $album.allow_who=='friends'}selected="selected"{/if}>{$LANG.MY_FRIENDS}</option>
                    </select>
            </td>
          </tr>
          <tr>
            <td><label for="description">{$LANG.ALBUM_DESCRIPTION}:</label></td>
            <td colspan="2"><textarea name="description" style="width:465px; height:45px;">{$album.description}</textarea></td>
          </tr>
        </table>
        <div class="usr_photo_sel_bar bar">
           <input type="submit" name="save_album" value="{$LANG.SAVE}"/>
           <input name="Button" type="button" value="{$LANG.CANCEL}" onclick="$('#usr_photos_upload_album').hide();"/>
        </div>
      </form>
    </div>
{/if}
{if $album_type == 'public'}
    <div class="usr_photos_notice">{$LANG.IS_PUBLIC_ALBUM} <a href="{if !$album.NSDiffer}/photos/{$album.id}{else}/clubs/photoalbum{$album.id}{/if}">{$LANG.ALL_PUBLIC_PHOTOS}</a></div>
{/if}
{if $album_type == 'private' && $album.description}
    <div id="usr_photos_upload_album">{$album.description|nl2br}</div>
{/if}
{if $photos}

        {if ($is_admin || $my_profile) && $album_type == 'private'}
        <form action="/users/{$user_id}/photos/editlist" method="post">
            <input type="hidden" name="album_id" value="{$album.id}" />
            <script type="text/javascript">
                function toggleButtons(){
                    var is_sel = $('.photo_id:checked').length;
                    if (is_sel > 0) {
                        $('#edit_btn, #delete_btn').prop('disabled', false);
                    } else {
                        $('#edit_btn, #delete_btn').prop('disabled', true);
                    }
                }
            </script>
        {/if}

		<table width="" cellpadding="0" cellspacing="0" border="0">

            {$maxcols="7"}
            {$col="1"}

			{foreach key=id item=photo from=$photos}
				{if $col==1} <tr> {/if}
				<td valign="top" width="">
					<div class="usr_photo_thumb">
                        <a class="usr_photo_link" href="{$photo.url}" title="{$photo.title|escape:'html'}">
                            <img border="0" src="{$photo.file}" alt="{$photo.title|escape:'html'}"/>
                        </a>
                        <div>
                            <span class="usr_photo_date">{$photo.fpubdate}</span>
                            <span class="usr_photo_hits"><strong>{$LANG.HITS}:</strong> {$photo.hits}</span>
                        </div>
                        {if ($is_admin || $my_profile) && $album_type == 'private'}
                            <input type="checkbox" name="photos[]" class="photo_id" value="{$photo.id}" onclick="toggleButtons()" />
                        {/if}
                    </div>
				</td>
				{if $col==$maxcols} </tr> {$col="1"} {else} {$col=$col+1} {/if}
			{/foreach}

            {if $col>1}
				<td colspan="{math equation="x - y + 1" x=$col y=$maxcols}">&nbsp;</td></tr>
			{/if}

		</table>

        {if ($is_admin || $my_profile) && $album_type == 'private'}
            <div class="usr_photo_sel_bar bar">
                {$LANG.SELECTED_ITEMS}:
                <input type="submit" name="edit" id="edit_btn" value="{$LANG.EDIT}" disabled="disabled" />
                <input type="submit" name="delete" id="delete_btn" value="{$LANG.DELETE}" disabled="disabled" />
            </div>
            </form>
        {/if}

		{$pagebar}

{else}
    <p>{$LANG.NOT_PHOTOS}</p>
{/if}