<div class="pathway">
    {foreach key=tid item=path from=$pathway name='pathway'}
        {if $path.is_last}
            <span class="pathwaylink">{$path.title}</span>
        {else}
            <a href="{$path.link}" class="pathwaylink">{$path.title}</a>
        {/if}
        {if !$smarty.foreach.pathway.last}{$separator}{/if}
    {/foreach}
</div>
