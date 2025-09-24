<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\Framework\Request\Request;
use FluentSupportPro\App\Services\ProHelper;

class DocSuggestionController extends Controller
{
    public function index(Request $request)
    {
        $search = sanitize_text_field($request->search);

        $result = [];

        if ($search) {
            $ticketConfig = ProHelper::getTicketFormConfig();
            if ($ticketConfig['enable_docs'] != 'yes' || empty($ticketConfig['docs_post_types'])) {
                return [];
            }

            $search = apply_filters('fluent_support/search_doc_query', $search);
            $postTypes = apply_filters('fluent_support/search_doc_post_types', $ticketConfig['docs_post_types']);

            $posts = get_posts(apply_filters('fluent_support/doc_search_args', [
                'post_type'   => $postTypes,
                's'           => $search,
                'numberposts' => $ticketConfig['post_limits']
            ]));

            foreach ($posts as $item) {
                $result[] = [
                    'title' => $item->post_title,
                    'link'  => get_permalink($item)
                ];
            }
        }

        return apply_filters('fluent_support/search_doc_result', $result);
    }
}
