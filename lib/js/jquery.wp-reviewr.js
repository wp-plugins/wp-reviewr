
// Utility
if ( typeof Object.create !== 'function' ) {
	Object.create = function( obj ) {
		function F() {};
		F.prototype = obj;
		return new F();
	};
}

(function( $, window, document, undefined ) {
	jQuery('.reviewr-range').slider({
		range: 'min',
		slide: function(event, ui) {
	        jQuery(".wp-reviewr-fld-inpt " + $(this).data('target')).val(ui.value + '%');
	        
	    }
	});
	jQuery("#ranger").on('change',function(){
		get_val = jQuery(this).val();
		get_val = parseInt( get_val );
		target = jQuery(this).data('target');
		if(get_val > 100){
			jQuery(this).val('100%');
		}else if(get_val < 0){
			jQuery(this).val('0%');
		}
		jQuery(".reviewr-range" + target).slider("value", get_val);
	});

	//submit user form ratings
	jQuery('.wp-reviewr-the-form').submit(function(){
		var formdata = $(this).serializeArray();
		var getThis = $(this);
		var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
		var noError = true;
		var emailAd = $(this).find('.wp-reviewr-email').val();
		if(!emailReg.test(emailAd)){
			$(this).find('.wp-reviewr-email').addClass('wp-reviewr-error');
			noError = false;
		}else{
			$(this).find('.wp-reviewr-email').removeClass('wp-reviewr-error');
		}
		$(this).find('.reviewr-required').each(function(){
			if($(this).val() == ''){
				$(this).addClass('wp-reviewr-error');
				noError = false;
			}else{
				$(this).removeClass('wp-reviewr-error');
			}
		}).promise().done(function(){
			if(noError){
				jQuery.ajax({
					type : "post",
					dataType : "json",
					url : reviewr_vars.ajaxurl,
					data : {action: "reviewr_rate", formdata : formdata},
					success: function(response){
						$( getThis ).parent('.wp-reviewr-form').parent('.wp-reviewr-right').parent('.wp-reviewr-inner').find('.wp-reviewr-title a').hide();
						$( getThis ).closest('.wp-reviewr-form').fadeOut('fast',function(){
							$( getThis ).parent('.wp-reviewr-form').parent('.wp-reviewr-right').find('.wp-reviewr-success').show();
						});
					}
				});
			}
		});

		return false;
	});

	jQuery(document).on('click', '.wp-reviewr-loadmore a',function(e){
		var a_this = jQuery(this);
		var post_id = jQuery(this).parent('.wp-reviewr-loadmore').parent('.wp-reviewr-reviews').find('.wp-reviewr-post_id').val();
		var paged = jQuery(this).parent('.wp-reviewr-loadmore').parent('.wp-reviewr-reviews').find('.wp-reviewr-paged').val();
		var per_page = jQuery(this).parent('.wp-reviewr-loadmore').parent('.wp-reviewr-reviews').find('.wp-reviewr-per_page').val();
		jQuery(this).hide();
		jQuery(this).parent('.wp-reviewr-loadmore').find('span').show();
		// console.log(paged);
		jQuery.ajax({
			type : "post",
			url : reviewr_vars.ajaxurl,
			data : {action: "wpreviewer_loadmore", post_id : post_id, paged: paged, per_page: per_page},
			success: function(response){
				if( response.length <= 1 ){
					a_this.parent('.wp-reviewr-loadmore').hide();
				}else{
					new_page = parseInt(paged)+1;
					a_this.parent('.wp-reviewr-loadmore').parent('.wp-reviewr-reviews').find('.wp-reviewr-more').append( response );
					a_this.parent('.wp-reviewr-loadmore').parent('.wp-reviewr-reviews').find('.wp-reviewr-paged').val( new_page );
					a_this.parent('.wp-reviewr-loadmore').find('span').hide();
					a_this.show();
				}
				
			}
		});
		

		e.preventDefault();
	});

})( jQuery, window, document );