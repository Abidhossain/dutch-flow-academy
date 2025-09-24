<?php
/**
 * UpsellWP
 *
 * @package   checkout-upsell-woocommerce
 * @author    Anantharaj B <anantharaj@flycart.org>
 * @copyright 2024 UpsellWP
 * @license   GPL-3.0-or-later
 * @link      https://upsellwp.com
 */

namespace CUW\App\Pro\Modules\PostPurchase;

use CUW\App\Helpers\Config;
use CUW\App\Helpers\Plugin;

defined('ABSPATH') || exit;

class Templates
{
    /**
     * Templates data.
     *
     * @var array
     */
    private static $templates = [];

    /**
     * Register template post type.
     *
     * @return void
     */
    public static function registerPostTypes()
    {
        $labels = [
            'name' => esc_html_x('Templates', 'Template Library', 'checkout-upsell-woocommerce'),
            'singular_name' => esc_html_x('Template', 'Template Library', 'checkout-upsell-woocommerce'),
            'add_new' => esc_html__('Add New Template', 'checkout-upsell-woocommerce'),
            'add_new_item' => esc_html__('Add New Template', 'checkout-upsell-woocommerce'),
            'edit_item' => esc_html__('Edit Template', 'checkout-upsell-woocommerce'),
            'new_item' => esc_html__('New Template', 'checkout-upsell-woocommerce'),
            'all_items' => esc_html__('All Templates', 'checkout-upsell-woocommerce'),
            'view_item' => esc_html__('View Template', 'checkout-upsell-woocommerce'),
            'search_items' => esc_html__('Search Template', 'checkout-upsell-woocommerce'),
            'not_found' => esc_html__('No Templates found', 'checkout-upsell-woocommerce'),
            'not_found_in_trash' => esc_html__('No Templates found in Trash', 'checkout-upsell-woocommerce'),
            'parent_item_colon' => esc_html__('Parent Template:', 'checkout-upsell-woocommerce'),
            'menu_name' => esc_html_x('Templates', 'Template Library', 'checkout-upsell-woocommerce'),
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'rewrite' => false,
            'menu_icon' => 'dashicons-admin-page',
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'exclude_from_search' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => [
                'title',
                'thumbnail',
            ],
        ];

        if ($post_type = self::getPostType('wordpress')) {
            $wordpress_args = $args;
            $wordpress_args['show_in_rest'] = true;
            $wordpress_args['supports'][] = 'editor';
            register_post_type($post_type, $wordpress_args);
        }

        if ($post_type = self::getPostType('elementor')) {
            $elementor_args = $args;
            $elementor_args['supports'][] = 'elementor';
            register_post_type($post_type, $elementor_args);
        }
    }

    /**
     * Get template data.
     *
     * @param int|\WP_Post $post
     * @return array|false
     */
    public static function getData($post)
    {
        $template_post = get_post($post);
        if ($template_post && self::isValid($template_post)) {
            return [
                'post_id' => $template_post->ID,
                'name' => $template_post->post_name,
                'content' => $template_post->post_content,
                'builder' => array_flip(self::getPostTypes())[$template_post->post_type] ?? '',
                'url' => get_permalink($template_post),
            ];
        }
        return false;
    }

    /**
     * Check is template valid.
     *
     * @param int|\WP_Post $post
     * @return bool
     */
    public static function isValid($post)
    {
        $template_post = !empty($post) ? get_post($post) : null;
        return !empty($template_post)
            && $template_post->post_status == 'publish'
            && !empty(trim($template_post->post_content))
            && in_array($template_post->post_type, self::getPostTypes());
    }

    /**
     * Get template builders.
     *
     * @return array
     */
    private static function getPostTypes()
    {
        return [
            'wordpress' => 'cuw_wp_template',
            'elementor' => 'cuw_el_template',
        ];
    }

    /**
     * Get template post type.
     */
    public static function getPostType($builder)
    {
        return self::getPostTypes()[$builder] ?? '';
    }

    /**
     * Get templates.
     *
     * @param string $builder
     * @return array
     */
    public static function getTemplates($builder, $load = false)
    {
        if (isset(self::$templates[$builder]) && !$load) {
            return self::$templates[$builder];
        }

        $post_type = self::getPostTypes()[$builder] ?? '';
        if (empty($post_type)) {
            return [];
        }

        $template_posts = get_posts([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'numberposts' => -1,
            'order' => 'ASC',
        ]);

        $has_valid_templates = false;
        foreach ($template_posts as $template_post) {
            if (self::isValid($template_post)) {
                $has_valid_templates = true;
                break;
            }
        }

        if (!$has_valid_templates && $load && empty($_GET['loaded'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ($builder == 'wordpress') {
                self::loadWordPressTemplates();
            } elseif ($builder == 'elementor') {
                self::loadElementorTemplates();
            }
            wp_safe_redirect(add_query_arg('loaded', 1));
            exit;
        }

        $templates = [];
        foreach ($template_posts as $template_post) {
            if ($template_data = self::getData($template_post)) {
                $templates[$template_post->ID] = $template_data;
            }
        }

        self::$templates[$builder] = $templates;
        return $templates;
    }

    /**
     * Get template.
     */
    public static function getTemplate($builder, $template)
    {
        return self::getTemplates($builder)[$template] ?? false;
    }

    /**
     * Get template content.
     */
    public static function getTemplateContent($builder, $template)
    {
        return self::getTemplates($builder)[$template]['content'] ?? '';
    }

    /**
     * Get edit url.
     */
    public static function getEditUrl($template)
    {
        if ($data = self::getData($template)) {
            $url = get_edit_post_link($data['post_id']);
            if (!empty($url) && $data['builder'] == 'elementor') {
                $url = add_query_arg('action', 'elementor', remove_query_arg('action', $url));
            }
            return $url;
        }
        return '';
    }

    /**
     * Load WordPress templates.
     */
    private static function loadWordPressTemplates()
    {
        $templates = [
            'template-1' => [
                'name' => 'Template 1',
                'file' => 'template-1.html',
            ],
            'template-2' => [
                'name' => 'Template 2',
                'file' => 'template-2.html',
            ],
        ];
        foreach ($templates as $key => $template) {
            if (empty($template['content']) && !empty($template['file'])) {
                $content = file_get_contents(CUW_PLUGIN_PATH . '/templates/post-purchase/wordpress/' . $template['file']);
                if (!empty($content)) {
                    $template['content'] = $content;
                    self::insertTemplate('wordpress', $key, $template);
                }
            }
        }
    }

    /**
     * Load Elementor templates.
     */
    private static function loadElementorTemplates()
    {
        $templates = [
            'template-1' => [
                'name' => 'Template 1',
                'file' => 'template-1.json',
            ],
            'template-2' => [
                'name' => 'Template 2',
                'file' => 'template-2.json',
            ],
        ];
        foreach ($templates as $key => $template) {
            if (!empty($template['file'])) {
                $json = file_get_contents(CUW_PLUGIN_PATH . '/templates/post-purchase/elementor/' . $template['file']);
                if (!empty($json)) {
                    $template['content'] = '';
                    $template['meta'] = [
                        '_elementor_edit_mode' => 'builder',
                        '_elementor_template_type' => 'wp-post',
                        '_elementor_version' => '3.0.0',
                        '_elementor_pro_version' => '3.0.0',
                        '_wp_page_template' => 'default',
                    ];
                    $post_id = self::insertTemplate('elementor', $key, $template);
                    if ($post_id && $document = self::getElementorDocument($post_id)) {
                        $document->save([
                            'elements' => json_decode(trim($json), true),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Insert template.
     */
    public static function insertTemplate($builder, $template, $data)
    {
        if (empty($data) || empty($data['name'])) {
            return false;
        }
        $post_type = self::getPostType($builder);
        if (empty($post_type)) {
            return false;
        }

        $post_id = wp_insert_post([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'post_title' => $data['name'],
            'post_content' => $data['content'] ?? '',
            'ping_status' => 'closed',
            'comment_status' => 'closed',
            'meta_input' => array_merge(($data['meta'] ?? []), [
                'cuw_campaign_type' => 'post_purchase_upsells',
            ]),
        ]);
        if (!empty($post_id) && !is_wp_error($post_id) && is_numeric($post_id)) {
            return $post_id;
        }
        return false;
    }

    /**
     * Render template.
     *
     * @param int|array $template
     * @param bool $preview
     * @return string
     */
    public static function getHtml($template, $preview = false)
    {
        if (is_numeric($template)) {
            $template = self::getData($template);
        }
        if (empty($template)) {
            return '';
        }

        $html = '';
        $GLOBALS['cuw_ppu_preview'] = $preview;

        if (!empty($template['builder']) && !empty($template['post_id']) && $template['builder'] == 'elementor') {
            $html = self::getElementorTemplateHtml($template['post_id']);
        } elseif (!empty($template['content'])) {
            $html = do_shortcode($template['content']);
        }

        $html = apply_filters('cuw_post_purchase_template_html', $html, $template);
        unset($GLOBALS['cuw_ppu_preview']);
        return $html;
    }

    /**
     * Get elementor template html.
     */
    public static function getElementorTemplateHtml($post_id, $with_css = true)
    {
        if ($document = self::getElementorDocument($post_id)) {
            ob_start();
            $document->print_elements_with_wrapper();
            if ($with_css && class_exists('\Elementor\Core\Files\CSS\Post')) {
                (new \Elementor\Core\Files\CSS\Post($post_id))->print_css();
            }
            return ob_get_clean();
        }
        return '';
    }

    /**
     * Get elementor document object.
     *
     * @return \Elementor\Core\Base\Document|false
     */
    public static function getElementorDocument($post_id)
    {
        return Plugin::isElementorActive() && class_exists('Elementor\Plugin')
            ? \Elementor\Plugin::instance()->documents->get($post_id) : false;
    }

    /**
     * Default template data.
     */
    public static function getDefaultTemplateData()
    {
        __('Add offer to my order', 'checkout-upsell-woocommerce');
        __('Decline offer', 'checkout-upsell-woocommerce');
        __('{discount} offer only for you!', 'checkout-upsell-woocommerce');
        __('Claim this offer before its gone. Add to your order with just one-click of the button.', 'checkout-upsell-woocommerce');
        __('Offer expires in: <strong>{minutes}:{seconds}</strong>', 'checkout-upsell-woocommerce');

        return [
            'title' => '{discount} offer only for you!',
            'description' => 'Claim this offer before its gone. Add to your order with just one-click of the button.',
            'accept_text' => 'Add offer to my order',
            'decline_text' => 'Decline offer',
            'order_details' => [
                'enabled' => '1',
                'notice_type' => 'custom',
            ],
            'order_totals' => [
                'enabled' => '1',
            ],
            'timer' => [
                'enabled' => '1',
                'notice_type' => 'custom',
                'minutes' => '5',
                'seconds' => '0',
                'message' => 'Offer expires in: <strong>{minutes}:{seconds}</strong>',
            ],
            'image_id' => 0,
            'styles' => [
                'template' => ['background-color' => '', 'padding' => '', 'border-width' => '', 'border-style' => '', 'border-color' => ''],
                'order_details' => ['font-size' => '18px', 'color' => '#181C25', 'background-color' => '#E8EAED'],
                'timer' => ['font-size' => '16px', 'color' => '#181C25', 'background-color' => '#FFFAEB'],
                'title' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                'description' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                'product_totals' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                'accept_button' => ['font-size' => '', 'color' => '', 'background-color' => ''],
                'decline_button' => ['font-size' => '', 'color' => '', 'background-color' => ''],
            ],
        ];
    }
}