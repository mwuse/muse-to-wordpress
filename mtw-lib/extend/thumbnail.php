<?php
function mtw_thumbnail_replacer($dom){

	global $post;
	$mtw_thumbs = dom_getElementsByClass( $dom , "mtw-thumb" );

	if( $mtw_thumbs->length )
	{	
			foreach ( $mtw_thumbs as $key => $mtw_thumb ) 
			{
				if( $mtw_thumb->getAttribute('data-custom') != ' ' && $custom_field = $mtw_thumb->getAttribute('data-custom') )
				{
					$custom_field = do_shortcode( trim( $custom_field ) );;
					$custom_field_meta = get_post_meta( $post->ID, $custom_field, true );
					$thumb = $custom_field_meta;					
				}				

				if( !$thumb )
				{
					$thumb = get_post_thumbnail_id($post->ID);
				}

				if( $thumb )
				{
					$mtw_thumb->parentNode->setAttribute('data-src_id', $thumb);
					$thumb = false;
				}
				
				
			}

	}

}
add_action( 'DOMDocument_loaded', 'mtw_thumbnail_replacer', 10, 1 );


function mtw_replace_src_id()
{
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('[data-src_id]').each(function(index, el) {

			jQuery.post(
			    ajaxurl, 
			    {
			        'action': 'mtw_get_best_thumb_size',
			        'data':   
			        {
			        	'src_id' : $(el).data('src_id'),
			        	'width' : $(el).width(),
			        	'height' : $(el).height()
			        }
			    }, 
			    function(response){
			        $(el).css('background-image', 'url(' + $.trim(response) + ')' );
			        $(el).css('background-position', 'center center' );
			        $(el).css('background-size', 'cover' );
			        console.log( response );
			    }
			);
		});

		$('.mtw-thumb').each(function(index, el) {
			if( !$(el).parent().data('src_id') )
			{
				if( $(el).hasClass('empty_display_none') )
				{
					$(el).parent().hide();
					if( $(el).parent().parent().hasClass('browser_width') )
					{
						$(el).parent().parent().hide();
					}
				}
			}
		});
		
	});
	</script>
	<?php
}
add_action( 'wp_footer' , 'mtw_replace_src_id' );

add_action('wp_head','pluginname_ajaxurl');
function pluginname_ajaxurl() {
?>
<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<?php
}


function mtw_get_best_thumb_size()
{
	$src = wp_get_attachment_image_src( $_POST['data']['src_id'], array( $_POST['data']['width'], $_POST['data']['height'] ) );
	if( $src[0] )
	{
		die( $src[0] );
	}
}
//add_action( 'wp_ajax_mtw_get_best_thumb_size' 'mtw_get_best_thumb_size' );
add_action( 'wp_ajax_mtw_get_best_thumb_size', 'mtw_get_best_thumb_size' );
add_action( 'wp_ajax_nopriv_mtw_get_best_thumb_size', 'mtw_get_best_thumb_size' );


?>