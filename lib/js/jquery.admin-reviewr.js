
// Utility
if ( typeof Object.create !== 'function' ) {
	Object.create = function( obj ) {
		function F() {};
		F.prototype = obj;
		return new F();
	};
}

(function( $, window, document, undefined ) {
	jQuery('.reviewr-admin-slider').slider({
		range: 'min',
		slide: function(event, ui) {
	        jQuery(".reviewr-admin-slider-input" + $(this).data('target')).val(ui.value + '%');
	    },
	    create: function(event, ui){
	    	jQuery(this).slider('value', jQuery(this).data('value') );
	    }
	});
	jQuery(document).on('change','.reviewr-admin-slider-input',function(){
		get_val = jQuery(this).val();
		get_val = parseInt( get_val );
		target = jQuery(this).data('target');
		console.log(get_val);
		if(get_val > 100){
			jQuery(this).val('100%');
		}else if(get_val < 0){
			jQuery(this).val('0%');
		}
		jQuery(".reviewr-admin-slider" + target).slider("value", get_val);
	});

	jQuery( ".reviewr-criteria-lists" ).sortable();
	//add criteria item
	var randTime = (new Date).getTime();
	jQuery('.reviewr-add-criteria').on('click',function(){
		randTime = (new Date).getTime();
		var criteria = '<li class="reviewr-criteria-single" id="reviewr-criteria-'+ randTime +'"><input type="hidden" name="reviewr[criteria]['+randTime+'][id]" value="criteria'+ randTime +'" /><table class="form-table reviewr-criteria"><tbody><tr><th scope="row"><label for="reviewr-title-fld-'+ randTime +'">'+ vars.title +'</label></th><td colspan="2"><input type="text" id="reviewr-title-fld-'+ randTime +'" name="reviewr[criteria]['+randTime+'][title]" class="widefat" /></td><td class="reviewr-td-last">&nbsp;</td></tr><tr><th scope="row"><label for="reviewr-score-fld-'+ randTime +'">'+ vars.score +'</label></th><td><div class="reviewr-admin-slider" id="reviewr-score-fld-'+ randTime +'" data-target="#reviewr-slider-'+ randTime +'"></div></td><td class="reviewr-td-small"><input type="text" id="reviewr-slider-'+ randTime +'" class="reviewr-admin-slider-input" data-target="#reviewr-score-fld-'+ randTime +'" name="reviewr[criteria]['+randTime+'][score]" /></td><td class="reviewr-td-last"><input type="button" class="button button-primary button-large reviewr-criteria-delete" data-target="#reviewr-criteria-'+ randTime +'" value="'+ vars.delete +'"></td></tr></tbody></table></li>';
		jQuery('.reviewr-criteria-lists').append( criteria );
		setTimeout(function() {
			jQuery('.reviewr-admin-slider').slider({
				range: 'min',
				slide: function(event, ui) {
			        jQuery(".reviewr-admin-slider-input" + $(this).data('target')).val(ui.value + '%');
			        
			    }
			});
		},100);
	});

	//delete criteria
	jQuery('.reviewr-criteria-delete').live('click',function(){
		target = jQuery(this).data('target');
		if (confirm( vars.confirm )) {
			jQuery('.reviewr-criteria-single' + target).fadeOut('fast',function(){
				jQuery('.reviewr-criteria-single' + target).remove();
			});
		}
		return false;
	});

})( jQuery, window, document );