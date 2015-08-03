$(document).ready(function(){
	$('#hmenu ul li').hover(
		function() {
			$(this).find('ul:first').show();
			$(this).addClass("hilite");
		},
		function() {            
			$(this).find('ul:first').hide();
			$(this).removeClass("hilite");						
		}
	);    
	//$('#hmenu li:has(ul)').find('a:first').append(' &raquo;');
	$('#hmenu ul li ul li').find('ul:first').addClass("fleft");
	
	$('.jclock').jclock();
	
	$('input[type=button]').addClass('button');
	$('input[type=submit]').addClass('button');

    $('input[name=published][type=checkbox]').click(function(){

        var checked = $(this).prop('checked');

        if (checked){
            $('label[for=published] strong').css('color', 'green');
        } else {
            $('label[for=published] strong').css('color', 'red');
        }

    });

    var checked = $('input[name=published][type=checkbox]').prop('checked');

    if (checked){
        $('label[for=published] strong').css('color', 'green');
    } else {
        $('label[for=published] strong').css('color', 'red');
    }

});