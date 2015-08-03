/*
 * @author stuartb
 * @date 2008.10.08
 * @description Wizard forms made easy.
 */
jQuery.fn.wizard = function(settings)
{
    settings = jQuery.extend({
         show: function(element) { return true; },
         prevnext: true,
         submitpage: null
      }, settings);

    // Hide all pages save the first.
    jQuery(this).children(".wizardpage").hide();
    jQuery(this).children(".wizardpage:first").show();
    settings.show(jQuery(this).children(".wizardpage:first"));

    // Also highlight the first nav item.
    jQuery(this).children(".wizard-nav").children("a:first").addClass("active");

    // Wire progress thingy
    jQuery(this).children(".wizard-nav").children("a").click(function(){
//        var target = jQuery(this).attr("href");
//        jQuery(this).parent().parent().children(".wizardpage").hide();
//        jQuery(target).fadeIn('slow');
//        settings.show(jQuery(target));
//        jQuery(this).parent().children('a').removeClass('active', 'slow');
//        jQuery(this).addClass('active', 'slow');
	      return false;
    });

    // Prevent form submission on a wizard page...
    jQuery(this).children(".wizardpage").each(function(i){
        // unless there is a submit button on this page
        if((settings.submitpage == null && jQuery(this).find('input[type="submit"]').length < 1) ||
           (settings.submitpage != null && !$(this).is(settings.submitpage)))
        {
            $(this).find('input,select').keypress(function(event){
                return event.keyCode != 13;
            });
        }
    });

    if(settings.prevnext)
    {
        // Add prev/next step buttons
        jQuery(this).children(".wizardpage")
        .append('<div class="row wizardcontrols"></div>')
        .children(".wizardcontrols")
            .append('<input type="button" class="wizardprev" value="'+LANG_INS_BACK+'" /><input type="button" class="wizardnext" value="'+LANG_INS_NEXT+'" />');
        jQuery('.wizardpage:first input[type="button"].wizardprev').hide(); // hide prev button on first page
        jQuery('.wizardpage:last input[type="button"].wizardnext').hide();  // hide next button on last page

        // Wire prev/next step buttons
        jQuery(this).children(".wizardpage")
        .children(".wizardcontrols")
        .children('input[type="button"].wizardprev').click(function(){
            var wizardpage = jQuery(this).parent().parent(); // wizardcontrols div, wizardpage div
            var wizardnav  = wizardpage.parent().children(".wizard-nav")

            wizardpage.hide();
            wizardpage.prev().fadeIn();
            settings.show(wizardpage.prev());

            try{ wizardpage.prev().find("input:first").focus(); } catch(err) {}
            wizardnav.children('a').removeClass('active', 'slow');
            wizardnav.children('a[href="#' + wizardpage.attr('id') + '"]').prev().addClass('active', 'slow');
        });
        jQuery(this).children(".wizardpage")
        .children(".wizardcontrols")
        .children('input[type="button"].wizardnext').click(function(){
            var wizardpage = jQuery(this).parent().parent(); // wizardcontrols div, wizardpage div
            var wizardnav  = wizardpage.parent().children(".wizard-nav")

            wizardpage.hide();
            wizardpage.next().fadeIn();
            settings.show(wizardpage.next());

            try{ wizardpage.prev().find("input:first").focus(); } catch(err) {}
            wizardpage.prev().find("input:first").focus();
            wizardnav.children('a').removeClass('active', 'slow');
            wizardnav.children('a[href="#' + wizardpage.attr('id') + '"]').next().addClass('active', 'slow');
        });
    }

    return jQuery(this);
};
