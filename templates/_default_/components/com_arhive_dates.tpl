<div class="con_heading">{$pagetitle}</div>

{if $items}
    <ul class="arhive_list">
        {foreach key=id item=item from=$items}
            <li>
                <a href="/arhive/{$item.year}/{$item.month}">{$item.fmonth}</a>{if $do == 'view'}, <a href="/arhive/{$item.year}">{$item.year}</a>{/if} <span>({$item.num|spellcount:$LANG.ARTICLE1:$LANG.ARTICLE2:$LANG.ARTICLE10})</span>
            </li>
        {/foreach}
    </ul>
{else}
    <p>{$LANG.ARHIVE_NO_MATERIALS}</p>
{/if}