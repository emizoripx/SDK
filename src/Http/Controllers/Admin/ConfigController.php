<?php

namespace Emizor\SDK\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Repositories\ParametricRepository;
use Illuminate\Http\Request;
use Emizor\SDK\Facade\EmizorSdk;


class ConfigController extends Controller
{
    public function index(Request $request)
    {
        // Asumir que hay un account_id en session o auth
        // Para demo, usar el primero o un ID fijo
        $accountId = $request->get('account_id', 'some-uuid'); // Cambiar según auth

        $account = BeiAccount::find($accountId);

        if (!$account) {
            return view('emizor::admin.config.index', ['error' => 'Account not found']);
        }

        $parametricRepo = new ParametricRepository();

        $globalTypes = ['motivos-de-anulacion', 'tipos-documento-de-identidad', 'metodos-de-pago', 'unidades'];
        $specificTypes = ['actividades', 'leyendas', 'productos-sin'];

        $globalParametrics = [];
        foreach ($globalTypes as $type) {
            $globalParametrics[$type] = $parametricRepo->list($type);
        }

        $specificParametrics = [];
        foreach ($specificTypes as $type) {
            $specificParametrics[$type] = $parametricRepo->list($type, $accountId);
        }

        return view('emizor::admin.config.index', compact('account', 'globalParametrics', 'specificParametrics', 'globalTypes', 'specificTypes'));
    }

    public function check(Request $request)
    {
        $accountId = $request->get('account_id', 'some-uuid');

        $account = BeiAccount::find($accountId);

        $checks = [
            'cuenta_existente' => !is_null($account),
            'parametros_por_defecto' => $account ? !is_null($account->bei_defaults) : false,
            'existe_token' => $account ? !is_null($account->bei_token) : false,
        ];

        $parametricRepo = new ParametricRepository();

        // Required parametric types
        $requiredParametrics = [
            'motivos-de-anulacion',
            'tipos-documento-de-identidad',
            'metodos-de-pago',
            'unidades',
            'actividades',
            'leyendas',
            'productos-sin'
        ];

        $parametricChecks = [];
        $parametricsComplete = true;
        foreach ($requiredParametrics as $type) {
            $hasType = $parametricRepo->hasType($type, $accountId);
            $parametricChecks[$type] = $hasType;
            if (!$hasType) {
                $parametricsComplete = false;
            }
        }

        $checks['parametricas_completas'] = $parametricsComplete;
        $checks['detalle_de_parametricas'] = $parametricChecks;

        $isComplete = !in_array(false, array_filter($checks, fn($v) => !is_array($v)));

        return view('emizor::admin.config.check', compact('checks', 'isComplete'));
    }

    public function sync(Request $request, string $type)
    {
        $accountId = $request->get('account_id', 'some-uuid');

        $account = BeiAccount::find($accountId);

        if (!$account) {
            return redirect()->back()->with('error', 'Account not found');
        }

        try {
            $api = app('emizorsdk', ['accountId' => $accountId]);
            $api->syncParametrics([$type]);
            return redirect()->back()->with('success', "Parametric '{$type}' synced successfully");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to sync parametric: ' . $e->getMessage());
        }
    }

    public function accounts()
    {
        $accounts = BeiAccount::all();
        return view('emizor::admin.accounts.index', compact('accounts'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'environment' => 'required|in:piloto,production',
            'owner_type' => 'nullable|string',
            'owner_id' => 'nullable|string',
        ]);

        try {
            $accountId = EmizorSdk::register(function ($builder) use ($request) {
                $builder->setClientId($request->client_id)
                        ->setClientSecret($request->client_secret);

                if ($request->environment === 'production') {
                    $builder->useProductionEnvironment();
                } else {
                    $builder->usePilotoEnvironment();
                }

                if ($request->owner_type && $request->owner_id) {
                    $builder->setOwnerType($request->owner_type)
                            ->setOwnerId($request->owner_id);
                }
            });

            return redirect()->route('emizor.admin.accounts')->with('success', 'Account registered successfully with ID: ' . $accountId);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    public function setDefaults(Request $request, string $accountId)
    {
        $account = BeiAccount::find($accountId);
        if (!$account) {
            return redirect()->back()->with('error', 'Account not found');
        }

        $request->validate([
            'branch' => 'nullable|integer|min:0|max:20',
            'pos' => 'nullable|integer|min:0|max:100',
            'payment_method' => 'nullable|string',
            'reason_revocation' => 'nullable|in:1,3',
            'sin_product_code' => 'nullable|string',
            'activity_code' => 'nullable|string',
        ]);

        $parametricRepo = new ParametricRepository();

        // Validate parametrics
        $errors = [];
        if ($request->type_document && (!$parametricRepo->hasType('tipos-documento-de-identidad', $accountId) || empty($parametricRepo->list('tipos-documento-de-identidad', $accountId, $request->type_document)))) {
            $errors[] = 'Invalid type document';
        }
        if ($request->payment_method && (!$parametricRepo->hasType('metodos-de-pago', $accountId) || empty($parametricRepo->list('metodos-de-pago', $accountId, $request->payment_method)))) {
            $errors[] = 'Invalid payment method';
        }
        if ($request->sin_product_code && (!$parametricRepo->hasType('productos-sin', $accountId) || empty($parametricRepo->list('productos-sin', $accountId, $request->sin_product_code)))) {
            $errors[] = 'Invalid SIN product code';
        }
        if ($request->activity_code && (!$parametricRepo->hasType('actividades', $accountId) || empty($parametricRepo->list('actividades', $accountId, $request->activity_code)))) {
            $errors[] = 'Invalid activity code';
        }

        if (!empty($errors)) {
            return redirect()->back()->with('error', 'Validation failed: ' . implode(', ', $errors));
        }

        try {
            $api = EmizorSdk::for($account);
            $api->setDefaults(function ($builder) use ($request) {
                if ($request->type_document) $builder->setTypeDocument($request->type_document);
                if ($request->branch !== null) $builder->setBranch($request->branch);
                if ($request->pos !== null) $builder->setPos($request->pos);
                if ($request->payment_method) $builder->setPaymentMethod($request->payment_method);
                if ($request->reason_revocation) $builder->setReasonRevocation($request->reason_revocation);
                if ($request->sin_product_code) $builder->setSinProductCode($request->sin_product_code);
                if ($request->activity_code) $builder->setActivityCode($request->activity_code);
            });

            return redirect()->back()->with('success', 'Defaults updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update defaults: ' . $e->getMessage());
        }
    }
}
