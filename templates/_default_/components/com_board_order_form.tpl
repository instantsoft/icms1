<form action="{$action_url}" method="POST" id="obform">
	<div class="photo_sortform">
		<table cellspacing="2" cellpadding="2" >
			<tr>
				<td >{$LANG.TYPE}: </td>
				<td >
					<select name="obtype" id="obtype" onchange="$('form#obform').submit();">
						<option value="all" {if (empty($btype))} selected {/if}>{$LANG.ALL_TYPE}</option>
						{$btypes}
					</select>
				</td>
				<td >{$LANG.CITY}: </td>
				<td >
					{$bcities}
				</td>
				<td >{$LANG.ORDER}: </td>
				<td >
					<select name="orderby" id="orderby">
						<option value="title" {if $orderby=='title'} selected {/if}>{$LANG.ORDERBY_TITLE}</option>
						<option value="pubdate" {if $orderby=='pubdate'} selected {/if}>{$LANG.ORDERBY_DATE}</option>
						<option value="hits" {if $orderby=='hits'} selected {/if}>{$LANG.ORDERBY_HITS}</option>
						<option value="obtype" {if $orderby=='obtype'} selected {/if}>{$LANG.ORDERBY_TYPE}</option>
						<option value="user_id" {if $orderby=='user_id'} selected {/if}>{$LANG.ORDERBY_AVTOR}</option>
					</select>
					<select name="orderto" id="orderto">';
						<option value="desc" {if $orderto=='desc'} selected {/if}>{$LANG.ORDERBY_DESC}</option>
						<option value="asc" {if $orderto=='asc'} selected {/if}>{$LANG.ORDERBY_ASC}</option>
					</select>
					<input type="submit" value="{$LANG.FILTER}" />
				</td>
			</tr>
		</table>
	</div>
</form>