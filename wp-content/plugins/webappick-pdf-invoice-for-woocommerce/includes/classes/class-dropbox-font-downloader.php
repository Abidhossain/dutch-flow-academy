<?php

/**
 * Font downloader for DropBox
 *
 * @since      3.3.25
 * @package    Challan_Free
 * @subpackage Challan_Free/library
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */
class Challan_DropBoxFontDownloader
{
    /**
     * Rename file if contains html encoded space
     *
     * @param   $filePath
     * @return  void
     */
    static public function maybeRequireRename( $filePath ) {
        $fileSep    = explode('/', $filePath);
        $fileName   = $fileSep[ count($fileSep) - 1 ];
        if ( strpos($fileName, '%20') ) {
            $newFileName = str_replace('%20', ' ', $fileName);
            $newFilePath = CHALLAN_FREE_FONT_DIR . $newFileName;
//            rename($filePath, $newFilePath);
            copy($filePath, $newFilePath);
        }
    }

    /**
     * Dispatch font download process
     *
     * @param string $source
     * @param string $dest
     * @return  bool    true|false
     */
    static public function dispatchDownload( $source, $dest = '' ) {
        $fileSep = explode('/', $source);
        $destination = ! empty($dest) ? $dest : CHALLAN_FREE_FONT_DIR . $fileSep[ count($fileSep) - 1 ];

        //download font
        $downloadSuccess = self::downloadFont($source, $destination);

        if ( $downloadSuccess ) {
            if ( self::isZipArchiveFont($destination) ) {
                //then extract zip file
                if ( self::extractZipArchiveFont($destination, CHALLAN_FREE_FONT_DIR) ) {
//                    self::maybeRequireRename($destination);
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Download font file from DropBox. If download being success return true either false
     *
     * @param string $source
     * @param string $destination
     * @return  bool      true|false
     */
    static public function downloadFont( $source, $destination ) {
        if ( ! woo_invoice_is_uploads_folder_writable() ) {
            return;
        }

        // Load WordPress Filesystem API
        global $wp_filesystem;

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ),
        );
        $data = file_get_contents( $source, false, stream_context_create( $arrContextOptions ) ); // phpcs:ignore

        // Use the WordPress Filesystem API to write the data to the destination
        if ( $wp_filesystem->put_contents( $destination, $data, FS_CHMOD_FILE ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check source file is a zip archive file or not.
     *
     * @param string $file
     * @return  bool    true|false
     */
    static public function isZipArchiveFont( $file ) {
        return $file && strpos($file, '.zip');
    }

    /**
     * Extract zip file to destination directory.
     *
     * @param string $zipFile
     * @param string $extractTo
     * @return  bool    true|false
     */
    static public function extractZipArchiveFont( $zipFile, $extractTo ) {
        $zip = new ZipArchive();
        $res = $zip->open($zipFile);
        if ( TRUE === $res ) {
            $zip->extractTo($extractTo);
            $zip->close();
            if ( file_exists( $zipFile ) ) {
                wp_delete_file( $zipFile );
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Prepare download configuration for MPDF
     *
     * @param   $return
     * @return  mixed|void
     */
    static public function prepareConfigForDownloadFont( $return = true ) {
        $fontConfig = include CHALLAN_FREE_ROOT_FILE_PATH . "includes/configs/config-fonts-dropbox.php";
        $fonts      = $fontConfig["others"];
        $downloaded = [];
        $download   = [];
        foreach ( $fonts as $font ) {
            $fileSep = explode('/', $font);
            $destination = CHALLAN_FREE_FONT_DIR . $fileSep[ count($fileSep) - 1 ];
            if ( file_exists(rtrim($destination, ".zip")) ) {
                $downloaded[] = $font;
            } else {
                $download[] = $font;
            }
        }
        $fontConfig["downloaded"]       = $downloaded;
        $fontConfig["download"]         = $download;
        $fontConfig["font_downloaded"]  = count($downloaded);
        $fontConfig["font_remaining"]   = count($download);
        $fontConfig["font_total"]       = count($fontConfig["others"]);
        if ( $return ) {
            return $fontConfig;
        }
    }
}
