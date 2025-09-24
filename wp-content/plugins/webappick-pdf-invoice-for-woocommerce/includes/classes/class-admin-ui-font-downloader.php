<?php
/**
 * Fonts downloader for admin ui from DropBox
 *
 * @since      3.3.25
 * @package    Challan_Free
 * @subpackage Challan_Free/library
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */

/**
 * Admin UI font downloader
 */
class Challan_AdminUIFontDownloader
{

    /**
     * Font configuration form config file
     *
     * @var array
     */
    static public $fontConfig = [];

    /**
     * Download data files for MPDF
     *
     * @return  bool true|false
     */
    static public function downloadFont() {
        //has already been downloaded
        if ( self::hasDownloaded() ) {
            return true;
        }
        if ( ! woo_invoice_is_uploads_folder_writable() ) {
            return;
        }
        // default font downloader
        $fontConfig     = self::getConfig();
        $baseUrl        = implode("", $fontConfig['base-url']);
        $path           = $fontConfig['admin-ui-fonts'];
        $source         = "{$baseUrl}{$path}";
        $fileSep        = explode('/', $source);
        $destination    = CHALLAN_ADMIN_UI_FONTS_DIR . $fileSep[ count($fileSep) - 1 ];
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ),
        );
        //download file
        if ( file_put_contents($destination, file_get_contents( $source, false, stream_context_create( $arrContextOptions ) ) ) ) { //phpcs:ignore
            $extractTo  = CHALLAN_ADMIN_UI_FONTS_DIR;
            $zipFile    = $destination;
            $zip        = new ZipArchive();
            $res        = $zip->open($zipFile);
            if ( TRUE === $res ) {
                $zip->extractTo($extractTo);
                $zip->close();
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Check fonts already downloaded or not
     *
     * @return bool
     */
    static public function hasDownloaded() {
        $fontConfig = self::getConfig();
        foreach ( $fontConfig['admin-ui-font-list'] as $fontpath ) {
            if ( file_exists(CHALLAN_ADMIN_UI_FONTS_DIR . $fontpath) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Load and get default config
     *
     * @return array|mixed
     */
    static public function getConfig() {
        if ( empty(self::$fontConfig) ) {
            self::$fontConfig = include CHALLAN_FREE_ROOT_FILE_PATH . "includes/configs/config-fonts-dropbox.php"; // phpcs:ignore
        }
        return self::$fontConfig;
    }
}
