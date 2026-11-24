<?php

namespace App\Http\Controllers\Web\Admin;

use App\Support\InternalApi;
use Illuminate\Http\Request;

/** Établissements : liste, création, fiche, statut. */
class AccommodationController extends AdminController
{
    public const TYPES = ['hotel' => 'Hôtel', 'apartment' => 'Résidence meublée', 'guesthouse' => "Maison d'hôtes", 'lodge' => 'Lodge', 'other' => 'Autre'];

    public const STATUSES = ['all' => 'Tous les statuts', 'published' => 'En ligne', 'pending' => 'En attente', 'disabled' => 'Désactivé', 'rejected' => 'Refusé', 'removed' => 'Retiré'];

    public function index(Request $request)
    {
        $filters = $this->only(['search' => trim((string) $request->query('search')), 'status' => $request->query('status')]);
        $res = InternalApi::get('admin/accommodations', $filters + ['page' => $request->integer('page') ?: 1, 'per_page' => 20]);

        return view('web.admin.accommodations.index', ['items' => $res->json('data', []), 'meta' => $this->meta($res), 'filters' => $filters]);
    }

    public function create()
    {
        $hosts = InternalApi::get('admin/hosts', ['per_page' => 100])->json('data', []);
        return view('web.admin.accommodations.create', ['hosts' => collect($hosts)->mapWithKeys(fn ($h) => [$h['id'] => $h['name'] . ' — ' . $h['email']])->all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'host_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(self::TYPES)),
            'description' => 'required|string|max:5000',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:120',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'price_per_night' => 'required|numeric|min:0',
            'max_guests' => 'required|integer|min:1',
            'whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
        ], ['host_id.required' => 'Choisissez le compte gérant de cet établissement.']);
        $data += ['bedrooms' => 1, 'bathrooms' => 1, 'status' => 'pending'];
        if ($data['type'] === 'other') {
            $data['type_other_label'] = 'Autre';
        }

        $res = InternalApi::post('admin/accommodations', $data);
        if ($fail = $res->failBack()) {
            return $fail;
        }
        $id = $res->json('data.id') ?? $res->json('id');
        return redirect()->route('admin.accommodations.show', $id)->with('success', 'Établissement créé. Le gérant peut maintenant compléter sa fiche.');
    }

    public function show(int $id)
    {
        $res = InternalApi::get("admin/accommodations/{$id}");
        abort_unless($res->ok(), 404);
        return view('web.admin.accommodations.show', [
            'a' => $res->json('data'),
            'rooms' => InternalApi::get("admin/accommodations/{$id}/rooms")->items(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:120',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'price_per_night' => 'required|numeric|min:0',
            'description' => 'required|string|max:5000',
        ]);
        $res = InternalApi::put("accommodations/{$id}", array_filter($data, fn ($v) => $v !== null));
        return $res->failBack() ?? back()->with('success', 'Établissement enregistré.');
    }

    public function status(Request $request, int $id)
    {
        $action = $request->input('action');
        abort_unless(in_array($action, ['approve', 'reject', 'disable', 'enable', 'toggle-featured', 'remove'], true), 404);
        $request->validate(['reason' => (in_array($action, ['reject', 'remove'], true) ? 'required' : 'nullable') . '|string|max:2000'], ['reason.required' => 'Indiquez le motif : il est transmis au gérant.']);

        $res = InternalApi::post("admin/accommodations/{$id}/{$action}", array_filter(['reason' => $request->input('reason')]));
        $done = [
            'approve' => 'Établissement mis en ligne.', 'reject' => 'Fiche refusée. Le gérant est prévenu.',
            'disable' => 'Établissement désactivé : il n’apparaît plus sur le site.', 'enable' => 'Établissement réactivé.',
            'toggle-featured' => 'Mise en avant modifiée.', 'remove' => 'Établissement retiré.',
        ][$action];
        return $res->failBack() ?? back()->with('success', $done);
    }
}
