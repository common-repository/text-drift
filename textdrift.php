<?php
/**
 * Plugin Name: Text Drift
 * Description: Take the conversation directly to the customer with Text Drift.
 * Version: 1.0.0
 * Author: Intrakit Media
 * Author URI: intrakitmedia.com
 */

/**
 * create settings link in plugins list page
 */
if (!function_exists('textdrift_settings_link')) {
    function textdrift_settings_link($links) {
    $settings_link = '<a href="admin.php?page=textdrift-settings-page">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
    }
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'textdrift_settings_link' );

/**
 * show admin settings page
 */
if (!function_exists('textdrift_settings_menu')) {
    function textdrift_settings_menu() {
        add_menu_page(
            __('Text Drift Settings', 'textdrift'), // page_title
            __('Text Drift', 'textdrift'), // menu_title
            'manage_options', // capability
            'textdrift-settings-page', // menu_slug
            'textdrift_settings_template_callback', // function to show UI
            '',
            null
        );
    }
}
add_action('admin_menu', 'textdrift_settings_menu');

if (!function_exists('textdrift_settings_template_callback')) {
    function textdrift_settings_template_callback() {
    ?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title());?></h1>
    <form action="options.php" method="post">
        <?php
            // security check
            settings_fields('textdrift-settings-page');

            // show form elements
            do_settings_sections('textdrift-settings-page');

            // save button
            submit_button('Save Settings');
            ?>
    </form>
</div>
<?php
    }
}

/**
 * Settings template
 */
if (!function_exists('textdrift_settings_init')) {
    function textdrift_settings_init() {
        // Setup settings section
        add_settings_section(
            'textdrift_settings_section',
            '',
            '',
            'textdrift-settings-page'
        );

        // Register input field
        register_setting(
            'textdrift-settings-page',
            'textdrift_appkey',
            array(
            'type' => 'string',
            'sanityze_callback' => 'sanityze_text_field',
            'default' => ''
            )
        );

        // Add textbox field
        add_settings_field(
            'textdrift_appkey',
            __('App Key', 'textdrift'),
            'textdrift_settings_input_field_callback',
            'textdrift-settings-page',
            'textdrift_settings_section'
        );

        // Register radio field
        register_setting(
            'textdrift-settings-page',
            'textdrift_active',
            array(
            'type' => 'string',
            'sanityze_callback' => 'sanityze_text_field',
            'default' => '1'
            )
        );

        // Add radio Field
        add_settings_field(
            'textdrift_active',
            __('Status', 'textdrift'),
            'textdrift_settings_radio_field_callback',
            'textdrift-settings-page',
            'textdrift_settings_section'
        );

        // Register profile photo hidden field
        register_setting(
            'textdrift-settings-page',
            'textdrift_photo',
            array(
            'type' => 'string',
            'sanityze_callback' => 'sanityze_text_field',
            'default' => ''
            )
        );

        // Add profile photo field
        add_settings_field(
            'textdrift_photo',
            __('Profile Picture', 'textdrift'),
            'textdrift_settings_photo_field_callback',
            'textdrift-settings-page',
            'textdrift_settings_section'
        );
    }
}
add_action('admin_init', 'textdrift_settings_init');

/**
 * Create a function to show textbox
 */
if (!function_exists('textdrift_settings_input_field_callback')) {
    function textdrift_settings_input_field_callback() {
        $textdrift_appkey = get_option('textdrift_appkey');
        ?>
<input type="text" name="textdrift_appkey" class="regular-text"
    value="<?php echo isset($textdrift_appkey) ? esc_attr($textdrift_appkey) : ""; ?>" />
<?php
    }
}

/**
 * Create a function to show radio buttons
 */
if (!function_exists('textdrift_settings_radio_field_callback')) {
    function textdrift_settings_radio_field_callback() {
        $textdrift_active = get_option('textdrift_active');
    ?>
<label for="1">
    <input type="radio" name="textdrift_active" class="regular-text" value="1"
        <?php checked("1", $textdrift_active);?> /> Active
</label>
<label for="0" style="margin-left:15px">
    <input type="radio" name="textdrift_active" class="regular-text" value="0"
        <?php checked("0", $textdrift_active);?> /> Disable
</label>
<?php
    }
}

/**
 * Create a function to have hidden field to have profile photo url
 */
if (!function_exists('textdrift_settings_photo_field_callback')) {
    function textdrift_settings_photo_field_callback() {
        $textdrift_photo = get_option('textdrift_photo');
        ?>
<input type="hidden" id="textdrift_photo" name="textdrift_photo" class="regular-text"
    value="<?php echo isset($textdrift_photo) ? esc_attr($textdrift_photo) : ""; ?>" />
<input type="button" name="uploadBtn" id="uploadBtn" class="class-control" value="Upload Profile Picture" />
<?php
    if(isset($textdrift_photo) && $textdrift_photo!="") {
        $src = $textdrift_photo;
        $width = 100;
        $hideCss = 'display:block';
    } else {
        $src = "#";
        $width = 0;
        $hideCss = 'display:none';
    }
    ?>
<br />
<img id="profilePicThumb" src="<?php echo esc_url($src);?>" width="<?php echo esc_attr($width);?>"
    style="border:1px solid #cdcdcd;border-radius:10px;margin-top:20px" />
<br />
<a href="#" id="deletePicture" style="text-decoration:none;<?php echo esc_attr($hideCss);?>">Delete</a>
<?php
    }
}

/**
 * add script tag and js, just before </body>
 */
if (!function_exists('textdrift_load_js_script')) {
    function textdrift_load_js_script() {
        $textdrift_appkey = get_option('textdrift_appkey');
        $textdrift_active = get_option('textdrift_active');
        $textdrift_photo = get_option('textdrift_photo');
        if($textdrift_appkey != '' && $textdrift_active == 1) {
    ?>
<script type=" text/javascript">
var textdrift_appkey = "<?php echo esc_js($textdrift_appkey);?>";
var textdrift_photo = "<?php echo esc_js($textdrift_photo);?>";
window.TEMPOCHATCONFIG = {
    appkey: textdrift_appkey,
    profileimage: textdrift_photo,
    urlconfig: true
};
(function(c, h, a, t) {
    let s, n;
    c.settings = {
        appv: 1
    };
    s = h.getElementsByTagName('head')[0];
    n = h.createElement('script');
    n.async = 1;
    n.src = a + t + c.settings.appv;
    s.appendChild(n);
})(window, document, 'https://app.textdrift.com/c/chat.js', '?cav=');
</script>
<?php
        }
    }
}
add_action("wp_footer", "textdrift_load_js_script");

// add textdrift_load_wp_enq_media in header on /wp-admin/admin.php?page=textdrift-settings-page
if (!function_exists('textdrift_load_wp_enq_media')) {
    function textdrift_load_wp_enq_media() {
        if(stristr($_SERVER['REQUEST_URI'], 'admin.php?page=textdrift-settings-page')) {
            wp_enqueue_media();
        }
    }
}
add_action("admin_head", "textdrift_load_wp_enq_media");

// add below jquery before </body> on /wp-admin/admin.php?page=textdrift-settings-page
if (!function_exists('textdrift_load_js_upload_button')) {
    function textdrift_load_js_upload_button() {
        if(stristr($_SERVER['REQUEST_URI'], 'admin.php?page=textdrift-settings-page')) {
    ?>
<script>
;
jQuery(function() {
    jQuery("#uploadBtn").on('click', function() {
        var images = wp.media({
            title: "Upload Picture",
            multiple: false
        }).open().on('select', function(e) {
            var uploadedPics = images.state().get('selection').first();
            var selectedPic = uploadedPics.toJSON();
            jQuery("#textdrift_photo").val(selectedPic.url);
            jQuery("#profilePicThumb").attr('src', selectedPic.url);
            jQuery("#profilePicThumb").attr('width', "100");
        });
    });

    jQuery("#deletePicture").on('click', function() {
        jQuery("#textdrift_photo").val('');
        jQuery("#profilePicThumb").css('display', 'none');
        jQuery("#deletePicture").css('display', 'none');
    });

});
</script>
<?php
        }
    }
}
add_action("admin_footer", "textdrift_load_js_upload_button");