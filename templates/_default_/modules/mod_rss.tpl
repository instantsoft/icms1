<table width="100%" cellpadding="4" cellspacing="0">
{$col="1"}
{foreach key=id item=item from=$rs.items}
{if $col==1}<tr>{/if}
    {if $cfg.showicon}
        <td width="16" valign="top">
            <img src="/images/icons/rssitem.gif" />
        </td>
        <td valign="top">
            <div><a target="_blank" href="{$item.link}">{$item.title}</a></div>
            {if $cfg.showdesc}
                <div>{$item.description}</div>
            {/if}
        </td>
    {/if}
{if $col==$cfg.cols}</tr>{$col="1"}{else}{$col=$col+1}{/if}
{/foreach}
</table>