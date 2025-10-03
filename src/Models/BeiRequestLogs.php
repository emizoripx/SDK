<?php

namespace Emizor\SDK\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;
class BeiRequestLogs extends Model
{
    const EMISSION_EVENT = "EMISSION";
    const REVOCATION_EVENT = "REVOCATION";
    const GET_DETAIL_EVENT = "DETAIL";
    const GET_STATUS_EVENT = "STATUS";
    const SYNC_SPECIFIC_PARAMETRIC = "SYNC_SPECIFIC_PARAMETRIC";
    const SYNC_GLOBAL_PARAMETRIC = "SYNC_GLOBAL_PARAMETRIC";
    const SYNC_BRANCHES = "SYNC_BRANCHES";
    const VALIDATION_NIT = "VALIDATION_NIT";

    protected $table = 'bei_request_logs';

    protected $guarded = [];

    public $timestamps = false;

    public static function saveLog($ticket, $data, $event, $http_code = 0, $id_request_log = null)
    {

        if (is_null($id_request_log)) {

            $id_request = Str::uuid();
            BeiRequestLogs::create([
                'id'=>$id_request,
                'bei_ticket' => $ticket,
                'bei_event' => $event,
                'bei_send_request_date' => Carbon::now()->timezone('America/La_Paz')->toDateTimeString(),
                'bei_request' => json_encode($data),
                'bei_response' => json_encode([]),
                'bei_http_code' => is_null($http_code)? 0 : $http_code
            ]);

            return $id_request;

        }else {
            $obj = BeiRequestLogs::find($id_request_log);
            $obj->bei_event = $event;
            $obj->bei_receive_response_date = Carbon::now()->timezone('America/La_Paz')->toDateTimeString();
            $obj->bei_response = json_encode($data);
            $obj->bei_http_code = is_null($http_code)? 0 : $http_code;
            $obj->save();

        }
    }



}
