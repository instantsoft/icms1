<div class="cmm_heading">
	{$labels.comments} (<span id="comments_count">{$comments_count}</span>)
</div>

<div class="cm_ajax_list">
{if !$cfg.cmm_ajax}
	{$html}
{/if}
</div>

<a name="c"></a>
<div class="cmm_links">
    <span id="cm_add_link0" class="cm_add_link add_comment">
        <a href="javascript:void(0);" onclick="{$add_comment_js}" class="ajaxlink">{$labels.add}</a>
    </span>
    {if $cfg.subscribe}
        {if $is_user}
            {if !$user_subscribed}
            <span class="subscribe">
                <a href="/subscribe/{$target}/{$target_id}">{$LANG.SUBSCRIBE_TO_NEW}</a>
            </span>
            {else}
            <span class="unsubscribe">
                <a href="/unsubscribe/{$target}/{$target_id}">{$LANG.UNSUBSCRIBE}</a>
            </span>
            {/if}
        {/if}
    {/if}
    {if $comments_count}
        <span class="cmm_rss">
            <a href="/rss/comments/{$target}-{$target_id}/feed.rss">{$labels.rss}</a>
        </span>
    {/if}
</div>
<div id="cm_addentry0"></div>
<script type="text/javascript">
    var target_author_can_delete = {$target_author_can_delete};
{if $cfg.cmm_ajax}
    var anc = '';
    if (window.location.hash){
        anc = window.location.hash;
    }
    $(function(){
        loadComments('{$target}', {$target_id}, anc);
    });
{/if}
</script>