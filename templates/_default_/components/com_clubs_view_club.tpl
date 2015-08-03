<div class="con_heading">{$club.title}</div>

{if $is_access}

<table class="club_full_entry" cellpadding="0" cellspacing="0">
    <tr>
        <td valign="top" class="left">
            <div class="image"><img src="/images/clubs/{$club.f_imageurl}" border="0"/></div>
            <div class="members_list">
                <div class="title">{$LANG.CLUB_ADMINISTRATION}:</div>
                <div class="list"><a href="{profile_url login=$club.login}"><img border="0" class="usr_img_small" src="{$club.admin_avatar}" style="float:left; margin: 0 7px 0 0;" /> {$club.nickname}</a><br /><em style="font-size:10px">{$LANG.CLUB_ADMIN}</em><br />{$club.flogdate}</div>
                {if $club.moderators}
                	{foreach key=tid item=moderator from=$club.moderators_list}
						<div class="list"><a href="{profile_url login=$moderator.login}"><img border="0" class="usr_img_small" src="{$moderator.admin_avatar}" style="float:left; margin: 0 7px 0 0;" /> {$moderator.nickname}</a><br /><em style="font-size:10px">{$LANG.MODERATOR}</em>{if $moderator.is_online}<br><span class="online">{$LANG.ONLINE}</span>{/if}</div>
                    {/foreach}
                {/if}
            </div>
            {if $club.members_list}
                <div class="members_list">
                    <div class="title">
                    	{if $club.members-$club.moderators > $cfg.club_perpage}
                    		<a href="/clubs/{$club.id}/members-1">{$LANG.CLUB_MEMBERS} ({$club.members-$club.moderators}):</a>
                        {else}
                        	{$LANG.CLUB_MEMBERS} ({$club.members-$club.moderators}):
                    {/if}
                </div>
                    <div class="list">
                    {foreach key=tid item=member from=$club.members_list}

                        <div class="member_list" align="center"><a href="{profile_url login=$member.login}"><img border="0" class="usr_img_small" src="{$member.admin_avatar}" /></a><br /><a href="{profile_url login=$member.login}" title="{$member.nickname|escape:'html'}">{$member.nickname|truncate:8}</a>{if $member.is_online}<span class="online">{$LANG.ONLINE}</span>{/if}</div>

                    {/foreach}
                    </div>
                </div>
            {/if}
        </td>
        <td valign="top">
            <div class="data">
                <div class="details">
                    {if $club.is_vip}
                        <span class="vip"><strong>{$LANG.VIP_CLUB}</strong></span>
                    {else}
                        <span class="rating"><strong>{$LANG.RATING}:</strong> {$club.rating}</span>
                    {/if}
                    <span class="members"><strong>{$club.members+1|spellcount:$LANG.CLUB_USER:$LANG.CLUB_USER2:$LANG.CLUB_USER10}</strong></span>
                    <span class="date">{$club.fpubdate}</span>
                </div>
                <div class="description">
                    {$club.description}
                </div>
                {if $is_member || $is_admin || $is_moder || $user_id}
                <div class="clubmenu">
                    {if $is_admin}
                        <div class="config"><a  href="/clubs/{$club.id}/config.html">{$LANG.CONFIG_CLUB}</a></div>
                    	<div class="messages"><a class="ajaxlink" href="javascript:void(0)" onclick="clubs.sendMessages({$club.id});return false;" title="{$LANG.SEND_MESSAGE_TO_MEMBERS}">{$LANG.SEND_MESSAGE}</a></div>
                    {/if}
                    {if $user_id}
                        {if ($is_member || $is_admin || $is_moder) && $club.clubtype=='public'}
                        	<div class="invite"><a class="ajaxlink" href="javascript:void(0)" onclick="clubs.intive({$club.id});return false;">{$LANG.INVITE}</a></div>
                        {/if}
                        {if $is_member}
                        	<div class="leave"><a class="ajaxlink" href="javascript:void(0)" onclick="clubs.leaveClub({$club.id}, '{csrf_token}');return false;">{$LANG.LEAVE_CLUB}</a></div>
                        {elseif $club.admin_id != $user_id}
                        	<div class="join"><a class="ajaxlink" href="javascript:void(0)" onclick="clubs.joinClub({$club.id});return false;">{$LANG.JOIN_CLUB}</a></div>
                        {/if}
                    {/if}
                </div>
                {/if}
            </div>
            <div class="clubcontent">
                {if $club.enabled_blogs}
                <div class="blog">
                    <div class="title">{$LANG.CLUB_BLOG}</div>
                    <div class="content">
                    {if $club.blog_posts}
                        {foreach key=id item=post from=$club.blog_posts}
							<div class="club_blog_post">
                            <a href="{$post.url}" title="{$post.title|escape:'html'}" class="club_post_title">{$post.title|truncate:40}</a> &mdash;
                            <a href="{profile_url login=$post.login}" class="club_post_author">{$post.author}</a>,
                            <span class="club_post_descr">{if !$post.published}<span style="color:#CC0000">{$LANG.ON_MODERATE}</span>{else}{$post.fpubdate}{/if}{if ($post.comments_count > 0)}, {$post.comments_count|spellcount:$LANG.COMMENT:$LANG.COMMENT2:$LANG.COMMENT10}{/if}
                            </span>
							</div>
                        {/foreach}
					{else}
                        <div class="usr_albums_block">
                            <ul class="usr_albums_list">
                                <li class="no_albums">{$LANG.NO_BLOG_POSTS}</li>
                            </ul>
                        </div>
                    {/if}

                    <p style="margin:0 0 5px 0">
                    	<span><a href="/clubs/{$club.id}_blog">{$LANG.POSTS_RSS} ({$club.total_posts})</a></span>
                    {if $is_admin || $is_moder || $is_blog_karma_enabled}
                    	<span><a href="/clubs/{$club.id}/newpost.html" class="service">{$LANG.NEW_POST}</a></span>
                    {/if}
                    </p>

                    </div>
                </div>
                {/if}
                {if $club.enabled_photos}
                <div class="album">
                    <div class="title">{$LANG.PHOTOALBUMS}</div>
                    <div class="content">
                        <div id="album_list">{include file='com_clubs_albums.tpl'}</div>
                        <p>
                        {if $club.all_albums > $cfg.club_album_perpage}
                        	<span><a href="/clubs/{$club.id}/photoalbums">{$LANG.ALL_ALBUMS} (<strong id="count_photo">{$club.all_albums}</strong>)</a></span>
                        {/if}
                        {if $is_admin || $is_moder || $is_photo_karma_enabled}
                        	<span><a class="service ajaxlink" href="javascript:void(0)" onclick="clubs.addAlbum({$club.id});">{$LANG.ADD_PHOTOALBUM}</a></span>
                        {/if}
                        </p>
                    </div>
                </div>
                {/if}
                {if $plugins}
                    {foreach key=id item=plugin from=$plugins}
                    	{if !is_array($plugin.html) }
                        	<div id="plugin_{$plugin.name}">{$plugin.html}</div>
                        {/if}
                    {/foreach}
                {/if}
            </div>
            <div class="wall">
                <div class="header">
                	{$LANG.CLUB_WALL}
                    <div class="club_wall_addlink">
                        <a href="javascript:void(0)" id="addlink" class="ajaxlink" onclick="addWall('clubs', '{$club.id}');return false;">
                            {$LANG.WRITE_ON_WALL}
                        </a>
                    </div>
                </div>
                <div class="body">
                    <div class="wall_body">{$club.wall_html}</div>
                </div>
            </div>
        </td>
    </tr>
</table>

{else}
    <p>{$LANG.CLUB_PRIVATE}</p>
    <p>{$LANG.CLUB_ADMIN}: <a href="{profile_url login=$club.login}">{$club.nickname}</a></p>
{/if}