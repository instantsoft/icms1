<div class="con_heading">{$pagetitle}</div>

{if $items}
    {foreach key=id item=item from=$items}
        <div class="arhive_date"><a href="/arhive/{$item.year}/{$item.month}/{$item.day}">{$item.fpubdate}</a></div>
        <h2 class="arhive_title"><a href="{$item.url}">{$item.title}</a> &rarr; <a href="{$item.category_url}">{$item.cat_title}</a></h2>
        <div class="arhive_desc">
        {if $item.showdesc && $item.description}
            {if $item.image}
                <div class="con_image">
                    <img src="/images/photos/small/{$item.image}" border="0" alt="{$item.title|escape:'html'}"/>
                </div>
            {/if}
            {$item.description}
        {/if}
        </div>
    {/foreach}
{else}
    <p>{$LANG.ARHIVE_NO_MATERIALS}</p>
{/if}