<?php declare(strict_types=1);

use Wbs\Plugin;


call_user_func(function() {

    $pluginEntryFile = wp_normalize_path(__DIR__.'/plugin.php');


    global $gzp_wbs_active_installations;

    $insts = &$gzp_wbs_active_installations;
    if (!is_array($insts)) {
        $insts = array();
    }

    $insts[$pluginEntryFile] = true;

    register_activation_hook($pluginEntryFile, function() use ($pluginEntryFile, $insts) {
        unset($insts[$pluginEntryFile]);
        if ($insts) {
            deactivate_plugins(array_keys($insts));
        }
    });

    if (count($insts) === 2) {
        add_action('admin_notices', function() use ($pluginEntryFile) {
            $plugin = get_file_data($pluginEntryFile, ['Plugin Name'])[0];
            ?>
            <div class="notice notice-error">
                <p>
                    Multiple active <?= esc_html($plugin) ?> installations detected.
                    All except one are temporarily deactivated to prevent possible errors.
                    Please deactivate the installations you do not need manually.
                </p>
            </div>
            <?php
        });
    }

    if (count($insts) > 1) {
        return;
    }


    // This fixes the following issue:
    // 1. Activate the 'Real Cookie Banner (Free)' plugin (probably any plugin with plugin-update-checker "scoped" with php-scoper).
    // 2. Activate this plugin.
    // 3. Notice the fatal error saying Puc_v4_Factory class not found in the UpdateService.
    //
    // The issue happens because composers' "files" autoload type includes an only autoload file out of all
    // available having the same hash. The hash is based on package name and file path.
    //
    // "Scoping" plugin-update-checker with composer-capsule breaks it since PUC depends on the class name structure
    // which is changed due to the way CC handles classes in the root namespace.
    if (!class_exists('Puc_v4_Factory')) {
        require_once(__DIR__.'/server/vendor/yahnis-elsts/plugin-update-checker/load-v4p11.php');
    }

    if (!class_exists(Plugin::class, false)) {
        require_once(__DIR__."/server/vendor/autoload.php");
        Plugin::setupOnce($pluginEntryFile);
    }

    file_exists($f = __DIR__.'/server/wbsng/plugin.php') and require($f);
});