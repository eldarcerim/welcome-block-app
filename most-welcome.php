<?php
/**
 * Plugin Name: Welcome Block App – Community Services
 * Description: Besplatan dvojezični uvodni blok s dvije fotografije, tekstom i dugmetom za centre koji pružaju usluge u zajednici.
 * Version: 1.1.0
 * Author: PONTEM – Sociopedagoški centar MOST
 * Text Domain: most-welcome
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

final class MOST_Welcome_Plugin {
    const VERSION = '1.1.0';
    const OPTION = 'most_welcome_settings';
    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode('most_welcome', array($this, 'shortcode'));
        add_shortcode('welcome_block', array($this, 'shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'register_assets'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
        add_action('admin_post_most_welcome_save', array($this, 'save_settings'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_links'));
    }

    public static function activate() {
        if (!get_option(self::OPTION)) {
            add_option(self::OPTION, self::defaults());
        }
    }

    private static function defaults() {
        return array(
            'left_image_id' => 0,
            'right_image_id' => 0,
            'heading_bs' => 'Dobro došli!',
            'text_bs' => 'Sociopedagoški centar MOST je mjesto gdje kroz igru, učenje, zajedničku saradnju i učestvovanje ostvarujemo najbolje potencijale djece, mladih i roditelja.',
            'button_label_bs' => 'Saznaj više',
            'heading_en' => 'Welcome!',
            'text_en' => 'MOST Social Pedagogy Centre is a place where, through play, learning, cooperation and active participation, we help children, young people and parents reach their full potential.',
            'button_label_en' => 'Learn more',
            'button_url' => 'https://centar-most.ba/#o-centru',
        );
    }

    private function settings() {
        $stored = get_option(self::OPTION, array());
        if (!isset($stored['heading_bs']) && isset($stored['heading'])) {
            $stored['heading_bs'] = $stored['heading'];
        }
        if (!isset($stored['text_bs']) && isset($stored['text'])) {
            $stored['text_bs'] = $stored['text'];
        }
        if (!isset($stored['button_label_bs']) && isset($stored['button_label'])) {
            $stored['button_label_bs'] = $stored['button_label'];
        }
        return wp_parse_args($stored, self::defaults());
    }

    private function language($requested = 'auto') {
        $requested = strtolower(sanitize_key($requested));
        if (in_array($requested, array('bs', 'en'), true)) {
            return $requested;
        }
        $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
        return strpos(strtolower((string) $locale), 'en') === 0 ? 'en' : 'bs';
    }

    public function register_assets() {
        wp_register_style('most-welcome', plugins_url('assets/most-welcome.css', __FILE__), array(), self::VERSION);
    }

    public function shortcode($atts = array()) {
        wp_enqueue_style('most-welcome');
        $atts = shortcode_atts(array('lang' => 'auto'), $atts, 'welcome_block');
        $lang = $this->language($atts['lang']);
        $settings = $this->settings();
        $left = $this->image_data(absint($settings['left_image_id']), $lang === 'en' ? 'Left photo of MOST Centre' : 'Lijeva fotografija Centra MOST');
        $right = $this->image_data(absint($settings['right_image_id']), $lang === 'en' ? 'Right photo of MOST Centre' : 'Desna fotografija Centra MOST');
        $has_images = $left || $right;
        $heading = $settings['heading_' . $lang];
        $text = $settings['text_' . $lang];
        $button_label = $settings['button_label_' . $lang];
        $title_id = function_exists('wp_unique_id') ? wp_unique_id('most-welcome-title-') : 'most-welcome-title-' . $lang;

        ob_start();
        ?>
        <section class="most-welcome<?php echo $has_images ? ' has-images' : ''; ?>" lang="<?php echo esc_attr($lang); ?>" aria-labelledby="<?php echo esc_attr($title_id); ?>">
            <div class="most-welcome-inner">
                <?php if ($left) : ?>
                    <figure class="most-welcome-image most-welcome-image-left">
                        <img src="<?php echo esc_url($left['url']); ?>" alt="<?php echo esc_attr($left['alt']); ?>">
                    </figure>
                <?php endif; ?>

                <div class="most-welcome-content">
                    <span class="most-welcome-dots" aria-hidden="true"></span>
                    <h2 id="<?php echo esc_attr($title_id); ?>"><?php echo esc_html($heading); ?></h2>
                    <p><?php echo esc_html($text); ?></p>
                    <a class="most-welcome-button" href="<?php echo esc_url($settings['button_url']); ?>"><?php echo esc_html($button_label); ?></a>
                </div>

                <?php if ($right) : ?>
                    <figure class="most-welcome-image most-welcome-image-right">
                        <img src="<?php echo esc_url($right['url']); ?>" alt="<?php echo esc_attr($right['alt']); ?>">
                    </figure>
                <?php endif; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    private function image_data($attachment_id, $fallback_alt) {
        if (!$attachment_id) {
            return null;
        }
        $url = wp_get_attachment_image_url($attachment_id, 'large');
        if (!$url) {
            return null;
        }
        $alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
        return array('url' => $url, 'alt' => $alt !== '' ? $alt : $fallback_alt);
    }

    public function plugin_links($links) {
        array_unshift($links, '<a href="' . esc_url(admin_url('admin.php?page=most-welcome')) . '">Postavke</a>');
        return $links;
    }

    public function admin_menu() {
        add_menu_page('Welcome Block App', 'Welcome Block', 'manage_options', 'most-welcome', array($this, 'settings_page'), 'dashicons-format-image', 27);
    }

    public function admin_assets() {
        if (isset($_GET['page']) && sanitize_key(wp_unslash($_GET['page'])) === 'most-welcome') {
            wp_enqueue_media();
        }
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $settings = $this->settings();
        ?>
        <div class="wrap">
            <h1>Welcome Block App</h1>
            <?php if (isset($_GET['updated'])) : ?><div class="notice notice-success is-dismissible"><p>Postavke su sačuvane.</p></div><?php endif; ?>
            <p>Bosanski blok: <code>[welcome_block lang="bs"]</code> &nbsp; Engleski blok: <code>[welcome_block lang="en"]</code>. Bez atributa se koristi jezik WordPress stranice. Stari <code>[most_welcome]</code> shortcode ostaje podržan.</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="most_welcome_save">
                <?php wp_nonce_field('most_welcome_save'); ?>
                <table class="form-table">
                    <?php $this->image_field('left_image_id', 'Lijeva fotografija', absint($settings['left_image_id'])); ?>
                    <?php $this->image_field('right_image_id', 'Desna fotografija', absint($settings['right_image_id'])); ?>
                    <tr><th colspan="2"><h2>Bosanski tekst</h2></th></tr>
                    <tr><th><label for="most_heading_bs">Naslov</label></th><td><input id="most_heading_bs" type="text" class="regular-text" name="heading_bs" value="<?php echo esc_attr($settings['heading_bs']); ?>" maxlength="120" required></td></tr>
                    <tr><th><label for="most_text_bs">Tekst</label></th><td><textarea id="most_text_bs" class="large-text" name="text_bs" rows="5" maxlength="800" required><?php echo esc_textarea($settings['text_bs']); ?></textarea></td></tr>
                    <tr><th><label for="most_button_label_bs">Tekst dugmeta</label></th><td><input id="most_button_label_bs" type="text" class="regular-text" name="button_label_bs" value="<?php echo esc_attr($settings['button_label_bs']); ?>" maxlength="80" required></td></tr>
                    <tr><th colspan="2"><h2>English text</h2></th></tr>
                    <tr><th><label for="most_heading_en">Heading</label></th><td><input id="most_heading_en" type="text" class="regular-text" name="heading_en" value="<?php echo esc_attr($settings['heading_en']); ?>" maxlength="120" required></td></tr>
                    <tr><th><label for="most_text_en">Text</label></th><td><textarea id="most_text_en" class="large-text" name="text_en" rows="5" maxlength="800" required><?php echo esc_textarea($settings['text_en']); ?></textarea></td></tr>
                    <tr><th><label for="most_button_label_en">Button label</label></th><td><input id="most_button_label_en" type="text" class="regular-text" name="button_label_en" value="<?php echo esc_attr($settings['button_label_en']); ?>" maxlength="80" required></td></tr>
                    <tr><th><label for="most_button_url">Link dugmeta</label></th><td><input id="most_button_url" type="url" class="regular-text" name="button_url" value="<?php echo esc_attr($settings['button_url']); ?>" required></td></tr>
                </table>
                <?php submit_button('Sačuvaj postavke'); ?>
            </form>
        </div>
        <script>
        jQuery(function($){
            var frame;
            $('.most-welcome-pick').on('click', function(e){
                e.preventDefault();
                var target = $(this).data('target');
                frame = wp.media({title:'Odaberite fotografiju', button:{text:'Koristi fotografiju'}, multiple:false});
                frame.on('select', function(){
                    var image = frame.state().get('selection').first().toJSON();
                    $('#' + target).val(image.id);
                    $('#' + target + '_preview').html('<img src="' + image.url + '" alt="">');
                    $('[data-remove="' + target + '"]').show();
                });
                frame.open();
            });
            $('.most-welcome-remove').on('click', function(e){
                e.preventDefault();
                var target = $(this).data('remove');
                $('#' + target).val('0');
                $('#' + target + '_preview').empty();
                $(this).hide();
            });
        });
        </script>
        <style>
        .most-welcome-preview img{display:block;width:260px;height:150px;margin:0 0 10px;object-fit:cover;border-radius:12px}.most-welcome-remove{margin-left:6px!important}
        </style>
        <?php
    }

    private function image_field($name, $label, $attachment_id) {
        $url = $attachment_id ? wp_get_attachment_image_url($attachment_id, 'medium') : '';
        ?>
        <tr>
            <th><?php echo esc_html($label); ?></th>
            <td>
                <input type="hidden" id="<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo absint($attachment_id); ?>">
                <div class="most-welcome-preview" id="<?php echo esc_attr($name); ?>_preview"><?php if ($url) : ?><img src="<?php echo esc_url($url); ?>" alt=""><?php endif; ?></div>
                <button type="button" class="button most-welcome-pick" data-target="<?php echo esc_attr($name); ?>">Odaberi fotografiju</button>
                <button type="button" class="button most-welcome-remove" data-remove="<?php echo esc_attr($name); ?>"<?php echo $url ? '' : ' style="display:none"'; ?>>Ukloni</button>
            </td>
        </tr>
        <?php
    }

    public function save_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('Nemate ovlaštenje.');
        }
        check_admin_referer('most_welcome_save');
        $defaults = self::defaults();
        $button_url = esc_url_raw(wp_unslash($_POST['button_url'] ?? $defaults['button_url']));
        update_option(self::OPTION, array(
            'left_image_id' => absint($_POST['left_image_id'] ?? 0),
            'right_image_id' => absint($_POST['right_image_id'] ?? 0),
            'heading_bs' => sanitize_text_field(wp_unslash($_POST['heading_bs'] ?? $defaults['heading_bs'])),
            'text_bs' => sanitize_textarea_field(wp_unslash($_POST['text_bs'] ?? $defaults['text_bs'])),
            'button_label_bs' => sanitize_text_field(wp_unslash($_POST['button_label_bs'] ?? $defaults['button_label_bs'])),
            'heading_en' => sanitize_text_field(wp_unslash($_POST['heading_en'] ?? $defaults['heading_en'])),
            'text_en' => sanitize_textarea_field(wp_unslash($_POST['text_en'] ?? $defaults['text_en'])),
            'button_label_en' => sanitize_text_field(wp_unslash($_POST['button_label_en'] ?? $defaults['button_label_en'])),
            'button_url' => $button_url ?: $defaults['button_url'],
        ));
        wp_safe_redirect(admin_url('admin.php?page=most-welcome&updated=1'));
        exit;
    }
}

register_activation_hook(__FILE__, array('MOST_Welcome_Plugin', 'activate'));
MOST_Welcome_Plugin::instance();
