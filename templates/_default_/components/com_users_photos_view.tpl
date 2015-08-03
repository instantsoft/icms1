{if $is_allow}

    {if $myprofile || $is_admin}
        <div class="float_bar" style="background: none">
            <a class="usr_photo_link_edit" href="/users/{$usr.id}/editphoto{$photo.id}.html">{$LANG.EDIT}</a>
            <a class="usr_photo_link_delete"  href="/users/{$usr.id}/delphoto{$photo.id}.html">{$LANG.DELETE}</a>
        </div>
    {/if}

    <div class="con_heading">{$photo.title}</div>

    <div class="bar">
        {$photo.genderlink} &mdash; {$photo.pubdate} &mdash; <strong>{$LANG.HITS}:</strong> {$photo.hits}
    </div>

    <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>

            <td width="50%">
                {if $previd}
                    <a class="usr_photo_prev_link" href="/users/{$usr.id}/photo{$previd.id}.html" title="{$previd.title|escape:'html'}"></a>
                {else}
                    &nbsp;
                {/if}
            </td>

            <td>
                <div class="usr_photo_view">
                    {if $nextid}<a href="/users/{$usr.id}/photo{$nextid.id}.html">{/if}
                        <img border="0" src="/images/users/photos/medium/{$photo.imageurl}" alt="{$photo.title|escape:'html'}" />
                    {if $nextid}</a>{/if}
                </div>
            </td>

            <td width="50%">
                {if $nextid}
                    <a class="usr_photo_next_link" href="/users/{$usr.id}/photo{$nextid.id}.html" title="{$nextid.title|escape:'html'}"></a>
                {else}
                    &nbsp;
                {/if}
            </td>

        </tr>
    </table>

    {if $photo.description}
        <div class="photo_desc">{$photo.description}</div>
    {/if}

    {$tagbar}

{else}
    <div class="con_heading">{$photo.title}</div>

    <div class="bar">
        {$photo.genderlink} &mdash; {$photo.pubdate} &mdash; <strong>{$LANG.HITS}:</strong> {$photo.hits}
    </div>

    <table cellpadding="0" cellspacing="0" border="0" width="100%" height="300">
        <tr>

            <td width="30%">
                {if $previd}
                    <a class="usr_photo_prev_link" href="/users/{$usr.id}/photo{$previd.id}.html" title="{$previd.title|escape:'html'}"></a>
                {else}
                    &nbsp;
                {/if}
            </td>

            <td width="40%">
                <div class="usr_photo_view">
                    {if $nextid}<a href="/users/{$usr.id}/photo{$nextid.id}.html">{/if}
                        <span>{$LANG.PHOTO_NOT_FOUND_TEXT}</span>
                    {if $nextid}</a>{/if}
                </div>
            </td>

            <td width="30%">
                {if $nextid}
                    <a class="usr_photo_next_link" href="/users/{$usr.id}/photo{$nextid.id}.html" title="{$nextid.title|escape:'html'}"></a>
                {else}
                    &nbsp;
                {/if}
            </td>

        </tr>
    </table>

{/if}