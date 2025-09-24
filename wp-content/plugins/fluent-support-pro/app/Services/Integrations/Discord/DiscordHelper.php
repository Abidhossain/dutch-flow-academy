<?php

namespace FluentSupportPro\App\Services\Integrations\Discord;

use FluentSupport\App\Services\Helper;

class DiscordHelper
{

	public static function getSettings()
	{
		$settings = Helper::getIntegrationOption('discord_settings', []);

		$defaults = [
			'webhook_url'            => '',
			'notification_events'   => [],
			'status'                => 'no'
		];


		if(!$settings) {
			return $defaults;
		}

		return wp_parse_args($settings, $defaults);
	}
}
