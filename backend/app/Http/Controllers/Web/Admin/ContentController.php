<?php

namespace App\Http\Controllers\Web\Admin;

use App\Support\InternalApi;
use Illuminate\Http\Request;

/** Avis, promotions, programme fidélité, mentions légales et journal d'activité. */
class ContentController extends AdminController
{
    public function reviews(Request $request)
    {
        $status = in_array($request->query('moderation_status'), ['pending', 'approved', 'hidden'], true) ? $request->query('moderation_status') : null;
        $res = InternalApi::get('admin/reviews', $this->only(['moderation_status' => $status, 'page' => $request->integer('page') ?: null]));
        return view('web.admin.reviews', ['reviews' => $res->items(), 'meta' => $this->meta($res), 'status' => $status]);
    }

    public function reviewAct(int $id, string $action)
    {
        $res = InternalApi::post("admin/reviews/{$id}/moderate", ['action' => $action]);
        return $res->failBack() ?? back()->with('success', $action === 'approve' ? 'Avis publié.' : 'Avis masqué.');
    }

    public function promotions(Request $request)
    {
        $res = InternalApi::get('admin/promotions', $this->only(['page' => $request->integer('page') ?: null]));
        return view('web.admin.promotions', ['promotions' => $res->json('data', []), 'meta' => $this->meta($res)]);
    }

    public function togglePromotion(int $id)
    {
        $res = InternalApi::post("admin/promotions/{$id}/toggle");
        return $res->failBack() ?? back()->with('success', 'Promotion mise à jour.');
    }

    public function programme()
    {
        return view('web.admin.programme', [
            'stats' => InternalApi::get('admin/loyalty/stats')->json('data', []),
            'tiers' => InternalApi::get('admin/loyalty/tiers')->json('data', []),
            'rewards' => InternalApi::get('admin/loyalty/reward-tiers')->json('data', []),
            'establishments' => InternalApi::get('admin/loyalty/establishments')->json('data', []),
        ]);
    }

    public function legal(Request $request)
    {
        $docs = InternalApi::get('admin/legal-documents')->json('data', []);
        $slug = $request->query('doc', $docs[0]['slug'] ?? null);
        return view('web.admin.legal', ['docs' => $docs, 'current' => collect($docs)->firstWhere('slug', $slug)]);
    }

    public function updateLegal(Request $request, string $slug)
    {
        $data = $request->validate(['title' => 'required|string|max:255', 'content' => 'required|string', 'is_published' => 'boolean']);
        $data['is_published'] = $request->boolean('is_published');
        $res = InternalApi::put("admin/legal-documents/{$slug}", $data);
        return $res->failBack() ?? back()->with('success', 'Document enregistré.');
    }

    public function activity(Request $request)
    {
        $res = InternalApi::get('admin/activity-log', ['page' => $request->integer('page') ?: 1, 'per_page' => 30]);
        return view('web.admin.activity', ['logs' => $res->json('data', []), 'meta' => $this->meta($res)]);
    }
}
