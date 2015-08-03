<script type="text/javascript">
    function mod_text(){
        if ($('#only_mod').prop('checked')){
			$('#text_mes').html('<strong>{$LANG.STEP_1}</strong>: {$LANG.PHOTO_DESCS}.');
			$('#text_title').html('{$LANG.PHOTO_TITLES}:');
			$('#text_desc').html('{$LANG.PHOTO_DESCS}:');
        } else {
			$('#text_mes').html('<strong>{$LANG.STEP_1}</strong>: {$LANG.PHOTO_DESC}.');
			$('#text_title').html('{$LANG.PHOTO_TITLE}:');
			$('#text_desc').html('{$LANG.PHOTO_DESC}:');
        }
    }
</script>

<h3 style="border-bottom: solid 1px gray" id="text_mes">
	<strong>{$LANG.STEP_1}</strong>: {$LANG.PHOTO_DESC}.
</h3>
<div class="usr_photos_notice">{$LANG.PHOTO_PLEASE_NOTE}</div>
<form action="" method="POST">
	<table width="100%" cellpadding="4">
		<tr>
			<td width="260" id="text_title"><strong>{$LANG.PHOTO_TITLE}:</strong></td>
			<td>
				<input name="title" type="text" id="title" class="text-input" style="width:350px;" maxlength="250" value="{$mod.title|escape:'html'}" />
			</td>
		</tr>
		<tr>
			<td valign="top" id="text_desc"><strong>{$LANG.PHOTO_DESC}:</strong></td>
			<td valign="top">
				<textarea name="description" style="width:350px;" rows="5" class="text-input">{$mod.description}</textarea>
			</td>
		</tr>
        {if !$no_tags}
		<tr>
			<td><strong>{$LANG.TAGS}:</strong></td>
			<td>
				<input name="tags" type="text" id="tags" class="text-input" style="width:350px;" value="{$mod.tags|escape:'html'}"/>
				<div><small>{$LANG.KEYWORDS}</small></div>
				<script type="text/javascript">
					{$autocomplete_js}
				</script>
			</td>
		</tr>
        {/if}
        {if $is_admin}
          <tr>
            <td valign="top"><strong>{$LANG.COMMENT_PHOTO}:</strong></td>
            <td><select name="comments" style="width:60px">
                    <option value="0">{$LANG.NO}</option>
                    <option value="1" selected="selected">{$LANG.YES}</option>
                </select>
            </td>
          </tr>
        {/if}
        {if $cfg.seo_user_access || $is_admin}
            <tr>
                <td valign="top"><strong>{$LANG.SEO_PAGETITLE}</strong><div class="hint">{$LANG.SEO_PAGETITLE_HINT}</div></td>
                <td>
                    <input name="pagetitle" style="width:350px" class="text-input" value="{$mod.pagetitle|escape:'html'}" />
                </td>
            </tr>
            <tr>
                <td valign="top"><strong>{$LANG.SEO_METAKEYS}</strong></td>
                <td>
                    <input name="meta_keys" style="width:350px" class="text-input" value="{$mod.meta_keys|escape:'html'}" />
                </td>
            </tr>
            <tr>
                <td valign="top"><strong>{$LANG.SEO_METADESCR}</strong><div class="hint">{$LANG.SEO_METADESCR_HINT}</div></td>
                <td>
                    <textarea name="meta_desc" rows="3" style="width:350px" class="text-input">{$mod.meta_desc|escape:'html'}</textarea>
                </td>
            </tr>
         {/if}
		<tr>
			<td colspan="2" valign="top">
		    <input type="submit" name="submit" id="text_subm" value="{$LANG.GO_TO_UPLOAD}" /> <input id="only_mod" name="only_mod" type="checkbox" value="1" onclick="mod_text()" />  <label for="only_mod">{$LANG.ADD_MULTY}</label></td>
		</tr>
	</table>
</form>