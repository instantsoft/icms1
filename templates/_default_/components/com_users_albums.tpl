{if $my_profile}
    <div class="float_bar">
        <a href="/users/addphoto.html" class="usr_photo_add">{$LANG.ADD_PHOTO}</a>
    </div>
{/if}

<div class="con_heading">
    <a href="{profile_url login=$user.login}">{$user.nickname}</a> &rarr; {$LANG.PHOTOALBUMS}
</div>

{if $albums}

    <div class="usr_albums_block" style="margin-top:30px">
        <ul class="usr_albums_list">
            {foreach key=key item=album from=$albums}
                <li>
                    <div class="usr_album_thumb">
                        <a href="/users/{$user.login}/photos/{$album.type}{$album.id}.html" title="{$album.title|escape:'html'}">
                            <img src="{$album.imageurl}" width="64" height="64" border="0" alt="{$album.title|escape:'html'}" />
                        </a>
                    </div>
                    <div class="usr_album">
                        <div class="link">
                            <a href="/users/{$user.login}/photos/{$album.type}{$album.id}.html">{$album.title}</a>
                        </div>
                        <div class="count">{$album.photos_count|spellcount:$LANG.PHOTO:$LANG.PHOTO2:$LANG.PHOTO10}</div>
                        <div class="date">{$album.pubdate}</div>
                    </div>
                </li>
            {/foreach}
         </ul>
         <div class="blog_desc"></div>
    </div>

{else}
    <p>{$LANG.NOT_PHOTOS}</p>
{/if}