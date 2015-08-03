<div>
{foreach key=tid item=tag from=$tags}

    <a class="tag" title="{$tag.num|spellcount:$LANG.TAG_ITEM1:$LANG.TAG_ITEM2:$LANG.TAG_ITEM10}" href="/search/tag/{$tag.tag|urlencode}" style="{if $cfg.colors}color: {cycle values=$cfg.colors};{/if}{if $tag.fontsize}font-size: {$tag.fontsize}px;{/if}">{$tag.tag|icms_ucfirst}</a>

{/foreach}
</div>