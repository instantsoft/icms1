<div class="float_bar">
    <a href="/content/add.html" class="usr_article_add">{$LANG.ADD_ARTICLE}</a>
</div>

<h1 class="con_heading">{$LANG.MY_ARTICLES} ({$total})</h1>

{if $articles}
    <style type="text/css">
    .art_list {
        border: 1px solid #ccc;
        border-collapse: collapse;
    }
    .art_list thead {
        background: #333;
        color: #fff;
    }
    </style>

<table width="100%" cellpadding="8" cellspacing="0" border="0" class="art_list">
	<thead>
		<tr class="thead">
			<td width="100"><strong>{$LANG.DATE}</strong></td>
			<td colspan="2"><strong>{$LANG.ARTICLE}</strong></td>
			<td width="100" align="center"><strong>{$LANG.STATUS}</strong></td>
			<td width="16">&nbsp;</td>
			<td width="20">&nbsp;</td>
			<td width="100"><strong>{$LANG.CAT}</strong></td>
			<td width="70" align="center"><strong>{$LANG.ACTION}</strong></td>
		</tr>
	</thead>
	<tbody>
	{foreach key=tid item=article from=$articles}
		<tr>
			<td>{$article.fpubdate}</td>
			<td><img src="/templates/{template}/images/icons/article.png" border="0"></td>
			<td><a href="{$article.url}">{$article.title}</a></td>
			<td align="center">
            	{if $article.published}
                	<span style="color:green">{$LANG.PUBLISHED}</span>
                {else}
                	<span style="color:#CC0000">{$LANG.NO_PUBLISHED}</span>
                 {/if}
            </td>
			<td><img src="/templates/{template}/images/icons/comments.png" border="0"></td>
			<td>{$article.comments}</td>
			<td><a href="{$article.cat_url}">{$article.cat_title}</a></td>
			<td align="center">
				<a href="/content/edit{$article.id}.html" title="{$LANG.EDIT}"><img src="/templates/{template}/images/icons/edit.png" border="0"/></a>
				{if $user_can_delete}
					<a href="/content/delete{$article.id}.html" title="{$LANG.DELETE}"><img src="/templates/{template}/images/icons/delete.png" border="0"/></a>
				{/if}
			</td>
		</tr>
	{/foreach}
	</tbody>
</table>

{$pagebar}
<script type="text/javascript">
$(document).ready(function(){
	zebra();
	function zebra() {
	   $('.art_list tr').not('.thead').removeClass('search_row1').removeClass('search_row2');
	   $('.art_list tr:odd').not('.thead').addClass('search_row1');
	   $('.art_list tr:even').not('.thead').addClass('search_row2');
	}
});
</script>
{else}
	<p>{$LANG.NO_YOUR_ARTICL_ON_SITE}</p>
{/if}