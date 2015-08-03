<div id="mod_user_stats">
    {if $cfg.show_total}
    <div class="stat_block">
        <div class="title">{$LANG.HOW_MUCH_US}</div>
        <div class="body">
            <ul>
                <li>{$total_usr|spellcount:$LANG.USER:$LANG.USER2:$LANG.USER10}</li>
            </ul>
        </div>
    </div>
    {/if}

    {if $cfg.show_online}
    <div class="stat_block">
        <div class="title">{$LANG.STATS_WHO_ONLINE}</div>
        <div class="body">
            <ul>
                <li>{$people.users|spellcount:$LANG.USER:$LANG.USER2:$LANG.USER10}</li>
                <li>{$people.guests|spellcount:$LANG.GUEST:$LANG.GUEST2:$LANG.GUEST10}</li>
                <li>
                {if $usr_online}
                	<a href="/users/all.html" rel="nofollow">{$LANG.SHOW_ALL}</a>
                {else}
                	<a href="/users/online.html" rel="nofollow">{$LANG.SHOW_ONLY_ONLINE}</a>
                {/if}
                </li>
            </ul>
        </div>
    </div>
    {/if}

    {if $cfg.show_gender}
    <div class="stat_block">
        <div class="title">{$LANG.STATS_WHO}</div>
        <div class="body">
            <ul>
                <li><a href="javascript:void(0)" rel=”nofollow” onclick="searchGender('m')">{$gender_stats.male|spellcount:$LANG.MALE1:$LANG.MALE2:$LANG.MALE10}</a></li>
                <li><a href="javascript:void(0)" rel=”nofollow” onclick="searchGender('f')">{$gender_stats.female|spellcount:$LANG.FEMALE1:$LANG.FEMALE2:$LANG.FEMALE10}</a></li>
                <li>{$LANG.UNKNOWN} &mdash; {$gender_stats.unknown}</li>
            </ul>
        </div>
    </div>
    {/if}

    {if $cfg.show_city}
    <div class="stat_block">
        <div class="title">{$LANG.WHERE_WE_FROM}</div>
        <div class="body">
            <ul>
                {foreach key=tid item=city from=$city_stats}
                    {if $city.href}
                        <li><a href="{$city.href}" rel=”nofollow”>{$city.city}</a> &mdash; {$city.count}</li>
                    {else}
                        <li>{$city.city} &mdash; {$city.count}</li>
                    {/if}
                {/foreach}
            </ul>
        </div>
    </div>
    {/if}

    {if $cfg.show_bday && $bday}
        <div class="stat_block_bday" style="margin-top:10px;">
            <div class="title">{$LANG.TODAY_BIRTH}:</div>
            <div class="body">
                {$bday}
            </div>
        </div>
    {/if}

</div>
<script type="text/javascript">
function searchGender(gender){
	$('body').append('<form id="sform" style="display:none" method="post" action="/users"><input type="hidden" name="gender" value="'+gender+'"/></form>');
	$('form#sform').submit();
}
</script>