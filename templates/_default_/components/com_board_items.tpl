{if $page_title}<h1 class="con_heading">{$page_title}</h1>{/if}
{if $order_form}{$order_form}{/if}
<div class="board_gallery">
	{if $items}
		<table width="100%" cellpadding="3" cellspacing="0" border="0">
			{$col="1"}
            {$is_moder="0"}
			{foreach key=tid item=con from=$items}
				{if $col==1} <tr> {/if}
				<td valign="top" width="{$colwidth}%">
                    <div class="bd_item{if $con.is_vip}_vip{/if}">
					<table width="100%" height="" cellspacing="" cellpadding="0" class="b_table_tr">
						<tr>
							{if $cfg.photos}
								<td width="30" valign="top">
									<img class="bd_image_small" src="/images/board/small/{$con.file}" border="0" alt="{$con.title|escape:'html'}"/>
								</td>
							{/if}
							<td valign="top">
                                {if $con.moderator}
                                {$is_moder="1"}
                                <div class="bd_moderate_link">
                                    <a href="/board/edit{$con.id}.html">{$LANG.EDIT}</a> |
                                    <a href="/board/delete{$con.id}.html">{$LANG.DELETE}</a>
                                </div>
                                {/if}
								<div class="bd_title">
									<a href="/board/read{$con.id}.html" title="{$con.title|escape:'html'}">{$con.title}</a>
								</div>
								<div class="bd_text">
                                    {$con.content|strip_tags|truncate:250}
								</div>
								<div class="bd_item_details">
                                		{if $cat.showdate && $con.published}
											<span class="bd_item_date">{$con.fpubdate}</span>
                                        {/if}
                                        {if !$con.published && $con.is_overdue}
                                            <span class="bd_item_status_bad">{$LANG.ADV_EXTEND_INFO}</span>
                                        {elseif !$con.published}
                                            <span class="bd_item_status_bad">{$LANG.WAIT_MODER}</span>
                                        {/if}
                                        <span class="bd_item_hits">{$con.hits}</span>
										{if $con.city}
											<span class="bd_item_city"><a href="/board/city/{$con.enc_city|escape:'html'}">{$con.city}</a></span>
										{/if}
										{if $con.nickname}
											<span class="bd_item_user"><a href="{profile_url login=$con.login}">{$con.nickname}</a></span>
                                        {else}
                                        	<span class="bd_item_user">{$LANG.BOARD_GUEST}</span>
										{/if}
                                        {if $con.cat_title}
                                        	<span class="bd_item_cat"><a href="/board/{$con.category_id}">{$con.cat_title}</a></span>
                                        {/if}
								</div>
							</td>
						</tr>
					</table>
                    </div>
				</td>
				{if $col==$maxcols} </tr> {$col="1"} {else} {$col=$col+1} {/if}
			{/foreach}
			{if $col>1}
				<td colspan="{math equation="x - y + 1" x=$col y=$maxcols}">&nbsp;</td></tr>
			{/if}
		</table>
		{$pagebar}
	{elseif $cat.id != $root_id}
		<p>{$LANG.ADVS_NOT_FOUND}</p>
	{/if}
</div>
{if $is_moder}
<script type="text/javascript" language="JavaScript">
	$(document).ready(function(){
		$('.b_table_tr .bd_moderate_link').css({ opacity:0.3, filter:'alpha(opacity=30)' });
		$('.b_table_tr').hover(
			function() {
				$(this).find('.bd_moderate_link').css({ opacity:1.0, filter:'alpha(opacity=100)' });
			},
			function() {
				$(this).find('.bd_moderate_link').css({ opacity:0.3, filter:'alpha(opacity=30)' });
			}
		);
	});
</script>
{/if}