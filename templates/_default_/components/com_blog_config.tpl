<form action="{$form_action}" method="post" name="cfgform" id="cfgform" style="margin-top:5px">
  <table width="100%" border="0" cellpadding="4" cellspacing="0">
	<tr>
	  <td width="240"><strong>{$LANG.BLOG_TITLE}: </strong></td>
	  <td><input name="title" type="text" id="title" class="text-input" value="{$blog.title|escape:'html'}" style="width:360px"/></td>
	</tr>
	<tr>
	  <td><strong>{$LANG.SHOW_BLOG}:</strong></td>
	  	<td>
			<select name="allow_who" id="allow_who" style="width:360px" class="text-input">
				<option value="all" selected="selected" {if ($blog.allow_who == 'all')} selected {/if}>{$LANG.TO_ALL}</option>
				<option value="friends" {if ($blog.allow_who == 'friends')} selected {/if}>{$LANG.TO_MY_FRIENDS}</option>
				<option value="nobody" {if ($blog.allow_who == 'nobody')} selected {/if}>{$LANG.TO_ONLY_ME}</option>
			</select>
		</td>
	</tr>
	<tr>
	  <td><strong>{$LANG.SHOW_CAT}</strong>: </td>
	  <td>
		  <select name="showcats" id="showcats" class="text-input">
			<option value="1" selected="selected" {if ($blog.showcats == 1)} selected {/if}>{$LANG.YES}</option>
			<option value="0" {if ($blog.showcats == 0)} selected {/if}>{$LANG.NO}</option>
		  </select>
	  </td>
	</tr>
{if $cfg.seo_user_access || $is_admin}
    <tr>
        <td valign="top"><strong>{$LANG.SEO_PAGETITLE}</strong><div class="hint">{$LANG.SEO_PAGETITLE_HINT}</div></td>
        <td>
            <input name="pagetitle" style="width:360px" class="text-input" value="{$blog.pagetitle|escape:'html'}" />
        </td>
    </tr>
    <tr>
        <td valign="top"><strong>{$LANG.SEO_METAKEYS}</strong></td>
        <td>
            <input name="meta_keys" style="width:360px" class="text-input" value="{$blog.meta_keys|escape:'html'}" />
        </td>
    </tr>
    <tr>
        <td valign="top"><strong>{$LANG.SEO_METADESCR}</strong><div class="hint">{$LANG.SEO_METADESCR_HINT}</div></td>
        <td>
            <textarea name="meta_desc" rows="3" style="width:360px" class="text-input">{$blog.meta_desc|escape:'html'}</textarea>
        </td>
    </tr>
 {/if}
  </table>
  <table width="100%" border="0" cellpadding="4" cellspacing="0">
	<tr>
	  <td width="240"><strong>{$LANG.BLOG_TYPE}: </strong></td>
	  <td>
		  <select name="ownertype" id="ownertype" onchange="selectOwnerType()" style="width:360px" class="text-input">
			<option value="single" {if ($blog.ownertype == 'single')} selected {/if}>{$LANG.PERSONAL} {if $is_restrictions && $cfg.min_karma_private>0}({$LANG.BLOG_KARMA_NEED} {$cfg.min_karma_private}){/if}</option>
			<option value="multi" {if ($blog.ownertype == 'multi')} selected {/if}>{$LANG.COLLECTIVE} {if $is_restrictions && $cfg.min_karma_public>0}({$LANG.BLOG_KARMA_NEED} {$cfg.min_karma_public}){/if}</option>
		  </select>
	  </td>
	</tr>
  </table>
  <table width="100%" border="0" cellpadding="4" cellspacing="0" id="multiblogcfg" style="display:{if $blog.ownertype=='single'}none;{else}block;{/if}">
	<tr>
	  <td width="240"><strong>{$LANG.PREMODER_POST}: </strong></td>
	  <td>
		  <select name="premod" id="premod" style="width:360px" class="text-input">
			  <option value="1" {if ($blog.premod == 1)} selected {/if}>{$LANG.ON}</option>
			  <option value="0" {if ($blog.premod == 0)} selected {/if}>{$LANG.OFF}</option>
		  </select>
	  </td>
	</tr>
	<tr>
	  <td><strong>{$LANG.WHO_CAN_WRITE_TO_BLOG}: </strong></td>
	  <td>
		  <select name="forall" id="forall" onchange="selectAuthorsType()" style="width:360px" class="text-input">
			  <option value="1" {if ($blog.forall == 1)} selected {/if}>{$LANG.ALL_USERS}</option>
			  <option value="0" {if ($blog.forall == 0)} selected {/if}>{$LANG.LIST_USERS}</option>
		  </select>
	  </td>
	</tr>
  </table>
  <table width="100%" border="0" cellspacing="0" cellpadding="4" id="multiuserscfg" style="margin:5px 0;display: {if $blog.ownertype=='single' || $blog.forall}none;{else}table;{/if}">
      <tr>
	  <td align="center" valign="top"><strong>{$LANG.CAN_WRITE_TO_BLOG}: </strong><br/>
		<select name="authorslist[]" size="15" multiple id="authorslist" style="width:200px" class="text-input">
			{$authors_list}
		</select>
	  </td>
	  <td align="center">
	  	  <div><input name="author_add" type="button" id="author_add" value="&lt;&lt;"></div>
		  <div><input name="author_remove" type="button" id="author_remove" value="&gt;&gt;" style="margin-top:4px"></div>
	  </td>
	  <td align="center" valign="top"><strong>{$LANG.ALL_USERS}:</strong><br/>
		<select name="userslist" size="15" multiple id="userslist" style="width:200px" class="text-input">
			{$users_list}
		</select>
	  </td>
	</tr>
  </table>
    <input type="hidden" name="goadd" value="1" />
    <input type="hidden" name="csrf_token" id="csrf_token" value="{csrf_token}" />
</form>
<div class="sess_messages" style="display:none">
  <div class="message_info" id="error_mess"></div>
</div>
<script type="text/javascript" src="/includes/jquery/jquery.form.js"></script>
<script type="text/javascript">
    $().ready(function() {
      $('#author_remove').click(function() {
            return !$('#authorslist option:selected').remove().appendTo('#userslist');
      });
      $('#author_add').click(function() {
            return !$('#userslist option:selected').remove().appendTo('#authorslist');
      });

    });
    function selectOwnerType(){
        var ot = $('#ownertype').val();
        if (ot == 'multi') {
            $('#multiblogcfg').show();
            if ($('#forall').val()==0){
                $('#multiuserscfg').show();
            }
        } else {
            $('#multiblogcfg').hide();
            $('#multiuserscfg').hide();
        }
    }
    function selectAuthorsType(){
        var ot = $('#forall').val();
        if (ot == '0') {
            $('#multiuserscfg').show();
        } else {
            $('#multiuserscfg').hide();
        }
    }
</script>