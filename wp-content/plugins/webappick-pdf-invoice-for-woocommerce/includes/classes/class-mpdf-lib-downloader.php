<?php
/**
 * Data file downloader for MPDF from DropBox
 *
 * @since      3.3.25
 * @package    Challan_Free
 * @subpackage Challan_Free/library
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */

/**
 * MPDF library downloader
 */
class Challan_MpdfLibDownloader {

    static public function hasDownloaded(){
        return file_exists(CHALLAN_FREE_INVOICE_DIR . "mpdf/vendor/autoload.php");
    }
    /**
     * Download data files for MPDF
     *
     * @return  bool true|false
     */
    static public function downloadLib(){
        //has downloaded already
        if ( self::hasDownloaded() ) {
            return true;
        }

        if ( ! woo_invoice_is_uploads_folder_writable() ) {
            return;
        }
        // default font downloader
        $fontConfig     = include CHALLAN_FREE_ROOT_FILE_PATH . "includes/configs/config-fonts-dropbox.php"; // phpcs:ignore
        $baseUrl        = implode("", $fontConfig['base-url']);
        $path           = $fontConfig['mpdf-lib'];
        $source         = "{$baseUrl}{$path}";
        $fileSep        = explode('/', $source);
        $destination    = CHALLAN_FREE_INVOICE_DIR . $fileSep[ count($fileSep) - 1 ];
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ),
        );
        //download file
        if ( file_put_contents($destination, file_get_contents( $source, false, stream_context_create( $arrContextOptions ) ) ) ) { //phpcs:ignore
            $extractTo  = CHALLAN_FREE_INVOICE_DIR;
            $zipFile    = $destination;
            $zip        = new ZipArchive();
            $res        = $zip->open($zipFile);
            if ( TRUE === $res ) {
                $zip->extractTo($extractTo);
                $zip->close();
                wp_delete_file( $zipFile );
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
