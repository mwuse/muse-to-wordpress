<?php
class MtwSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $current_field;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_mtw_theme_page' ), 10 );
        add_action( 'admin_init', array( $this, 'admin_init' ), 10 );
    }

    /**
     * Add options page
     */
    public function add_mtw_theme_page()
    {
        // This page will be under "Settings"
        //add_menu_page( 'Muse to Wordpress', 'Muse to Wordpress', 'manage_options', 'muse-to-wordpress-setting', array( $this, 'create_admin_page' )/*, get_template_directory_uri() . "/images/icon-white.png"*/ );
        add_theme_page( 'Muse to Wordpress', 'Muse to Wordpress', 'manage_options', 'muse-to-wordpress-setting', array( $this, 'create_admin_page' ) );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'mtw_option' );
        ?>
        <div class="wrap">
            <h2><img src="<?php echo get_template_directory_uri() . "/images/logo.png"; ?>"> Muse to Wordpress</h2>  
            <a target="_blank" href="http://muse-to-wordpress.com">Learn more about Muse to Wordpress</a><br/>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'mtw_option_group' );   
                do_settings_sections( 'muse-to-wordpress-setting' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php

        ttr_page_linker();
    }

    /**
     * Register and add settings
     */
    public function admin_init()
    {
    	

        register_setting(
            'mtw_option_group', // Option group
            'mtw_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'mtw_first_setting_section', // ID
            __( 'General settings', 'muse-to-wordpress' ), // Title
            array( $this, 'print_section_info' ), // Callback
            'muse-to-wordpress-setting' // Page
        );

        /*add_settings_field(
            'mtw_production_mode', // ID
            __('Production mode', 'muse-to-wordpress'), // Title 
            array( $this, 'get_bool_field' ), // Callback
            'muse-to-wordpress-setting', // Page
            'mtw_first_setting_section', // Section 
            array( 'id' => 'mtw_production_mode' ) // Callback args
        );  */

        add_settings_field(
            'mtw_auto_page', // ID
            __('Synchronize Muse and Wordpress Pages', 'muse-to-wordpress'), // Title 
            array( $this, 'get_bool_field' ), // Callback
            'muse-to-wordpress-setting', // Page
            'mtw_first_setting_section', // Section 
            array( 'id' => 'mtw_auto_page' ) // Callback args
        );

        add_settings_field(
            'mtw_default_project', // ID
            __('Default Muse project', 'muse-to-wordpress'), // Title 
            array( $this, 'get_select_field' ), // Callback
            'muse-to-wordpress-setting', // Page
            'mtw_first_setting_section', // Section 
            array( 'id' => 'mtw_default_project', 'choices' => $this->muse_project_choices() ) // Callback args
        );
 
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        
        if( isset( $input['mtw_auto_page'] ) )
            $new_input['mtw_auto_page'] = $input['mtw_auto_page'] ;

        if( isset( $input['mtw_default_project'] ) )
            $new_input['mtw_default_project'] = $input['mtw_default_project'] ;

        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = absint( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        //return $new_input;

        return $input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        //print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */

    public function muse_project_choices()
    {
    	$projects = ttr_get_muse_projects();

    	$return = array();

    	foreach ($projects as $key => $value) {
    		$return[] = $key;
    	}

    	return $return;
    }
    
    public function get_select_field( $args )
    {

    	$id = $args['id'];
    	$choices = $args['choices'];

    	?>
    	<select name="mtw_option[<?php echo $id; ?>]" >
    		<?php
    		foreach ($choices as $value) {
    			?>
    			<option <?php echo ( $this->options[$id] == $value ) ? 'selected' : '' ; ?> value="<?php echo $value ?>"><?php echo $value ?></option>
    			<?php
    		}
    		?>
    	</select>
    	<?php
    }

    public function get_bool_field( $args )
    {

    	$id = $args['id'];
    	
        printf(
            '<input type="checkbox" id="'.$id.'" name="mtw_option['.$id.']" value="checked" %s />',
            isset( $this->options[$id] ) ? esc_attr( $this->options[$id]) : ''
        );
    }

}

if( is_admin() )
    $MtwSettingsPage = new MtwSettingsPage();

?>