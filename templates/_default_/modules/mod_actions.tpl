{if $actions}
    <div class="actions_list">
        {foreach key=aid item=action from=$actions}
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
    {if $cfg.show_link}
    <p>
        <a href="/actions" class="mod_act_all">{$LANG.ALL_ACTIONS}</a>
    </p>
    {/if}
{/if}