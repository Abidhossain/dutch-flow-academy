<?php

namespace Wbs;

use WbsVendors\Dgm\SimpleProperties\SimpleProperties;


/**
 * @property-read string $root
 * @property-read string $assets
 * @property-read string $tplFile
 * @property-read string $globalStubTplFile
 */
class PluginPaths extends SimpleProperties
{
    public function __construct($root)
    {
        $this->root = rtrim($root, '/\\');
        $this->assets = defined('WBS_DEV') ? "{$this->root}/../client/build" : "{$this->root}/..";
        $this->tplFile = "{$this->root}/tpl/main.php";
        $this->globalStubTplFile = "{$this->root}/tpl/global-stub.php";
    }

    public function get($location)
    {
        return $this->makePath(null, $location);
    }

    public function getAssetUrl($asset = null): string
    {
        return plugins_url($asset, $this->assets.'/.');
    }

    protected $root;
    protected $assets;
    protected $tplFile;
    protected $globalStubTplFile;

    private function makePath($location = null, $path = null)
    {
        if (!isset($location) && !isset($path)) {
            return $this->root;
        }

        $parts = array();

        $parts[] = $this->root;

        if (isset($location)) {
            $parts[] = $location;
        }

        if (isset($path)) {
            $parts[] = $path;
        }

        return join('/', $parts);
    }
}