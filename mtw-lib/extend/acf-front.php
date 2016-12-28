<?php 

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) ) {

	function mtw_acf_form_head( $dom, $url )
	{
		if ( function_exists('acf_form_head') )
		{
			$acf_forms = dom_getElementsByClass( $dom , "acf-front-end" );
			$mtw_transitor_target = dom_getElementsByClass( $dom , "mtw_transitor_target" );
			
			if(  $acf_forms->length )
			{
				acf_form_head();
				wp_enqueue_script( "mtw-acf-front", TTR_MW_PLUGIN_URL . "extend/acf-front.js" , array( 'jquery' ), '1.0', true );
			}	
		}
	}
	add_action( 'DOMDocument_loaded' , 'mtw_acf_form_head', 10, 2 );
	//add_action( "wp_enqueue_scripts" , "mtw_acf_form_head" );

	function acf_front_loader($dom)
	{

		$acf_forms = dom_getElementsByClass( $dom , "acf-front-end" );

		foreach ($acf_forms as $acf_key => $acf_form) {

			
			$field_groups = explode( ',', str_replace(' ', '', $acf_form->getAttribute('data-groups') ) )  ;


			$haveTitle = $acf_form->getAttribute('data-havetitle') ;
			$post_id = trim( $acf_form->getAttribute('data-post_id') );
			$post_type = trim( $acf_form->getAttribute('data-post_type') );
			$post_status = trim( $acf_form->getAttribute('data-post_status') );
			$action = trim( $acf_form->getAttribute('data-action') );
			$hook_name = trim( $acf_form->getAttribute('data-hook_name') );
			$bt_text = trim( $acf_form->getAttribute('data-bt_text') );
			$redirect = trim( $acf_form->getAttribute('data-redirect') );

		
			
			$options = array(
				'new_post' => array(
					'post_type' => $post_type,
					'post_status' => $post_status
					),
				'post_id' => 'new_post',
				'post_title' => $haveTitle,
				'field_groups' => $field_groups,
				'submit_value' => __("Envoyer", 'acf')
				);
			
			if( $action == "update_user" )
			{
				$user = wp_get_current_user();
				$user_id = $user->ID;
				unset($options['new_post']);
				$options['post_id'] = 'user_'.$user_id;
			}

			if ( $hook_name ) {
				$options['id'] = $hook_name;
			}

			if ( $bt_text ) {
				$options['submit_value'] = $bt_text;
			}

			if( $post_id &&  $action != 'insert' ) {
				$options['post_id'] = do_shortcode( $post_id );
				unset( $options['new_post'] );
			}

			if( $redirect ){

				global $projectName;
				$redirect1 = ttr_get_page_by_template( $projectName . '/' . $redirect  );

				if ( @$redirect1[0]['ID'] ) {
					$options['return'] = get_permalink( $redirect1[0]['ID'] );
				}else{
					$options['return'] = site_url();
				}
			}
			
			/*echo "<pre>";
			print_r( $options );
			echo "</pre>";
			acf_form( $options );
			exit();*/

			ob_start();
			
			acf_form( $options );

			$ob_return = ob_get_clean();


			$newDom = new DOMDocument();
			$newDom->loadHTML( ' <meta http-equiv="Content-Type" content="text/html;charset=UTF8"> ' . $ob_return);

			$form = $newDom->getElementsByTagName('form')->item(0);

			if ( $hook_name ) {

				$hook_name_hidden = $newDom->createElement('input');
				$hook_name_hidden->setAttribute('name', 'unique-id');
				$hook_name_hidden->setAttribute('type', 'hidden');
				$hook_name_hidden->setAttribute('value', $hook_name);

				$form->appendChild($hook_name_hidden);
			}


			if ( $action == "get_form" )
			{
				$form->setAttribute('id', '');
				$form->setAttribute('method', 'get');
				$hiddenDivs = dom_getElementsByClass( $newDom , "acf-hidden" );

				$deletes = array();
				foreach ($hiddenDivs as $hiddenDiv) {
					$deletes[] = $hiddenDiv;
				}
				foreach ($deletes as $delete) {
					$delete->parentNode->removeChild($delete);
				}

				if($options['return'])
				{
					$form->setAttribute('action', $options['return']);
				}

				/*echo '<pre>';
				print_r($hiddenDiv);
				exit();*/
			}


			$newNode = $dom->importNode($form, true);
			$acf_form->nodeValue = "";
			$acf_form->appendChild( $newNode );

			
		}

	}

	add_action( 'DOMDocument_body_load', 'acf_front_loader', 10, 1 );




	function list_acf_fields( $post_object )
	{
		global $post;
		global $singleFields;

		if( $post && get_fields( $post->ID ) ){
			$post_id = $post->ID;
		
			foreach ( get_fields( $post->ID ) as $field_name => $value )
			{

				$field_object = get_field_object( $field_name , $post_id , array('load_value' => false) );
				$field_object['value'] = $value;

				if( empty($value) && $value != '0' ){
					$field_object['value'] = "-";
				}

				$singleFields[$post->ID][$field_object['key']] = $field_object;	
			}
		}
		/*echo "<pre>";
		print_r($post);
		echo "</pre>";*/
		
	}

	add_action( "the_post", "list_acf_fields" );


	function get_acf_field( $atts ) {

		global $post;
		global $singleFields;

		$atts = shortcode_atts( array(
			'key' => 'none',
			'h' => '',
			'w' => ''
		), $atts );

		

		if( $atts['key'] != 'none' )
		{
			$singleFields[$post->ID][$atts['key']]['atts'] = $atts;
			return acf_value_filter( $singleFields[$post->ID][$atts['key']] );
			//return $post->ID;
		}
	
		// do shortcode actions here
	}

	add_shortcode( 'ACF','get_acf_field' );


	function acf_value_filter( $field_object )
	{
		global $singleFields;
		global $post;


		$value = $field_object['value'];

		

		if ( $field_object['type'] == "taxonomy" ) {

			$value = get_term( $value, $field_object['taxonomy'])->name ;

		}elseif ( $field_object['type'] == "select" ) {

			$value = utf8_decode( $field_object['choices'][$value] );

		}elseif ( $field_object['type'] == "checkbox" ) {
			
			$newvalue = array();
			if( is_array( $value ) )
			{
				foreach ($value as $key) {
					if( $key != "other" )
					{
						$newvalue[] = $field_object['choices'][$key];
					}
				}
				$value = implode(", ", $newvalue);
			}

		}elseif ( $field_object['type'] == "repeater" && count( $field_object['value'][0] ) == 1  ) {
			
			$newvalue = array();
			foreach ($field_object['value'] as $array1) {
				foreach ($array1 as $result) {
					$newvalue[] = $result;
				}
			}
			$value = implode(", ", $newvalue);

		}
		elseif ( $field_object['type'] == "image" ) 
		{
			

			if( $field_object['atts']['w'] && $field_object['atts']['w'] )
			{
				
				$size[] = $field_object['atts']['w'];
				$size[] = $field_object['atts']['h'];
				$size[] = 1;

				$src =  wp_get_attachment_image_src(  $field_object['value']['ID'] , $size )[0] ;

				ob_start();
				?>
				<div style="
					width:100%;
					height:<?php echo $size[1] ?>px;
					background-image:url(<?php echo $src ?>);
					background-size: cover;
				"></div>
				<?php
				$value = ob_get_clean();

			}
			else
			{
				$value = $field_object['value']['url'];
			}

			
		}




		if( $field_object['value'] == "other" || ( is_array( $field_object['value'] ) && in_array( "other" , $field_object['value']) ) )
		{

			$classs = explode(" ", trim( $field_object['wrapper']['class'] ) ) ;

			foreach ( $classs as $key => $class ) {
				preg_match("#acf-other=(.*)#", $class, $matche);
				if($matche){

					if( !is_array( $field_object['value'] ) ){
						$value = acf_value_filter( $singleFields[$post->ID][$matche[1]] );	
					}else{
						if( $field_object['type'] == "checkbox" )
						{
							if( count( $field_object['value'] ) > 1 ){
								$value.= ', ';
							}
							$value.= acf_value_filter( $singleFields[$post->ID][$matche[1]] );
						}					
					}
					
				}
			}
			
		}

		if($value == '-'){
			$value = '&nbsp;';
		}




		return $value;
	}
}



?>