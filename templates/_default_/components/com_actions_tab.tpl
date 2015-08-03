{if $actions}
<div class="actions_list" id="actions_list">

<p><strong>{if $user_id}{$LANG.ACTIONS_USERS} "<a href="{$user.user_url}">{$user.user_nickname}</a>"{else}{$LANG.ALL_ACTIONS_FR}{/if}, {$LANG.SHOWN_LAST} {$cfg.perpage_tab}.</strong></p>

        {foreach key=aid item=action from=$actions}
            {if $action.item_date}
                <h3>{$action.item_date}</h3>
            {/if}
            <div class="action_entry act_{$action.name}">
                <div class="action_date{if $action.is_new} is_new{/if}">{$action.pubdate} {$LANG.BACK}</div>
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
{else}
    <p>{$LANG.ACTIONS_NOT_FOUND}.</p>
{/if}
<input name="user_id" type="hidden" value="" />