<?php

namespace App\Http\Controllers\Api;

use App\Constant;
use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\IssueAction;
use GatewayClient\Gateway;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    public function __construct()
    {
        Gateway::$registerAddress = '127.0.0.1:' . config('values.workerman_register_port');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        // dd($data);
        $new_Issue_obj = new Issue();
        $new_Issue_obj->index = Issue::where('sheet_id', $data['sheet_id'])->max('index') + 1;
        $new_Issue_obj->proj_id = $data['proj_id'];
        $new_Issue_obj->issue = $data['issue'];
        $new_Issue_obj->category = $data['category'];
        $new_Issue_obj->priority = $data['priority'];
        $new_Issue_obj->close_date = array_key_exists('close_date', $data) ? $data['close_date'] : null;
        $new_Issue_obj->initiator = $data['initiator'];
        $new_Issue_obj->issue_owners = json_encode($data['issue_owners']);
        $new_Issue_obj->sheet_id = $data['sheet_id'];
        $new_Issue_obj->save();

        // if (! empty($data['action'])) {
        //     $this->setActionAttribute($new_Issue_obj->id, $data['action']);
        // }

        // $result = Issue::with([
        //     'issue_actions' => function ($query) {
        //         $query->where('action_date', '<', date('Y-m-d'));
        //     },
        // ])->find($new_Issue_obj->id);

        $result = Issue::find($new_Issue_obj->id);

        $new_message = [
            'type' => 'NEW_ISSUE',
            'data' => $result,
        ];

        Gateway::sendToGroup($result->proj_id, json_encode($new_message));

        return $this->success(null, Constant::CREATE_SUCCESS);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Issue $issue)
    {
        $issue->delete();
        $new_message = [
            'type' => 'REMOVE_ISSUE',
            'data' => $issue,
        ];
        Gateway::sendToGroup($issue->proj_id, json_encode($new_message));
        return $this->success(null, Constant::DELETE_SUCCESS);
    }

    public function setActionAttribute($issue_id, $action_data)
    {
        $action = IssueAction::firstOrNew(
            [
                'issue_id' => $issue_id,
                'action_date' => date('Y-m-d'),
            ]
        );
        $action->action = $action_data;
        $action->save();
    }
}
