$(function(){
  geo = {
    viewForm: function(field_id) {

        geo.field_id = field_id;

        var city = $('#'+this.field_id+' .city_view').val();
        url = city ? '/geo/city/'+city : '/geo';

        core.message(LANG_SELECT_CITY);
        $.post(url, {}, function(html){
          if(html){
              $('#popup_message').html(html);
              $('#popup_progress').hide();
              $('#popup_ok').val(LANG_SELECT);
              $('#popup_ok').click(function(){
                  $('#popup_ok').prop('disabled', true);
                  $('.ajax-loader').show();
                  geo.selectCity();
              });
              geo.changeCity($('select[name=cities]'));
          }
        });
    },
	changeParent: function(list, child_list_id) {

        $(list).parent().nextAll('.list').hide();
        $('.ajax-loader').show();
        if ($('select[name=cities]').is(':visible') && $('select[name=cities]').val() > 0){
            $('#popup_ok').show();
        }else{
            $('#popup_ok').hide();
        }

        var id = $(list).val();

        var child_list = $('select[name='+child_list_id+']');

        if (id == 0) {
            child_list.parent('.list').hide();
            if (child_list_id=='regions'){
                $('select[name=cities]').parent('.list').hide();
            }
            $('#popup_ok').hide();
            $('.ajax-loader').hide();
            return false;
        }

        $.post('/geo/get', {type: child_list_id, parent_id: id}, function(result){

            if (result.error) { return false; }

            child_list.html('');

            for(var item_id in result.items){

                var item_name = result.items[item_id];

                child_list.append( '<option value="'+ item_id +'">' + item_name +'</option>' );

            }

            child_list.parent('.list').show();

            $('.ajax-loader').hide();

            if (child_list_id != 'cities'){
                geo.changeParent(child_list, 'cities');
            }

        }, 'json');

	},
    changeCity: function(list){

        var id = $(list).val();

        if (id > 0) {
            $('#popup_ok').show();
        }  else {
            $('#popup_ok').hide();
        }

    },
    selectCity: function(){

        var cities    = $('#geo_window select[name=cities]');
        var name = $('option:selected', cities).html();

        var city_id    = cities.val();
        var region_id  = $('#geo_window select[name=regions]').val();
        var country_id = $('#geo_window select[name=countries]').val();

        if (!name){ return false; }

        $('#'+geo.field_id+' .city_name').val(name);
        $('#'+geo.field_id+' .city_view').val(name);
        $('#'+geo.field_id+' .city_id').val(city_id);
        $('#'+geo.field_id+' .region_id').val(region_id);
        $('#'+geo.field_id+' .country_id').val(country_id);
        $('#'+geo.field_id+' .city_clear_link').show();

        core.box_close();

    },
    clear: function(field_id){

        $('#'+field_id+' .city_name, #'+field_id+' .city_view, #'+field_id+' .city_id, #'+field_id+' .region_id, #'+field_id+' .country_id').val('');
        $('#'+field_id+' .city_clear_link').hide();

    }
}});