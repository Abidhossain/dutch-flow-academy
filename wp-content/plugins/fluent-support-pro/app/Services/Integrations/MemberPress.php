<?php

namespace FluentSupportPro\App\Services\Integrations;

use Mepr\User\MeprUser;

class MemberPress
{

    public function boot()
    {
        add_filter('fluent_support/customer_extra_widgets', array($this, 'getMemberPressInfo'), 1100, 2);
    }
    public function getMemberPressInfo($widgets, $customer)
    {
        $userId = $customer->user_id;
        $user = new \MeprUser($userId);


        $subscriptions = $user->active_product_subscriptions('products');

        if (empty($subscriptions)) {
            return $widgets;
        }

        $purchases = $this->getPurchaseInformation($subscriptions, $userId);
        $status = $user->is_active() ? 'Active' : 'Inactive';

        ob_start();
        ?>
        <div class="support-widget">
            <div class="widget-header" style="display: inline-block;">
                <h4 style="display: inline-block; margin-right: 10px;">
                    User status :
                </h4>
                <div class="fs_memberpress_status-tag" style=" background-color: <?php echo $status === 'Active' ? '#B8EAD9' : '#F7D3D3'; ?>; color: <?php echo $status === 'Active' ? '#12B881' : '#DE4242'; ?>;">
                        <?php echo $status; ?>
                </div>
            </div>

        <div class="widget-body">
            <ul class="subscription-list">
                <li class="subscription-item">
                    <div class="product-info">
                        <p class="fs_mepr_product_list">
                            <strong>Recurring</strong>
                        </p>
                    </div>
                </li>
                <?php foreach ($purchases as $purchase): ?>
                    <?php if (!empty($purchase['subscription_url'])) : ?>
                        <li class="subscription-item">
                            <div class="product-info">
                                <p class="fs_mepr_product_list">
                            <span class="icon" style="vertical-align: middle;">
                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" width="16px" data-name="Layer 1" viewBox="0 0 100 100" id="product">
                                    <path fill="#1d1d1b" d="M82.55,27.8,50.88,3.3a1.47,1.47,0,0,0-1.78,0L17.45,27.8A1.43,1.43,0,0,0,16.89,29v42.1a1.43,1.43,0,0,0,.56,1.15L49.1,96.7A1.47,1.47,0,0,0,50,97a1.45,1.45,0,0,0,.89-.3L82.55,72.2a1.43,1.43,0,0,0,.56-1.15V29A1.43,1.43,0,0,0,82.55,27.8ZM50,52.09,36,41.26l14-11.1,14,11.1-9.82,7.6ZM66.37,39.43l-14-11.12,12.9-10.23L79.6,29.19ZM50,6.29l12.88,10L33.63,39.43,20.4,29.19Zm-30.2,26.1L48.54,54.63V92.58L19.79,70.34ZM51.45,92.58V54.63L80.21,32.39v38Z"></path>
                                </svg>
                            </span>
                                    <a href="<?php echo $purchase['subscription_url'] ?>" target="_blank" style="display: inline-block; vertical-align: middle; color: #2271b1; text-decoration: none;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';"><?php echo $purchase['product_title']; ?></a>
                                </p>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <li class="subscription-item">
                    <div class="product-info">
                        <p class="fs_mepr_product_list">
                            <strong>Non-Recurring</strong>
                        </p>
                    </div>
                </li>
                <?php foreach ($purchases as $purchase): ?>
                    <?php if (empty($purchase['subscription_url'])) : ?>
                        <li class="subscription-item">
                            <div class="product-info">
                                <p class="fs_mepr_product_list">
                            <span class="icon" style="vertical-align: middle;">
                                <svg xmlns="http://www.w3.org/2000/svg" height="16px" width="16px" data-name="Layer 1" viewBox="0 0 100 100" id="product">
                                    <path fill="#1d1d1b" d="M82.55,27.8,50.88,3.3a1.47,1.47,0,0,0-1.78,0L17.45,27.8A1.43,1.43,0,0,0,16.89,29v42.1a1.43,1.43,0,0,0,.56,1.15L49.1,96.7A1.47,1.47,0,0,0,50,97a1.45,1.45,0,0,0,.89-.3L82.55,72.2a1.43,1.43,0,0,0,.56-1.15V29A1.43,1.43,0,0,0,82.55,27.8ZM50,52.09,36,41.26l14-11.1,14,11.1-9.82,7.6ZM66.37,39.43l-14-11.12,12.9-10.23L79.6,29.19ZM50,6.29l12.88,10L33.63,39.43,20.4,29.19Zm-30.2,26.1L48.54,54.63V92.58L19.79,70.34ZM51.45,92.58V54.63L80.21,32.39v38Z"></path>
                                </svg>
                            </span>
                                    <?php echo $purchase['product_title']; ?>
                                </p>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>


        <?php
        $content = ob_get_clean();

        $widgets['mepr'] = [
            'header'    => __('MemberPress Purchases '. '('.count($purchases).')', 'fluent-support-pro'),
            'body_html' => $content
        ];

        return $widgets;
    }

    private function getPurchaseInformation($subscriptions, $userId)
    {
        $subscriptionData = [];
        foreach ($subscriptions as $subscription) {

            $subscriptionLink = $this->getSubscriptionLink($subscription, $userId);


            $subscriptionData[] = [
                'subscription_id' => $subscription->ID,

                'product_title' => $subscription->post_title,
                'subscription_url' =>$subscriptionLink
            ];
        }

        return $subscriptionData;
    }

    public function getSubscriptionLink($subscription, $userId)
    {
        global $wpdb;

        $mepr_db = new \MeprDb();

        $sql = $wpdb->prepare("SELECT id FROM {$mepr_db->subscriptions} WHERE product_id = %d AND user_id = %d", $subscription->ID, $userId);
        $results = $wpdb->get_results($sql);

        $id = array_column($results, 'id');

        if (empty($id)) {
            return '';
        }

        return admin_url('admin.php?page=memberpress-subscriptions&subscription=' . $id[0]);
    }

}
