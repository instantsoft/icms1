<div class="mod_clubs">
{foreach key=tid item=club from=$clubs}
	<div class="club_entry{if $club.is_vip}_vip{/if}">
		<div class="image">
			<a href="/clubs/{$club.id}" title="{$club.title|escape:'html'}" class="{$club.clubtype}">
				<img src="/images/clubs/small/{$club.imageurl}" border="0" alt="{$club.title|escape:'html'}"/>
			</a>
		</div>
		<div class="data">
			<div class="title">
				<a href="/clubs/{$club.id}" class="{$club.clubtype}" {if $club.clubtype=='private'}title="{$LANG.PRIVATE}"{/if}>{$club.title}</a>
			</div>
			<div class="details">
            	{if $club.is_vip}
            		<span class="vip"><strong>{$LANG.VIP_CLUB}</strong></span>
                {/if}
				<span class="rating"><strong>{$LANG.RATING}</strong> &mdash; {$club.rating}</span>
				<span class="members"><strong>{$club.members_count|spellcount:$LANG.CLUB_USER:$LANG.CLUB_USER2:$LANG.CLUB_USER10}</strong></span>
			</div>
		</div>
	</div>
{/foreach}
</div>
