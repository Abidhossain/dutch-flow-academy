<?php
namespace Wbs;

use WbsVendors\Dgm\SimpleProperties\SimpleProperties;


/**
 * @property-read string $updates
 */
class PluginServerEndpoints extends SimpleProperties
{
    public function __construct($configFile = null)
    {
        $this->updates = $this->getApiEndpoint('updates', $configFile);
    }

    private function getApiEndpoint($service = null, $configFile = null)
    {
        $apiEndpoint = null;

        if (isset($configFile) && file_exists($configFile)) {

            $config = require($configFile);

            if (isset($config['apiEndpoint'])) {
                $apiEndpoint = $config['apiEndpoint'];
            }
        }

        if (!isset($apiEndpoint)) {
            $apiEndpoint = 'https://weightbasedshipping.com/api';
        }

        if (isset($service)) {
            $apiEndpoint .= "/{$service}";
        }

        return $apiEndpoint;
    }


    protected $updates;
}