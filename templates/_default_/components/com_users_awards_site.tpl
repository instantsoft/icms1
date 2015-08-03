<div class="con_heading">{$LANG.SITE_AWARDS}</div>
{if $aws}
    <table width="100%" cellspacing="2" cellpadding="3" class="usr_aw_table">
    {foreach key=tid item=aw from=$aws}
        <tr>
            <td width="32" valign="top">
                <img class="usr_aw_img" src="/images/users/awards/{$aw.imageurl}" border="0"/>
            </td>
            <td width="30%" valign="top">
                <div class="usr_aw_title"><strong>{$aw.title}</strong></div>
                <div class="usr_aw_desc">{$aw.description}</div>

                <table border="0" cellspacing="0" cellpadding="3" class="usr_aw_dettable">
                    {if $aw.p_comment}
                        <tr>
                            <td><img src="/images/autoawards/p_comment.gif" width="16" height="16" /></td>
                            <td>
                              {$aw.p_comment}
                             {$LANG.COMMENTS}</td>
                        </tr>
                    {/if}
                    {if $aw.p_forum}
                        <tr>
                            <td><img src="/images/autoawards/p_forum.gif" width="16" height="16" /></td>
                            <td>
                              {$aw.p_forum}
                             {$LANG.MESS_IN_FORUM}</td>
                        </tr>
                    {/if}
                    {if $aw.p_content}
                        <tr>
                            <td><img src="/images/autoawards/p_forum.gif" width="16" height="16" /></td>
                            <td>
                              {$aw.p_content}
                             {$LANG.PUBLISHED_ARTICLES}</td>
                        </tr>
                    {/if}
                    {if $aw.p_blog}
                        <tr>
                            <td><img src="/images/autoawards/p_blog.gif" width="16" height="16" /></td>
                            <td>
                              {$aw.p_blog}
                             {$LANG.POSTS_IN_BLOG}</td>
                        </tr>
                    {/if}
                    {if $aw.p_karma}
                        <tr>
                            <td><img src="/images/autoawards/p_karma.gif" width="16" height="16" /></td>
                            <td>
                              {$aw.p_karma}
                             {$LANG.KARMA_POINTS}</td>
                        </tr>
                    {/if}
                    {if $aw.p_photo}
                        <tr>
                            <td><img src="/images/autoawards/p_photo.gif" width="16" height="16" /></td>
                            <td>
                              {$aw.p_photo}
                             {$LANG.PHOTOS_IN_ALBUMS}</td>
                        </tr>
                    {/if}
                    {if $aw.p_privphoto}
                        <tr>
                            <td><img src="/images/autoawards/p_privphoto.gif" width="16" height="16" /></td>
                            <td>
                              {$aw.p_privphoto}
                             {$LANG.PHOTOS_IN_PRIVATE_ALBUM}</td>
                        </tr>
                    {/if}
                </table>
            </td>
            <td valign="top" class="usr_aw_who">
                <div class="usr_aw_users"><strong>{$LANG.AWARD_HAVES}:</strong></div>
                <div class="usr_aw_userslist">{$aw.uhtml}</div>
            </td>

        </tr>
    {/foreach}
    </table>
{else}
    <p>{$LANG.NOT_AWARDS_ON_SITE}</p>
{/if}
