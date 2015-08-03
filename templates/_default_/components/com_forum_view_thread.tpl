<h1 class="con_heading" id="thread_title">{$thread.title}</h1>

<div id="thread_description" {if !$thread.description}style="display: none"{/if}>{$thread.description}</div>

{if $user_id}
<table width="100%" cellspacing="0" cellpadding="5"  class="forum_toolbar"><tr>
    <td width="5">&nbsp;</td>
    <td class="forum_toollinks">
        {include file='com_forum_toolbar.tpl'}
    </td>
</tr></table>
{/if}

{if $thread_poll}
    <div id="thread_poll">{include file='com_forum_thread_poll.tpl'}</div>
{/if}

<table class="posts_table" width="100%" cellspacing="2" cellpadding="5" border="0" bordercolor="#999999">
    {foreach key=pid item=post from=$posts}
    <tr>
        <td colspan="2" class="darkBlue-LightBlue">
            <div class="post_date">{if $post.pinned && $num > 1}<img src="/templates/{template}/images/icons/forum/sticky.png" width="14px;" alt="{$LANG.ATTACHED_MESSAGE}" title="{$LANG.ATTACHED_MESSAGE}" />  {/if}<strong><a name="{$post.id}" href="/forum/thread{$thread.id}-{$page}.html#{$post.id}">#{$num}</a></strong> - {$post.fpubdate}, {$post.wday}</div>
            {if $user_id && !$thread.closed}
                <div class="msg_links">
                    <a href="javascript:" onclick="forum.addQuoteText(this);return false;" rel="{$post.nickname|escape:html}" class="ajaxlink" title="{$LANG.ADD_SELECTED_QUOTE}">{$LANG.ADD_QUOTE_TEXT}</a> | <a href="/forum/thread{$thread.id}-quote{$post.id}.html" title="{$LANG.REPLY_FULL_QUOTE}">{$LANG.REPLY}</a>
                    {if $is_admin || $is_moder || $post.is_author_can_edit}
                        | <a href="/forum/editpost{$post.id}-{$page}.html">{$LANG.EDIT}</a>
                        {if $num > 1}
                            {if $is_admin || $is_moder}
                                | <a href="javascript:" onclick="forum.movePost('{$thread.id}','{$post.id}');return false;" class="ajaxlink" title="{$LANG.MOVE_POST}">{$LANG.MOVE}</a>
                                {if !$post.pinned}
                                | <a href="/forum/pinpost{$thread.id}-{$post.id}.html">{$LANG.PIN}</a>
                                {else}
                                | <a href="/forum/unpinpost{$thread.id}-{$post.id}.html">{$LANG.UNPIN}</a>
                                {/if}
                            {/if}
                            | <a href="javascript:" class="ajaxlink" onclick="forum.deletePost({$post.id}, '{csrf_token}', {$page});">{$LANG.DELETE}</a>
                        {/if}
                    {/if}
                </div>
            {/if}
        </td>
    </tr>
    <tr class="posts_table_tr">
        <td class="post_usercell" width="140" align="center" valign="top" height="150">
            <div>
                <a class="post_userlink" href="javascript:" onclick="addNickname(this);return false;" title="{$LANG.ADD_NICKNAME}" rel="{$post.nickname|escape:html}" >{$post.nickname|escape:html}</a>
            </div>
            <div class="post_userrank">
                {if $post.userrank.group}
                    <span class="{$post.userrank.class}">{$post.userrank.group}</span>
                {/if}
                {if $post.userrank.rank}
                    <span class="{$post.userrank.class}">{$post.userrank.rank}</span>
                {/if}
            </div>
            <div class="post_userimg">
                <a href="{profile_url login=$post.login}" title="{$LANG.GOTO_PROFILE}"><img border="0" class="usr_img_small" src="{$post.avatar_url}" alt="{$post.nickname|escape:html}" /></a>
                {if $post.user_awards}
                    <div class="post_userawards">
                        {foreach key=aid item=award from=$post.user_awards}
                            <img src="/images/icons/award.gif" border="0" alt="{$award.title|escape:html}" title="{$award.title|escape:html}"/>
                        {/foreach}
                    </div>
                {/if}
            </div>
            <div class="post_usermsgcnt">{$LANG.MESSAGES}: {$post.post_count}</div>
            {if $post.city}
                <div class="post_usermsgcnt">{$post.city}</div>
            {/if}
            <div>{$post.flogdate}</div>
        </td>
        <td width="" class="post_msgcell" align="left" valign="top">
        {if $thread.closed || !$user_id || $post.is_author || $post.is_voted}
            <div class="votes_links">{$post.rating|rating}</div>
        {else}
            <div class="votes_links" id="votes{$post.id}">
                <table border="0" cellpadding="0" cellspacing="0"><tr>
                <td>{$post.rating|rating}</td>
                <td><a href="javascript:void(0);" onclick="forum.votePost({$post.id}, -1);"><img border="0" alt="-" src="/templates/{template}/images/icons/comments/vote_down.gif" style="margin-left:8px"/></a></td>
                <td><a href="javascript:void(0);" onclick="forum.votePost({$post.id}, 1);"><img border="0" alt="+" src="/templates/{template}/images/icons/comments/vote_up.gif" style="margin-left:2px"/></a></td>
                </tr></table>
            </div>
        {/if}
            <div class="post_content">{$post.content_html}</div>
            {if $post.attached_files && $cfg.fa_on}
                <div id="attached_files_{$post.id}">
                {include file='com_forum_attached_files.tpl'}
                </div>
            {/if}
            {if $post.edittimes}
                <div class="post_editdate">{$LANG.EDITED}: {$post.edittimes|spellcount:$LANG.COUNT1:$LANG.COUNT2:$LANG.COUNT1} ({$LANG.LAST_EDIT}: {$post.peditdate})</div>
            {/if}
            {if $post.signature_html}
                <div class="post_signature">{$post.signature_html}</div>
            {/if}
        </td>
    </tr>
    {$num=$num+1}
    {/foreach}
</table>
{if $page == $lastpage}<a name="new"></a>{/if}

{if $user_id}
<table width="100%" cellspacing="0" cellpadding="5"  class="forum_toolbar"><tr>
    <td><a href="#">{$LANG.GOTO_BEGIN_PAGE}</a></td>
    <td class="forum_toollinks">
        {include file='com_forum_toolbar.tpl'}
    </td>
</tr>
</table>
{/if}

<div class="forum_navbar">
    <table width="100%"><tr>
        <td align="left">
            <table cellpadding="5" cellspacing="0" border="0" align="left" style="margin-left:auto;margin-right:auto"><tr>
                {if $prev_thread}
                    <td align="right" width="">
                        <div>&larr; <a href="/forum/thread{$prev_thread.id}.html" title="{$LANG.PREVIOUS_THREAD}">{$prev_thread.title|truncate:30}</a></div>
                    </td>
                {/if}
                {if $prev_thread && $next_thread}<td>|</td>{/if}
                {if $next_thread}
                    <td align="left" width="">
                        <div><a href="/forum/thread{$next_thread.id}.html" title="{$LANG.NEXT_THREAD}">{$next_thread.title|truncate:30}</a> &rarr;</div>
                    </td>
                {/if}
            </tr></table>
        </td>
        <td width="150" align="right">{$LANG.GOTO_FORUM}: </td>
        <td width="220" align="right">
            <select name="goforum" id="goforum" style="width:220px; margin:0px" onchange="window.location.href = '/forum/' + $(this).val();">
            {foreach key=fid item=item from=$forums}
                {if $item.cat_title != $last_cat_title}
                    {if $last_cat_title}</optgroup>{/if}
                    <optgroup label="{$item.cat_title|escape:html}">
                {/if}
                <option value="{$item.id}" {if $item.id == $forum.id} selected="selected" {/if}>{$item.title}</option>
                {if $item.sub_forums}
                    {foreach key=sid item=sub_forum from=$item.sub_forums}
                        <option value="{$sub_forum.id}" {if $sub_forum.id == $forum.id} selected="selected" {/if}>--- {$sub_forum.title}</option>
                    {/foreach}
                {/if}
                {$last_cat_title=$item.cat_title}
            {/foreach}
            </optgroup>
            </select>
        </td>
    </tr></table>
</div>

<div style="float: right;margin: 8px 0 0;">{$pagebar}</div>

{if $cfg.fast_on && !$thread.closed}
<div class="forum_fast">
    <div class="forum_fast_header">{$LANG.FAST_ANSWER}</div>
    {if $user_id && $is_can_add_post}
        {if $cfg.fast_bb}
            <div class="usr_msg_bbcodebox">
                {$bb_toolbar}
            </div>
            {$smilies}
        {/if}
        <div class="forum_fast_form">
            <form action="/forum/reply{$thread.id}.html" method="post" id="msgform">
                <input type="hidden" name="gosend" value="1" />
                <input type="hidden" name="csrf_token" value="{csrf_token}" />
                <div class="cm_editor">
                    <textarea id="message" name="message" rows="7"></textarea>
                </div>
                <div class="forum_fast_submit" style="float:right;padding:5px;"><input type="button" value="{$LANG.SEND}" onclick="$(this).prop('disabled', true);$('#msgform').submit();" /></div>
                {if $is_admin || $is_moder || $thread.is_mythread}
                    <div style="float:right;padding:8px;">
                        <label><input type="checkbox" name="fixed" value="1" /> {$LANG.TOPIC_FIXED_LABEL}</label>
                    </div>
                {/if}
            </form>
        </div>
    {else}
        <div style="padding:5px">{$LANG.FOR_WRITE_ON_FORUM}.</div>
    {/if}
</div>
{/if}

{if $user_id}
<script type="text/javascript" language="JavaScript">
    $(document).ready(function(){
        $('.darkBlue-LightBlue .msg_links').css({ opacity:0.4, filter:'alpha(opacity=40)' });
        $('.posts_table_tr').hover(
            function() {
                $(this).prev().find('.msg_links').css({ opacity:1.0, filter:'alpha(opacity=100)' });
            },
            function() {
                $(this).prev().find('.msg_links').css({ opacity:0.4, filter:'alpha(opacity=40)' });
            }
        );
        $('.msg_links').hover(
            function() {
                $(this).css({ opacity:1.0, filter:'alpha(opacity=100)' });
            },
            function() {
                $(this).css({ opacity:0.4, filter:'alpha(opacity=40)' });
            }
        );
    });
</script>
{/if}