{if $items_count}
    {if $cfg.showtype=='list'}
        {foreach key=cid item=item from=$items}
            <div class="{cycle values="cartrow1,cartrow2"}">
                <div class="cart_item">
                    <a href="/catalog/item{$item.id}.html">{$item.title|truncate:30}</a>
                </div>
                {if $item.itemscount == 1}
                    <div class="cart_price">{$item.totalcost} {$LANG.CURRENCY}</div>
                {else}
                    <div class="cart_price">{$item.itemscount} x {$item.price} = {$item.totalcost} {$LANG.CURRENCY}</div>
                {/if}
            </div>
        {/foreach}
        <div align="right" class="cart_total">
            <a href="/catalog/viewcart.html" title="{$LANG.CART_GOTO_CART}"><strong>{$LANG.CART_SUMM}:</strong> {$total_summ} {$LANG.CURRENCY}.</a>
        </div>
    {else}
        <div class="cart_count">
            <strong>{$LANG.CART_ITEMS}:</strong> <a href="/catalog/viewcart.html" title="{$LANG.CART_GOTO_CART}">{$items_count} {$LANG.CART_QTY}</a>
        </div>
        {if $cfg.showtype == 'qtyprice'}
            <div class="cart_total"><strong>{$LANG.CART_TOTAL}:</strong> {$total_summ} {$LANG.CURRENCY}.</div>
        {/if}
    {/if}
{else}
    <p style="clear:both"><strong>{$LANG.CART_NOT_ITEMS}</strong></p>
{/if}