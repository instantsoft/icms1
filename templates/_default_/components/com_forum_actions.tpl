<div class="float_bar">{if $user_id}<a href="/forum/my_activity.html">{$LANG.MY_ACTIVITY}</a> | {/if}{if $do == 'latest_posts'}<a href="/forum/latest_thread">{$LANG.NEW_THREADS}</a>{else}<a href="/forum/latest_posts">{$LANG.LATEST_POSTS}</a>{/if} | <a href="/forum">{$LANG.FORUMS}</a></div>

<h1 class="con_heading">{$pagetitle} ({$total})</h1>
{if $actions}
    <div class="actions_list">
        {foreach key=aid item=action from=$actions}
            {if $action.item_date}
                <h3>{$action.item_date}</h3>
            {/if}
            <div class="action_entry act_{$action.name}">
                <div class="action_date{if $action.is_new && $user_id != $action.user_id} is_new{/if}">{$action.pubdate} {$LANG.BACK}</div>
                <div class="action_title">
                    <a href="{$action.user_url}" class="action_user">{$action.user_nickname}</a>
                    {if $action.message}
                        {$action.message}{if $action.description}:{/if}
                    {else}
                        {if $action.description}
                            &rarr; {$action.description}
                        {/if}
                    {/if}
                </div>
                {if $action.message}
                    {if $action.description}
                        <div class="action_details">{$action.description}</div>
                    {/if}
                {/if}
            </div>
        {/foreach}
    </div>
    {$pagebar}
{/if}