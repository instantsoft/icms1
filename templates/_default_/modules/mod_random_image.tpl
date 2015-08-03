{if $is_img}

		<p align="center"><a href="/photos/photo{$item.id}.html"><img src="/images/photos/small/{$item.file}" border="0" /></a></p>

		{if $cfg.showtitle}
			<p align="center"><a href="/photos/photo{$item.id}.html">{$item.title}</a></p>
		{/if}

{/if}