<?php 
/**
 * EventON event details
 * @version 4.9.11
 */

// Prevent function redeclaration
if (!function_exists('hasUnclosedTags')) {
    function hasUnclosedTags($html) {
        if (EVO()->cal->check_yn('evo_desc_check', 'evcal_1')){
            $html = preg_replace('/<!--[\s\S]*?-->/', '', $html);
            $openCount = preg_match_all('/<([a-z]+)(?:\s+[^>]*)?>/i', $html, $openMatches);
            $closeCount = preg_match_all('/<\/([a-z]+)>/i', $html, $closeMatches);

            // Check nesting within <p> tags
            preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $html, $pMatches);
            foreach ($pMatches[1] as $pContent) {
                $openInP = preg_match_all('/<([a-z]+)(?:\s+[^>]*)?>/i', $pContent);
                $closeInP = preg_match_all('/<\/([a-z]+)>/i', $pContent);
                if ($openInP !== $closeInP) return true;
            }

            return $openCount !== $closeCount;
        }
        return false;
    }
}

// Main event details
$moreCode = '';
$evoMoreActiveClass = '';

if (!empty($evOPT['evo_morelass']) && $evOPT['evo_morelass'] !== 'yes' && strlen($object->fulltext) > 600) {
    $moreCode = "<p class='eventon_shad_p' style='padding:5px 0 0; margin:0'><button class='evcal_btn evo_btn_secondary evobtn_details_show_more' content='less'><span class='ev_more_text' data-txt='" . evo_lang_get('evcal_lang_less', 'less') . "'>" . evo_lang_get('evcal_lang_more', 'more') . "</span><span class='ev_more_arrow ard'></span></button></p>";
    $evoMoreActiveClass = 'shorter_desc';
}

$iconHTML = "<span class='evcal_evdata_icons'><i class='fa " . get_eventON_icon('evcal__fai_001', 'fa-align-justify', $evOPT) . "'></i></span>";

//$fullEventDetails = html_entity_decode(stripslashes($object->fulltext), ENT_QUOTES | ENT_HTML5, 'UTF-8');
$fullEventDetails = stripslashes($object->fulltext);

?>
<div class="evo_metarow_details evorow evcal_evdata_row evcal_event_details<?php echo $end_row_class; ?>">
    <?php echo $object->excerpt . $iconHTML; ?>
    <div class="evcal_evdata_cell <?php echo $evoMoreActiveClass; ?>">
        <div class="eventon_full_description">
            <h3 class="padb5 evo_h3"><?php echo $iconHTML . evo_lang_get('evcal_evcard_details', 'Event Details'); ?></h3>
            <div class="eventon_desc_in" itemprop="description">
                <?php 
                if (hasUnclosedTags($fullEventDetails) !== false ) {
                    echo "<p style='color: red; font-weight: bold;'>" . __('Error: There are unclosed HTML tags in the event details. Please fix the content in the editor.', 'eventon') . "</p>";
                } else {
                    $_full_event_details = stripslashes($object->fulltext);
                    echo apply_filters('evo_eventcard_details', EVO()->frontend->filter_evo_content($fullEventDetails));
                }
                ?>
            </div>
            <?php 
            do_action('eventon_eventcard_event_details');
            echo $moreCode . "<div class='clear'></div>";
            ?>
        </div>
    </div>
</div>