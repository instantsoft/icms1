<div id="similar_posts">
	<h4>{$LANG.P_TITLE}</h4>
	<ul>
		{foreach item=post from=$posts}
			<li>
				<a href="{$post.url}" title="{$post.title|escape:'html'}"><img src="{$post.fileurl}" alt="{$post.title|escape:'html'}" /></a>
                <h5><a href="{$post.url}">{$post.title}</a></h5>
				<p>{$post.content}</p>
			</li>
		{/foreach}
	</ul>
</div>