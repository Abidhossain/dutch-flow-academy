<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\Meta;
use FluentSupport\App\Models\Ticket;
use FluentSupport\Framework\Support\Arr;
use FluentSupportPro\App\Services\CustomFieldsService;
use FluentSupport\Framework\Request\Request;

class CustomFieldsController extends Controller
{
    /**
     * this function will return all custom field and it's type
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        return [
            'fields' => CustomFieldsService::getCustomFields(),
            'field_types' => CustomFieldsService::getFieldTypes()
        ];
    }

    /**
     * store method will create custom fields
     * @param Request $request
     * @return array
     */
    public function store(Request $request)
    {
        $fields = $request->get('fields');
        $fields = json_decode($fields, true);

        // Validate the field
        $validFields = [];
        $newSlugs = [];


        foreach ($fields as $field) {
            if(empty($field['label'])) {
                continue;
            }

            $field['label'] = sanitize_text_field($field['label']);

            $slug = $this->generateSlug($field);
            if(in_array($slug, $newSlugs)) {
                continue;
            }

            $field['slug'] = $slug;
            $validFields[] = $field;
            $newSlugs[] = $slug;
        }

        $oldSlugs = CustomFieldsService::getFieldSlugs();

        $deletedSlugs = array_diff($oldSlugs, $newSlugs);

        if($deletedSlugs) {
            Meta::where('object_type', 'ticket_meta')
                ->whereIn('key', $deletedSlugs)
                ->delete();
        }

        CustomFieldsService::updateCustomFields($validFields);

        return [
            'message' => __('Custom fields are successfully saved', 'fluent-support-pro'),
            'fields' => CustomFieldsService::getCustomFields()
        ];
    }

    /**
     * syncTicketData method will synchronize custom fields data with ticket
     * @param Request $request
     * @param $ticket_id
     * @return array
     */
    public function syncTicketData(Request $request, $ticket_id)
    {
        $customData = $request->get('custom_fields', []);
        $ticket = Ticket::findOrFail($ticket_id);

        $ticket->syncCustomFields($customData);

        return [
            'message' => __('Custom Data has been updated', 'fluent-support-pro'),
            'custom_data' => $ticket->customData('admin', false),
            'custom_data_rendered' => $ticket->customData('admin', true),
        ];
    }

    /**
     * @param $field
     * @return false|string
     */
    protected function generateSlug($field)
    {
        $slug = Arr::get($field, 'slug');

        $label = str_replace(' ', '_', $field['label']);

        if(is_numeric($slug)) {
            $slug = 'cf_'.$slug;
        }

        if($slug && substr( $slug, 0, 3 ) === "cf_") {
            return sanitize_title($slug, 'cf_custom_field', 'view');
        } else if($slug) {
            $slug = 'cf_'.$slug;
        } else {
            $slug = 'cf_'.$label;
        }

        $slug = sanitize_title($slug, 'cf_custom_field', 'view');
        return substr($slug, 0, 20);
    }

}
