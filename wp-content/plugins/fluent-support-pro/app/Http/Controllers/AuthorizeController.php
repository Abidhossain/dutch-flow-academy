<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\App;
use FluentSupport\Framework\Request\Request;
use FluentSupport\App\Http\Controllers\Controller;

class AuthorizeController extends Controller
{
    public function handleAuthorizeDropbox(Request $request)
    {
        $code = $request->get('code');

        // Verify the code
        do_action('fluent_support_pro/verify_dropbox_code', $code);

        wp_redirect(admin_url('admin.php?page=fluent-support#/settings/upload_integration'));
        exit();
    }

    public function handleAuthorizeGoogleDrive(Request $request)
    {
        $code = $request->get('code');
       // do_action('fluent_support_pro/verify_google_code', $code);

        wp_redirect(admin_url('admin.php?page=fluent-support#/settings/upload_integration_google?code='.$code));
        exit();
    }
}
