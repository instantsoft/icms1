{if $is_author || $is_admin}
<div class="float_bar">
<a class="ajaxlink" href="javascript:void(0)" onclick="photos.editPhoto({$photo.id});return false;">{$LANG.EDIT}</a>{if $is_admin}  | <a class="ajaxlink" href="javascript:void(0)" onclick="photos.movePhoto({$photo.id});return false;">{$LANG.MOVE}</a>{if !$photo.published}<span id="pub_photo_link">  | <a class="ajaxlink" href="javascript:void(0)" onclick="photos.publishPhoto({$photo.id});return false;">{$LANG.PUBLISH}</a></span>{/if}{/if}   | <a class="ajaxlink" href="javascript:void(0)" onclick="photos.deletePhoto({$photo.id}, '{csrf_token}');return false;">{$LANG.DELETE}</a>
</div>
{/if}

<h1 class="con_heading">{$photo.title}</h1>

{if $photo.description}
    <div class="photo_desc">
        {$photo.description|nl2br}
    </div>
{/if}

<table cellpadding="0" cellspacing="0" border="0" class="photo_layout">
    <tr>
        <td valign="top" style="padding-right:15px; max-width: 630px;">
            <img src="/images/photos/medium/{$photo.file}" border="0" alt="{$photo.title|escape:'html'}" />

            {if $photo.album_nav}
                <div align="center" style="margin:5px 0 0 0">
                    {if $previd}
                        &larr; <a href="/photos/photo{$previd.id}.html">{$LANG.PREVIOUS}</a>
                    {/if}
                    {if $previd && $nextid} | {/if}
                    {if $nextid}
                        <a href="/photos/photo{$nextid.id}.html">{$LANG.NEXT}</a> &rarr;
                    {/if}
                </div>
			{/if}
        </td>
        <td width="7" class="photo_larr">&nbsp;

        </td>
        <td width="250" valign="top">
            <div class="photo_details">

                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td>
                            <p><strong>{$LANG.RATING}: </strong><span id="karmapoints">{$photo.rating|rating}</span></p>
                            <p><strong>{$LANG.HITS}: </strong> {$photo.hits}</p>
                        </td>
                        {if $photo.karma_buttons}
                            <td width="25">{$photo.karma_buttons}</td>
                        {/if}
                    </tr>
                </table>

                <div class="photo_date_details">
                    <p>{if !$photo.published}<span id="pub_photo_wait" style="color:#F00;">{$LANG.WAIT_MODERING}</span><span id="pub_photo_date" style="display:none;">{$photo.pubdate}</span>{else}{$photo.pubdate}{/if}</p>
                    <p>{$photo.genderlink}</p>
                </div>

                {if $cfg.link}
                    <p class="photo_date_details"><a class="lightbox-enabled" rel="lightbox-galery" href="/images/photos/{$photo.file}" title="{$photo.title|escape:'html'}">{$LANG.OPEN_ORIGINAL}</a></p>
                {/if}

            </div>

            {if $photo.album_nav}
                <div class="photo_sub_details">
                    {$LANG.BACK_TO} <a href="/photos/{$photo.album_id}">{$LANG.TO_ALBUM}</a><br/>
                    {$LANG.BACK_TO}  <a href="/photos">{$LANG.TO_LIST_ALBUMS}</a>
                </div>
            {/if}

            {if $photo.a_bbcode}
            <div class="photo_details" style="margin-top:5px;font-size: 12px">
                {$LANG.CODE_INPUT_TO_FORUMS}:<br/>
                <input onclick="$(this).select();" type="text" class="photo_bbinput" value="{$bbcode}"/>
            </div>
            {/if}

            <div class="photo_sub_details" style="padding:0px 20px">
                {$tagbar}
            </div>

        </td>
    </tr>
</table>