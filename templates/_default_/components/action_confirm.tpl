<div class="con_heading">{$confirm.title}</div>
<p style="font-size:18px">{$confirm.text}</p>
<div style="margin-top:20px">
    <form action="{$confirm.action|escape:html|default:''}" method="{$confirm.method|default:'POST'}">
            {$confirm.other}
            <input type="hidden" name="csrf_token" value="{csrf_token}" />
            <input style="font-size:24px; width:100px"
                   type="{$confirm.yes_button.type|default:'submit'}"
                   name="{$confirm.yes_button.name|default:'go'}"
                   value="{$confirm.yes_button.title|default:$LANG.YES}"
                   onclick="{$confirm.yes_button.onclick|default:'true'}"
            />
            <input style="font-size:24px; width:100px"
                   type="{$confirm.no_button.type|default:'button'}"
                   name="{$confirm.no_button.name|default:'cancel'}"
                   value="{$confirm.no_button.title|default:$LANG.NO}"
                   onclick="{$confirm.no_button.onclick|default:'window.history.go(-1)'}"
            />
	</form>
</div>