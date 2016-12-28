jQuery(document).ready(function($) {
	
	var acfopen = false;
	var countclick = 0;
	var target;
	

	$(document).on('click', function(event) {
		//event.preventDefault();
		if( acfopen == true )
		{
			countclick++;
			
			if(countclick == 2){
				countclick = 0;
				acfopen = false;
				target.parent().find('.acf-fc-popup').remove()
			}
			
		}

	});

	$('.acf-button, .acf-icon').on('click', function(event) {
		//event.preventDefault();
		acfopen = true;
		target = $(this);
		
	});


	(function($) {
	  $(document).ready(function() {
	    // disable the ACF js navigate away pop up
	    //if( $('.acf-prevent-to-save').length == 0 ) 
	    //{
	    	acf.unload.active = false;
	    //}
	  });
	})(jQuery);

});

