<ul id="{$menu}" class="menu">

    {if $cfg.show_home}

        <li {if $menuid==1}class="selected"{/if}>

            <a href="/" {if $menuid==1}class="selected"{/if}><span><i class="fa fa-home"></i>{$LANG.PATH_HOME}</span></a>

        </li>

    {/if}

    {foreach key=key item=item from=$items}

        {if $item.NSLevel == $last_level}</li>{/if}

        {$tail=$last_level-$item.NSLevel}

        {section name=foo start=0 loop=$tail step=1}

            </li></ul></li>

        {/section}

        {if $item.NSLevel > 1 && $item.NSLevel > $last_level}<ul>{/if}

            <li class="{$item.css_class} {if ($menuid==$item.id || $current_uri == $item.link) || ($currentmenu.NSLeft > $item.NSLeft && $currentmenu.NSRight < $item.NSRight)}selected{/if}">

                <a href="{$item.link}" target="{$item.target}" {if ($menuid==$item.id || $current_uri == $item.link)}class="selected"{/if} title="{$item.title|escape:'html'}">

                    <span>
                        {if $item.icon_as_img}<img src="/images/menuicons/{$item.iconurl}" alt="{$item.title|escape:'html'}" />{elseif $item.icon_as_css}<i class="{$item.iconurl}"></i>{/if}{$item.title}
                    </span>

                </a>

        {$last_level=$item.NSLevel}

    {/foreach}

    {section name=foo start=0 loop=$last_level step=1}

        </li>

    {/section}

</ul>