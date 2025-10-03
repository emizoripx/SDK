<?php

namespace Emizor\SDK\Models;

use Emizor\SDK\Jobs\TrackOffline;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BeiOfflineInvoiceTracking extends Model
{

    protected $table = 'bei_offline_invoice_tracking';

    protected $fillable = [];

    protected $guarded = [];

    public $timestamps = false;

    public static function register( $ticket)
    {
        self::create([
            "ticket" =>$ticket,
            "registered_at" => Carbon::now()->timezone('America/La_Paz')->format("Y-m-d H:i:s"),
            "next_try" => Carbon::now()->timezone('America/La_Paz')->format("Y-m-d H:i:s"),
        ]);
        TrackOffline::dispatch($ticket);
    }

    public static function remove( $ticket)
    {
        self::where('ticket', $ticket)->delete();
    }

    public function setupNextTry()
    {
        $this->tries += 1;
        $this->last_tried_at = $this->next_try;
        $this->next_try = Carbon::now()->timezone('America/La_Paz')->addMinutes($this->tries * 3)->format("Y-m-d H:i:s");
        $this->save();
    }

    public function reachMaxTries()
    {
        return $this->tries >10;
    }

}
