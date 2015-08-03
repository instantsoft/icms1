<div class="fa_attach" id="fa_attach_{$post.id}">
<div class="fa_attach_title">{$LANG.ATTACHED_FILE}:</div>
{$file_count="0"}
{foreach key=aid item=attached_file from=$post.attached_files}
    <div class="fa_filebox" id="filebox{$attached_file.id}">
        <table class="fa_file"><tr>
                {if $attached_file.is_img}
                    <td><img src="/upload/forum/post{$post.id}/{$attached_file.filename|escape:html}" border="0" width="130" /></td>
                {else}
                    <td width="16">{$attached_file.icon}</td>
                {/if}
                <td>
                    <a class="fa_file_link" href="/forum/download{$attached_file.id}.html">{$attached_file.filename}</a> | <span class="fa_file_desc">{$attached_file.filesize_kb} {$LANG.KBITE} | {$LANG.DOWNLOADED}: {$attached_file.hits|spellcount:$LANG.COUNT1:$LANG.COUNT2:$LANG.COUNT1}</span>
                    {if $is_admin || $is_moder || $post.is_author_can_edit}
                        <a href="javascript:" title="{$LANG.RELOAD_FILE}" onclick="forum.reloadFile('{$attached_file.id}');"><img src="/images/icons/reload.gif" border="0" /></a>
                        <a href="javascript:" title="{$LANG.DELETE_FILE}" onclick="forum.deleteFile('{$attached_file.id}', '{csrf_token}', {$post.id});"><img src="/images/icons/delete.gif" border="0" /></a>
                    {/if}
                </td>
        </tr></table>
    </div>
    {$file_count=$file_count+1}
{/foreach}
<input type="hidden" name="file_count" id="file_count" value="{$file_count}" />
</div>