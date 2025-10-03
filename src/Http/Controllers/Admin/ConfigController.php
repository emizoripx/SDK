<?php

namespace Emizor\SDK\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Repositories\ParametricRepository;
use Illuminate\Http\Request;


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
            'account_exists' => !is_null($account),
            'account_enabled' => $account ? $account->bei_enable : false,
            'defaults_set' => $account ? !is_null($account->bei_defaults) : false,
            'token_present' => $account ? !is_null($account->bei_token) : false,
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

        $checks['parametrics_complete'] = $parametricsComplete;
        $checks['parametric_details'] = $parametricChecks;

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
}