<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BranchController extends Controller
{
    public function index(): View
    {
        $branches = auth()->user()->store
            ->branches()
            ->orderBy('name')
            ->get();

        return view('dashboard.branches.index', compact('branches'));
    }

    public function create(): View
    {
        $this->checkBranchLimit();
        return view('dashboard.branches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkBranchLimit();

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $data['store_id'] = auth()->user()->store_id;
        $data['active']   = true;

        Branch::create($data); // qr_token se genera en el boot del modelo

        return redirect()->route('dashboard.branches.index')
            ->with('success', 'Sucursal creada correctamente.');
    }

    public function edit(Branch $branch): View
    {
        $this->authorizeBranch($branch);
        return view('dashboard.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorizeBranch($branch);

        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'active'  => ['sometimes', 'boolean'],
        ]);

        $data['active'] = $request->boolean('active', $branch->active);

        $branch->update($data);

        return redirect()->route('dashboard.branches.index')
            ->with('success', 'Sucursal actualizada.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $this->authorizeBranch($branch);
        $branch->delete();

        return redirect()->route('dashboard.branches.index')
            ->with('success', 'Sucursal eliminada.');
    }

    /** Página de configuración antes de imprimir */
    public function qrConfigure(Branch $branch): View
    {
        $this->authorizeBranch($branch);
        $branch->load('store');

        return view('dashboard.branches.qr-configure', compact('branch'));
    }

    /** Guardar configuración del QR */
    public function qrSave(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorizeBranch($branch);

        $valid = [
            'blue','green','dark','purple','orange','red','sky','pink','teal','amber',
        ];

        $data = $request->validate([
            'qr_scheme'        => ['required', 'string', 'in:' . implode(',', $valid)],
            'qr_layout'        => ['required', 'in:a5,a4'],
            'qr_headline'      => ['nullable', 'string', 'max:80'],
            'qr_instruction'   => ['nullable', 'string', 'max:200'],
            'qr_show_logo'     => ['boolean'],
            'qr_show_branch'   => ['boolean'],
            'qr_logo_position' => ['required', 'in:left,center,right'],
            'qr_qr_size'       => ['required', 'in:sm,md,lg,xl'],
            'qr_headline_size' => ['required', 'in:sm,md,lg'],
            'qr_instr_size'    => ['required', 'in:sm,md,lg'],
            'qr_logo_size'     => ['required', 'in:sm,md,lg'],
        ]);

        $data['qr_show_logo']   = $request->boolean('qr_show_logo');
        $data['qr_show_branch'] = $request->boolean('qr_show_branch');
        $data['qr_headline']    = $data['qr_headline']  ?? 'Verificá tu precio';

        $branch->update($data);

        return redirect()->route('dashboard.branches.qr.configure', $branch)
            ->with('success', 'Configuración del QR guardada.');
    }

    /** Página de impresión del QR — se abre en pestaña nueva */
    public function qr(Branch $branch): View
    {
        $this->authorizeBranch($branch);

        $branch->load('store');

        $svg = QrCode::format('svg')
            ->size(500)
            ->margin(1)
            ->generate($branch->scanUrl());

        // Logo en base64 para embeber en la página sin rutas de storage
        $logoBase64 = null;
        $logoMime   = null;
        if ($branch->store->logo_path && Storage::disk('public')->exists($branch->store->logo_path)) {
            $logoBase64 = base64_encode(Storage::disk('public')->get($branch->store->logo_path));
            $ext        = strtolower(pathinfo($branch->store->logo_path, PATHINFO_EXTENSION));
            $logoMime   = match ($ext) {
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
        }

        return view('dashboard.branches.qr-print', compact('branch', 'svg', 'logoBase64', 'logoMime'));
    }

    // ----------------------------------------------------------------

    private function authorizeBranch(Branch $branch): void
    {
        abort_if($branch->store_id !== auth()->user()->store_id, 403);
    }

    private function checkBranchLimit(): void
    {
        $store = auth()->user()->store;
        $sub   = $store->subscription;

        // Trial = acceso total sin límites
        if ($sub?->hasFullAccess()) {
            return;
        }

        if ($sub && $sub->plan && $sub->plan->max_branches !== null) {
            $count = $store->branches()->where('active', true)->count();
            if ($count >= $sub->plan->max_branches) {
                throw new \Illuminate\Http\Exceptions\HttpResponseException(
                    redirect()->route('dashboard.branches.index')
                        ->with('limit_reached', "Alcanzaste el límite de {$sub->plan->max_branches} sucursal(es) de tu plan. Actualizá tu plan para agregar más.")
                );
            }
        }
    }
}
