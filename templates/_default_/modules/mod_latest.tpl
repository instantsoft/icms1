{if $cfg.is_pag}
	<script type="text/javascript">
		function conPage(page, module_id){
            $('div#module_ajax_'+module_id).css({ opacity:0.4, filter:'alpha(opacity=40)' });
			$.post('/modules/mod_latest/ajax/latest.php', { 'module_id': module_id, 'page':page }, function(data){
				$('div#module_ajax_'+module_id).html(data);
                $('div#module_ajax_'+module_id).css({ opacity:1.0, filter:'alpha(opacity=100)' });
			});
		}
    </script>
{/if}
{if !$is_ajax}<div id="module_ajax_{$module_id}">{/if}

{foreach key=aid item=article from=$articles}
	<div class="mod_latest_entry">
        {if $article.image}
            <div class="mod_latest_image">
                <img src="/images/photos/small/{$article.image}" width="32" height="32" alt="{$article.title|escape:'html'}"/>
            </div>
        {/if}
	    <a class="mod_latest_title" href="{$article.url}">{$article.title}</a>
		{if $cfg.showdate}
            <div class="mod_latest_date">
                {$article.fpubdate} - <a href="{profile_url login=$article.user_login}">{$article.author}</a>{if $cfg.showcom} - <a href="{$article.url}" title="{$article.comments|spellcount:$LANG.COMMENT1:$LANG.COMMENT2:$LANG.COMMENT10}" class="mod_latest_comments">{$article.comments}</a>{/if} - <span class="mod_latest_hits">{$article.hits}</span>
            </div>
        {/if}
        {if $cfg.showdesc}
            <div class="mod_latest_desc" style="overflow:hidden">
                {$article.description|strip_tags|truncate:200}
            </div>
        {/if}
	</div>
{/foreach}
{if $cfg.showrss}
	<div class="mod_latest_rss">
		<a href="/rss/content/{if $cfg.cat_id}{$cfg.cat_id}{else}all{/if}/feed.rss">{$LANG.LATEST_RSS}</a>
	</div>
{/if}
{if $cfg.is_pag && $pagebar_module}
    <div class="mod_latest_pagebar">{$pagebar_module}</div>
{/if}
{if !$is_ajax}</div>{/if}