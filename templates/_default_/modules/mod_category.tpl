{if $subcats_list}
<ul class="mod_cat_list">
    {$last_level=1}
    {foreach key=tid item=cat from=$subcats_list}

        {if $cat.NSLevel == $last_level}</li>{/if}
        {$tail=$last_level-$cat.NSLevel}
        {section name=foo start=0 loop=$tail step=1}
            </li></ul>
        {/section}

        {if $cat.NSLevel <= 1}
            <li>
        {/if}
        {if $cat.NSLevel <= 1}
            <a class="folder" href="{$cat.url}">{if $cat.seolink == $current_seolink} <strong>{$cat.title} ({$cat.content_count})</strong>{else}{$cat.title} ({$cat.content_count}){/if}</a>
        {else}
            {if $cat.NSLevel > $last_level}
                <a href="javascript:" class="cat_plus" style="{if $cfg.expand_all}display:none{/if}" title="{$LANG.EXPAND}"></a>
                <a href="javascript:" class="cat_minus" style="{if !$cfg.expand_all}display:none{/if}" title="{$LANG.TURN}"></a>
            	<ul>
            {/if}
                <li>
					<a class="folder" href="{$cat.url}">{if $cat.seolink == $current_seolink} <strong>{$cat.title} ({$cat.content_count})</strong>{else}{$cat.title} ({$cat.content_count}){/if}</a>
        {/if}
        {$last_level=$cat.NSLevel}

    {/foreach}
    {section name=foo start=0 loop=$last_level step=1}
        </li></ul>
    {/section}

</ul>

<script type="text/javascript">

        {if !$cfg.expand_all}
            $('ul.mod_cat_list li > ul').hide();
        {/if}

        $('.cat_plus').click(function(){
            $(this).fadeOut();
            $(this).parent('li').find('.cat_minus').eq(0).show();
            $(this).parent('li').find('ul').eq(0).fadeIn();
        });

        $('.cat_minus').click(function(){
            $(this).fadeOut();
            $(this).parent('li').find('.cat_plus').eq(0).show();
            $(this).parent('li').find('ul').hide();
            $(this).parent('li').find('ul').find('.cat_minus').hide();
            $(this).parent('li').find('ul').find('.cat_plus').show();
        });

</script>
{/if}