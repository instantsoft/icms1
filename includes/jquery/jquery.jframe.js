// jFrame
// $Revision: 1.101 $
// Author: Frederic de Zorzi
// Contact: fredz@_nospam_pimentech.net
// Revision: $Revision: 1.101 $
// Date: $Date: 2008-04-29 11:36:24 $
// Copyright: 2007 PimenTech SARL
// Tags: ajax javascript pimentech english jquery


jQuery.fn.waitingJFrame = function () {
    // Overload this function in your code to place a waiting event 
    // message, like :  $(this).html("<b>loading...</b>");
}


jQuery.fn.onUnloadJFrame = function () {
    jQuery(this).find("div[src][onunload]").add(jQuery(this))
    .each(
          function(i) {
              if (eval(jQuery(this).attr("onunload")) == false) {
                  return false;
              }
          });
}

function jFrameSubmitInput(input) {
    var target = jQuery(input).getJFrameTarget();
    if (target.length) {
        var form = input.form;
        if (form.onsubmit && form.onsubmit() == false 
            || target.preloadJFrame() == false) {
            return false;
        }
        jQuery(form).ajaxSubmit({ 
            target: target,
                    beforeSubmit: function(formArray) { 
                    formArray.push({ name:"submit", value: jQuery(input).attr("value") }); 
                },
                    success: function() { 
                    target.attr("src", jQuery(form).attr("action"));
                    eval(target.attr("onload"));
                    target.activateJFrame(); 
                }
            });
        return false;
    }
    return true;
}

jQuery.fn.preloadJFrame = function () {
    if (jQuery(this).onUnloadJFrame() == false) {
        return false;
    }
    jQuery(this).waitingJFrame();
}


jQuery.fn.getJFrameTarget = function () {
    // Returns first parent jframe element, if exists
    var div = jQuery(this).parents("div[src]").get(0);
    if (div) {
        var target = jQuery(this).attr("target");
        if (target) {
            return jQuery("#" + target);
        }
    }
    return jQuery(div);
};


jQuery.fn.loadJFrame = function(url, callback) {
    // like ajax.load, for jFrame. the onload attribute is supported
    var this_callback = jQuery(this).attr("onload");
    callback = callback || function(){};
    url = url || jQuery(this).attr("src");
    if (url && url != "#") {
        if (jQuery(this).preloadJFrame() == false) {
            return false;
        }
        jQuery(this).load(url, 
                     function() { 
                         jQuery(this).attr("src", url);
                         jQuery(this).activateJFrame(); 
                         jQuery(this).find("div[src]").each(function(i) {
                                 jQuery(this).loadJFrame();
                             } );
                         eval(this_callback);
                         callback();
                     });
    }
    else {
        jQuery(this).activateJFrame(); 
    }
};

jQuery.fn.activateJFrame = function() {
    // Add an onclick event on all <a> and <input type="submit"> tags
    jQuery(this).find("a")
    .not("[jframe='no']")
    .unbind("click")
    .click(function() { 
            var target = jQuery(this).getJFrameTarget();
            if (target.length) {
                var href = jQuery(this).attr("href");
                if (href && href.indexOf('javascript:') != 0) {
                    target.loadJFrame(href);
                    return false;
                }
            }
            return true;
        } );

    jQuery(":image,:submit,:button", this)
    .not("[jframe='no']")
    .unbind("click")
    .click(function() { return jFrameSubmitInput(this); } );

	// Only for IE6 : enter key invokes submit event
    jQuery(this).find("form")
    .unbind("submit")
    .submit(function() {
			return jFrameSubmitInput(jQuery(":image,:submit,:button", this).get(0));
    } ); 
};


jQuery(document).ready(function() { 
    jQuery(document).find("div[src]").each(function(i) {
            jQuery(this).loadJFrame();
    } );
} );
