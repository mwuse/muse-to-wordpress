<?php

function mtw_scroll_top()
{
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$("a").on('click', function(event) {
				if( $(this).attr('href') == "http://#top" || $(this).attr('href') == "https://#top" )
				{
					$('html, body').animate({
							scrollTop: $("html").offset().top
						}, 'slow');
					event.preventDefault();
				}
			});
		});
	</script>
	<?php
}

add_action( "wp_footer", "mtw_scroll_top" );

?>