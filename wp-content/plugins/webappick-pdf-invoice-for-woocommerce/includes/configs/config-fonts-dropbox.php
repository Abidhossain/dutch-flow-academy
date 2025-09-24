<?php
/**
 * Fonts download configuration for dropbox
 *
 * @since      4.1.27
 * @package    Challan_Pro
 * @subpackage Challan_Pro/configuration
 * @author     Anwar <anwar.webappick@gmail.com>
 * @link       https://webappick.com
 *
 *
 * $defaultFonts = [
 * "freeserif" => [
 * 'R' => "FreeSerif.ttf",
 * 'B' => "FreeSerifBold.ttf",
 * 'I' => "FreeSerifItalic.ttf",
 * 'BI' => "FreeSerifBoldItalic.ttf",
 * ],
 * 'sun-exta' => 'Sun-ExtA.ttf',
 * 'sun-extb' => 'Sun-ExtB.ttf',
 * 'unbatang' => 'UnBatang_0613.ttf',
 * ]
 */



return [
    "base-url"           => [ "ht", "tps", "://", "gith", "ub.com", "/webappick", "/challan", "-assets", "/ra", "w/ma", "in/" ],
    "default"            => [
        "FreeSerif.ttf.zip",
        "FreeSerifBold.ttf.zip",
        "FreeSerifBoldItalic.ttf.zip",
        "FreeSerifItalic.ttf.zip",
        "Sun-ExtA.ttf.zip",
        "Sun-ExtB.ttf.zip",
        "UnBatang_0613.ttf.zip",
        "DejaVuSerifCondensed-Bold.ttf.zip",
        "DejaVuSerifCondensed-BoldItalic.ttf.zip",
        "DejaVuSerifCondensed-Italic.ttf.zip",
        "DejaVuSerifCondensed.ttf.zip",
        "currencies.ttf.zip",
    ],
    "others"             => [
        "AboriginalSansREGULAR.ttf.zip",
        "Abyssinica_SIL.ttf.zip",
        "Aegean.otf.zip",
        "Aegyptus.otf.zip",
        "Akkadian.otf.zip",
        "ayar.ttf.zip",
        "damase_v.2.ttf.zip",
        "DBSILBR.ttf.zip",
        "DejaVuSans-Bold.ttf.zip",
        "DejaVuSans-BoldOblique.ttf.zip",
        "DejaVuSans-Oblique.ttf.zip",
        "DejaVuSans.ttf.zip",
        "DejaVuSansCondensed-Bold.ttf.zip",
        "DejaVuSansCondensed-BoldOblique.ttf.zip",
        "DejaVuSansCondensed-Oblique.ttf.zip",
        "DejaVuSansCondensed.ttf.zip",
        "DejaVuSansMono-Bold.ttf.zip",
        "DejaVuSansMono-BoldOblique.ttf.zip",
        "DejaVuSansMono-Oblique.ttf.zip",
        "DejaVuSansMono.ttf.zip",
        "DejaVuSerif-Bold.ttf.zip",
        "DejaVuSerif-BoldItalic.ttf.zip",
        "DejaVuSerif-Italic.ttf.zip",
        "DejaVuSerif.ttf.zip",
        "DejaVuSerifCondensed-Bold.ttf.zip",
        "DejaVuSerifCondensed-BoldItalic.ttf.zip",
        "DejaVuSerifCondensed-Italic.ttf.zip",
        "DejaVuSerifCondensed.ttf.zip",
        "Dhyana-Bold.ttf.zip",
        "Dhyana-Regular.ttf.zip",
        "FreeMono.ttf.zip",
        "FreeMonoBold.ttf.zip",
        "FreeMonoBoldOblique.ttf.zip",
        "FreeMonoOblique.ttf.zip",
        "FreeSans.ttf.zip",
        "FreeSansBold.ttf.zip",
        "FreeSansBoldOblique.ttf.zip",
        "FreeSansOblique.ttf.zip",
        "FreeSerif.ttf.zip",
        "FreeSerifBold.ttf.zip",
        "FreeSerifBoldItalic.ttf.zip",
        "FreeSerifItalic.ttf.zip",
        "Garuda-Bold.ttf.zip",
        "Garuda-BoldOblique.ttf.zip",
        "Garuda-Oblique.ttf.zip",
        "Garuda.ttf.zip",
        "Jomolhari.ttf.zip",
        "kaputaunicode.ttf.zip",
        "KhmerOS.ttf.zip",
        "lannaalif-v1-03.ttf.zip",
        "LateefRegOT.ttf.zip",
        "Lohit-Kannada.ttf.zip",
        "ocrb10.ttf.zip",
        "Padauk-book.ttf.zip",
        "Pothana2000.ttf.zip",
        "Quivira.otf.zip",
        "Sun-ExtA.ttf.zip",
        "Sun-ExtB.ttf.zip",
        "SundaneseUnicode-1.0.5.ttf.zip",
        "SyrCOMEdessa.otf.zip",
        "TaameyDavidCLM-Medium.ttf.zip",
        "TaiHeritagePro.ttf.zip",
        "Tharlon-Regular.ttf.zip",
        "TharlonOFL.txt.zip",
        "UnBatang_0613.ttf.zip",
        "Uthman.otf.zip",
        "XB-Riyaz.ttf.zip",
        "XB-RiyazBd.ttf.zip",
        "XB-RiyazBdIt.ttf.zip",
        "XB-RiyazIt.ttf.zip",
        "ZawgyiOne.ttf.zip",
    ],
    /**
     * MPDF dictionary data
     */
    "data-files"         => [
        "35vatl1sea53brq/linebrdictK.dat.zip",
        "lmpck0nwukgu6ge/linebrdictL.dat.zip",
        "tz8tyvurd61rom2/linebrdictT.dat.zip",
    ],
    "mpdf-lib"           => "mpdf.zip",

    "admin-ui-fonts"     => "i91wh0v4v9q1jyl/cerebrisans.zip",

    /**
     * List of font for admin UI
     */
    "admin-ui-font-list" => [
        "cerebrisans/cerebrisans-medium.eot",
        "cerebrisans/cerebrisans-medium.ttf",
        "cerebrisans/cerebrisans-medium.woff",
        "cerebrisans/cerebrisans-mediumd41d.eot",
        "cerebrisans/cerebrisans-regular.eot",
        "cerebrisans/cerebrisans-regular",
        "cerebrisans/cerebrisans-regular.woff",
        "cerebrisans/cerebrisans-regulard41d.eot",
        "cerebrisans/cerebrisans-semibold.eot",
        "cerebrisans/cerebrisans-semibold.ttf",
        "cerebrisans/cerebrisans-semibold.woff",
        "cerebrisans/cerebrisans-semiboldd41d.eot",
    ],
];
