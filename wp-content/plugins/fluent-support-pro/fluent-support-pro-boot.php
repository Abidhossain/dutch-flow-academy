<?php

!defined('WPINC') && die;

define('FLUENTSUPPORTPRO', 'fluent-support-pro');
define('FLUENTSUPPORTPRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FLUENTSUPPORTPRO_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FLUENTSUPPORTPRO_PLUGIN_VERSION', '1.9.2');
define('FLUENTSUPPORT_MIN_CORE_VERSION', '1.9.2');

spl_autoload_register(function ($class) {

    $match = 'FluentSupportPro';
    if (!preg_match("/\b{$match}\b/", $class)) {
        return;
    }

    $path = plugin_dir_path(__FILE__);
    $file = str_replace(
        ['FluentSupportPro', '\\', '/App/', 'Database'],
        ['', DIRECTORY_SEPARATOR, 'app/', 'database'],
        $class
    );
    require(trailingslashit($path) . trim($file, '/') . '.php');
});


class FluentSupportPro_Dependency
{
    public function init()
    {
        $this->injectDependency();
        //add_action('admin_post_install_fluent_support', [$this, 'handlePluginInstallation']);
    }

    /**
     * Notify the user about the FluentSupport dependency and instructs to install it.
     */
    protected function injectDependency()
    {
        add_action('admin_notices', function () {
            $pluginInfo = $this->getInstallationDetails();

            $class = 'notice notice-error';

            $install_url_text = 'Click Here to Install the Plugin';

            if ($pluginInfo->action == 'activate') {
                $install_url_text = 'Click Here to Activate the Plugin';
            }

            $message = 'Fluent Support Pro  Requires Fluent Support Base Plugin, <b><a href="' . $pluginInfo->url
                . '">' . $install_url_text . '</a></b>';

            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
        });
    }

    /**
     * Get the FluentSupport plugin installation information e.g. the URL to install.
     *
     * @return \stdClass $activation
     */
    protected function getInstallationDetails()
    {
        $activation = (object)[
            'action' => 'install',
            'url'    => ''
        ];

        $allPlugins = get_plugins();

        if (isset($allPlugins['fluent-support/fluent-support.php'])) {
            $url = wp_nonce_url(
                self_admin_url('plugins.php?action=activate&plugin=fluent-support/fluent-support.php'),
                'activate-plugin_fluent-support/fluent-support.php'
            );

            $activation->action = 'activate';
        } else {
//            $url = wp_nonce_url(
//                admin_url('admin-post.php?action=install_fluent_support'),
//                'install_fluent_support'
//            );

            $api = (object)[
                'slug' => 'fluent-support'
            ];

            $url = wp_nonce_url(
                self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug),
                'install-plugin_' . $api->slug
            );
        }

        $activation->url = $url;

        return $activation;
    }

    public function handlePluginInstallation()
    {
        \FluentSupportPro\App\Services\ProHelper::backgroundInstallerDirect([
            'name'      => 'Fluent Support',
            'repo-slug' => 'fluent-support',
            'file'      => 'fluent-support.php'
        ], 'fluent-messages', 'https://s3.amazonaws.com/wpcolorlab/fluent-support.zip');
    }

}

add_action('init', function () {
    if (!defined('FLUENT_SUPPORT_VERSION')) {
        (new FluentSupportPro_Dependency())->init();
    }
});
