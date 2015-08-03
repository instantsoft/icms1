<form action="{$form_action}" method="post" id="edit_photo_form">
<input type="hidden" value="1" name="edit_photo" />
<table width="100%" border="0" cellspacing="0" cellpadding="3" style="margin:10px">
  <tr>
    <td width="240" valign="top"><strong>{$LANG.PHOTO_TITLE}:</strong></td>
    <td><input name="title" type="text" class="text-input" size="40" maxlength="250" style="width:350px" value="{$photo.title|escape:'html'}"/></td>
  </tr>
  <tr>
    <td valign="top"><strong>{$LANG.PHOTO_DESC}:</strong></td>
    <td><textarea name="description" cols="39" rows="5" style="width:350px" class="text-input">{$photo.description|escape:'html'}</textarea></td>
  </tr>
{if !$no_tags}
  <tr>
    <td valign="top"><strong>{$LANG.TAGS}:</strong></td>
    <td><input name="tags" type="text" class="text-input" style="width:350px" size="40" value="{$photo.tags|escape:'html'}"/><br /><span><small>{$LANG.KEYWORDS}</small></span></td>
  </tr>
{/if}
  <tr>
    <td valign="top"><strong>{$LANG.REPLACE_FILE}:</strong></td>
    <td><input name="Filedata" type="file" class="text-input" style="width:350px" /><br><br><img alt="" src="/images/photos/small/{$photo.file}" border="0" /></td>
  </tr>
{if $is_admin}
  <tr>
    <td valign="top"><strong>{$LANG.COMMENT_PHOTO}:</strong></td>
    <td><select name="comments" style="width:60px">
            <option value="0" {if !$photo.comments}selected="selected"{/if}>{$LANG.NO}</option>
            <option value="1" {if $photo.comments}selected="selected"{/if} >{$LANG.YES}</option>
        </select>
    </td>
  </tr>
{/if}
{if $cfg.seo_user_access || $is_admin}
    <tr>
        <td valign="top"><strong>{$LANG.SEO_PAGETITLE}</strong><div class="hint">{$LANG.SEO_PAGETITLE_HINT}</div></td>
        <td>
            <input name="pagetitle" style="width:350px" class="text-input" value="{$photo.pagetitle|escape:'html'}" />
        </td>
    </tr>
    <tr>
        <td valign="top"><strong>{$LANG.SEO_METAKEYS}</strong></td>
        <td>
            <input name="meta_keys" style="width:350px" class="text-input" value="{$photo.meta_keys|escape:'html'}" />
        </td>
    </tr>
    <tr>
        <td valign="top"><strong>{$LANG.SEO_METADESCR}</strong><div class="hint">{$LANG.SEO_METADESCR_HINT}</div></td>
        <td>
            <textarea name="meta_desc" rows="3" style="width:350px" class="text-input">{$photo.meta_desc|escape:'html'}</textarea>
        </td>
    </tr>
 {/if}
</table>
</form>
<script type="text/javascript" src="/includes/jquery/jquery.form.js"></script>