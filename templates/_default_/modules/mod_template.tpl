<form name="templform" action="/modules/mod_template/set.php" method="post">
    <select name="template" id="template" style="width:100%">
        <option value="0">{$LANG.TEMPLATE_DEFAULT}</option>
        {foreach key=id item=template from=$templates}
            <option value="{$template}" {if $template == $current_template}selected="selected"{/if}>{$template}</option>
        {/foreach}
    </select><br/>
    <input style="margin-top:5px" type="submit" value="{$LANG.TEMPLATE_CHOOSE}"/>
</form>