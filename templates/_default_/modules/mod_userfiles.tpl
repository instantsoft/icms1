{if $latest}
    <div style="margin-bottom:8px"><strong>{$LANG.USERFILES_NEW_FILES}</strong></div>

    <table width="100%" cellpadding="0" cellspacing="2" border="0" style="margin-bottom:10px">
        {foreach key=id item=file from=$latest}
            <tr>
                <td><a href="/users/files/download{$file.id}.html">{$file.filename}</a> - {$file.size} {$LANG.SIZE_MB}</td>
                <td width="35">
                    <a href="{profile_url login=$file.user_login}" title="{$file.user_nickname|escape:'html'}">
                        <img src="/images/icons/users.gif" border="0" />
                    </a>
                    <a href="/users/{$file.user_id}/files.html" title="{$LANG.USERFILES_ALL_USER_FILES}">
                        <img src="/images/markers/folder.png" border="0" />
                    </a>
                </td>
            </tr>
        {/foreach}
    </table>
{/if}

{if $popular}
    <div style="margin-bottom:8px"><strong>{$LANG.USERFILES_POPULAR_FILES}</strong></div>

    <table width="100%" cellpadding="0" cellspacing="2" border="0" style="margin-bottom:10px">
        {foreach key=id item=file from=$popular}
            <tr>
                <td><a href="/users/files/download{$file.id}.html">{$file.filename}</a> - {$file.size} {$LANG.SIZE_MB}</td>
                <td width="35">
                    <a href="{profile_url login=$file.user_login}" title="{$file.user_nickname|escape:'html'}">
                        <img src="/images/icons/users.gif" border="0" />
                    </a>
                    <a href="/users/{$file.user_id}/files.html" title="{$LANG.USERFILES_ALL_USER_FILES}">
                        <img src="/images/markers/folder.png" border="0" />
                    </a>
                </td>
            </tr>
        {/foreach}
    </table>
{/if}

{if $cfg.sw_stats}
    <div><strong>{$LANG.USERFILES_TOTAL_FILES}:</strong> {$stats.total_files}</div>
    <div><strong>{$LANG.USERFILES_TOTAL_SIZE}:</strong> {$stats.total_size} {$LANG.SIZE_MB}</div>
{/if}