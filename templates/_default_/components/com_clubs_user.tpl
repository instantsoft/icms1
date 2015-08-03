{if $can_create}
	<div class="new_club">
		{$LANG.YOU_CAN} <a href="javascript:void(0)" onclick="clubs.create(this);return false;">{$LANG.TO_CREATE_NEW_CLUB}</a>
	</div>
    <script type="text/javascript" src="/components/clubs/js/clubs.js"></script>
{/if}

<h3 style="margin:8px 0">{$LANG.USER_CLUBS}</h3>

{if $clubs}

	{foreach key=tid item=club from=$clubs}
		<div class="club_entry{if $club.is_vip}_vip{/if}">
            <div class="{$club.role} user_role" title="{$LANG.USER_ROLE_INCLUB}">
                {if $club.role == 'member'}
                   {$LANG.MEMBER}
                {elseif !$club.role}
                   {$LANG.CLUB_ADMIN}
                {else}
                   {$LANG.MODERATOR}
                {/if}
            </div>
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
                    <span class="date"><strong>{$club.fpubdate}</strong></span>
				</div>
			</div>
		</div>
	{/foreach}

	{if $pagination}<div style="margin-top:40px">{$pagination}</div>{/if}
{else}
    {if $my_profile}
    	<p style="clear:both">{$LANG.YOU_NOT_IN_CLUBS}</p>
    {else}
        <p style="clear:both"><strong>{$user.nickname}</strong> {$LANG.USET_NOT_IN_CLUBS}</p>
    {/if}
{/if}
