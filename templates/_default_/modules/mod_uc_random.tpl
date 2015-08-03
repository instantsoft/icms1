{if $is_uc}
	{foreach key=tid item=item from=$items}
		<div align="center" id="uc_random_img"><a href="/catalog/item{$item.id}.html"><img src="/images/catalog/small/{$item.imageurl}" border="0"/></a></div>

		{if $cfg.showtitle}
			<div style="margin-top:10px" id="uc_random_title" align="center"><a href="/catalog/item{$item.id}.html"><strong>{$item.title}</strong></a></div>

			{if $item.viewtype == 'shop'}
				<div style="margin-bottom:10px" align="center" id="uc_random_price">{$item.price} {$LANG.CURRENCY}</div>
			{/if}
		{/if}

		{if $cfg.showcat}
			<div align="center" id="uc_random_cat">{$LANG.UC_RANDOM_RUBRIC}: <a href="/catalog/{$item.category_id}">{$item.category}</a></div>
		{/if}

	{/foreach}
{else}
	<p>{$LANG.UC_RANDOM_NO_ITEMS}</p>
{/if}