<?php
/**
 * Mixed content template, HTML|CSS|JavaScript
 *
 * @since      3.3.25
 * @package    Challan_Free
 * @subpackage Challan_Free/templates
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 */


if ( ! woo_invoice_is_uploads_folder_writable() ) {
    return;
}

if ( ! class_exists("Challan_DropBoxFontDownloader") ) {
    require_once CHALLAN_FREE_ROOT_FILE_PATH . "includes/classes/class-dropbox-font-downloader.php";
}
$fontConfig = Challan_DropBoxFontDownloader::prepareConfigForDownloadFont();
//if font download is not remain then disable download features
if ( isset($fontConfig["font_remaining"]) ) {
    if ( $fontConfig["font_remaining"] <= 0 ) {
        return '';
    }
}

?>
<style>
    #wpifw-preparing-font-progress-wrap{
        /*display: none;*/
        margin: 20px 20px 20px 0;
    }

    #wpifw-preparing-font-progress {
        display: flex;
        height: 1rem;
        overflow: hidden;
        font-size: .75rem;
        background-color: #e9ecef;
        background-color: #e1e4e7;
        border-radius: 0.25rem;
        height: 16px;
    }

    #wpifw-preparing-font-progress-bar {
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow: hidden;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        background-color: #0d6efd;
        /*transition: width 1s ease;*/

        width: 1%;
        transition: width 5s, transform 5s;
        transition-timing-function: linear;
    }
</style>
<div class="wrap" id="wpifw-preparing-font-progress-wrap" style="">
    <p><strong>Preparing Font</strong>
        <svg xmlns="http://www.w3.org/2000/svg"
             xmlns:xlink="http://www.w3.org/1999/xlink"
             style="
            height: 34px;
            float: left;
            margin: -7px 0 0 0;
        "
             viewBox="0 0 100 100"
             preserveAspectRatio="xMidYMid">
            <g transform="translate(50 50)">
                <g>
                    <animateTransform attributeName="transform" type="rotate" values="0;45" keyTimes="0;1" dur="0.22075055187637968s" repeatCount="indefinite"></animateTransform><path d="M29.491524206117255 -5.5 L37.491524206117255 -5.5 L37.491524206117255 5.5 L29.491524206117255 5.5 A30 30 0 0 1 24.742744050198738 16.964569457146712 L24.742744050198738 16.964569457146712 L30.399598299691117 22.621423706639092 L22.621423706639096 30.399598299691114 L16.964569457146716 24.742744050198734 A30 30 0 0 1 5.5 29.491524206117255 L5.5 29.491524206117255 L5.5 37.491524206117255 L-5.499999999999997 37.491524206117255 L-5.499999999999997 29.491524206117255 A30 30 0 0 1 -16.964569457146705 24.742744050198738 L-16.964569457146705 24.742744050198738 L-22.621423706639085 30.399598299691117 L-30.399598299691117 22.621423706639092 L-24.742744050198738 16.964569457146712 A30 30 0 0 1 -29.491524206117255 5.500000000000009 L-29.491524206117255 5.500000000000009 L-37.491524206117255 5.50000000000001 L-37.491524206117255 -5.500000000000001 L-29.491524206117255 -5.500000000000002 A30 30 0 0 1 -24.742744050198738 -16.964569457146705 L-24.742744050198738 -16.964569457146705 L-30.399598299691117 -22.621423706639085 L-22.621423706639092 -30.399598299691117 L-16.964569457146712 -24.742744050198738 A30 30 0 0 1 -5.500000000000011 -29.491524206117255 L-5.500000000000011 -29.491524206117255 L-5.500000000000012 -37.491524206117255 L5.499999999999998 -37.491524206117255 L5.5 -29.491524206117255 A30 30 0 0 1 16.964569457146702 -24.74274405019874 L16.964569457146702 -24.74274405019874 L22.62142370663908 -30.39959829969112 L30.399598299691117 -22.6214237066391 L24.742744050198738 -16.964569457146716 A30 30 0 0 1 29.491524206117255 -5.500000000000013 M0 -17A17 17 0 1 0 0 17 A17 17 0 1 0 0 -17" fill="#0976f0"></path></g></g>
        </svg>
    </p>
    <div id="wpifw-preparing-font-progress">
        <div id="wpifw-preparing-font-progress-bar"></div>
    </div>
</div>
<script type="text/javascript">

    jQuery(async function($){
        let wpif_progress_bar = 1;
        let wpif_default_progress_bar = 16;
        let wpif_progress_bar_setp = 2;
        let wpf_progress_bar_interval = null;
        //url should be replaced before deploying to production
        let wpif_prepare_font_endpoint = "<?php echo esc_url( admin_url( 'admin-ajax.php?action=prepare_fonts&nonce=' . wp_create_nonce( "prepare_fonts_nonce" ) ) ) ?>";


        const wpif_init_default_progress_bar_cb_func = function () {
            wpf_progress_bar_interval = setInterval(function (){
                wpif_progress_bar += 2;
                $('#wpifw-preparing-font-progress-bar').width(wpif_progress_bar+'%');
                if (wpif_progress_bar >= wpif_default_progress_bar){
                    clearInterval(wpf_progress_bar_interval);
                }
            }, 2000);
        }
        const wpif_show_progress_bar_cb_func = function(){
            $("#wpifw-preparing-font-progress-wrap").show();
        }

        const wpif_hide_progress_bar_cb_func = function(){
            $("#wpifw-preparing-font-progress-wrap").hide();
        }
        const wpif_update_progress_bar_cb_func = function () {
            wpif_progress_bar += wpif_progress_bar_setp;
            $('#wpifw-preparing-font-progress-bar').width(wpif_progress_bar+'%');
        }
        const wpif_finish_progress_bar_cb_func = function () {
            wpif_progress_bar = 100;
            $('#wpifw-preparing-font-progress-bar').width(wpif_progress_bar+'%');
        }


        var wpif_prepare_font = async function() {
            // console.log("Preparing font ajax request [starting]");
            let response = await $
                .ajax({
                    url         : wpif_prepare_font_endpoint,
                    data        : {},
                    dataType    : "JSON",
                    beforeSend  : async function( xhr ) {
                        await wpif_show_progress_bar_cb_func();
                    }
                })
                .done(function(data, textStatus, jqXHR ) {
                    // console.log("Font preparing one step up but still in-progress");
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    // console.log("Something went wrong in preparing font [fail]");
                })
                .always(async function(data, textStatus, jqXHR) {
                    //dismiss default progress if running

                    if (wpf_progress_bar_interval){
                        clearInterval(wpf_progress_bar_interval);
                        wpif_progress_bar_setp = 1;
                    }

                    let response = data?.response_body;
                    if (response?.font_remaining <= 0) {
                        //finish download
                        await wpif_finish_progress_bar_cb_func();
                        // console.log("Font preparing has been finished!");
                        //dismiss progress bar
                        setTimeout(async function (){
                            await wpif_hide_progress_bar_cb_func();
                        }, 5000);
                    } else {
                        if (wpif_progress_bar >= 100) {
                            await wpif_finish_progress_bar_cb_func();
                            // console.log("Font preparing has been finished!");
                            //dismiss progress bar
                            setTimeout(async function (){
                                await wpif_hide_progress_bar_cb_func();
                            }, 2000);
                        } else {
                            // re initiate font download request
                            await wpif_update_progress_bar_cb_func();
                            wpif_prepare_font();
                        }
                    }
                });
        }
        await wpif_init_default_progress_bar_cb_func();
        wpif_prepare_font();
    });
</script>