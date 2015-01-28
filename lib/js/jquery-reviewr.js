/*
    WP Reviewr Scripts
*/

// Utility
if ( typeof Object.create !== 'function' ) {
	Object.create = function( obj ) {
		function F() {};
		F.prototype = obj;
		return new F();
	};
}

(function( $, window, document, undefined ) {

	var wp_reviewr = {
		init: function( options, elem ) {
			var self = this;

			self.elem = elem;
			self.$elem = $( elem );
			self.counter = [] ;
			self.timeout = [] ;

			self.percent();
			self.linear();
			self.validate();
			self.rate();
			
		},
		percent: function(){
			var self = this;
			var i = 0 , prec;
			var degs = self.$elem.find('.wp-reviewr-prcnt').data('percent');
			get_id = self.$elem.attr('id');
			if( isNaN(self.counter[ get_id ])){
				self.counter[ get_id ] = 0;
			}else{
				self.counter[ get_id ] = parseInt(self.counter[ get_id ]) + 1;
			}
			
			if (self.counter[ get_id ] < 0)
		        self.counter[ get_id ] = 0;
		    if (self.counter[ get_id ] > degs)
		        self.counter[ get_id ] = degs;

			prec = (100*self.counter[ get_id ])/360;   
			// console.log(degs);
    		self.$elem.find(".wp-reviewr-prcnt").html(Math.round(prec)+"%");
    		if (self.counter[ get_id ]<=180){
	    		self.$elem.find('.wp-reviewr-border').css('background-image','linear-gradient(' + (90+self.counter[ get_id ]) + 'deg, transparent 50%, '+ reviewr_vars.percentbg +' 50%),linear-gradient(90deg, '+ reviewr_vars.percentbg +' 50%, transparent 50%)');
			}else{
		        self.$elem.find('.wp-reviewr-border').css('background-image','linear-gradient(' + (self.counter[ get_id ]-90) + 'deg, transparent 50%, '+ reviewr_vars.fillbg +' 50%),linear-gradient(90deg, '+ reviewr_vars.percentbg +' 50%, transparent 50%)');
		    }

			if(self.counter[ get_id ] == degs){
				clearTimeout( self.timeout[ get_id ] );
			}else{
				self.timeout[ get_id ] = setTimeout(function(){ self.percent(); },0.01);
			}
		},
		linear: function(){
			var self = this;
			self.$elem.find('.wp-reviewr-bar').each(function(){
				get_w = $(this).data('percent');
				$(this).animate({ 'width' : get_w + '%' }, 700);
			});
		},
		rate: function(){
			var self = this;
			self.$elem.find('.wp-reviewr-title a').on('click',function(e){
				self.$elem.find('.wp-reviewr-content-rate').fadeOut(250, function(){
					self.$elem.find('.wp-reviewr-form').fadeIn(150);
				});
				e.preventDefault();
			});

			self.$elem.find('.wp-reviewr-close').on('click',function(e){
				$(this).parent('.wp-reviewr-parent').fadeOut(250, function(){
					self.$elem.find('.wp-reviewr-content-rate').fadeIn(150);
				});
				e.preventDefault();
			});
		},
		validate: function(){
			var self = this;
			self.$elem.find('.reviewr-criteria-values').on('change',function(){
				get_val = parseInt( $(this).val() );
				target = jQuery(this).data('target');
				if(get_val > 100){
					jQuery(this).val('100%');
				}else if(get_val < 0){
					jQuery(this).val('0%');
				}else{
					jQuery(this).val(get_val + '%');
				}

				jQuery(".reviewr-range" + target).slider("value", get_val);
			});
		}
	};

	$.fn.reviewrFn = function( options ) {
		return this.each(function() {
			var reviewr = Object.create( wp_reviewr );
			
			reviewr.init( options, this );

			$.data( this, 'reviewrFn', reviewr );
		});
	};

	$.fn.reviewrFn.options = {
		position : "left", //left or right
	};
	
	jQuery('.wp-reviewr').reviewrFn();

})( jQuery, window, document );