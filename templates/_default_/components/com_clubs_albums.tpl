{strip}
{if $show_title}
    {if $is_admin || $is_moder || $is_karma_enabled}
        <div class="float_bar">
            <a class="service ajaxlink" href="javascript:void(0)" onclick="clubs.addAlbum({$club.id});">{$LANG.ADD_PHOTOALBUM}</a>
        </div>
    {/if}
    <h1 class="con_heading">{$pagetitle}</h1>
{/if}

{if $club.photo_albums}
    <div class="usr_albums_block" style="margin-top:25px">
        <ul class="usr_albums_list">
            {foreach key=key item=album from=$club.photo_albums}
                <li id="{$album.id}">
                    <div class="usr_album_thumb">
                        <a href="/clubs/photoalbum{$album.id}" title="{$album.title|escape:'html'}">
                            <img src="/images/photos/small/{$album.file}" width="64" height="64" border="0" alt="{$album.title|escape:'html'}" />
                        </a>
                    </div>
                    <div class="usr_album">
                        <div class="link">
                            <a href="/clubs/photoalbum{$album.id}" title="{$album.title|escape:'html'}">{$album.title|truncate:14}</a>
                        </div>
                        <div class="count">{if $album.content_count} {$album.content_count|spellcount:$LANG.PHOTO:$LANG.PHOTO2:$LANG.PHOTO10} {else} {$LANG.NOT_PHOTO}{/if}</div>
                        <div class="date">{$album.pubdate}</div>
                    </div>
                </li>
            {/foreach}
         </ul>
    </div>
        
{else}
    <div class="usr_albums_block" style="margin-top:30px">
        <ul class="usr_albums_list">
    		<li class="no_albums">{$LANG.NO_PHOTOALBUM}</li>
        </ul>
    </div>
{/if}
{/strip}