<div class="photo_details">
<div id="found_search"><strong>{$LANG.SEARCH_BY_TAG}:</strong> &laquo;{$query}&raquo;, {$LANG.SEARCH_FOR} <a href="javascript:" onclick="searchOtherTag()" class="ajaxlink">{$LANG.ANOTHER_TAG}</a></div>
<div id="other_tag" style="display:none">
    <form id="sform"action="/search" method="post" enctype="multipart/form-data">
        <strong>{$LANG.SEARCH_BY_TAG}: </strong>
        <input type="hidden" name="do" value="tag" />
        <input type="text" name="query" id="query" size="40" value="" class="text-input" />
		<script type="text/javascript">
            {$autocomplete_js}
        </script>
        <input type="submit" value="{$LANG.FIND}"/> <input type="button" value="{$LANG.CANCEL}" onclick="$('#other_tag').hide();$('#found_search').fadeIn('slow');"/>
    </form>
</div>
</div>

{if $results}
<p class="usr_photos_notice"><strong>{$LANG.FOUND} {$total|spellcount:$LANG.1_MATERIALS:$LANG.2_MATERIALS:$LANG.10_MATERIALS}</strong></p>
    <table width="100%" cellpadding="5" cellspacing="0" border="0">
	{foreach key=tid item=item from=$results}
        <tr>
  <td class="{$item.class}">
                    <div class="tagsearch_item">
                    <table><tr>
                        <td><img src="/components/search/tagicons/{$item.target}.gif"/></td>
                        <td>{$item.itemlink}</td>
                    </tr></table>
                    </div>
                    <div class="tagsearch_bar">{$item.tag_bar}</div>
          </td>
        </tr>
	{/foreach}
    </table>
	{$pagebar}
{else}
<p class="usr_photos_notice">{$LANG.BY_TAG} <strong>"{$query}"</strong> {$LANG.NOTHING_FOUND}. <a href="{$external_link}" target="_blank">{$LANG.CONTINUE_TO_SEARCH}?</a></p>
{/if}
<script type="text/javascript">
function searchOtherTag(){
    $('#found_search').hide();$('#other_tag').fadeIn('slow');$('.text-input').focus();
}
</script>