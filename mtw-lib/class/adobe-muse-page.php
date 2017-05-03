<?php
class MusePage 
{

	public $project_name;
	public $file_url;
	public $template_slug;
	public $base_name;
	public $folderName;
	public $muse_title;
	public $wp_pages = array();
	public $types = array();
	public $types_HTML;
	public $warnings = array();
	public $DOMDocument;

	

	public function init( $fileUrl, $complet = true )
	{

		global $folderName;
		global $deviceType;

		$detect = new Mobile_Detect;
		$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

		$this->file_url = $fileUrl;
		$this->template_slug = str_replace(TTR_MW_TEMPLATES_URL, "", $fileUrl);
		$folderName = explode("/", $this->template_slug)[0];
		$this->folderName = $folderName;

		$this->base_name = basename( $fileUrl );

		$this->cacheGroup = 'MTW' . $this->template_slug;

		$this->DOMDocument = $this->get_DOMDocument();


		if( $complet )
		{
			
			repaire_responsive( $this->DOMDocument );

			do_action( 'DOMDocument_loaded', $this->DOMDocument, $this->file_url );


			$tag_title = $this->DOMDocument->getElementsByTagName('title');
			if( $this->DOMDocument->getElementsByTagName('title')->length > 0 )
			{
				$this->muse_title = $this->DOMDocument->getElementsByTagName('title')->item(0)->nodeValue;
				$title = $this->DOMDocument->getElementsByTagName('title')->item(0);
				$title->parentNode->removeChild($title);
			}

			//$this->wp_pages = ttr_get_page_by_template($this->template_slug);

			$explodeSlug = explode("/", $this->template_slug);
			$this->project_name = $explodeSlug[0];

			//$this->__init_active_types();
			$this->__logic_links();

		}
		

	}

	private function __logic_links(){
		//check a tag logic link error
		$as = $this->DOMDocument->getElementsByTagName('a');
		
		$logic_links = get_option( 'logic_links_' . $this->template_slug );

		if( !$logic_links ){
			$logic_links = array();
			$update = true;
		}
		if( $_GET['update_logic_links'] == $this->template_slug || $_GET['update_all_logic_links'] ){
			$update = true;
		}

		//$update = true;

		foreach ($as as $key => $a) {
			
			$href = $a->getAttribute('href');

			if( isset( $update ) ){

				$match = $this->__best_match_link($a);

				if ( $match ) 
				{
					if( $match != get_option( 'page_on_front' ) )
					{
						$logic_links[$href] =  get_permalink($match);
					}
					else
					{
						$site_url = site_url();
						$logic_links[$href] = $site_url;						
					}
				}
			}

			if(isset($logic_links[$href])){
				$a->setAttribute('href', $logic_links[$href] );
			}

			if( strpos( $href, '%5B' ) != NULL && strpos( $href, '%5D' ) != NULL && !is_admin() ){
				$newLink = str_replace( 'http://' , '' , $href );
				$newLink = str_replace( 'https://' , '' , $newLink );
				
				$newLink = do_shortcode( urldecode( $newLink ) );
				$a->setAttribute('href', $newLink);
			}

			if( strpos( $href, 'archive-' ) === 0 ){				
				$post_type = str_replace( 'archive-' , '' , $href );
				$post_type = str_replace( '.html' , '' , $post_type );
				$post_type = str_replace( '-'.strtolower( get_locale() ) , '' , $post_type );

				if( post_type_exists( $post_type ) )
				{
					$a->setAttribute('href', get_post_type_archive_link($post_type) );
				}
				elseif ( $href == 'archive-'.strtolower( get_locale() ).'.html' ) 
				{
					if( get_option( 'show_on_front' ) == 'page' ) 
					{
						$a->setAttribute('href', get_permalink( get_option('page_for_posts' ) ) );
					}
					else
					{
						$a->setAttribute('href', esc_url( home_url() ) );	
					} 
				}
			}

			if( strpos( $href, 'assets/' )  === 0 )
			{
				$a->setAttribute('href', TTR_MW_TEMPLATES_URL . $this->folderName . '/' . $href  );	
			}
						
		}


		if( $update )
		{
			update_option( 'logic_links_' . $this->template_slug, $logic_links );
		}

	}

	private function __best_match_link($a)
	{

		
		$array = ttr_get_muse_html_array( $this->project_name);
		$match = ttr_get_page_by_template( $this->project_name . '/' . urldecode( $a->getAttribute('href') ) );

		if( count($match) == 0 )
		{
			$return = null;
		}
		elseif ( count($match) == 1 ) 
		{
			$return = $match[0]["ID"];
		} 
		elseif ( count($match) > 1 )
		{
			$return = $match[0]["ID"];
			$this->warnings[] = 'Multiple page error -> '.$a->getAttribute('href');
		}

		return $return ;
	}


	
	public function get_DOMDocument()
	{
		
		global $wp_filesystem;
		WP_Filesystem();
		
		$content = $wp_filesystem->get_contents( TTR_MW_TEMPLATES_PATH . $this->template_slug );
		$content = exclude_html_dom_bug( $content );
		
		$dom = new DOMDocument;
		$dom->loadHTML( $content );

		apply_filters( 'mtw_init_dom', $dom, $this );
		
		return $dom;

	}

	private function __init_active_types()
	{
		$types = array();
		$types_HTML = array();

		if ( count( $this->wp_pages ) == 0 )
		{
			$types[0] = "no-type";
			$types_HTML[0] = '<font color="FireBrick">Not yet associated</font>';
		}
		elseif ( count( $this->wp_pages ) == 1 )
		{
			$types[0] = "unique-wp-page";
			$types_HTML[0] = '<a href="' . get_permalink( $this->wp_pages[0]['ID'] ) . '" target="_blank">Unique page template</a>';
		}
		elseif ( count( $this->wp_pages ) > 1 ) 
		{
			$types[0] = "multi-wp-page";
			ob_start(); ?>
				<?php add_thickbox(); ?>
				<div id="<?php echo sanitize_title( $page->muse_title ).'wppages';  ?>" style="display:none;">
				    <p><?php echo $this->get_wp_pages(); ?></p>
				</div>
				<a href="#TB_inline?width=800&height=600&inlineId=<?php echo sanitize_title( $page->muse_title ).'wppages';  ?>" class="thickbox inline">
					Multi page template
				</a>
			<?php
			$types_HTML[0] = ob_get_clean();
		}

		$base_name_explode = explode( "-" ,  $this->base_name );
		$exclude = array( 'single', 'taxonomy' );

		if( strpos( strtolower($this->base_name) , "single" ) === 0 
			|| strpos( strtolower($this->base_name) , "taxonomy" ) === 0
			|| strpos( strtolower($this->base_name) , "search" ) === 0  )
		{
			$find = str_replace("single-", "", $this->base_name);
			$find = str_replace(".html", "", $find);
			if($find == "single")
			{
				$find = 'post';
			}
			$types[0] = "single";
			$types[1] = "exclude";
			$types_HTML[0] = 'Auto template';
			
		}		

		$this->types = $types;
		$this->types_HTML = implode(",", $types_HTML);

	}

	private function __check_warnings($dom)
	{
		//check a tag logic link error
		$as = $dom->getElementsByTagName('a');

		foreach ($as as $key => $a) {
			$this->warnings[] = best_match_link($a, $this->project_name);
		}
	}

	public function get_warnings_list(){
		ob_start();
		?>

			<h4><?php echo $this->muse_title; ?></h4>
			<h5>Warnigns List</h5>
			<p>
				<pre>
					<?php print_r($this->warnings); ?>
				</pre>
			</p>
		<?php
		return ob_get_clean();
	}

	public function get_wp_pages()
	{
		ob_start();
		?>
		<h4><?php echo $this->muse_title; ?></h4>
		<h5>Wordpress pages</h5>
		<p>
			<?php foreach ($this->wp_pages as $wp_page) { ?>
				<p>	
					ID <?php echo $wp_page['ID']; ?><br>  
					<a href="<?php echo get_permalink( $wp_page['ID'] ); ?>" target="_blank"><?php echo get_the_title( $wp_page['ID'] ); ?></a> 
					- 
					<a href="<?php echo get_edit_post_link( $wp_page['ID'] ); ?>">Edit</a> 
					- 
					<a href="<?php echo get_delete_post_link(  $wp_page['ID'] ); ?>">Delete</a> 
				</p>
			<?php } ?>
		</p>
		<?php
		return ob_get_clean();
	}

	public function get_single_manager()
	{
		ob_start();
		?>

			<h4><?php echo $this->muse_title; ?></h4>
			<h5>Single options</h5>

		<?php
		return ob_get_clean();
	}
	
}
?>