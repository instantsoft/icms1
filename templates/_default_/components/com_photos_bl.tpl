<h1 class="con_heading">{$pagetitle}</h1>

{$col="1"}
<div class="photo_gallery">
    <table cellpadding="0" cellspacing="0" border="0">
    {foreach key=tid item=photo from=$photos}
        {if $col==1} <tr> {/if}
        <td align="center" valign="middle" width="{math equation="100/x" x=$maxcols}%">
            <div class="photo_thumb" align="center">
                <a href="/photos/photo{$photo.id}.html" title="{$photo.title|escape:'html'}">
                    <img class="photo_thumb_img" src="/images/photos/small/{$photo.file}" alt="{$photo.title|escape:'html'}" border="0" />
                </a><br />
                <a href="/photos/photo{$photo.id}.html" title="{$photo.title|escape:'html'}">{$photo.title|truncate:18}</a>
                <div class="mod_lp_albumlink"><a href="/photos/{$photo.album_id}" title="{$photo.cat_title|escape:'html'}">{$photo.cat_title|truncate:18}</a>
                <div class="mod_lp_details">
                <table cellpadding="2" cellspacing="0" align="center" border="0"><tr>
                    <td><img src="/templates/{template}/images/icons/calendar.png" border="0"/></td>
                    <td>{$photo.pubdate}</td>
                    <td><img src="/templates/{template}/images/icons/comment-small.png" border="0"/></td>
                    <td><a href="/photos/photo{$photo.id}.html#c" title="{$photo.comments|spellcount:$LANG.COMMENT1:$LANG.COMMENT2:$LANG.COMMENT10}">{$photo.comments}</a></td>
                    <td><img src="/templates/{template}/images/icons/rating.png" /></td>
                    <td>{$photo.rating|rating}</td>
                </tr></table>
                </div>
        		</div>
            </div>
        </td>
    {if $col==$maxcols} </tr> {$col="1"} {else} {$col=$col+1} {/if}
    {/foreach}
    {if $col>1}
        <td colspan="{math equation="x - y + 1" x=$col y=$maxcols}">&nbsp;</td></tr>
    {/if}
    </table>
</div>