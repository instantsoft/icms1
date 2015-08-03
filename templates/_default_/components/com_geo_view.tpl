<style type="text/css">
html #popup_container .popup_body {
    width: 333px;
}
#geo_window {
    height: 93px;
    margin: 10px;
}
.list select {
    margin: 0 0 10px;
    padding: 3px;
    width: 300px;
}
</style>
<div id="geo_window">
    <div class="list">
        {html_options name=countries options=$countries selected=$country_id onchange="geo.changeParent(this, 'regions')"}
    </div>

    <div class="list" {if !$city_id}style="display:none"{/if}>
        {html_options name=regions options=$regions selected=$region_id onchange="geo.changeParent(this, 'cities')"}
    </div>

    <div class="list" {if !$city_id}style="display:none"{/if}>
        {html_options name=cities options=$cities selected=$city_id onchange="geo.changeCity(this)"}
    </div>
</div>
{if $country_id && !$city_id}
    <script type="text/javascript">
    $(function(){
        $('#geo_window select[name=countries]').trigger('change');
    });
    </script>
{/if}