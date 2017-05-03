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
        add_action( 'admin_enqueue_scripts', array( $this, 'init_settings_scripts'), 10 );
    }

    public function init_settings_scripts()
    {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script( 'wp-color-picker-transform', TTR_MW_PLUGIN_URL . 'scripts/color-picker/color-picker.js', array( 'wp-color-picker' ), false, true ); 
        wp_enqueue_script( 'mtw-ajax-to-form', TTR_MW_PLUGIN_URL . 'scripts/mtw-ajax-to-form/jquery.form.min.js', array( 'jquery' ), false, false );
    }

    /**
     * Add options page
     */
    public function add_mtw_theme_page()
    {
        global $submenu;

        add_menu_page( 'Muse to Wordpress', 'Muse to<br> Wordpress', 'manage_options', 'muse-to-wordpress-setting', array( $this, 'create_admin_page' ), NULL, 58 );
        if ( function_exists( 'mtw_get_widgets_creator' ) ) 
        {
            add_submenu_page( 'muse-to-wordpress-setting', __('Widgets Creator', 'muse-to-wordpress' ), __('Widgets Creator', 'muse-to-wordpress' ), 'manage_options', 'mtw-widgets-creator', array( $this, 'create_admin_page_shortcode_to_widget' ) );
        }   
        add_submenu_page( 'muse-to-wordpress-setting', __('Upload Website', 'muse-to-wordpress' ), __('Upload Website', 'muse-to-wordpress' ), 'manage_options', 'mtw-upload' , array( $this, 'create_admin_page_upload' ) );

        
        $submenu['muse-to-wordpress-setting'][0][0] = 'General Settings';

    }

    public function create_admin_page_add_ons()
    {
        $this->musetowordpress_admin_style();
        ?>
        <div class="wrap">
            <h2><img src="<?php echo TTR_MW_PLUGIN_URL . "images/logo.png"; ?>"> <?php  _e('Add-ons', 'muse-to-wordpress') ?></h2>  
        </div>
        <?php
    }

    public function create_admin_page_shortcode_to_widget()
    {
        $this->musetowordpress_admin_style();
        
        if ( function_exists( 'mtw_get_widgets_creator' ) ) 
        {
            echo mtw_get_widgets_creator();
        }
        else
        {
            ?>
            <div class="wrap">
            <h2><img src="<?php echo TTR_MW_PLUGIN_URL . "images/logo.png"; ?>"> <?php  _e('Widgets Creator', 'muse-to-wordpress') ?></h2> 
            <p>Mwuser Only</p>
            </div>
            <?php
        }
        
        
    }

    private function delete_path($path)
    {
        if (is_dir($path) === true)
        {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file)
            {
                $this->delete_path(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        }

        else if (is_file($path) === true)
        {
            return unlink($path);
        }

        return false;
    }

    public function create_admin_page_upload()
    {
        if( $_FILES['mtw-zip'] && $_FILES['mtw-zip']['type'] == 'application/x-zip-compressed' )
        {
            global $wp_filesystem;
            WP_Filesystem();

            move_uploaded_file( $_FILES["mtw-zip"]["tmp_name"], TTR_MW_TEMPLATES_PATH . $_FILES["mtw-zip"]["name"] );
            $file = TTR_MW_TEMPLATES_PATH . $_FILES["mtw-zip"]["name"];
            if( file_exists( $file ) )
            {
                $zip = new ZipArchive;
                if ($zip->open($file) === TRUE) {

                    for($i = 0; $i < $zip->numFiles; $i++) 
                    {
                       $filename = $zip->getNameIndex($i);
                       if( is_dir( TTR_MW_TEMPLATES_PATH . $filename  ) )
                       {
                            $this->delete_path( TTR_MW_TEMPLATES_PATH . $filename );
                       }
                    }

                    $zip->extractTo( TTR_MW_TEMPLATES_PATH );
                    $zip->close();

                    unlink( $file );

                    $wp_filesystem->get_contents( admin_url() );
                    sleep(1);

                    wp_redirect( admin_url( "admin.php?page=muse-to-wordpress-setting" ) );
                    exit();
                }
            }
        }
        $this->musetowordpress_admin_style();
        ?>
        <div class="wrap">
            <h2><img src="<?php echo TTR_MW_PLUGIN_URL . "images/logo.png"; ?>"> <?php  _e('Upload Website', 'muse-to-wordpress') ?></h2> <br/>
            <form  method="post" enctype="multipart/form-data">
                <label><?php _e('Upoad a .zip of your project(s)' , 'muse-to-wordpress'); ?></label><br/><br/>
                <input name="mtw-zip" type="file" accept=".zip,application/octet-stream,application/zip,application/x-zip,application/x-zip-compressed" />
                <input type="submit" value="<?php _e('Send it', 'muse-to-wordpress') ?>" />
            </form>
            <br/><hr/><br/>
            <p>
                <em><?php _e('The structure of your project in zip will always be the same: one folder by project.', 'muse-to-wordpress'); ?><br/>
                <?php _e('If the folder already exists on the server, the entire project will be overwritten.', 'muse-to-wordpress' ); ?><br/></em>
                <br/><br/>
                <b><?php _e('Example with one project' , 'muse-to-wordpress'); ?></b><br/><br/>
                <img src="<?php echo TTR_MW_PLUGIN_URL . "images/zip-one-project.png"; ?>">
                <br/><br/><br/>
                <b><?php _e('Example with two project' , 'muse-to-wordpress') ?></b><br/><br/>
                <img src="<?php echo TTR_MW_PLUGIN_URL . "images/zip-2-projects.png"; ?>">
            </p>
        </div>
        <?php
    }

    public function create_admin_page_mwuser()
    {
        $this->musetowordpress_admin_style();
        ?>
        <div class="wrap">
            <h2><img src="<?php echo TTR_MW_PLUGIN_URL . "images/logo.png"; ?>"> <?php  _e('Mwuser only', 'muse-to-wordpress') ?></h2>  
        </div>
        <?php
    }

    public function musetowordpress_admin_style()
    {
        $current_screen = get_current_screen();
        if( $current_screen->parent_base == 'muse-to-wordpress-setting' )
        {
        ?>
        <style type="text/css">
            #wpwrap, .wp-toolbar
            {
                background: #23242B;
                background: linear-gradient(to right,#23242B 44%,#04080A );
            }
            .wrap
            {
                max-width: 500px;
            }
            #page-linker
            {
                /*display: none;*/
            }
            ul#adminmenu a.wp-has-current-submenu::after,
            ul#adminmenu > li.current > a.current::after
            {
                border-right-color: #23242B !important;
            }
            h1, h2, h3
            {
                color: #E0E1E4;
            }
            .form-table th, label, #wpwrap p, .mtw-check-value
            {
                color: #C2C2C5;
            }
            .wp-admin input,
            .wp-admin select,
            .wp-admin textarea,
            .wp-admin button
            {
                color: #C2C2C5;
                background: rgba( 30,31,38,1 );
                border: none;
            }
            .wp-admin button
            {
                padding: 10px 10px;
            }
            .wp-admin button:hover
            {
                color: #00CC6D;
            }
            .wp-admin textarea,
            .wp-admin input[type=text]
            {
                width: 100%;
                margin-top: 10px;
            }
            .wp-admin select
            {
                width: 250px;    
            }
            
            .update-all-links
            {
                color: #00CC6D;
            }
            hr 
            {
                border: 0;
                border-bottom: 1px solid rgba( 30,31,38,1 );
            }
            a[href="admin.php?page=mtw-Mwuser"]
            {
                color: #00CC6D !important;
            }
            .wp-admin input:disabled,
            .wp-admin select:disabled
            {
                opacity: 0.5;
            }
            .form-table
            {
                max-width: 450px;
            }
            .mtw-check-value
            {
                cursor: pointer;
            }
            .notice 
            {
                background: rgba( 30,31,38,1 ) !important;
            }
            .notice p
            {
                color: #F4F4F2 !important;
            }
        </style>
        <script type="text/javascript">
        jQuery(document).ready(function($) {

            function mtw_verify_check_box()
            {
                $('input[type="checkbox"]').next('.mtw-check-value').css('color', '#8B8C91').text('false');
                $('input[type="checkbox"]:checked').next('.mtw-check-value').css('color', '#00CC6D').text('true');
            }
            mtw_verify_check_box();
            $('input[type="checkbox"]').on('change', function(event) {
                mtw_verify_check_box();
            });
            $('.mtw-check-value').click(function(event) {
                event.preventDefault();
                if( $('.mtw-check-value').prev('input[type="checkbox"]:checked').length == 1 )
                {
                    $('.mtw-check-value').prev('input[type="checkbox"]:checked').prop('checked', false);
                }
                else
                {
                    $('.mtw-check-value').prev('input[type="checkbox"]').prop('checked', true);
                }
                mtw_verify_check_box();        
            });
        });
        </script>
        <?php
        }
    }
    
    

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->musetowordpress_admin_style();
        
        ?>
        <?php

        $this->options = get_option( 'mtw_option' );
        ?>
        <div class="wrap">
            <h2><img src="<?php echo TTR_MW_PLUGIN_URL . "images/logo.png"; ?>"> Muse to Wordpress</h2>  
            <br/><br/>
            
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'mtw_option_group' );   
                do_settings_sections( 'muse-to-wordpress-setting' );
                submit_button(); 
            ?>
            </form>
        
            <br/><hr/><br/>
            <a href="?page=muse-to-wordpress-setting&update_all_logic_links=1" class="update-all-links">Update all links</a>
            <p>Use "Update all links", If you are working <br/>without "Synchronize Muse and Wordpress Pages automatically".<br/><br/>
            If you have an error on one link, try it one time. If that's not working <a target="_blank" href="http://musetowordpress.com/#contact-us">contact us.</a></p>
            <br/><hr/><br/>
            <a target="_blank" href="http://muse-to-wordpress.com">Learn more about Muse to Wordpress</a>
            <br/><br/>
            <p>
                Muse to WordPress is and will remain free and open-source forever,<br/>
                included all essentials elements and learning.
                <br/><br/>
                Why <a href="http://musetowordpress.com/join-us/">become a Mwuser</a> ?<br/>
                To benefit from a comfort of use and allow the project to evolve.
            </p>
        </div>
        <br/><br/>
        <div id="page-linker">
        <?php
        ttr_page_linker();
        ?>
        </div>
        <?php
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
            __('Synchronize Muse and Wordpress Pages automatically', 'muse-to-wordpress'), // Title 
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
            '<input type="checkbox" id="'.$id.'" name="mtw_option['.$id.']" value="checked" %s /> <span class="mtw-check-value">'.__('yes', 'muse-to-wordpress').'</span>',
            isset( $this->options[$id] ) ? esc_attr( $this->options[$id]) : ''
        );
    }

}

if( is_admin() )
{
    $MtwSettingsPage = new MtwSettingsPage();
}

?>