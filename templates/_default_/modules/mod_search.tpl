<form id="search_form"action="/search" method="GET" enctype="multipart/form-data" style="clear:both">
    <strong>{$LANG.SEARCH_ON_SITE}: </strong>
    <input type="text" name="query" id="query" size="40" value="" class="text-input" />
    <select name="look" style="width:100px" onchange="$('#search_form').submit();	">
            <option value="allwords" selected="selected">{$LANG.ALL_WORDS}</option>
            <option value="anyword" >{$LANG.ANY_WORD}</option>
            <option value="phrase" >{$LANG.PHRASE}</option>
    </select>
    <input type="submit" value="{$LANG.FIND}"/>
    <a href="javascript:" onclick="$('#from_search').fadeIn('slow');" class="ajaxlink">{$LANG.SEARCH_PARAMS}</a>
    <div id="from_search">
    <strong>{$LANG.WHERE_TO_FIND}:</strong>
	<table width="" border="0" cellspacing="0" cellpadding="3">
          {$col="1"}
            {foreach key=tid item=enable_component from=$enable_components}
                {if $col==1} <tr> {/if}
                <td width="">
                <label id="l_{$enable_component.link}" class="selected">
                	<input name="from_component[]" onclick="toggleInput('l_{$enable_component.link}')" type="checkbox" value="{$enable_component.link}" checked="checked" />
                    {$enable_component.title}</label></td>
                {if $col==5} </tr> {$col="1"} {else} {$col=$col+1} {/if}
            {/foreach}
            {if $col>1}
                <td colspan="{math equation="x - y + 1" x=$col y=5}">&nbsp;</td></tr>
            {/if}
        </table>
        <p><strong>{$LANG.PUBDATE}:</strong></p>
        <select name="from_pubdate" style="width:200px">
          <option value="" selected="selected">{$LANG.ALL}</option>
          <option value="d" >{$LANG.F_D}</option>
          <option value="w" >{$LANG.F_W}</option>
          <option value="m" >{$LANG.F_M}</option>
          <option value="y" >{$LANG.F_Y}</option>
        </select>
        <label id="order_by_date" class="selected">
                	<input name="order_by_date" onclick="toggleInput('order_by_date')" type="checkbox" value="1" checked="checked" />
                    {$LANG.SORT_BY_PUBDATE}</label>
        <div style="position:absolute; top:0; right:0; font-size:10px;">
        	<a href="javascript:" onclick="$('#from_search').fadeOut();" class="ajaxlink">{$LANG.HIDE}</a>
        </div>
        <div style="position:absolute; bottom:0; right:0; font-size:10px;">
        	<a href="javascript:" onclick="$('#search_form input:checkbox').prop('checked', true);$('#from_search label').addClass('selected');" class="ajaxlink">{$LANG.SELECT_ALL}</a> |
			<a href="javascript:" onclick="$('#search_form input:checkbox').prop('checked', false);$('#from_search label').removeClass('selected');" class="ajaxlink">{$LANG.REMOVE_ALL}</a>
        </div>
    </div>
</form>

<script type="text/javascript">
    function toggleInput(id){
        $('#from_search label#'+id).toggleClass('selected');
    }
</script>