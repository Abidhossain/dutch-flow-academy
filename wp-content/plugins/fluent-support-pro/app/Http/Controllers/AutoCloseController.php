<?php

namespace FluentSupportPro\App\Http\Controllers;


use FluentSupport\Framework\Request\Request;
use FluentSupport\App\Http\Controllers\Controller;
use FluentSupportPro\App\Services\AutoCloseService;

class AutoCloseController extends Controller
{
    public function getSettings(Request $request)
    {
        $settings = AutoCloseService::getSettings();

        return [
            'settings' => $settings,
            'fields'   => AutoCloseService::getSettingsFields()
        ];
    }

    public function saveSettings(Request $request)
    {
        $settings = $request->get('settings', []);

        foreach ($settings as $key => $value) {
            if ($key === 'close_response_body') {
                $settings[$key] = wp_kses_post($value);
            } else {
                $settings[$key] = is_array($value) ? map_deep($value, 'sanitize_text_field') : sanitize_text_field($value);
            }
        }

        if ($settings['enabled'] == 'yes') {
            // validate
            $this->validate($settings, [
                'inactive_days'               => 'required|min:1',
                'exclude_if_customer_waiting' => 'required',
                'close_silently'              => 'required',
                'add_close_response'          => 'required'
            ]);

            if (!empty($settings['include_tags']) && !empty($settings['exclude_tags'])) {
                if (array_intersect($settings['include_tags'], $settings['exclude_tags'])) {
                    return $this->sendError([
                        'message' => __('Please use different tags in include and exclude tags field', 'fluent-support-pro')
                    ]);
                }
            }

            if ($settings['exclude_if_customer_waiting'] == 'no' && !$settings['closed_by_agent']) {
                return $this->sendError([
                    'message' => __('Default fallback agent is required', 'fluent-support-pro')
                ]);
            }

            if ($settings['add_close_response'] == 'yes' && empty($settings['close_response_body'])) {
                return $this->sendError([
                    'message' => __('Response body is required', 'fluent-support-pro')
                ]);
            }
        }

        AutoCloseService::saveSettings($settings);

        return [
            'message'  => __('Settings has been updated', 'fluent-support-pro'),
            'settings' => AutoCloseService::getSettingsFields()
        ];

    }

}
