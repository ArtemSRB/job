<?php
/* Stylesheet include */
add_action( 'wp_enqueue_scripts', 'enqueue_flagiconswitcher' );
add_action( 'admin_enqueue_scripts', 'enqueue_flagiconswitcher' );

function enqueue_flagiconswitcher() {
    wp_enqueue_style('flagiconswitcher', FLAGS_URI . '/assets/css/style.css' );
}

function flagicons_register_script() {
    wp_enqueue_script( 'flagicons_more', FLAGS_URI . '/scripts/moreflag.js');
}
add_action( 'admin_enqueue_scripts', 'flagicons_register_script' );
/*add_action( 'wp_enqueue_scripts', 'flagicons_register_script' );*/

/* Shortcode */
add_shortcode('flagswitcher', 'flagswitcher_func');

function flagswitcher_func () {
    ob_start();
    include(FLAGS_HOME . '/template/template.php');
    return ob_get_clean();
}

/* Settings */
class FlagIconsSwitcher {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }
    /**
     * Add options page
     */
    public function add_plugin_page()
    {
// This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'Flag Icons Setting',
            'manage_options',
            'flagicons_setting',
            array( $this, 'create_admin_page' )
        );
    }
    /**
     * Options page callback
     */
    public function create_admin_page()
    {
// Set class property
        $this->options = get_option( 'flagi_option' );
        ?>
        <div class="wrap">
            <h2>Flag options</h2>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields( 'flag_group' );
                do_settings_sections( 'flagicons_setting' );
                submit_button();
                ?>

            </form>

            <div class="flagicons_support_notes">
                Current full supported flags are: "en, de, dk, se, no, it, ru, fi"
            </div>

        </div>
        <?php
    }
    public function page_init()
    {
        register_setting(
            'flag_group', // Option group
            'flagi_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );
        add_settings_section(
            'flag_section', // ID
            'Flagicons settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'flagicons_setting' // Page
        );
        add_settings_field(
            'add_flag', // ID
            'Current Flags', // Title
            array( $this, 'add_flag' ), // Callback
            'flagicons_setting', // Page
            'flag_section' // Section
        );
        add_settings_field(
            'link_to_add', // ID
            '', // Title
            array( $this, 'link_to_add' ), // Callback
            'flagicons_setting', // Page
            'flag_section' // Section
        );
        if (empty(get_option('flagi_option'))) {
            $def_set = array(
                'add_flag' => 'en',
                'link_to_add' => get_site_url(),
            );
            update_option('flagi_option', $def_set);
        }
    }
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['add_flag'] ) )
            $new_input['add_flag'] = array_filter( $input['add_flag'] );
        if( isset( $input['link_to_add'] ) )
            $new_input['link_to_add'] = array_filter( $input['link_to_add'] );
        return $new_input;
    }
    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function add_flag()
    {
        $states = $this->options['add_flag'];
        $domains = $this->options['link_to_add'];
        $results = array_combine($states, $domains);
        if($results):
            foreach($results as $key => $value) {
                if($key != "" && $value != "") { ?>
                    <tr>
                        <td>State:<br> <input type="text" id="add_flag" name="flagi_option[add_flag][]"
                                              value="<?php echo $key; ?>" placeholder=""/></td>
                        <td>Domain link:<br> <input type="text" id="link_to_add" name="flagi_option[link_to_add][]"
                                                    value="<?php echo $value; ?>" placeholder=""/> <img src="<?php echo FLAGS_URI; ?>/assets/img/32/<?php echo $key ?>.png"></td>
                    </tr>
                    <?
                }
            }
        endif; ?>
        <div id="dynamicInput" class="add-new-state">
            <tr><th>Add More State's / Flag's</th></tr>
            <tr>
                <td>State:<br> <input type="text" id="add_flag" name="flagi_option[add_flag][]" placeholder="en" /></td>
                <td>Domain link:<br> <input type="text" id="link_to_add" name="flagi_option[link_to_add][]" placeholder="<?php echo get_site_url(); ?>" /></td>
            </tr>
            <tr class="flag_backup"></tr>
            <tr>
                <td><input class="add-more-state" type="button" value=" + " ></td>
            </tr>
        </div>
        <?php
    }
    public function link_to_add() {
    }

}

if( is_admin() ) :
    $my_settings_page = new FlagIconsSwitcher();
endif;
