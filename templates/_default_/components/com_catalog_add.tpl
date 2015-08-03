<div class="con_heading">
    {if $do=='add_item'}{$LANG.ADD_ITEM}{/if}
    {if $do=='edit_item'}{$LANG.EDIT_ITEM}{/if}
</div>

<div id="configtabs">

    <div id="form">
        <form id="add_form" method="post" action="/catalog/{$cat_id}/submit.html" enctype="multipart/form-data">
        <table cellpadding="5" cellspacing="0" style="margin-bottom:10px" width="100%">
            <tr>
                <td width="210">
                    <strong>{$LANG.TITLE}:</strong>
                </td>
                <td><input type="text" name="title" id="title" class="text-input" value="{$item.title|escape:'html'}" style="width:300px"/></td>
            </tr>
            {if $is_admin}
            <tr>
                <td width="210">
                    <strong>{$LANG.CAT}:</strong>
                </td>
                <td><select style="width:300px" class="text-input" name="new_cat_id" id="cat_id" >{$cats}</select></td>
            </tr>
            {/if}
            <tr>
                <td width="">
                    <strong>{$LANG.IMAGE}:</strong>
                </td>
                <td>
                    {if $do=='edit_item' && $item.imageurl}
                        <div style="margin-bottom:4px;">
                            <a href="/images/catalog/{$item.imageurl}" target="_blank">{$item.imageurl}</a>
                        </div>
                    {/if}
                    <table border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td><input name="imgfile" type="file" id="imgfile" style="width:300px" class="text-input" /></td>
                            {if $do=='edit_item' && $item.imageurl}
                                <td style="padding-left:15px">
                                    <label>
                                        <input type="checkbox" value="1" name="delete_img" />
                                        {$LANG.DELETE}
                                    </label>
                                </td>
                            {/if}
                        </tr>
                    </table>
                </td>
            </tr>
            {if $cat.view_type=='shop'}
            <tr>
                <td width="">
                    <strong>{$LANG.PRICE}:</strong>
                </td>
                <td>
                    <input type="text" class="text-input" name="price" value="{$item.price|escape:'html'}" style="width:300px"/>
                </td>
            </tr>
            <tr>
                <td width="">
                    <strong>{$LANG.CAN_MANY}:</strong>
                </td>
                <td>
                    <label><input type="radio" name="canmany" value="1" {if $item.canmany}checked="checked"{/if}> {$LANG.YES} </label>
                    <label><input type="radio" name="canmany" value="0" {if !$item.canmany}checked="checked"{/if}> {$LANG.NO} </label>
                </td>
            </tr>
            {/if}
            <tr>
                <td width="">
                    <strong>{$LANG.TAGS}:</strong><br/>
                    <span class="hint">{$LANG.KEYWORDS}</span>
                </td>
                <td>
                    <input type="text" name="tags" class="text-input" value="{$item.tags|escape:'html'}" style="width:300px"/>
                </td>
            </tr>
        {foreach key=id item=field from=$fields}
            <tr>
                {if $field.ftype=='link' || $field.ftype == 'text'}
                <td valign="top">
                    <strong>{$field.title}:</strong>
                    {if $field.ftype=='link'} <br/><span class="hint">{$LANG.TYPE_LINK}</span>{/if}
                    {if $field.makelink} <br/><span class="hint">{$LANG.COMMA_SEPARATE}</span>{/if}
                </td>
                <td>
                    <input style="width:300px" name="fdata[{$id}]" type="text" class="text-input" value="{if $field.value}{$field.value|escape:'html'}{/if}"/>
                </td>
                {else}
                    <td valign="top"><strong>{$field.title}:</strong></td>
                    <td>
                        {wysiwyg name="fdata[$id]" value=$field.value height=300 width='98%'}
                    </td>
                {/if}
            </tr>
        {/foreach}
        {if $is_admin}
            <tr>
                <td width="">
                    <strong>{$LANG.SEO_KEYWORDS}:</strong><br/>
                    <span class="hint">{$LANG.SEO_KEYWORDS_HINT}</span>
                </td>
                <td>
                    <input type="text" name="meta_keys" class="text-input" value="{$item.meta_keys|escape:'html'}" style="width:300px"/>
                </td>
            </tr>
            <tr>
                <td width="">
                    <strong>{$LANG.SEO_DESCRIPTION}:</strong>
                </td>
                <td>
                    <input type="text" name="meta_desc" class="text-input" value="{$item.meta_desc|escape:'html'}" style="width:300px"/>
                </td>
            </tr>
            <tr>
                <td width="">
                    <strong>{$LANG.IS_COMMENTS}:</strong>
                </td>
                <td>
                    <label><input type="radio" name="is_comments" value="1" {if $item.is_comments}checked="checked"{/if}> {$LANG.YES} </label>
                    <label><input type="radio" name="is_comments" value="0" {if !$item.is_comments}checked="checked"{/if}> {$LANG.NO} </label>
                </td>
            </tr>
        {/if}
        </table>
        {if $cfg.premod && !$is_admin}
            <p style="margin-top:15px;color:red">
                {$LANG.ITEM_PREMOD_NOTICE}
            </p>
        {/if}
        <p style="margin-top:15px">
            <input type="hidden" name="opt" value="{if $do=='add_item'}add{else}edit{/if}" />
            {if $do=='edit_item'}
                <input type="hidden" id="item_id" name="item_id" value="{$item.id}" />
            {/if}
            <input type="submit" name="submit" value="{$LANG.SAVE}" style="font-size:18px" />
            <input type="button" name="back" value="{$LANG.CANCEL}"  style="font-size:18px" onClick="window.history.go(-1)" />
        </p>
        </form>
    </div>

</div>
