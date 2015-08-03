<ul class="uc_cat_list">
	{foreach key=tid item=cat from=$cats}
		<li class="uc_cat_item"><a href="/catalog/{$cat.id}">{$cat.title}</a> ({$cat.content_count})</li>
	{/foreach}
</ul>