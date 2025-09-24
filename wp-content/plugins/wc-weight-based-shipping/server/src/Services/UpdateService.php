<?php
namespace Wbs\Services;

use Puc_v4_Factory;
use WbsVendors\Dgm\PluginServices\IService;
use WbsVendors\Dgm\PluginServices\IServiceReady;
use WP_Error;


class UpdateService implements IService, IServiceReady
{
    public function __construct($updatesApiUrl, $pluginFile, $licenseId)
    {
        $this->updatesApiUrl = $updatesApiUrl;
        $this->pluginFile = $pluginFile;
        $this->licenseId = $licenseId;
    }

    public function ready()
    {
        return defined('WP_CLI') || is_admin();
    }

    public function install()
    {
        $updatesApiUrl = $this->updatesApiUrl;
        $pluginFile = $this->pluginFile;
        $licenseId = $this->licenseId;

        $updateChecker = Puc_v4_Factory::buildUpdateChecker(
            $updatesApiUrl,
            $pluginFile,
            dirname(plugin_basename($pluginFile))
        );

        /** @noinspection NullPointerExceptionInspection */
        $updateChecker->addQueryArgFilter(function($queryArgs) use($licenseId) {
            $queryArgs['license'] = $licenseId;
            return $queryArgs;
        });

        add_filter('upgrader_pre_download', function($response, $downloadUrl) use($updatesApiUrl, $pluginFile) {

            if (strpos($downloadUrl, $updatesApiUrl) !== false) {

                if ($response === false) {
                    $downloadUrl .= (strpos($downloadUrl, '?') === false ? '?' : '&') . 'check=1';
                    $checkResponse = wp_safe_remote_get($downloadUrl);
                    if (is_array($checkResponse) && @$checkResponse['body'] && $checkResponse['body'] !== 'OK') {
                        $response = new WP_Error('download_failed', '', $checkResponse['body']);
                    }
                }

                if ($response === false) {
                    if (file_exists(dirname($pluginFile).'/.git') || file_exists(dirname($pluginFile).'/.idea')) {
                        $response = new WP_Error('download_failed', '', 'Development plugin copy protected from erasing during update.');
                    }
                }
            }

            return $response;

        }, 10, 3);
    }


    private $updatesApiUrl;
    private $pluginFile;
    private $licenseId;
}