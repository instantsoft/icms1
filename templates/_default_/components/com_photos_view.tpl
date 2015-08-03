{if $album.id == $root_album_id && $cfg.showlat}
<div class="float_bar">
    <table cellspacing="0" cellpadding="0">
      <tr>
        <td width="23"><img src="/templates/{template}/images/icons/calendar.png" /></td>
        <td style="padding-right: 10px"><a href="/photos/latest.html">{$LANG.LAST_UPLOADED}</a></td>
        <td width="23"><img src="/templates/{template}/images/icons/rating.png" /></td>
        <td><a href="/photos/top.html">{$LANG.BEST_PHOTOS}</a></td>
      </tr>
    </table>
</div>
{elseif $album.id != $root_album_id && $album.orderform}

    <div class="float_bar">
        <form action="" method="POST" style="float: left">
            {$LANG.SORTING_PHOTOS}:
            <select name="orderby" id="orderby">
                <option value="title" {if $orderby=='title'} selected {/if}>{$LANG.ORDERBY_TITLE}</option>
                <option value="pubdate" {if $orderby=='pubdate'} selected {/if}>{$LANG.ORDERBY_DATE}</option>
                <option value="rating" {if $orderby=='rating'} selected {/if}>{$LANG.ORDERBY_RATING}</option>
                <option value="hits" {if $orderby=='hits'} selected {/if}>{$LANG.ORDERBY_HITS}</option>
            </select>
            <select name="orderto" id="orderto">
                <option value="desc" {if $orderto=='desc'} selected {/if}>{$LANG.ORDERBY_DESC}</option>
                <option value="asc" {if $orderto=='asc'} selected {/if}>{$LANG.ORDERBY_ASC}</option>
            </select>
            <input type="submit" value=">>" />
        </form>
        {if $can_add_photo}
            <a class="photo_add_link" href="/photos/{$album.id}/addphoto.html">{$LANG.ADD_PHOTO_TO_ALBUM}</a>
        {/if}
    </div>

{elseif $can_add_photo && $album.parent_id > 0}
	<div class="float_bar"><a class="photo_add_link" href="/photos/{$album.id}/addphoto.html">{$LANG.ADD_PHOTO_TO_ALBUM}</a></div>
{/if}

<h1 class="con_heading">{$album.title} {if $total}({$total}){/if}</h1>

<div class="clear"></div>
{if $album.description}
	<p class="usr_photos_notice">{$album.description|nl2br}</p>
{/if}
{if $subcats}
    {$col="1"}
        {foreach key=tid item=cat from=$subcats}
        {if $col==1}<div class="photo_row">{/if}
            <div class="photo_album_tumb">
                <div class="photo_container">
                    <a href="/photos/{$cat.id}"><img class="photo_album_img" src="/images/photos/small/{$cat.file}" alt="{$cat.title|escape:'html'}" width="{$cat.thumb1}px" /></a>
                </div>
                <div class="photo_txt">
                    <ul>
                        <li class="photo_album_title"><a href="/photos/{$cat.id}">{$cat.title}</a> ({$cat.content_count})</li>
                        {if $cat.description}<li>{$cat.description}</li>{/if}
                    </ul>
                </div>
            </div>

         {if $col==$cfg.maxcols}<div class="blog_desc"></div></div> {$col="1"} {else} {$col=$col+1} {/if}

        {/foreach}
        {if $col>1}
            <div class="blog_desc"></div></div>
        {/if}
{/if}

{if $photos}
{$col="1"}
<div class="photo_gallery">
    <table cellpadding="0" cellspacing="0" width="100%">
    {foreach key=tid item=photo from=$photos}
        {if $col==1} <tr> {/if}
        <td align="center" valign="middle" width="{(100/$album.maxcols)|round}%">
            <div class="{$album.cssprefix}photo_thumb" align="center">
                {if $album.showtype == 'lightbox'}
                <a class="lightbox-enabled" rel="lightbox-galery" href="/images/photos/medium/{$photo.file}" title="{$photo.title|escape:'html'}">
                {else}
                <a href="/photos/photo{$photo.id}.html" title="{$photo.title|escape:'html'}">
                {/if}
                    <img src="/images/photos/small/{$photo.file}" alt="{$photo.title|escape:'html'}" />
                </a><br />
                <a href="/photos/photo{$photo.id}.html" title="{$photo.title|escape:'html'}">{$photo.title|truncate:18}</a>
                {if $album.showdate}
                    <div class="mod_lp_albumlink"><div class="mod_lp_details">
                    <table cellpadding="2" cellspacing="0" align="center"><tr>
                        <td><img src="/templates/{template}/images/icons/calendar.png" /></td>
                        <td>{$photo.pubdate}</td>
                        <td><img src="/templates/{template}/images/icons/comment-small.png" /></td>
                        <td><a href="/photos/photo{$photo.id}.html#c" title="{$photo.comments|spellcount:$LANG.COMMENT1:$LANG.COMMENT2:$LANG.COMMENT10}">{$photo.comments}</a></td>
                    </tr></table>
                    </div></div>
                {/if}
                {if !$photo.published}
                    <div style="color:#F00; font-size:12px">{$LANG.WAIT_MODERING}</div>
                {/if}
        	</div>
        </td>
    {if $col==$album.maxcols} </tr> {$col="1"} {else} {$col=$col+1} {/if}
    {/foreach}
    {if $col>1}
        <td colspan="{math equation="x - y + 1" x=$col y=$album.maxcols}">&nbsp;</td></tr>
    {/if}
    </table>
</div>
{$pagebar}
{else}
	{if $album.parent_id > 0}<p>{$LANG.NOT_PHOTOS_IN_ALBUM}</p>{/if}
{/if}