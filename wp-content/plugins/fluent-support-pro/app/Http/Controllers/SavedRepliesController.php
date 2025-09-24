<?php

namespace FluentSupportPro\App\Http\Controllers;

use FluentSupport\App\Http\Controllers\Controller;
use FluentSupport\App\Models\SavedReply;
use FluentSupport\App\Services\Helper;
use FluentSupport\Framework\Request\Request;

class SavedRepliesController extends Controller
{
    public function index(Request $request)
    {
        $perPageLimit = $request->get('per_page', 10);

        $replies = SavedReply::orderBy('id', 'DESC')
            ->with(['person', 'product'])
            ->searchBy($request->get('search'))
            ->productBy($request->get('product_id'))
            ->paginate($perPageLimit);

        return [
            'replies' => $replies
        ];
    }

    public function create(Request $request)
    {
        $data = $request->all();
        $this->validate($data, [
            'title'   => 'required',
            'content' => 'required'
        ]);

        $currentPerson = Helper::getAgentByUserId();

        $data['created_by'] = $currentPerson->id;

        $reply = SavedReply::create($data);

        return [
            'reply'   => $reply,
            'message' => 'Reply Template has been created'
        ];
    }

    public function update(Request $request, $id)
    {
        $reply = SavedReply::findOrFail($id);
        $data = $request->all();
        $this->validate($data, [
            'title'   => 'required',
            'content' => 'required'
        ]);

        $reply->fill($data);
        $reply->save();


        return [
            'message' => __('Reply Template has been updated', 'fluent-support-pro'),
            'reply'   => $reply
        ];
    }

    public function delete(Request $request, $id)
    {
        $row = SavedReply::findOrFail($id);
        if(!$row){
            return $this->sendError(__('No reply template found with this id', 'fluent-support-pro'));
        }

        SavedReply::where('id', $id)->delete();

        return [
            'message' => __('Selected Reply Template has been deleted', 'fluent-support-pro')
        ];

    }

    public function get(Request $request, $id){
        $reply = SavedReply::where('id', $id)->first();
        if(!$reply){
            return $this->sendError(__('No reply template found with this id', 'fluent-support-pro'));
        }
        return $reply;
    }
}
