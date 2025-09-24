<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\Framework\Request\Request;

class UploadIntegrationController extends Controller
{
    /**
     * getSettings method will fetch the list of integration settings by integration key
     * @param Request $request
     * @return false
     */
    public function getSettings(Request $request)
    {
        $settingsKey = $request->getSafe('integration_key', 'sanitize_text_field');

        $settings = apply_filters('fluent_support_pro/file_storage_integration_settings_' . $settingsKey, [
            'fieldsConfig' => null,
            'settings'     => null
        ]);

        return $settings;
    }

    /**
     * saveSettings method will save the integration settings by integration key
     * @param Request $request
     * @return array
     */
    public function saveSettings(Request $request)
    {
        $settingsKey = $request->getSafe('integration_key', 'sanitize_text_field');
        $settings = wp_unslash($request->getSafe('settings', 'sanitize_text_field', []));

        return apply_filters('fluent_support_pro/file_storage_integration_settings_save_' . $settingsKey, [
            'message' => __('Sorry, upload driver not found')
        ], $settings);
    }
    
}
