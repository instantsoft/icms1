{$col="1"}
<table cellpadding="2" cellspacing="0" border="0" width="100%">
{foreach key=tid item=photo from=$photos}
    {if $col==1} <tr> {/if}
    <td align="center" valign="middle" width="{math equation="100/x" x=$cfg.maxcols}%" class="mod_lp_photo">
            <a href="/{if $photo.NSDiffer != ''}clubs{else}photos{/if}/photo{$photo.id}.html" title="{$photo.title|escape:'html'}">
                <img class="photo_thumb_img" src="/images/photos/small/{$photo.file}" alt="{$photo.title|escape:'html'}" />
            </a>
            {if $cfg.is_full}
            <br /><a href="/{if $photo.NSDiffer != ''}clubs{else}photos{/if}/photo{$photo.id}.html" title="{$photo.title|escape:'html'}">{$photo.title|truncate:18}</a>
            <div class="mod_lp_albumlink"><a href="/{if $photo.NSDiffer != ''}clubs/photoalbum{else}photos/{/if}{$photo.album_id}" title="{$photo.cat_title|escape:'html'}">{$photo.cat_title|truncate:18}</a>
                <div class="mod_lp_details">
                <table cellpadding="2" cellspacing="0" align="center" border="0"><tr>
                    <td><img src="/templates/{template}/images/icons/calendar.png" border="0"/></td>
                    <td>{$photo.pubdate}</td>
                    <td><img src="/templates/{template}/images/icons/comment-small.png" border="0"/></td>
                    <td><a href="/photos/photo{$photo.id}.html#c" title="{$photo.comments|spellcount:$LANG.COMMENT1:$LANG.COMMENT2:$LANG.COMMENT10}">{$photo.comments}</a></td>
                    <td><img src="/templates/{template}/images/icons/rating.png" /></td>
                    <td>{$photo.rating|rating}</td>
                </tr></table>
                </div></div>
            {/if}
    </td>
{if $col==$cfg.maxcols} </tr> {$col="1"} {else} {$col=$col+1} {/if}
{/foreach}
{if $col>1}
    <td colspan="{math equation="x - y + 1" x=$col y=$cfg.maxcols}">&nbsp;</td></tr>
{/if}
</table>
{if $cfg.showmore}
<div>
	{if $cfg.sort == 'pubdate'}
    	<a href="/photos/latest.html">{$LANG.NEW_PHOTO_IN_GALLERY}</a> &rarr;
    {elseif $cfg.sort == 'rating'}
    	<a href="/photos/top.html">{$LANG.BEST_PHOTOS}</a> &rarr;
    {elseif $cfg.is_full}
    	<a href="{if $photo.NSDiffer != ''}clubs/photoalbum{else}photos/{/if}{$photo.album_id}">{$photo.cat_title|escape:'html'}</a> &rarr;
    {/if}
</div>
{/if}
{if $cfg.is_lightbox}
<script type="text/javascript">
    $(function(){
        $( '.photo_thumb_img' ).each( function( idx ){
            var regex = /small\//;
            var orig = $( this ).attr( "src" ).replace( regex, 'medium/' );
            var ss = $( this ).parent( 'a' );
            ss.attr( "rel", "gal" ).attr( "href", orig ).addClass( 'photobox' );
        });
        $('a.photobox').colorbox({ rel: 'gal', transition: "none", slideshow: true, width: "650px", height: "650px" });
    });
</script>
{/if}