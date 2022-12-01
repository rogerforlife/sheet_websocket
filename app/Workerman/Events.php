<?php

namespace App\Workerman;

use App\Models\Issue;
use App\Models\Sheet;
use DB;
use GatewayWorker\Lib\Gateway;
use Log;

class Events
{
    public static function onWorkerStart($businessWorker)
    {
        echo "BusinessWorker    Start\n";
    }

    public static function onConnect($client_id)
    {
    }

    public static function onWebSocketConnect($client_id, $data)
    {
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:{$client_id} session:" . json_encode($_SESSION) . "\n";
        Log::info("client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:{$client_id} session:" . json_encode($_SESSION) . "\n");

        // $token = $data['get']['token'];
        $proj_id = $data['get']['proj_id'];
        $sheet_id = $data['get']['sheet_id'] !== '' ? (int) $data['get']['sheet_id'] : null;

        $emplid = $data['get']['emplid'];
        $name = $data['get']['name'];

        // if (!isset($token) || !isset($proj_id)) {
        //     Gateway::closeClient($client_id);
        // }

        // $secret = config('jwt.secret');
        // $jws = SimpleJWS::load($token);
        // if (!$jws->isValid($secret)) {
        //     Gateway::closeClient($client_id);
        // }

        // $payload = $jws->getPayload();
        // $emplid = $payload['emplid'];
        // $name = $payload['name'];

        if (!isset($sheet_id)) {
            $sheet_object = Sheet::firstOrNew(
                [
                    'proj_id' => $proj_id,
                    'name' => 'sheet1',
                    'order' => 1,
                    'visible_columns' => [
                        'index',
                        'issue',
                        'category',
                        'action',
                        'priority',
                        'open_date',
                        'close_date',
                        'initiator',
                        'issue_owners',
                    ],
                ]
            );

            $sheet_object->save();
            $sheet_id = $sheet_object['id'];
        }

        $_SESSION['proj_id'] = $proj_id;
        $_SESSION['name'] = $name;
        $_SESSION['emplid'] = $emplid;
        $_SESSION['sheet_id'] = $sheet_id;

        //初始資料
        $messages = Issue::where('sheet_id', $sheet_id)
            ->orderBy('id')
            ->get();

        //發送新成員訊息
        $new_message = [
            'type' => 'NEW_USER_JOIN',
            'user' => [
                'name' => $name,
                'emplid' => $emplid,
            ],
        ];
        Gateway::sendToGroup($proj_id, json_encode($new_message));

        //原先房間成員
        $user_list = Gateway::getClientSessionsByGroup($proj_id);
        foreach ($user_list as $tmp_client_id => $item) {
            $user_name_list[] = [
                'name' => $item['name'],
                'emplid' => $item['emplid'],
                'location' => $item['location'] ?? null,
            ];
        }
        $user_name_list[] = [
            'name' => $name,
            'emplid' => $emplid,
            'location' => null,
        ];

        //加入房間
        Gateway::joinGroup($client_id, $proj_id);

        //回傳初始資料
        Gateway::sendToCurrentClient(json_encode([
            'type' => 'INIT_MESSAGE',
            'messages' => $messages,
            'sheet_list' => Sheet::orderBy('order')
                ->get(),
            'sheet_setting' => Sheet::where('id', $sheet_id)
                ->first(),
        ]));
        Gateway::sendToCurrentClient(json_encode([
            'type' => 'INIT_USER',
            'user_list' => $user_name_list,
        ]));
    }

    public static function onMessage($client_id, $message)
    {
        // debug
        echo "client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:{$client_id} session:" . json_encode($_SESSION) . ' onMessage:' . $message . "\n";
        Log::info("client:{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} gateway:{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  client_id:{$client_id} session:" . json_encode($_SESSION) . ' onMessage:' . $message . "\n");

        // 客户端传递的是json数据
        $message_data = json_decode($message, true);
        if (!$message_data) {
            return;
        }
        $proj_id = $_SESSION['proj_id'];
        $sheet_id = $_SESSION['sheet_id'];

        switch ($message_data['type']) {
            // 客户端回应服务端的心跳
            case 'pong':
                return;
            // 客户端登录 message格式: {type:login, name:xx, room_id:1} ，添加到客户端，广播给所有客户端xx进入聊天室
            case 'LOCATION_CHANGE':
                $_SESSION['location'] = $message_data['location'];

                $new_message = [
                    'type' => $message_data['type'],
                    'user' => [
                        'name' => $_SESSION['name'],
                        'emplid' => $_SESSION['emplid'],
                        'location' => $message_data['location'],
                    ],
                ];

                Gateway::sendToGroup($proj_id, json_encode($new_message), [$client_id]);
                return;
            case 'DATA_CHANGE':
                $new_message = $message_data;

                //update to db
                foreach ($message_data['changes'] as $change) {
                    $temp_message = Issue::find($change['rowId']);
                    if (!isset($temp_message)) {
                        continue;
                    }

                    $temp_message->update([
                        $change['columnId'] => $change['value'],
                    ]);

                    # when status close, update closed date if it's null
                    if ($change['columnId'] === 'status' && $value === 'Closed') {
                        if (!isset($temp_message->close_date)) {
                            $temp_message->update([
                                'close_date' => date('Y-m-d'),
                            ]);

                            $new_message['changes'][] = [
                                'rowId' => $temp_message->id,
                                'value' => date('Y-m-d'),
                                'columnId' => 'close_date',
                            ];
                        }
                    }
                }

                Gateway::sendToGroup($proj_id, json_encode($new_message), [$client_id]);
                return;

            case 'ORDER_CHANGE':
                $new_message = $message_data;

                //update to db
                $fromRowId = intval($message_data['fromRowId']);
                $toRowId = intval($message_data['toRowId']);

                $Issue_order_id_from_fromRowId = Issue::where('id', $fromRowId)->first()->order;
                $Issue_order_id_from_toRowId = Issue::where('id', $toRowId)->first()->order;

                $range_list = $Issue_order_id_from_fromRowId < $Issue_order_id_from_toRowId ? [$Issue_order_id_from_fromRowId, $Issue_order_id_from_toRowId] : [$Issue_order_id_from_toRowId, $Issue_order_id_from_fromRowId];

                $table = 'issues';
                if ($Issue_order_id_from_fromRowId < $Issue_order_id_from_toRowId) {
                    $update_statement = 'update ' . $table . ' SET "order" = "order" - 1 where ("order" >= ' . $range_list[0] . ' and "order" <= ' . $range_list[1] . 'and "sheet_id" == ' . $sheet_id . ')';
                } else {
                    $update_statement = 'update ' . $table . ' SET "order" = "order" + 1 where ("order" >= ' . $range_list[0] . ' and "order" <= ' . $range_list[1] . 'and "sheet_id" == ' . $sheet_id . ')';
                }

                DB::statement($update_statement);
                $change_target = Issue::where('id', $fromRowId)->first();
                $change_target->order = $Issue_order_id_from_toRowId;
                $change_target->save();

                Gateway::sendToGroup($proj_id, json_encode($new_message), [$client_id]);
                return;
            case 'UPDATE_SHEET_SETTING':
                $new_message = $message_data;

                // update to db
                $sheet_object = Sheet::where('id', $sheet_id)->first();
                $sheet_object->update($message_data['sheet_setting']);
                $sheet_object->save();

                Gateway::sendToGroup($proj_id, json_encode($new_message), [$client_id]);
                return;

            case 'CREATE_SHEET':

                $new_message = $message_data;
                $sheet_obj = $message_data['sheet'];
                $sheet_object = Sheet::firstOrNew(
                    [
                        'proj_id' => $proj_id,
                        'name' => $sheet_obj['name'],
                        'order' => Sheet::where('proj_id', $proj_id)->max('order') + 1,
                        'visible_columns' => [
                            'index',
                            'issue',
                            'category',
                            'action',
                            'priority',
                            'open_date',
                            'close_date',
                            'initiator',
                            'issue_owners',
                        ],
                    ]
                );
                $sheet_object->save();

                $temp_sheet_qs = Sheet::where('proj_id', $proj_id)->orderBy('id')->get();

                $new_message['type'] = 'CREATE_SHEET';
                $new_message['sheet_list'] = $temp_sheet_qs;

                Gateway::sendToGroup($proj_id, json_encode($new_message), []);
                return;

            case 'DELETE_SHEET':
                $new_message = $message_data;

                $sheet_object = Sheet::where('id', $message_data['sheet_id'])->first();
                $sheet_object->delete();

                Gateway::sendToGroup($proj_id, json_encode($new_message));

                $user_list = Gateway::getAllClientSessions();
                foreach ($user_list as $tmp_client_id => $item) {
                    if ($item['sheet_id'] === $message_data['sheet_id']) {
                        Gateway::closeClient($tmp_client_id);
                    }
                }

                return;

            default:
                Gateway::sendToGroup($proj_id, json_encode($message_data), [$client_id]);
                return;
        }
    }

    public static function onClose($client_id)
    {
        // debug
        if (isset($_SESSION['proj_id'])) {
            $proj_id = $_SESSION['proj_id'];

            //檢查使用者是否還有其他session在房間
            $user_list = Gateway::getClientSessionsByGroup($proj_id);
            foreach ($user_list as $tmp_client_id => $item) {
                if ($item['emplid'] === $_SESSION['emplid']) {
                    return;
                }
            }

            //回傳離開訊息
            $new_message = [
                'type' => 'USER_LEAVE',
                'user' => [
                    'name' => $_SESSION['name'],
                    'emplid' => $_SESSION['emplid'],
                ],
            ];
            Gateway::sendToGroup($proj_id, json_encode($new_message));
        }
    }

}
