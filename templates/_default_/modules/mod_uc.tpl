{if $cfg.showtype == 'thumb'}
    {foreach key=tid item=item from=$items}
        <div class="uc_latest_item">
            <table border="0" cellspacing="2" cellpadding="0" width="100%">
                <tr><td height="110" align="center" valign="middle">
                    <a href="/catalog/item{$item.id}.html">
                        <img alt="{$item.title|escape:'html'}" src="/images/catalog/small/{$item.imageurl}" border="0" />
                    </a>
                </td></tr>

                <tr><td align="center" valign="middle">
                    <a class="uc_latest_link" href="/catalog/item{$item.id}.html">{$item.title|truncate:30}</a>
                </td></tr>

                {if $item.viewtype == 'shop'}
                    <tr><td align="center" valign="middle">
                        <div id="uc_popular_price">{$item.price} {$LANG.CURRENCY}</div>
                    </td></tr>
                {/if}
            </table>
        </div>
    {/foreach}
    <div class="blog_desc"></div>
{/if}

{if $cfg.showtype == 'list'}
    <table width="100%" cellspacing="0" cellpadding="4" class="uc_latest_list">
        {foreach key=tid item=item from=$items}
            <tr>
                <td width="" valign="top">
                    <a class="uc_latest_link" href="/catalog/item{$item.id}.html">{$item.title|truncate:30}</a>
                </td>

                {section name=customer start=0 loop=$cfg.showf step=1}
                    <td valign="top">{$item.fdata[$smarty.section.customer.index]}</td>
                {/section}

                <td width="100" align="right" valign="top">{$item.key}</td>

                <td align="right" width="65">
                    {if $item.viewtype == 'shop'}
                        <div id="uc_popular_price">{$item.price} {$LANG.CURRENCY}</div>
                    {/if}
                </td>
            </tr>
        {/foreach}
    </table>
{/if}

{if $cfg.fulllink}
    <div style="margin-top:5px; text-align:right; clear:both"><a style="text-decoration:underline" href="/catalog">{$LANG.UC_MODULE_CATALOG}</a> {$LANG.UC_MODULE_ARR}</div>
{/if}