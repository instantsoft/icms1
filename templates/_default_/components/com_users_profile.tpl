{add_js file='includes/jquery/tabs/jquery.ui.min.js'}
{add_css file='includes/jquery/tabs/tabs.css'}

<script type="text/javascript">
    $(function(){ $(".uitabs").tabs(); });
</script>

<div id="usertitle">

    <div id="user_ratings">
        <div class="karma" title="{$LANG.KARMA}">
        	<div class="{if $usr.karma >= 0}value-positive{else}value-negative{/if}" id="u_karma_cont">
                <table cellpadding="2" cellspacing="0"><tr>
                    <td class="sign_link" style="color:green">
                    {if $usr.can_change_karma}
                        <a href="javascript:void(0)" onclick="users.changeKarma('{$usr.id}', 'plus');return false;" title="{$LANG.KARMA} +"><img src="/templates/{template}/images/icons/karma_up.png" alt="{$LANG.KARMA} +"/></a>
                    {/if}
                    </td>
                    <td><span class="user_karma_point" id="u_karma">{$usr.karma}</span></td>
                    <td style="color:red" class="sign_link">
                    {if $usr.can_change_karma}
                        <a href="javascript:void(0)" onclick="users.changeKarma('{$usr.id}', 'minus'); return false;" title="{$LANG.KARMA} -"><img src="/templates/{template}/images/icons/karma_down.png" alt="{$LANG.KARMA} -"/></a>
                    {/if}
                    </td>
                </tr></table>
            </div>
        </div>
        <div class="rating" title="{$LANG.RATING}">
            <div class="value">{$usr.user_rating}</div>
        </div>
    </div>

    <div class="user_group_name">
        <div class="{$usr.group_alias}">
            {if $cfg.sw_search == 2}
                {$usr.grp}
            {else}
            <a href="/users/group/{$usr.group_id}">{$usr.grp}</a>
            {/if}
        </div>
    </div>

    <div class="con_heading" id="nickname">
        {$usr.nickname} {if $usr.banned}<span style="color:red; font-size:12px;">{$LANG.USER_IN_BANLIST}</span>{/if}
    </div>

</div>

<div class="usr_status_bar">
    <div class="usr_status_text" {if !$usr.status_text}style="display:none"{/if}>
        <span>{$usr.status_text|escape:'html'}</span>
        <span class="usr_status_date" >// {$usr.status_date} {$LANG.BACK}</span>
    </div>
    {if $myprofile || $is_admin}
        <div class="usr_status_link"><a href="javascript:" onclick="setStatus({$usr.id})">{$LANG.CHANGE_STATUS}</a></div>
    {/if}
</div>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:14px">
	<tr>
		<td width="200" valign="top">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td align="center" valign="middle">
                        <div class="usr_avatar">
                            <img alt="{$usr.nickname|escape:'html'}" class="usr_img" src="{$usr.avatar}" />
                        </div>

						{if $is_auth}
							<div id="usermenu" style="">
                            <div class="usr_profile_menu">
							<table cellpadding="0" cellspacing="6" ><tr>

							{if !$myprofile}
                                <tr>
                                    <td><img src="/templates/{template}/images/icons/profile/message.png" border="0"/></td>
                                    <td><a class="ajaxlink" href="javascript:void(0)" title="{$LANG.WRITE_MESS}: {$usr.nickname|escape:'html'}" onclick="users.sendMess('{$usr.id}', 0, this);return false;">{$LANG.WRITE_MESS}</a></td>
                                </tr>
							{/if}

                            {if !$myprofile}
                            	{if !$usr.isfriend}
                                    <tr>
                                        <td><img src="/templates/{template}/images/icons/profile/friends.png" border="0"/></td>
                                        <td><a class="ajaxlink" href="javascript:void(0)" title="{$usr.nickname|escape:'html'}" onclick="users.addFriend('{$usr.id}', this);return false;">{$LANG.ADD_TO_FRIEND}</a></td>
                                    </tr>
                                {else}
                                <tr>
                                    <td class="add_friend_ajax" style="display: none;"><img src="/templates/{template}/images/icons/profile/friends.png" border="0"/></td>
                                    <td class="add_friend_ajax" style="display: none;"><a class="ajaxlink" href="javascript:void(0)" title="{$usr.nickname|escape:'html'}" onclick="users.addFriend('{$usr.id}', this);return false;">{$LANG.ADD_TO_FRIEND}</a></td>
                                    <td class="del_friend_ajax"><img src="/templates/{template}/images/icons/profile/nofriends.png" border="0"/></td>
                                    <td class="del_friend_ajax"><a id="del_friend" class="ajaxlink" href="javascript:void(0)" title="{$usr.nickname|escape:'html'}" onclick="users.delFriend('{$usr.id}', this);return false;">{$LANG.STOP_FRIENDLY}</a></td>
                                </tr>
                                {/if}
                            {/if}
                         	{if $myprofile}
                            	{if $cfg.sw_msg}
                                <tr>
                                    <td><img src="/templates/{template}/images/icons/profile/message.png" border="0"/></td>
                                    <td><a href="/users/{$usr.id}/messages.html" title="{$LANG.MY_MESS}">{$LANG.MY_MESS}</a></td>
                                </tr>
                                {/if}
                                {if $cfg.sw_photo}
                                <tr>
                                    <td><img src="/templates/{template}/images/icons/profile/addphoto.png" border="0"/></td>
                                    <td><a href="/users/addphoto.html" title="{$LANG.ADD_PHOTO}">{$LANG.ADD_PHOTO}</a></td>
                                </tr>
                                {/if}
                                <tr>
                                    <td><img src="/templates/{template}/images/icons/profile/avatar.png" border="0"/></td>
                                    <td><a href="/users/{$usr.id}/avatar.html" title="{$LANG.SET_AVATAR}">{$LANG.SET_AVATAR}</a></td>
                                </tr>
								{if $usr.invites_count}
                                <tr>
                                    <td><img src="/templates/{template}/images/icons/profile/invites.png" border="0"/></td>
                                    <td><a href="/users/invites.html" title="{$LANG.MY_INVITES}">{$LANG.MY_INVITES}</a> {$usr.invites_count}</td>
                                </tr>
								{/if}
                                <tr>
                                    <td><img src="/templates/{template}/images/icons/profile/edit.png" border="0"/></td>
                                    <td><a href="/users/{$usr.id}/editprofile.html" title="{$LANG.CONFIG_PROFILE}">{$LANG.MY_CONFIG}</a></td>
                                </tr>
                            {/if}
                            {if $is_admin && !$myprofile}
                            <tr>
                                <td><img src="/templates/{template}/images/icons/profile/edit.png" border="0"/></td>
                                <td><a href="/users/{$usr.id}/editprofile.html" title="{$LANG.CONFIG_PROFILE}">{$LANG.CONFIG_PROFILE}</a></td>
                            </tr>
                            {/if}
                            <tr>
                                <td><img src="/templates/{template}/images/icons/profile/karma.png" border="0"/></td>
                                <td><a href="/users/{$usr.id}/karma.html" title="{$LANG.KARMA_HISTORY}">{$LANG.KARMA_HISTORY}</a></td>
                            </tr>
							{if !$myprofile}
                            	{if $is_admin}
                                	{if !$usr.banned}
                                    <tr>
                                        <td><img src="/templates/{template}/images/icons/profile/award.png" border="0"/></td>
                                        <td><a href="/users/{$usr.id}/giveaward.html" title="{$LANG.TO_AWARD}">{$LANG.TO_AWARD}</a></td>
                                    </tr>
                                    {if $usr.id != 1}
                                    <tr>
                                        <td><img src="/templates/{template}/images/icons/profile/ban.png" border="0"/></td>
                                        <td><a href="/admin/index.php?view=userbanlist&do=add&to={$usr.id}" title="{$LANG.TO_BANN}">{$LANG.TO_BANN}</a></td>
                                    </tr>
                                    {/if}
                                    {/if}
                                {if $usr.id != 1}
                                    <tr>
                                        <td><img src="/templates/{template}/images/icons/profile/delprofile.png" border="0"/></td>
                                        <td><a href="/users/{$usr.id}/delprofile.html" title="{$LANG.DEL_PROFILE}">{$LANG.DEL_PROFILE}</a></td>
                                    </tr>
                                {/if}
                                {/if}
                         	{/if}

                            </table></div>
                            </div>
						{/if}
					</td>
				</tr>
			</table>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td>

                        <div id="user_profile_url">
                            <div>{$LANG.LINK_TO_THIS_PAGE}:</div>
                            <a href="{$usr.profile_link}" title="{$usr.nickname|escape:'html'}">{$usr.profile_link}</a>
                        </div>

					</td>
				</tr>
			</table>
	    </td>
    	<td valign="top" style="padding-left:10px">
			<div id="profiletabs" class="uitabs">
				<ul id="tabs">
					<li><a href="#upr_profile"><span>{$LANG.PROFILE}</span></a></li>
					{if $myprofile && $cfg.sw_feed}
						<li><a href="/actions/my_friends" title="upr_feed"><span>{$LANG.FEED}</span></a></li>
					{/if}
					{if $cfg.sw_clubs}
						<li><a href="/clubs/by_user_{$usr.id}" title="upr_clubs"><span>{$LANG.CLUBS}</span></a></li>
					{/if}
                    {if $cfg.sw_awards}
                        <li><a href="#upr_awards"><span>{$LANG.AWARDS}</span></a></li>
                    {/if}
                    {foreach key=id item=plugin from=$plugins}
                        <li><a href="{if $plugin.ajax_link}{$plugin.ajax_link}{else}#upr_{$plugin.name}{/if}" title="{$plugin.name}"><span>{$plugin.title}</span></a></li>
                    {/foreach}
				</ul>

				<div id="upr_profile">
					<div class="user_profile_data">

						<div class="field">
							<div class="title">{$LANG.LAST_VISIT}:</div>
							<div class="value">{$usr.flogdate}</div>
						</div>
						<div class="field">
							<div class="title">{$LANG.DATE_REGISTRATION}:</div>
							<div class="value">
                                {$usr.fregdate}
                            </div>
						</div>
                        {if $usr.inv_login}
                            <div class="field">
                                <div class="title">{$LANG.INVITED_BY}:</div>
                                <div class="value">
                                    <a href="{profile_url login=$usr.inv_login}">{$usr.inv_nickname}</a>
                                </div>
                            </div>
                        {/if}
                        {if $usr.city}
						<div class="field">
							<div class="title">{$LANG.CITY}:</div>
                            <div class="value"><a href="/users/city/{$usr.cityurl|escape:'html'}">{$usr.city}</a>{if $usr.country}, {$usr.country}{/if}</div>
						</div>
                        {/if}

						{if $usr.showbirth && $usr.fbirthdate}
						<div class="field">
							<div class="title">{$LANG.BIRTH}:</div>
							<div class="value">{$usr.fbirthdate}</div>
						</div>
						{/if}

						{if $usr.fgender}
						<div class="field">
							<div class="title">{$LANG.SEX}:</div>
							<div class="value">{$usr.fgender}</div>
						</div>
						{/if}

						{if $usr.showicq && $usr.icq}
						<div class="field">
							<div class="title">ICQ:</div>
							<div class="value">{$usr.icq}</div>
						</div>
						{/if}

						{if $usr.showphone && $usr.phone}
						<div class="field">
							<div class="title">{$LANG.PHONE}:</div>
							<div class="value">+{$usr.phone}</div>
						</div>
						{/if}

						{if $usr.showmail}
							{add_js file='includes/jquery/jquery.nospam.js'}
							<div class="field">
								<div class="title">E-mail:</div>
								<div class="value"><a href="#" rel="{$usr.email|NoSpam}" class="email">{$usr.email}</a></div>
							</div>
                            <script>
                                    $('.email').nospam({ replaceText: true });
                            </script>
						{/if}

						<div class="field">
							<div class="title">{$LANG.HOBBY} ({$LANG.TAGSS}):</div>
							<div class="value">{if $usr.fdescription}{$usr.fdescription}{else}<span style="color:#999"><em>{$LANG.TAGS_NOT_SPEC}</em></span>{/if}</div>
						</div>

						{if $cfg.privforms && $usr.form_fields}
							{foreach key=tid item=field from=$usr.form_fields}
                                <div class="field">
                                    <div class="title">{$field.title}:</div>
                                    <div class="value">{if $field.field}{$field.field}{else}<em>{$LANG.NOT_SET}</em>{/if}</div>
                                </div>
                            {/foreach}
						{/if}

					</div>

					<div>
                        <div class="usr_profile_block">
                            <div class="usr_wall_header">
                                {if !$myprofile}
                                    {$LANG.USER_CONTENT}
                                {else}
                                    {$LANG.MY_CONTENT}
                                {/if}
                            </div>
                            <div id="usr_links">
                                {if $cfg.sw_blogs}
                                    {if $usr.blog}
                                        <div id="usr_blog"><a href="/blogs/{$usr.blog.seolink}" title="{$usr.blog.title|escape:'html'}">{$LANG.BLOG}</a></div>
                                    {elseif $myprofile}
                                        <div id="usr_blog"><a href="/blogs/createblog.html">{$LANG.CREATE_BLOG}</a></div>
                                    {/if}
                                {/if}
                                {if $cfg.sw_files}
                                    <div id="usr_files">
                                        <a href="/users/{$usr.id}/files.html">{$LANG.FILES}</a> <sup>{$usr.files_count}</sup>
                                    </div>
                                {/if}
                                {if $cfg.sw_board && $usr.board_count}
                                    <div id="usr_board">
                                        <a href="/board/by_user_{$usr.login}">{$LANG.ADVS}</a> <sup>{$usr.board_count}</sup>
                                    </div>
                                {/if}
                                {if $cfg.sw_forum && $cfg_forum.component_enabled && $usr.forum_count}
                                    <div id="usr_forum">
                                        <a href="/forum/{$usr.login}_activity.html" title="{$LANG.MY_ACTIVITY_ON_FORUM}">{$LANG.FORUM}</a> <sup title="{$LANG.MESS_IN_FORUM}">{$usr.forum_count}</sup>
                                    </div>
                                {/if}
                                {if $cfg.sw_comm && $usr.comments_count}
                                    <div id="usr_comments">
                                        <a href="/comments/by_user_{$usr.login}" title="{$LANG.READ}">{$LANG.COMMENTS}</a> <sup>{$usr.comments_count}</sup>
                                    </div>
                                {/if}
                            </div>
                        </div>

                        {if $usr.albums}
                            <div class="usr_albums_block usr_profile_block">
                                {if $usr.albums_total > $usr.albums_show}
                                    <div class="float_bar">
                                        <a href="/users/{$usr.id}/photoalbum.html">{$LANG.ALL_ALBUMS}</a> ({$usr.albums_total})
                                    </div>
                                {/if}
                                <div class="usr_wall_header">
                                    {if !$myprofile}
                                        {$LANG.USER_PHOTOS}
                                    {else}
                                        {$LANG.MY_PHOTOS}
                                    {/if}
                                </div>
                                <ul class="usr_albums_list">
                                    {foreach key=key item=album from=$usr.albums}
                                        <li>
                                            <div class="usr_album_thumb">
                                                <a href="/users/{$usr.login}/photos/{$album.type}{$album.id}.html" title="{$album.title|escape:'html'}">
                                                    <img src="{$album.imageurl}" width="64" height="64" border="0" alt="{$album.title|escape:'html'}" />
                                                </a>
                                            </div>
                                            <div class="usr_album">
                                                <div class="link">
                                                    <a href="/users/{$usr.login}/photos/{$album.type}{$album.id}.html">{$album.title}</a>
                                                </div>
                                                <div class="count">{$album.photos_count|spellcount:$LANG.PHOTO:$LANG.PHOTO2:$LANG.PHOTO10}</div>
                                                <div class="date">{$album.pubdate}</div>
                                            </div>
                                        </li>
                                    {/foreach}
                                 </ul>
                            </div>
                        {/if}

                        {if $usr.friends}
                            <div class="usr_friends_block usr_profile_block">
                                {if $usr.friends_total > 6}
                                    <div class="float_bar">
                                        <a href="/users/{$usr.id}/friendlist.html">{$LANG.ALL_FRIENDS}</a> ({$usr.friends_total})
                                    </div>
                                {/if}
                                <div class="usr_wall_header">
                                    {if !$myprofile}
                                        {$LANG.USER_FRIENDS}
                                    {else}
                                        {$LANG.MY_FRIENDS}
                                    {/if}
                                </div>
                                {$col="1"}
                                <table width="" cellpadding="5" cellspacing="0" border="0" class="usr_friends_list" align="left">
                                  {foreach key=tid item=friend from=$usr.friends}
                                  {if $col==1}<tr>{/if}
                                            <td align="center" valign="top">
                                                <div class="usr_friend_cell">
                                                    <div align="center"><a class="friend_link" href="{profile_url login=$friend.login}">{$friend.nickname}</a></div>
                                                    <div align="center"><a href="{profile_url login=$friend.login}"><img border="0" class="usr_img_small" src="{$friend.avatar}" /></a></div>
                                                    <div align="center">{$friend.flogdate}</div>
                                                </div>
                                            </td>

                                      {if $col==6} </tr> {$col="1"} {else} {$col=$col+1} {/if}
                                  {/foreach}
                                  {if $col>1}<td colspan="{math equation="x - 6 + 1" x=$col}">&nbsp;</td></tr>{/if}
                                </table>
                            </div>
                        {/if}

						{if $cfg.sw_wall}
							<div class="usr_wall usr_profile_block">
								<div class="usr_wall_header">
                                    {$LANG.USER_WALL}
                                    <div class="usr_wall_addlink" style="float:right">
                                        <a href="javascript:void(0)" id="addlink" class="ajaxlink" onclick="addWall('users', '{$usr.id}');return false;">
                                            <span>{$LANG.WRITE_ON_WALL}</span>
                                        </a>
                                    </div>
                                </div>
								<div class="usr_wall_body" style="clear:both">
                                    <div class="wall_body">{$usr.wall_html}</div>
                                </div>
							</div>
						{/if}
					</div>
				</div>

				{if $myprofile && $cfg.sw_feed}
					<div id="upr_feed">

					</div>
				{/if}

				{if $cfg.sw_clubs}
					<div id="upr_clubs">

					</div>
				{/if}

                {if $cfg.sw_awards}
					<div id="upr_awards">
						<div class="awards_list_link">
							<a href="/users/awardslist.html">{$LANG.HOW_GET_AWARD}</a>
						</div>
						{if $usr.awards}
                            {foreach key=tid item=aw from=$usr.awards}
                                <div class="usr_award_block">
                                  <table style="width:100%; margin-bottom:2px;" cellspacing="2">
                                    <tr>
                                      <td class="usr_com_title"><strong>{$aw.title}</strong>
                                      {if $is_admin}
                                        [<a href="/users/delaward{$aw.id}.html">{$LANG.DELETE}</a>]
                                      {/if}
                                      </td>
                                    </tr>
                                    <tr>
                                      <td class="usr_com_body">
                                        <table border="0" cellpadding="5" cellspacing="0">
                                          <tr>
                                            <td valign="top"><img src="/images/users/awards/{$aw.imageurl}" border="0" alt="{$aw.title|escape:'html'}"/></td>
                                            <td valign="top">{$aw.description}
                                              <div class="usr_award_date">{$aw.pubdate}</div></td>
                                          </tr>
                                        </table>
                                      </td>
                                    </tr>
                                  </table>
                                </div>
                            {/foreach}
						{/if}
					</div>
                {/if}

                {foreach key=id item=plugin from=$plugins}
                    <div id="upr_{$plugin.name}">{$plugin.html}</div>
                {/foreach}

			</div>
	</td>
  </tr>
</table>
