<?php

namespace FluentSupportPro\App\Services;

use FluentSupport\App\Models\Agent;
use FluentSupport\App\Models\Customer;
use FluentSupport\App\Models\MailBox;
use FluentSupport\App\Models\Meta;
use FluentSupport\App\Models\Product;
use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Support\Arr;
use FluentSupportPro\App\Models\TimeTrack;
use FluentSupportPro\App\Services\Integrations\FluentEmailPiping\ByMailHandler;

class ProHelper
{
    public static function hasDocIntegration()
    {
        $config = self::getTicketFormConfig();
        return $config['enable_docs'] == 'yes' && !empty($config['docs_post_types']);
    }

    /**
     * getTicketFormConfig method will return the configuration for the ticket form
     * @return mixed
     */
    public static function getTicketFormConfig()
    {
        static $settings;
        if ($settings) {
            return $settings;
        }

        //Get all options for _ticket_form_settings from fs_meta table
        $settings = Helper::getOption('_ticket_form_settings', []);

        //Default settings for TicketFormConfig
        $defaults = [
            'enable_docs' => 'no',
            'docs_post_types' => [],
            'post_limits' => 5,
            'disable_rich_text' => 'no',
            'disabled_fields' => [],
            'submitter_type' => 'logged_in_users',
            'allowed_user_roles' => [],
            'field_labels' => [
                'subject' => __('Subject', 'fluent-support-pro'),
                'ticket_details' => __('Ticket Details', 'fluent-support-pro'),
                'details_help' => __('Please provide details about your problem', 'fluent-support-pro'),
                'product_services' => __('Related Product/Service', 'fluent-support-pro'),
                'priority' => __('Priority', 'fluent-support-pro'),
                'btn_text' => __('Create Ticket', 'fluent-support-pro'),
                'submit_heading' => __('Submit a Support Ticket', 'fluent-support-pro'),
                'create_ticket_cta' => __('Create Ticket', 'fluent-support-pro')
            ],
            'enable_woo_menu' => 'yes'
        ];

        $settings = wp_parse_args($settings, $defaults);
        return $settings;
    }

    public static function getProducts()
    {
        $products = Product::select(['id', 'title'])
            ->orderBy('title', 'ASC')
            ->get();

        if ($products->isEmpty()) {
            return [];
        }

        return $products->map(function ($product) {
            return [
                'id' => $product->id,
                'title' => $product->title,
            ];
        })->toArray();
    }

    public static function getAdvancedFilterOptions()
    {
        $groups = [
            'tickets' => [
                'label' => 'Tickets',
                'value' => 'tickets',
                'children' => [
                    [
                        'label' => 'Title',
                        'value' => 'title',
                    ],
                    [
                        'label' => 'Content',
                        'value' => 'content',
                    ],
                    [
                        'label' => 'Conversation Content',
                        'value' => 'conversation_content',
                    ],
                    [
                        'label' => 'Status',
                        'value' => 'status',
                        'type' => 'selections',
                        'component' => 'options_selector',
                        'option_key' => 'ticket_statuses',
                        'is_multiple' => true,
                        'is_singular_value' => true
                    ],
                    [
                        'label' => 'Client Priority',
                        'value' => 'client_priority',
                        'type' => 'selections',
                        'component' => 'options_selector',
                        'option_key' => 'client_priorities',
                        'is_multiple' => false,
                        'is_singular_value' => true
                    ],
                    [
                        'label' => 'Admin Priority',
                        'value' => 'priority',
                        'type' => 'selections',
                        'component' => 'options_selector',
                        'option_key' => 'admin_priorities',
                        'is_multiple' => true,
                        'is_singular_value' => true
                    ],
                    [
                        'label' => 'Tags',
                        'value' => 'tags',
                        'type' => 'selections',
                        'component' => 'options_selector',
                        'option_key' => 'ticket_tags',
                        'is_multiple' => true,
                    ],
                    [
                        'label' => 'Products',
                        'value' => 'product',
                        'type' => 'selections',
                        'component' => 'options_selector',
                        'option_key' => 'support_products',
                        'is_multiple' => true,
                        'is_singular_value' => true
                    ],
                    [
                        'label' => 'Waiting For Reply',
                        'value' => 'waiting_for_reply',
                        'type' => 'selections',
                        'option_key' => 'waiting_for_reply',
                        'is_singular_value' => true,
                        'options' => [
                            'yes' => 'Yes',
                            'no' => 'No'
                        ]
                    ],
                    [
                        'label' => 'Assigned Agent',
                        'value' => 'agent_id',
                        'type' => 'selections',
                        'component' => 'options_selector',
                        'option_key' => 'support_agents',
                        'is_singular_value' => true,
                    ],
                    [
                        'label' => 'Ticket Mailbox',
                        'value' => 'mailbox_id',
                        'type' => 'selections',
                        'component' => 'options_selector',
                        'option_key' => 'mailboxes',
                        'is_singular_value' => true,
                        'is_multiple' => true,
                    ],
                    [
                        'label' => 'Ticket Created',
                        'value' => 'created_at',
                        'type' => 'dates'
                    ],
                    [
                        'label' => 'Last Response',
                        'value' => 'updated_at',
                        'type' => 'dates'
                    ],
                    [
                        'label' => 'Customer Waiting From',
                        'value' => 'waiting_since',
                        'type' => 'dates'
                    ],
                    [
                        'label' => 'Last Agent Response',
                        'value' => 'last_agent_response',
                        'type' => 'dates'
                    ],
                    [
                        'label' => 'Last Customer Response',
                        'value' => 'last_customer_response',
                        'type' => 'dates'
                    ],
                ],
            ],
            'customer' => [
                'label' => 'Customer',
                'value' => 'customer',
                'children' => [
                    [
                        'label' => 'First Name',
                        'value' => 'first_name',
                    ],
                    [
                        'label' => 'Last Name',
                        'value' => 'last_name',
                    ],
                    [
                        'label' => 'Email',
                        'value' => 'email',
                    ],
                    [
                        'label' => 'Address Line 1',
                        'value' => 'address_line_1',
                    ],
                    [
                        'label' => 'Address Line 2',
                        'value' => 'address_line_2',
                    ],
                    [
                        'label' => 'City',
                        'value' => 'city',
                    ],
                    [
                        'label' => 'State',
                        'value' => 'state',
                    ],
                    [
                        'label' => 'Postal Code',
                        'value' => 'postal_code',
                    ],
//                    [
//                        'label'             => 'Country',
//                        'value'             => 'country',
//                        'type'              => 'selections',
//                        'component'         => 'options_selector',
//                        'option_key'        => 'countries',
//                        'is_multiple'       => true,
//                        'is_singular_value' => true
//                    ],
                    [
                        'label' => 'Phone',
                        'value' => 'phone',
                    ],
                ],
            ],
            'agent' => [
                'label' => 'Agent',
                'value' => 'agent',
                'children' => [
                    [
                        'label' => 'First Name',
                        'value' => 'first_name',
                    ],
                    [
                        'label' => 'Last Name',
                        'value' => 'last_name',
                    ],
                    [
                        'label' => 'Email',
                        'value' => 'email',
                    ]
                ],
            ]
        ];

        $groups = apply_filters('fluent_support/advanced_filter_options', $groups);

        return array_values($groups);
    }

    public static function sanitizeMessageId($text)
    {
        if (!$text) {
            return $text;
        }
        $messageId = str_replace(['<', '>'], ['[', '}'], $text);
        $messageId = sanitize_textarea_field($messageId);
        return str_replace(['[', ']'], ['<', '>'], $messageId);
    }

    public static function generateMessageID($email)
    {
        $emailParts = explode('@', $email);
        if (count($emailParts) != 2) {
            return false;
        }
        $emailDomain = $emailParts[1];
        try {
            return sprintf(
                "<%s.%s@%s>",
                base_convert(microtime(), 10, 36),
                base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36),
                $emailDomain
            );
        } catch (\Exception $exception) {
            return false;
        }
    }


    public static function encryptKey($value)
    {
        if (!$value) {
            return $value;
        }

        if (!extension_loaded('openssl')) {
            return $value;
        }

        $salt = (defined('LOGGED_IN_SALT') && '' !== LOGGED_IN_SALT) ? LOGGED_IN_SALT : 'this-is-a-fallback-salt-but-not-secure';

        if (defined('FLUENT_SUPPORT_ENCRYPTION_KEY')) {
            $key = FLUENT_SUPPORT_ENCRYPTION_KEY;
        } else {
            $key = (defined('LOGGED_IN_KEY') && '' !== LOGGED_IN_KEY) ? LOGGED_IN_KEY : 'this-is-a-fallback-key-but-not-secure';
        }

        $method = 'aes-256-ctr';
        $ivlen = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($ivlen);

        $raw_value = openssl_encrypt($value . $salt, $method, $key, 0, $iv);
        if (!$raw_value) {
            return false;
        }

        return base64_encode($iv . $raw_value); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
    }

    public static function decryptKey($raw_value)
    {
        if (!$raw_value) {
            return $raw_value;
        }

        if (!extension_loaded('openssl')) {
            return $raw_value;
        }

        $raw_value = base64_decode($raw_value, true); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

        $method = 'aes-256-ctr';
        $ivlen = openssl_cipher_iv_length($method);
        $iv = substr($raw_value, 0, $ivlen);

        $raw_value = substr($raw_value, $ivlen);

        if (defined('FLUENT_SUPPORT_ENCRYPTION_KEY')) {
            $key = FLUENT_SUPPORT_ENCRYPTION_KEY;
        } else {
            $key = (defined('LOGGED_IN_KEY') && '' !== LOGGED_IN_KEY) ? LOGGED_IN_KEY : 'this-is-a-fallback-key-but-not-secure';
        }

        $salt = (defined('LOGGED_IN_SALT') && '' !== LOGGED_IN_SALT) ? LOGGED_IN_SALT : 'this-is-a-fallback-salt-but-not-secure';

        $value = openssl_decrypt($raw_value, $method, $key, 0, $iv);
        if (!$value || substr($value, -strlen($salt)) !== $salt) {
            return false;
        }

        return substr($value, 0, -strlen($salt));
    }

    public static function willProcessMime($mime)
    {
        if (!$mime) {
            return true;
        }

        $exist = \FluentSupport\App\Models\Meta::where('object_type', '__processed_mimes')->first();

        if (!$exist) {
            $mimes = [];
            $mimes[$mime] = date('Ymd');

            \FluentSupport\App\Models\Meta::create([
                'object_type' => '__processed_mimes',
                'value' => maybe_serialize($mimes)
            ]);
            return true;
        }

        $existingValue = maybe_unserialize($exist->value);

        $isExits = isset($existingValue[$mime]);

        if (!$isExits) {
            $existingValue[$mime] = date('Ymd');
            $deletableDate = date('Ymd', strtotime('-3 days'));
            $existingValue = array_filter($existingValue, function ($date) use ($deletableDate) {
                return $date >= $deletableDate;
            });

            $exist->update([
                'value' => maybe_serialize($existingValue)
            ]);
        }

        return !$isExits;
    }


    public static function getBoxSecret($mailBox)
    {
        if (!$token = $mailBox->getMeta('_webhook_token')) {
            $token = substr(md5(wp_generate_uuid4()) . '_' . $mailBox->id . '_' . mt_rand(100, 10000), 0, 16);
            $mailBox->saveMeta('_webhook_token', $token);
        }
        return $token;
    }

    public static function validateLicense()
    {
        $licenseData = get_option('__fluentsupport_pro_license');
        if (empty($licenseData['license_key']) || $licenseData['status'] !== 'valid') {
            return new \WP_Error('invalid_license', 'License is missing or not valid.');
        }
        return $licenseData;
    }

    public static function getValidatedDateRange($dateRange)
    {
        if (!$dateRange || !is_array($dateRange)) {
            $dateRange = [];
        } else {
            $dateRange = array_filter($dateRange);
        }

        if (!$dateRange || count($dateRange) !== 2) {
            $start = current_time('timestamp');
            $dateRange = [
                date('Y-m-d H:i:s', strtotime('-1 week', $start)),
                date('Y-m-d 23:59:59', $start)
            ];
        } else {

            $dateRange = [
                date('Y-m-d 00:00:00', strtotime($dateRange[0])),
                date('Y-m-d 23:59:59', strtotime($dateRange[1]))
            ];

            if (strtotime($dateRange[1]) - strtotime($dateRange[0]) > 31 * 24 * 60 * 60) {
                $newEndDate = strtotime('+31 days', strtotime($dateRange[0]));

                if (date('Y', $newEndDate) < date('Y', strtotime($dateRange[1]))) {
                    $newEndDate = strtotime($dateRange[1]);
                }

                $dateRange[1] = date('Y-m-d 23:59:59', $newEndDate);
            }
        }

        return $dateRange;
    }

    public static function getTracks(array $dateRange, string $filterColumn, $filterValue, array $relations)
    {
        return TimeTrack::when($filterValue, function ($q) use ($filterColumn, $filterValue) {
            $q->whereIn($filterColumn, (array)$filterValue);
        })
            ->orderBy('updated_at', 'DESC')
            ->whereBetween('completed_at', $dateRange)
            ->with($relations)
            ->whereHas('ticket')
            ->get();
    }

    public static function formatTimeSheets($tracks, $itemKey)
    {
        $timeSheets = [];
        $formattedItems = [];

        foreach ($tracks as $track) {
            $itemId = $track->{$itemKey} ?? null;

            if ($itemId === null) {
                continue; // Skip if item ID is null
            }

            $relatedItemKey = $itemKey === 'ticket_id' ? 'ticket' : substr($itemKey, 0, -3);
            $relatedItem = $track->{$relatedItemKey} ?? null;

            if (!isset($formattedItems[$itemId]) && $relatedItem !== null) {
                $formattedItems[$itemId] = $relatedItem;
            }

            $date = date('Y-m-d', strtotime($track->completed_at ?? 'now'));
            if (!isset($timeSheets[$date][$itemId])) {
                $timeSheets[$date][$itemId] = [];
            }

            $timeSheets[$date][$itemId][] = self::formatTrack($track);
        }

        return [
            'sheets' => $timeSheets,
            'formattedItems' => array_filter($formattedItems), // Remove null items if any
        ];
    }


    public static function formatTrack($track)
    {
        return [
            'id' => $track->id,
            'created_at' => (string)$track->created_at,
            'completed_at' => $track->completed_at,
            'billable_minutes' => $track->billable_minutes,
            'message' => $track->message,
            'ticket' => $track->ticket,
            'agent' => $track->agent ?? null,
            'customer' => $track->customer ?? null,
        ];
    }

    public static function calculateTotalMinutes($tracks)
    {
        return $tracks->sum('billable_minutes');
    }

    public static function generateDateLabels(array $dateRange)
    {
        $start = strtotime($dateRange[0]);
        $end = strtotime($dateRange[1]);
        $dateLabels = [];

        while ($start <= $end) {
            $dateLabels[] = date('Y-m-d', $start);
            $start = strtotime('+1 day', $start);
        }

        return $dateLabels;
    }


    public static function getMailBoxes()
    {
        return MailBox::select(['id', 'name', 'box_type'])->orderBy('id', 'ASC')->get();
    }

    public static function getAllAgents()
    {
        return Agent::select(['id', 'first_name', 'last_name'])->orderBy('id', 'ASC')->get();
    }

    public static function getAllCustomers()
    {
        return Customer::select(['id', 'first_name', 'last_name'])->orderBy('id', 'ASC')->get();
    }

    /*
  * Install Plugins with direct download link ( which doesn't have wordpress.org repo )
  */
    public static function backgroundInstallerDirect($plugin_to_install, $plugin_id, $downloadUrl)
    {
        if (!empty($plugin_to_install['repo-slug'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            \WP_Filesystem();

            $skin = new \Automatic_Upgrader_Skin();
            $upgrader = new \WP_Upgrader($skin);
            $installed_plugins = array_reduce(array_keys(\get_plugins()), array(static::class, 'associate_plugin_file'), array());
            $plugin_slug = $plugin_to_install['repo-slug'];
            $plugin_file = isset($plugin_to_install['file']) ? $plugin_to_install['file'] : $plugin_slug . '.php';
            $installed = false;
            $activate = false;

            // See if the plugin is installed already.
            if (isset($installed_plugins[$plugin_file])) {
                $installed = true;
                $activate = !is_plugin_active($installed_plugins[$plugin_file]);
            }

            // Install this thing!
            if (!$installed) {
                // Suppress feedback.
                ob_start();

                try {
                    $package = $downloadUrl;
                    $download = $upgrader->download_package($package);

                    if (is_wp_error($download)) {
                        throw new \Exception($download->get_error_message());
                    }

                    $working_dir = $upgrader->unpack_package($download, true);

                    if (is_wp_error($working_dir)) {
                        throw new \Exception($working_dir->get_error_message());
                    }

                    $result = $upgrader->install_package(
                        array(
                            'source'                      => $working_dir,
                            'destination'                 => WP_PLUGIN_DIR,
                            'clear_destination'           => false,
                            'abort_if_destination_exists' => false,
                            'clear_working'               => true,
                            'hook_extra'                  => array(
                                'type'   => 'plugin',
                                'action' => 'install',
                            ),
                        )
                    );

                    if (is_wp_error($result)) {
                        throw new \Exception($result->get_error_message());
                    }

                    $activate = true;

                } catch (\Exception $e) {
                }

                // Discard feedback.
                ob_end_clean();
            }

            wp_clean_plugins_cache();

            // Activate this thing.
            if ($activate) {
                try {
                    $result = activate_plugin($installed ? $installed_plugins[$plugin_file] : $plugin_slug . '/' . $plugin_file);

                    if (is_wp_error($result)) {
                        throw new \Exception($result->get_error_message());
                    }
                    wp_redirect(admin_url('admin.php?page='.$plugin_slug.''));
                } catch (\Exception $e) {
                }
            }
        }
    }

    private static function associate_plugin_file($plugins, $key)
    {
        $path = explode('/', $key);
        $filename = end($path);
        $plugins[$filename] = $key;
        return $plugins;
    }
}
