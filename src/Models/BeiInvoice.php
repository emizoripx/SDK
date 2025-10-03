<?php

namespace Emizor\SDK\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BeiInvoice extends Model
{
    use HasFactory;

    protected $table = 'bei_invoices';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'bei_ticket',
        'bei_account_id',
        'bei_step_emission',
        'bei_step_revocation',
        'bei_amount_total',
        'bei_sector_document_id',
        'bei_pos_code',
        'bei_branch_code',
        'bei_payment_method',
        'bei_client',
        'bei_details',
        'bei_additional',
        'bei_emission_date',
        'bei_cuf',
        'bei_online',
        'bei_pdf_url',
        'bei_giftcard_amount',
        'bei_exception_code',
    ];


    protected $casts = [
        'bei_client' => 'array',
        'bei_details' => 'array',
        'bei_additional' => 'array',
    ];


    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return \Emizor\SDK\Database\Factories\BeiInvoiceFactory::new();
    }

    public function getTicket()
    {
        return $this->bei_ticket;
    }

    public static function findByTicket( $ticket): BeiInvoice
    {
        return BeiInvoice::where('bei_ticket', $ticket)->first();
    }

    public function bei_account()
    {
        return $this->belongsTo(BeiAccount::class, "bei_account_id");
    }

    public function getCredentials()
    {
        return $this->bei_account->getCredentials();
    }

    // EMISSION CHECKS TO PROCESS NEW AGAIN IN JOBS
    public function notEmitted()
    {
        return $this->bei_step_emission == 'none';

    }

    public function isInProgress()
    {
        return $this->bei_step_emission == 'in_progress';

    }
    public function isComplete()
    {
        return $this->bei_step_emission == 'complete';

    }
    public function isOffline()
    {
        return !$this->bei_online;
    }

    // REVOCATION CHECKS TO PROCESS NEW AGAIN IN JOBS
    public function notRevocated()
    {
        return is_null($this->bei_revocation_code) || $this->bei_step_revocation == 'none';
    }

    public function isInProgressRevocation()
    {
        return $this->bei_step_revocation == 'in_progress';
    }
    public function isCompleteRevocation()
    {
        return $this->bei_step_revocation == 'complete';
    }


    // EMISSION STEPS TO ENSURE COMPLETE ACTION
    public function markInProgressEmission()
    {
        $this->bei_step_emission = 'in_progress';
        $this->saveQuietly();
    }
    public function markSentEmission()
    {
        $this->bei_step_emission = 'sent';
        $this->saveQuietly();
    }

    public function markCompleteEmission()
    {
        $this->bei_step_emission = 'complete';
        $this->saveQuietly();
    }

    //REVOCATION STEPS TO ENSURE COMPLETE ACTION
    public function markInProgressRevocation()
    {
        $this->bei_step_revocation = 'in_progress';
        $this->saveQuietly();
    }
    public function markSentRevocation()
    {
        $this->bei_step_revocation = 'sent';
        $this->bei_revocation_date = Carbon::now()->timezone('America/La_Paz')->format("Y-m-d H:i:s");
        $this->saveQuietly();
    }
    public function markCompleteRevocation()
    {
        $this->bei_step_revocation = 'complete';
        $this->service()->handleCancellation()->save();
        $this->saveQuietly();
    }

    public function updateBEIfields($data)
    {
        $this->bei_cuf = $data['cuf'];
        $this->bei_emission_date = $data['fechaEmision'];

        $this->bei_additional = json_encode(
            [
                'leyenda' => isset($data['leyenda']) ?  $data['leyenda']: "",
                'urlSin' => isset($data['urlSin']) ? $data['urlSin']:"",
                'pdf_url' => isset($data['pdf_url']) ?$data['pdf_url']:"",
                'sucursal' => isset($data['sucursal']) ? $data['sucursal'] :"",
            ]
        );


        $this->saveQuietly();
    }

    public function getMontoTotalSujetoIva()
    {
        return $this->bei_amount_total - $this->bei_giftcard_amount;
    }

    public function getMontoTotal()
    {
        return $this->bei_amount_total;
    }

    public function getTotalPagar()
    {
        return $this->bei_amount_total;
    }

    public function getExchangeMoney()
    {
        return 1; // tipo cambio
    }

    public function getMoneyCode()
    {
        return 1;// BOLIVIANO
    }

    public function getSubtotal()
    {
        return collect($this->line_items)->sum('line_total');
    }


    public function getDiscount()
    {
        if ($this->is_amount_discount)
            return $this->discount;
        return $this->discount * $this->getSubtotal()/100;
    }

    public function setBeiRevocationCode()
    {
        if(! in_array($this->bei_revocation_code,[1,2,3,4])  ) {
            // $value = isset(request()) && isset(request()->bei_revocation_code ) && in_array(request()->bei_revocation_code, [1, 2, 3, 4]) ? request()->bei_revocation_code: 1;
            $value = null !== request() && isset(request()->bei_revocation_code) && in_array(request()->bei_revocation_code, [1, 2, 3, 4]) ? request()->bei_revocation_code : 1;

            $this->bei_revocation_code = $value;
            $this->saveQuietly();
        }
    }

    public function getLeyendaSin()
    {
        return '"ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY"';
    }

    public function getEmissionLeyenda()
    {
        if($this->bei_online)
            return  '"Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido en una modalidad de facturación en línea".';
        return  '“Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido fuera de línea, verifique su envío con su proveedor o en la página web <a href="https://www.impuestos.gob.bo"> www.impuestos.gob.bo </a> .”';
    }

    public function getAdditional()
    {
        return json_decode($this->bei_additional);
    }

    public function getLeyenda()
    {
        if (empty($additional = $this->getAdditional())) {
            return  isset($additional->leyenda) && !is_null($additional->leyenda) ? $additional->leyenda : '';
        }
        return "";
    }

    public function getUrlSin()
    {
        if (!  empty($additional = $this->getAdditional())) {
            return  isset($additional->urlSin) && !is_null($additional->urlSin) ? $additional->urlSin : '';
        }
        return "";
    }

    public function getStatusCode()
    {
        if(!$this->notEmitted() && $this->notRevocated())
            return 690;
        return 691;
    }

    public function getPdfPath() {

        if ($this->invitations()->exists()) {
            $invitation = $this->invitations()->first();
        } else {
            $this->service()->createInvitations();
            $invitation = $this->invitations()->first();
        }

        $file_path = $this->client->invoice_filepath($invitation).$this->numberFormatter().'.pdf';

        return Storage::url($file_path);
    }

    public function setBeiEmitter()
    {
        $this->bei_emited_by = auth()->user()->name();
        $this->saveQuietly();
    }

    public function setBeiRevocater()
    {
        $this->bei_revocated_by = auth()->user()->name();
        $this->saveQuietly();
    }

}
