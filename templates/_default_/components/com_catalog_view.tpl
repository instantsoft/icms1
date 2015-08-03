<div id="shop_toollink_div">
	<a id="shop_searchlink" href="/catalog/{$cat.id}/search.html">{$LANG.SEARCH_BY_CAT}</a>
	{if $cat.view_type=='shop'} {$shopcartlink}	{/if}
    {if $is_can_add}
    <a id="shop_addlink" href="/catalog/{$cat.id}/add.html">{$LANG.ADD_ITEM}</a>
    {/if}
</div>

{if $cfg.is_rss}
    <h1 class="con_heading">{$cat.title} <a href="/rss/catalog/{$cat.id}/feed.rss" title="{$LANG.RSS}"><img src="/images/markers/rssfeed.png" border="0" alt="{$LANG.RSS}"/></a></h1>
{else}
    <h1 class="con_heading">{$cat.title}</h1>
{/if}

{if $cat.description}
	<div class="con_description">{$cat.description}</div>
{/if}

{if $subcats}
	<div class="uc_subcats">{$subcats}</div>
{/if}

{if $alphabet} {$alphabet} {/if}

{if $cat.showsort} {$orderform} {/if}

{if $itemscount>0}

	{if $search_details} {$search_details} {/if}

		{foreach key=tid item=item from=$items}

			{if $cat.view_type=='list' || $cat.view_type=='shop'}
				<div class="catalog_list_item">
					<table border="0" cellspacing="2" cellpadding="0" id="catalog_item_table"><tr>
						<td valign="top" align="center" id="catalog_list_itempic" width="110">
								{if $item.imageurl}
									<a class="lightbox-enabled" title="{$item.title|escape:'html'}" rel="lightbox" href="/images/catalog/{$item.imageurl}">
										<img alt="{$item.title|escape:'html'}" src="/images/catalog/small/{$item.imageurl}" />
									</a>
								{else}
									<a href="/catalog/item{$item.id}.html">
										<img alt="{$item.title|escape:'html'}" src="/images/catalog/small/nopic.jpg" />
									</a>
								{/if}
							{if $cat.view_type=='shop'}
								<div id="shop_small_price">
									<span>{$item.price}</span> {$LANG.CURRENCY}
								</div>
							{/if}
						</td>
						<td class="uc_list_itemdesc" align="left" valign="top">
                            {if $item.can_edit}
                                <div class="uc_item_edit">
                                    <a href="/catalog/edit{$item.id}.html" class="uc_item_edit_link">{$LANG.EDIT}</a>
                                </div>
                            {/if}
							<div>
								<a class="uc_itemlink" href="/catalog/item{$item.id}.html">{$item.title}</a>
								{if $item.is_new}
									<span class="uc_new"><img src="/images/ratings/new.gif" /></span>
								{/if}
							</div>
							{if $cat.is_ratings}
								<div class="uc_rating">{$item.rating}</div>
							{/if}

							<div class="uc_itemfieldlist">
								{foreach key=field item=value from=$item.fields}
                                    {if $value}
                                        {if !strstr($field, '/~l~/')}
                                            <div class="uc_itemfield"><strong>{$field}</strong>: {$value}
                                        {else}
                                            {$value}
                                        {/if}
                                    {/if}
								{/foreach}
							</div>
                            {if $item.tagline && $cat.showtags}
								<div class="uc_tagline"><strong>{$LANG.TAGS}:</strong> {$item.tagline}</div>
							{/if}

							{if $cat.view_type=='list'}
								{if $cat.showmore}
									<a href="/catalog/item{$item.id}.html">{$LANG.DETAILS}...</a>
								{/if}
							{else}
								<div id="shop_list_buttons">
									<a href="/catalog/item{$item.id}.html" title="{$LANG.DETAILS}">
										<img src="/components/catalog/images/shop/more.jpg" alt="{$LANG.DETAILS}"/>
									</a>
									<a href="/catalog/addcart{$item.id}.html" title="{$LANG.ADD_TO_CART}">
										<img src="/components/catalog/images/shop/addcart.jpg" alt="{$LANG.ADD_TO_CART}"/>
									</a>
								</div>
							{/if}

						</td>
					</tr></table>
				</div>
			{/if}

			{if $cat.view_type=='thumb'}
				<div class="uc_thumb_item">
					<table border="0" cellspacing="2" cellpadding="0" width="100%">
						<tr><td height="110" align="center" valign="middle">
							<a href="/catalog/item{$item.id}.html">
								{if $item.imageurl}
									<img alt="{$item.title|escape:'html'}" src="/images/catalog/small/{$item.imageurl}" />
								{else}
									<img alt="{$item.title|escape:'html'}" src="/images/catalog/small/nopic.jpg" />
								{/if}
							</a>
						</td></tr>
						<tr><td align="center" valign="middle">
							<a class="uc_thumb_itemlink" href="/catalog/item{$item.id}.html">{$item.title}</a>
						</td></tr>
					</table>
				</div>
			{/if}
		{/foreach}

		{$pagebar}
{/if}