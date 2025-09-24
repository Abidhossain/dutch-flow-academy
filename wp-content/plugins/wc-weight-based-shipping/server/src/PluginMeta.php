<?php
namespace Wbs;

use WbsVendors\Dgm\SimpleProperties\SimpleProperties;


/**
 * @property-read string $version
 * @property-read PluginPaths $paths
 * @property-read PluginServerEndpoints $serverEndpoints
 * @property-read string $license
 */
class PluginMeta extends SimpleProperties
{
    public function __construct($entryFile, $serverAppRoot)
    {
        $this->version = self::readVersionMeta($entryFile);
        $this->paths = new PluginPaths($serverAppRoot);
        $this->serverEndpoints = new PluginServerEndpoints($this->paths->get('.config.php'));
    }

    protected $paths;
    protected $version;
    protected $serverEndpoints;
    protected $license = false;

    function __get($property)
    {
        if ($property === 'license' && $this->license === false) {

            $this->license = null;

            if (file_exists($file = $this->paths->get('../license.key')) && ($contents = trim(file_get_contents($file)))) {
                $this->license = $contents;
            }
        }

        return parent::__get($property);
    }

    static private function readVersionMeta($entryFile)
    {
        $pluginFileAttributes = get_file_data($entryFile, array('Version' => 'Version'));
        $version = $pluginFileAttributes['Version'] ?: null;
        return $version;
    }
}