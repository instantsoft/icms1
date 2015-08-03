<h1 class="con_heading">{$pagetitle}</h1>
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
{else}
<p>{$LANG.EVENTS_NOT_FOUND}</p>
{/if}