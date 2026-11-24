<?php

namespace App\Http\Controllers\Web\Host;

use App\Models\AccommodationImage;
use App\Support\InternalApi;
use Illuminate\Http\Request;

/** Fiche de l'établissement : informations, conditions, équipements et photos. */
class PropertyController extends HostController
{
    public const AMENITIES = [
        'wifi', 'climatisation', 'parking', 'piscine', 'restaurant', 'bar', 'tv', 'room_service',
        'securite', 'groupe_electrogene', 'laundry', 'breakfast', 'balcon', 'cuisine',
    ];

    public function edit()
    {
        $acc = $this->property();
        $readiness = InternalApi::get("accommodations/{$acc['id']}/readiness");

        return $this->page('property', [
            'acc' => $acc,
            'images' => AccommodationImage::where('accommodation_id', $acc['id'])->orderByDesc('is_primary')->orderBy('order')->orderBy('id')->get(),
            'readiness' => $readiness->ok() ? $readiness->json() : null,
            'amenityKeys' => array_values(array_unique(array_merge(self::AMENITIES, array_map('mb_strtolower', $acc['amenities'] ?? [])))),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'description' => 'required|string|max:5000',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:120',
            'price_per_night' => 'required|numeric|min:0',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'cancellation_policy_hours' => 'nullable|integer|min:0|max:720',
            'deposit_required' => 'boolean',
            'deposit_amount' => 'nullable|in:first_night,percentage,fixed',
            'deposit_percentage' => 'nullable|required_if:deposit_amount,percentage|numeric|min:1|max:99',
            'deposit_fixed_amount' => 'nullable|required_if:deposit_amount,fixed|numeric|min:1',
            'breakfast_included' => 'boolean',
            'breakfast_price' => 'nullable|numeric|min:0',
            'reception_24h' => 'boolean',
            'pets_allowed' => 'boolean',
            'smoking_area' => 'boolean',
            'shuttle_service' => 'boolean',
            'laundry' => 'boolean',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:60',
            'special_conditions' => 'nullable|string|max:2000',
        ], [
            'whatsapp.regex' => 'Le numéro WhatsApp ne doit contenir que des chiffres.',
            'deposit_percentage.required_if' => 'Indiquez le pourcentage de la garantie.',
            'deposit_fixed_amount.required_if' => 'Indiquez le montant de la garantie.',
        ]);
        $data['amenities'] = array_values($data['amenities'] ?? []);
        foreach (['deposit_required', 'breakfast_included', 'reception_24h', 'pets_allowed', 'smoking_area', 'shuttle_service', 'laundry'] as $b) {
            $data[$b] = $request->boolean($b);
        }

        $res = InternalApi::put('accommodations/' . $this->propertyId(), $data);
        return $res->failBack() ?? back()->with('success', 'Fiche de l’établissement enregistrée.');
    }

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'media' => 'required|array|max:10',
            'media.*' => 'image|mimes:jpeg,jpg,png,webp|max:10240',
        ], ['media.required' => 'Choisissez au moins une photo.', 'media.*.max' => 'Chaque photo doit faire moins de 10 Mo.']);

        $res = InternalApi::post('accommodations/' . $this->propertyId() . '/media', [], ['media' => $request->file('media')]);
        return $res->failBack() ?? back()->with('success', 'Photos ajoutées.');
    }

    public function primaryMedia(int $image)
    {
        $res = InternalApi::post('accommodations/' . $this->propertyId() . "/media/{$image}/primary");
        return $res->failBack() ?? back()->with('success', 'Photo principale modifiée.');
    }

    public function deleteMedia(int $image)
    {
        $res = InternalApi::delete('accommodations/' . $this->propertyId() . "/media/{$image}");
        return $res->failBack() ?? back()->with('success', 'Photo supprimée.');
    }

    public function submit()
    {
        $res = InternalApi::post('accommodations/' . $this->propertyId() . '/submit-for-review');
        return $res->failBack() ?? back()->with('success', $res->message('Votre fiche a été envoyée pour validation.'));
    }
}
