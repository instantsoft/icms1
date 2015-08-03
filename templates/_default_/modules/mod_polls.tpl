{if !$is_ajax}<div id="poll_module_{$module_id}">{/if}

{if !$is_voted}

    <p class="mod_poll_title"><strong>{$poll.title}</strong></p>
    <form action="/polls/vote" method="post" id="mod_poll_submit_form">
    <input type="hidden" name="poll_id" value="{$poll.id}" />
    <input type="hidden" name="module_id" value="{$module_id}" />
    <input type="hidden" name="csrf_token" value="{csrf_token}" />
    <table class="mod_poll_answers">
    {foreach key=answer item=num from=$poll.answers}
        <tr>
          <td class="mod_poll_answer">
              <label>
                  <input name="answer" type="radio" value="{$answer|escape:'html'}" /> {$answer}{if $cfg.shownum} ({$num}){/if}
              </label>
          </td>
        </tr>
     {/foreach}
     </table>
     <div align="center"><input type="button" id="mod_poll_submit" class="mod_poll_submit" onclick="pollSubmit();" value="{$LANG.POLLS_VOTE} {if $cfg.shownum}({$poll.total_answers}){/if}"></div>
    </form>

{else}

    <p class="mod_poll_title"><strong>{$poll.title}</strong></p>

    {foreach key=answer item=num from=$poll.answers}

        {$percent = $num/$poll.total_answers*100}

        <span class="mod_poll_gauge_title">{$answer} ({$num})</span>
        {if $percent > 0}
            <table class="mod_poll_gauge" width="{$percent|ceil}%"><tr><td></td></tr></table>
        {else}
            <table class="mod_poll_gauge" width="5"><tr><td></td></tr></table>
        {/if}

    {/foreach}

{/if}

{if !$is_ajax}
</div>
<script type="text/javascript" src="/includes/jquery/jquery.form.js"></script>

<script type="text/javascript">
function pollSubmit(){
    $('#mod_poll_submit').prop('disabled', true);
    var options = {
        success: loadPoll
    };
    $('#mod_poll_submit_form').ajaxSubmit(options);
}
function loadPoll(result, statusText, xhr, $form){
    var module_id = {$module_id};
	if(statusText == 'success'){
		if(result.error == false){
            core.alert(result.text, '{$LANG.NOTICE}!');
            $.post('/modules/mod_polls/load.php', { module_id: module_id }, function(data){
                $('#poll_module_'+module_id).html(data);
            });
            setTimeout('core.box_close()', 900);
		} else {
            core.alert(result.text, '{$LANG.ATTENTION}!');
            $('#mod_poll_submit').prop('disabled', false);
        }
	}

}
</script>
{/if}