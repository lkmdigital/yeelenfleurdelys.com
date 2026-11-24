<?php

namespace App\Http\Controllers\Web\Host;

use App\Support\InternalApi;
use Illuminate\Http\Request;

class PromotionController extends HostController
{
    private function base(): string
    {
        return 'accommodations/' . $this->propertyId() . '/promotions';
    }

    public function index()
    {
        $res = InternalApi::get($this->base());
        $rooms = InternalApi::get('accommodations/' . $this->propertyId() . '/rooms/manage')->items();

        return $this->page('promotions', [
            'promotions' => $res->items(),
            'rooms' => collect($rooms)->pluck('name', 'id')->all(),
        ]);
    }

    public function store(Request $request)
    {
        $res = InternalApi::post($this->base(), $this->validated($request, true));
        return $res->failBack() ?? back()->with('success', 'Promotion créée.');
    }

    public function update(Request $request, int $promotion)
    {
        $res = InternalApi::put($this->base() . "/{$promotion}", $this->validated($request, false));
        return $res->failBack() ?? back()->with('success', 'Promotion enregistrée.');
    }

    public function toggle(int $promotion)
    {
        $res = InternalApi::post($this->base() . "/{$promotion}/toggle");
        return $res->failBack() ?? back()->with('success', 'Promotion mise à jour.');
    }

    public function destroy(int $promotion)
    {
        $res = InternalApi::delete($this->base() . "/{$promotion}");
        return $res->failBack() ?? back()->with('success', 'Promotion supprimée.');
    }

    private function validated(Request $request, bool $new): array
    {
        $data = $request->validate([
            'discount_type' => 'required|in:percent,fixed',
            'discount_percent' => 'nullable|required_if:discount_type,percent|numeric|min:1|max:100',
            'discount_amount' => 'nullable|required_if:discount_type,fixed|numeric|min:1',
            'room_id' => 'nullable|integer',
            'min_stay_nights' => 'nullable|integer|min:1|max:365',
            'promo_code' => 'nullable|string|max:100|alpha_dash',
            'start_date' => 'required|date' . ($new ? '|after_or_equal:today' : ''),
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string|max:500',
            'members_only' => 'boolean',
        ], [
            'discount_percent.required_if' => 'Indiquez le pourcentage de réduction.',
            'discount_amount.required_if' => 'Indiquez le montant de la réduction.',
            'start_date.after_or_equal' => 'La promotion ne peut pas commencer dans le passé.',
            'end_date.after' => 'La date de fin doit être après la date de début.',
            'promo_code.alpha_dash' => 'Le code promo ne doit contenir que des lettres, chiffres et tirets.',
        ]);
        $data['members_only'] = $request->boolean('members_only');
        if ($data['discount_type'] === 'percent') {
            $data['discount_amount'] = null;
        } else {
            $data['discount_percent'] = null;
        }
        $data['promo_code'] = !empty($data['promo_code']) ? strtoupper($data['promo_code']) : null;

        return $data;
    }
}
