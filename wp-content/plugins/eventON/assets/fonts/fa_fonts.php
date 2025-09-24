<?php
/**
 * font awesome fonts list array
 * @version       4.8
 * @fa_version    6.7.1
 */



   $cssFile = AJDE_EVCAL_PATH.'/assets/fonts/all.css';
   if (!file_exists($cssFile)) return false;

   // Read the contents of the CSS file
    $cssContent = file_get_contents($cssFile);


    // Use regex to capture blocks with both --fa and --fa--fa properties
   $pattern = '/\.fa-([a-z0-9\-]+)\s*\{\s*--fa:\s*"([^"]+)";\s*--fa--fa:\s*"[^"]+";\s*\}/i';
   preg_match_all($pattern, $cssContent, $matches);

   // Build the array from the captured data
   $normal = [];
   if (!empty($matches[1]) && !empty($matches[2])) {
       foreach ($matches[1] as $index => $className) {
           $normal[$className] = $matches[2][$index];
       }
   }

   // Use regex to capture blocks with only the --fa property
   $pattern = '/\.fa-([a-z0-9\-]+)\s*\{\s*--fa:\s*"([^"]+)";\s*\}/i';
   preg_match_all($pattern, $cssContent, $matches);

   // Build the array from the captured data
   $brands = [];
   if (!empty($matches[1]) && !empty($matches[2])) {
       foreach ($matches[1] as $index => $className) {
           $brands[$className] = $matches[2][$index];
       }
   }

   $all = array_merge($normal, $brands);

   $font_ = [];

   foreach($all as $key => $value){
      $add = ( array_key_exists($key, $brands)) ? 'fab ':'';
      $font_[] = $add . 'fa-'. $key;
   }
   
	
?>