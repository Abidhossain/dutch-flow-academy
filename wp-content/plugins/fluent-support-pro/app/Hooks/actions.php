<?php

/**
 * @var $app FluentSupport\Framework\Foundation\Application
 */

// init integrations
(new \FluentSupportPro\App\Services\Integrations\IntegrationInit())->init();
(new \FluentSupportPro\App\Hooks\Handlers\WorkflowHandler())->init();
(new \FluentSupportPro\App\Hooks\Handlers\EmailPipingRetryHandler())->register();


add_action('fluent_support/before_delete_email_box', function ($box) {
    if ($box->box_type == 'email') {
        (new \FluentSupportPro\App\Services\Integrations\FluentEmailPiping\Api)->removeMailBox([
            'masked_email_id' => $box->mapped_email,
            'site_url'        => site_url(),
            'box_token'       => $box->getMeta('_webhook_token')
        ]);
    }
});

$app->addAction('wp_ajax_fluent_support_export_tickets_timesheet', 'FluentSupportPro\App\Hooks\Handlers\DataExporter@exportTicketsTimeSheet', 10, 0);
$app->addAction('wp_ajax_fluent_support_export_customers_timesheet', 'FluentSupportPro\App\Hooks\Handlers\DataExporter@exportCustomersTimeSheet', 10, 0);
$app->addAction('wp_ajax_fluent_support_export_agents_timesheet', 'FluentSupportPro\App\Hooks\Handlers\DataExporter@exportAgentsTimeSheet', 10, 0);

add_action('admin_init', function () {
    $licenseManager = new \FluentSupportPro\App\Services\PluginManager\LicenseManager();
    $licenseManager->initUpdater();

    $licenseMessage = $licenseManager->getLicenseMessages();

    if ($licenseMessage) {
        add_action('admin_notices', function () use ($licenseMessage) {
            $class = 'notice notice-error fc_message';
            $message = $licenseMessage['message'];
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
        });
    }
}, 0);

$app->addAction('fluent_support\tickets_filter_customer', function ($query, $filter) {
    return (new \FluentSupport\App\Models\Ticket())->filterTicketByUser($provider='customer', $query, $filter);
}, 10, 2);

$app->addAction('fluent_support\tickets_filter_agent', function ($query, $filter) {
    return (new \FluentSupport\App\Models\Ticket())->filterTicketByUser($provider='agent', $query, $filter);
}, 10, 2);

$app->addAction('fluent_support\tickets_filter_tickets', function ($query, $filter){
    return (new \FluentSupport\App\Models\Ticket())->doSearchForAdvancedFilter($query, $filter);
},10, 2);

add_shortcode('fluent_support_admin_portal', function () {
    $app = \FluentSupport\App\App::getInstance();
    $assets = $app['url.assets'];
    wp_enqueue_style('fluent_support_login_style', $assets.'admin/css/all_public.css');

    add_filter('fluent_support/base_url', function ($url) {
        global $wp;
        return home_url(add_query_arg(array(), $wp->request)) . '/#/';
    });

    global $wp;
    $baseUrl = home_url(add_query_arg(array(), $wp->request)) . '/#/';

    if (!get_current_user_id()) {
        $return = '<div style="max-width: 500px; margin: 100px auto;" class="fst_login"><h3>'.__('Please Login', 'fluent-support-pro').'</h3>';
        $return .= do_shortcode('[fluent_support_login redirect-to="'.esc_url($baseUrl).'"]');
        $return .= '</div>';
        return $return;
    }

    $currentUserPermissions = \FluentSupport\App\Modules\PermissionManager::currentUserPermissions();
    if (!$currentUserPermissions) {
        return __('Sorry, You do not have permission to view this page', 'fluent-support-pro');
    }

    add_filter('fluent_support/secondary_menu_items', function ($items) {
        global $wp;
        $items[] = [
            'key'       => 'logout',
            'label'     => __('Logout', 'fluent-support-pro'),
            'permalink' => wp_logout_url(home_url(add_query_arg(array(), $wp->request)))
        ];
        return $items;
    });

    add_filter('fluent_support_app_vars', function ($vars) {
        $vars['is_frontend'] = true;
        return $vars;
    });

    if (is_admin() && !wp_doing_ajax()) {
        return '';
    }

    ob_start();
    echo '<div class="fst_front">';
    (new \FluentSupport\App\Hooks\Handlers\Menu())->renderApp();
    echo '</div>';
    return ob_get_clean();
});

add_action('fluent_support_hourly_tasks', function () {
    \FluentSupportPro\App\Services\AutoCloseService::maybeCloseTickets();
});

$app->addAction('fluent_support_half_hourly', 'FluentSupportPro\App\Hooks\Handlers\ProCronHandler@maybeUpdateStorageTokens', 100);
