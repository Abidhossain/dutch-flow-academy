<?php

namespace FluentSupportPro\App\Services\Integrations;

use FluentSupport\App\Modules\IntegrationSettingsModule;
use FluentSupportPro\App\Services\Integrations\Slack\SlackNotification;
use FluentSupportPro\App\Services\Integrations\Twilio\TwilioNotification;
use FluentSupportPro\App\Services\Integrations\Discord\DiscordNotification;
use FluentSupportPro\App\Services\Integrations\Telegram\TelegramNotification;


class IntegrationInit
{
    public function init()
    {
        // Easy Digital Downloads
        if (class_exists('\Easy_Digital_Downloads')) {
            (new Edd())->boot();
        }

        // WooCommerce
        if(defined('WC_PLUGIN_FILE')) {
            (new WooCommerce())->boot();
        }

        // LearnDash
        if (defined('LEARNDASH_VERSION')) {
            (new LearnDash())->boot();
        }

        // LifterLMS
        if (defined('LLMS_PLUGIN_FILE')) {
            (new LifterLMS())->boot();
        }

        // TutorLMS
        if(defined('TUTOR_VERSION')) {
            (new TutorLMS)->boot();
        }

        // BuddyBoss
        if(defined('BP_PLUGIN_DIR')) {
            (new BuddyBoss)->boot();
        }

        // PaidMembership Pro
        if(defined('PMPRO_VERSION')) {
            (new PMPro)->boot();
        }

        // Restrict Content Pro
        if(class_exists( '\Restrict_Content_Pro' )) {
            (new RCPro)->boot();
        }

        // WishListMember
        if(defined('WLM3_PLUGIN_VERSION')) {
            (new WishListMember)->boot();
        }


        if(class_exists('MeprUtils')) {
            (new MemberPress())->boot();
        }

        if ( defined('LP_PLUGIN_FILE') ) {
            (new LearnPress())->boot();
        }

        if(defined('FLUENT_COMMUNITY_PLUGIN_VERSION')) {
            (new FluentCommunity())->boot();
        }

        $this->addNotificationIntegrations();
        $this->addFileUploadIntegrations();
    }

    private function addNotificationIntegrations()
    {
        IntegrationSettingsModule::addIntegration(new TelegramNotification);
        IntegrationSettingsModule::addIntegration(new SlackNotification());
        IntegrationSettingsModule::addIntegration(new DiscordNotification());
        IntegrationSettingsModule::addIntegration(new TwilioNotification());
    }

    private function addFileUploadIntegrations()
    {
        // New Integration Drivers
        (new \FluentSupportPro\App\Services\FileUploadIntegration\Dropbox\Bootstrap)->register();
        (new \FluentSupportPro\App\Services\FileUploadIntegration\GoogleDrive\Bootstrap)->register();
    }
}
