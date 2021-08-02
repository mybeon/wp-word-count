<?php
/*
    Plugin Name: wp word count
    description: this is my first plugin in wordpress
    version: 1.0.0
    author: hicham
*/


if ( ! defined('ABSPATH') ) {
    exit("you cannot acces directly");
}

class wpWordCountPlugin {
    function __construct() {
        add_action('admin_menu', array($this, "menubar"));
        add_action('admin_init', array($this, 'settings'));
        add_filter("the_content", array($this, "contentLogic"));
    }

    function contentLogic($content) {
        if ( is_main_query() && is_single() && 
        ( get_option("wpwc_wordcount", "1") || 
        get_option("wpwc_charactercount", "1") ||  
        get_option("wpwc_readtime", "1")) ) 
            {
                return $this->add_content($content);
        }
        return $content;
    }

    function add_content($content) {
        $html = "<h3>" . get_option("wpwc_headline", "Post Statistics") . "</h3><p>";

        if( get_option("wpwc_wordcount", "1") || get_option("wpwc_readtime", "1") ) {
            $wordcount = str_word_count(strip_tags($content));
        }

        if (get_option("wpwc_wordcount", "1")) {
            $html .= "this post has about " . $wordcount . " words</br>";
        }

        if (get_option("wpwc_charactercount", "1")) {
            $html .= "this post has about " . strlen(strip_tags($content)) . " characters</br>";
        }

        if (get_option("wpwc_readtime", "1")) {
            $html .= "this post can take about " . round($wordcount/225) . " minute(s) to read</br>";
        }

        $html .= "</p>";

        if (get_option("wpwc_location", "0") == "0") {
            return $html . $content;
        }
        return $content .  $html;
    } 

    function settings() {
        add_settings_section( "wpwc_main_section", null, array($this, "sectionHtml"), "wp-word-count" );
        
        // option setting

        add_settings_field( "wpwc_location", "Display Location", array($this, "locationHTML"), "wp-word-count", "wpwc_main_section" );
        
        register_setting( "wpwcplugingroup", "wpwc_location", array(
            "sanitize_callback" => "sanitize_text_field",
            "default" => "0"
        ));

        // headline setting

        add_settings_field( "wpwc_headline", "Title", array($this, "headlineHTML"), "wp-word-count", "wpwc_main_section" );
        
        register_setting( "wpwcplugingroup", "wpwc_headline", array(
            "sanitize_callback" => "sanitize_text_field",
            "default" => "Post Statistics"
        ));

        //checkbox setting

        add_settings_field( "wpwc_wordcount", "Word count", array($this, "checkboxHTML"), "wp-word-count", "wpwc_main_section" , array(
            "name" => "wpwc_wordcount"
        ));
        
        register_setting( "wpwcplugingroup", "wpwc_wordcount", array(
            "sanitize_callback" => array($this, "checkboxsanitize_word"),
            "default" => "1"
        ));

        add_settings_field( "wpwc_charactercount", "Character count", array($this, "checkboxHTML"), "wp-word-count", "wpwc_main_section" , array(
            "name" => "wpwc_charactercount"
        ));
        
        register_setting( "wpwcplugingroup", "wpwc_charactercount", array(
            "sanitize_callback" => array($this, "checkboxsanitize_character"),
            "default" => "1"
        ));

        add_settings_field( "wpwc_readtime", "Read time", array($this, "checkboxHTML"), "wp-word-count", "wpwc_main_section", array(
            "name" => "wpwc_readtime"
        ) );
        
        register_setting( "wpwcplugingroup", "wpwc_readtime", array(
            "sanitize_callback" => array($this, "checkboxsanitize_read"),
            "default" => "1"
        ));
    }

    function checkboxsanitize_word($input) {
        if ($input != "1" && $input != "") {
            add_settings_error("wpwc_wordcount", "wpwc_wordcount_error", "input is not valid");
            return get_option("wpwc_wordcount");
        }
        return $input;
    }

    function checkboxsanitize_character($input) {
        if ($input != "1" && $input != "") {
            add_settings_error("wpwc_charactercount", "wpwc_charactercount_error", "input is not valid");
            return get_option("wpwc_charactercount");
        }
        return $input;
    }

    function checkboxsanitize_read($input) {
        if ($input != "1" && $input != "") {
            add_settings_error("wpwc_readtime", "wpwc_readtime_error", "input is not valid");
            return get_option("wpwc_readtime");
        }
        return $input;
    }


    function checkboxHTML($args) { ?>
        <input type="checkbox" name="<?php echo $args["name"]?>" <?php checked( get_option($args["name"]), "1" ) ?> value="1">
    <?php }

    function headlineHTML() { ?>
        <input type="text" name="wpwc_headline" value="<?php echo esc_attr(get_option("wpwc_headline")) ?>">
    <?php }

    function locationHTML() { ?>
        <select name="wpwc_location" >
            <option value="0"  <?php selected( get_option("wpwc_location"), "0" ) ?>>Start of the article</option>
            <option value="1" <?php selected( get_option("wpwc_location"), "1" ) ?>>End of the article</option>
        </select>
    <?php }

    function sectionHTML() {
        echo "<p>Welcome to the settings page where you can choose from different options to customize your experience. Happy bloging!</p>";
    }

    function menubar() {
        add_menu_page( 'WP Word Count settings', 'wpwc', 'manage_options', 'wp-word-count', array($this, 'adminHTML'), 'dashicons-text-page', 100 );
    }

    function adminHTML() {  ?>
        <div class="wrap">
            <h1>Word Count Plugin</h1>
            <form action="options.php" method="POST">
                <?php
                    settings_errors();
                    settings_fields( "wpwcplugingroup" );
                    do_settings_sections( "wp-word-count" );
                    submit_button();
                ?>
            </form>
        </div>
<?php }
} 


$wpwordcountplugin = new wpWordCountPlugin();