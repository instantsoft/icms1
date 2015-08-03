<table cellspacing="2" cellpadding="3" align="right">
    <tr>
        {if !$thread.closed}
        <td width="16">
            <img src="/templates/{template}/images/icons/add.png"/>
        </td>
        <td>
            <a href="/forum/reply{$thread.id}.html"><strong>{$LANG.NEW_MESSAGE}</strong></a>
        </td>
        {if !$is_subscribed}
            <td width="16"><img src="/templates/{template}/images/icons/subscribe.png"/></td>
            <td><a href="/forum/subscribe{$thread.id}.html">{$LANG.SUBSCRIBE_THEME}</a></td>
        {else}
            <td width="16"><img src="/templates/{template}/images/icons/unsubscribe.png"/></td>
            <td><a href="/forum/unsubscribe{$thread.id}.html">{$LANG.UNSUBSCRIBE}</a></td>
        {/if}
        {else}
            <td><strong>{$LANG.THREAD_CLOSE}</td>
        {/if}

        {if $is_admin || $is_moder}
            <td width="16" class="closethread" {if $thread.closed}style="display: none"{/if}>
                <img src="/templates/{template}/images/icons/forum/toolbar/lock_open.png"/>
            </td>
            <td class="closethread" {if $thread.closed}style="display: none"{/if}>
                <a class="ajaxlink" href="javascript:" onclick="forum.ocThread({$thread.id}, 1);">{$LANG.CLOSE}</a>
            </td>
            <td width="16" class="openthread" {if !$thread.closed}style="display: none"{/if}>
                <img src="/templates/{template}/images/icons/forum/toolbar/lock.png"/>
            </td>
            <td class="openthread" {if !$thread.closed}style="display: none"{/if}>
                <a class="ajaxlink" href="javascript:" onclick="forum.ocThread({$thread.id}, 0);">{$LANG.OPEN}</a>
            </td>

            <td width="16" class="pinthread" {if $thread.pinned}style="display: none"{/if}>
                <img src="/templates/{template}/images/icons/forum/toolbar/pinthread.png"/>
            </td>
            <td class="pinthread" {if $thread.pinned}style="display: none"{/if}>
                <a class="ajaxlink" href="javascript:" onclick="forum.pinThread({$thread.id}, 1);">{$LANG.PIN}</a>
            </td>
            <td width="16" class="unpinthread" {if !$thread.pinned}style="display: none"{/if}>
                <img src="/templates/{template}/images/icons/forum/toolbar/unpinthread.png"/>
            </td>
            <td class="unpinthread" {if !$thread.pinned}style="display: none"{/if}>
                <a class="ajaxlink" href="javascript:" onclick="forum.pinThread({$thread.id}, 0);">{$LANG.UNPIN}</a>
            </td>

            <td width="16"><img src="/templates/{template}/images/icons/move.png"/></td>
            <td><a class="ajaxlink" href="javascript:" onclick="forum.moveThread({$thread.id});">{$LANG.MOVE}</a></td>
        {/if}
        {if $is_admin || $is_moder || $thread.is_mythread}
            <td width="16"><img src="/templates/{template}/images/icons/edit.png"/></td>
            <td><a class="ajaxlink" href="javascript:" onclick="forum.renameThread({$thread.id});">{$LANG.RENAME}</a></td>
        {/if}
        {if $is_admin || $is_moder}
            <td width="16"><img src="/templates/{template}/images/icons/delete.png"/></td>
            <td><a class="ajaxlink" href="javascript:" onclick="forum.deleteThread({$thread.id}, '{csrf_token}');">{$LANG.DELETE}</a></td>
        {/if}
        <td width="16"><img src="/templates/{template}/images/icons/forum/toolbar/back.png"/></td>
        <td><a href="/forum/{$forum.id}">{$LANG.BACKB}</a></td>
    </tr>
</table>