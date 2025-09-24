<?php

namespace FluentSupportPro\App\Services\Integrations\Discord;

use FluentSupport\Framework\Support\Arr;
use FluentSupportPro\App\Services\Integrations\Discord\DiscordHelper;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class DiscordApi
{
	public static function send($message)
	{
		$settings = DiscordHelper::getSettings();

		$data = [
			'payload_json' => json_encode($message)
		];

		$response = wp_remote_post(Arr::get($settings, 'webhook_url'), [
			'body'   => $data,
			'header' => [
				'content-type' =>  'multipart/form-data',
			]
		]);

		if (is_wp_error($response)) {
			return new \WP_Error($response->get_error_code(), $response->get_error_message());
		}

		if (!$response) {
			return new \WP_Error('discord_error', 'Discord API Request Failed');
		}

		return $response;
	}
}
