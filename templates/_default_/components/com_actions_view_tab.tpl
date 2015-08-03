{if $actions}
{include file='com_actions_friends.tpl'}

{include file='com_actions_tab.tpl'}

{else}
    <p>{$LANG.FEED_DESC}</p>
    <p>{$LANG.FEED_EMPTY_TEXT}</p>
{/if}