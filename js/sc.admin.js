(function($){
	$(document).ready(function() {

    $("#tabs").tabs();
    $('#color, #colorTo, #borderColor, #fontColor, #hintFontColor, #hintColor, #hintBorderColor').ColorPicker({
  			onSubmit: function(hsb, hex, rgb, el){
  				$(el).val(hex);
  				$(el).ColorPickerHide();
  			},
  			onBeforeShow: function(){
  				$(this).ColorPickerSetColor(this.value);
  			}
  		}).bind('keyup', function(){
  		$(this).ColorPickerSetColor(this.value);
  	});
    $('#fb2AppId').qtip({
      id: 'fbAppTip',
      prerender: true,
      content: {
        text: $('#fb-app-hint')
      },
      style: {
        classes: 'ui-tooltip-light ui-tooltip-shadow ui-tooltip-rounded'
      },
      position: {
        my: 'top left',
        at: 'bottom center',
        target: $('#fb2AppId'),
        adjust: { 
          x: 5, 
          y: 5 
        }
      }
    });
    $('#fb2Secret').qtip({
      id: 'fbAppTip2',
      prerender: true,
      content: {
        text: $('#fb-app-hint-2')
      },
      style: {
        classes: 'ui-tooltip-light ui-tooltip-shadow ui-tooltip-rounded'
      },
      position: {
        my: 'top left',
        at: 'bottom center',
        target: $('#fb2Secret'),
        adjust: { 
          x: 5, 
          y: 5 
        }
      }
    });
    $('#lb_borderStyle_win7').qtip({
      id: 'win7Tip',
      prerender: true,
      content: {
        text: $('#win7-img')
      },
      style: {
        classes: 'ui-tooltip-light ui-tooltip-shadow ui-tooltip-rounded'
      },
      position: {
        my: 'top center',
        at: 'bottom center',
        target: $('#lb_borderStyle_win7'),
        adjust: { 
          x: 5, 
          y: 5 
        }
      }
    });
    $('#lb_borderStyle_default').qtip({
      id: 'stdTip',
      prerender: true,
      content: {
        text: $('#std-img')
      },
      style: {
        classes: 'ui-tooltip-light ui-tooltip-shadow ui-tooltip-rounded'
      },
      position: {
        my: 'top center',
        at: 'bottom center',
        target: $('#lb_borderStyle_default'),
        adjust: { 
          x: 5, 
          y: 5 
        }
      }
    });
		return false;
	});
})(jQuery)