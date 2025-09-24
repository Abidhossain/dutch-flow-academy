<?php

/**
 * WSAL Freemius SDK
 *
 * Freemius SDK initialization file for WSAL.
 *
 * @since 2.7.0
 * @package wsal
 */
use  WSAL\Helpers\WP_Helper ;
// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Freemius SDK
 *
 * Create a helper function for easy SDK access.
 *
 * @return Freemius
 * @throws Freemius_Exception
 */
class wsalFsNull {
    public function is_premium() {
        return true;
    }
    public function has_active_valid_license() {
        return true;
    }
    public function is__premium_only() {
        return true;
    }
    public function can_use_premium_code() {
        return true;
    }
    public function is_plan__premium_only() {
        return 'professional';
    }
    public function is_registered() {
        return true;
    }
    public function is_free_plan() {
        return false;
    }
    public function is_plan_or_trial__premium_only() {
        return 'professional';
    }
    public function is_anonymous() {
        return true;
    }
    public function is_not_paying() {
        return false;
    }
    public function has_api_connectivity() {
        return false;
    }
    public function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
        add_filter( $tag, $function_to_add, $priority, $accepted_args );
    }

    public function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
        add_action( $tag, $function_to_add, $priority, $accepted_args );
    }
}
function wsal_freemius()
{
    global  $wsal_freemius ;
    
    if ( !isset( $wsal_freemius ) && !apply_filters( 'wsal_disable_freemius_sdk', false ) ) {
        define( 'WP_FS__PRODUCT_94_MULTISITE', true );
        // Include Freemius SDK.
        require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, array(
            '..',
            'third-party',
            'freemius',
            'wordpress-sdk',
            'start.php'
        ) );
        // Check anonymous mode.
        $freemius_state = \WSAL\Helpers\Settings_Helper::get_option_value( 'wsal_freemius_state', 'anonymous' );
        $is_anonymous = 'anonymous' === $freemius_state || 'skipped' === $freemius_state;
        $is_premium = true;
        $is_anonymous = ( $is_premium ? false : $is_anonymous );
        // Trial arguments.
        $trial_args = array(
            'days'               => 14,
            'is_require_payment' => false,
        );
        if ( WpSecurityAuditLog::is_mainwp_active() && !WP_Helper::is_multisite() ) {
            $trial_args = false;
        }
        $wsal_freemius = new wsalFsNull();
    }
    
    return apply_filters( 'wsal_freemius_sdk_object', $wsal_freemius );
}

// Init Freemius.
wsal_freemius();
// Signal that SDK was initiated.
do_action( 'wsal_freemius_loaded' );