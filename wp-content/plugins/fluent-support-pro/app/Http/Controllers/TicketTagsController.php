<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\TicketTag;
use FluentSupport\Framework\Request\Request;

/**
 *  TicketTagsController class for REST API
 * This class is responsible for all interactions related to ticket
 * @package FluentSupport\App\Http\Controllers
 *
 * @version 1.0.0
 */

class TicketTagsController extends Controller
{

    /**
     * index method will return the list of ticket tag exist in database
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $tags = TicketTag::orderBy('id', 'DESC')->searchBy($request->get('search'))->paginate();

        foreach ($tags as $tag) {
            $tag->count = $tag->tickets()->count();
        }

        return [
            'tags' => $tags
        ];
    }

    /**
     * get method will return the list of ticket tag by tag id
     * @param Request $request
     * @param $tag_id
     * @return array
     */
    public function get(Request $request, $tag_id)
    {
        $product = TicketTag::findOrFail($tag_id);
        return [
            'tags' => $product
        ];
    }

    /**
     * Create method will create new tag
     * @param Request $request
     * @return array
     */
    public function create(Request $request)
    {
        $data = $request->all();//Get all data from request

        //Check data validity
        $this->validate($data, [
            'title' => 'required'
        ]);

        $data = wp_unslash($data);
        $product = TicketTag::create($data);

        return [
            'message' => __('Tag has been successfully created', 'fluent-support-pro'),
            'tag' => $product
        ];
    }

    /**
     * Update method will update existing tag by tag id
     * @param Request $request
     * @param $tag_id
     * @return array
     */
    public function update(Request $request, $tag_id)
    {
        $data = $request->all();//Get all data from request

        //Check data validity
        $this->validate($data, [
            'title' => 'required'
        ]);

        $product = TicketTag::findOrFail($tag_id);
        $product->fill($data);
        $product->save();

        return [
            'message' => __('Tag has been updated', 'fluent-support-pro'),
            'tag' => TicketTag::find($tag_id)
        ];
    }

    /**
     * delete method will delete tag by tag id
     * @param Request $request
     * @param $tag_id
     * @return array
     */
    public function delete(Request $request, $tag_id)
    {
        TicketTag::where('id', $tag_id)
            ->delete();

        return [
            'message' => __('Tag has been deleted', 'fluent-support-pro')
        ];
    }

    /**
     * getOptions method will fetch all tag for ticket and return
     * @return array
     */
    public function getOptions()
    {
        return [
            'option_key' => 'ticket_tags',
            'options' => TicketTag::select('id', 'title')->orderBy('title', 'ASC')->get()
        ];
    }

}
