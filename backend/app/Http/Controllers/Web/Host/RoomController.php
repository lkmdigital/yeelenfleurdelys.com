<?php

namespace App\Http\Controllers\Web\Host;

use App\Support\InternalApi;
use Illuminate\Http\Request;

/** Chambres : liste, fiche, photos, disponibilités et tarifs par période. */
class RoomController extends HostController
{
    /** Catégories reconnues par l'API (rooms.room_category). Le nom affiché est le champ « type » de l'API. */
    public const CATEGORIES = [
        'single' => 'Chambre simple',
        'double' => 'Chambre double',
        'twin' => 'Lits jumeaux',
        'triple' => 'Chambre triple',
        'suite' => 'Suite',
        'pmr' => 'Accessible PMR',
        'other' => 'Autre (studio, appartement…)',
    ];

    public const AMENITIES = ['wifi', 'climatisation', 'tv', 'balcon', 'cuisine', 'room_service', 'breakfast'];

    public const AVAILABILITY = [
        'available' => 'Disponible',
        'blocked' => 'Fermée à la vente',
        'maintenance' => 'En travaux',
        'occupied' => 'Occupée',
    ];

    private function base(): string
    {
        return 'accommodations/' . $this->propertyId() . '/rooms';
    }

    private function room(int $room): array
    {
        $res = InternalApi::get($this->base() . "/manage/{$room}");
        abort_unless($res->ok(), 404);
        return $res->json();
    }

    public function index()
    {
        $res = InternalApi::get($this->base() . '/manage');
        return $this->page('rooms.index', ['rooms' => $res->items()]);
    }

    public function create()
    {
        return $this->page('rooms.form', ['room' => null]);
    }

    public function store(Request $request)
    {
        $res = InternalApi::post($this->base(), $this->validated($request));
        if ($fail = $res->failBack()) {
            return $fail;
        }
        return redirect()->route('host.rooms.images', $res->json('id') ?? $res->json('room.id'))
            ->with('success', 'Chambre créée. Ajoutez maintenant ses photos.');
    }

    public function edit(int $room)
    {
        return $this->page('rooms.form', ['room' => $this->room($room)]);
    }

    public function update(Request $request, int $room)
    {
        $res = InternalApi::put($this->base() . "/{$room}", $this->validated($request) + ['is_active' => $request->boolean('is_active')]);
        return $res->failBack() ?? redirect()->route('host.rooms')->with('success', 'Chambre enregistrée.');
    }

    public function destroy(int $room)
    {
        $res = InternalApi::delete($this->base() . "/{$room}");
        return $res->failBack() ?? redirect()->route('host.rooms')->with('success', 'Chambre supprimée.');
    }

    public function images(int $room)
    {
        return $this->page('rooms.images', ['room' => $this->room($room)]);
    }

    public function uploadImage(Request $request, int $room)
    {
        $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:10240',
        ], ['images.required' => 'Choisissez au moins une photo.', 'images.*.max' => 'Chaque photo doit faire moins de 10 Mo.']);

        foreach ($request->file('images') as $file) {
            $res = InternalApi::post($this->base() . "/{$room}/images", [], ['image' => $file]);
            if ($fail = $res->failBack()) {
                return $fail;
            }
        }
        return back()->with('success', 'Photos ajoutées.');
    }

    public function primaryImage(int $room, int $image)
    {
        $res = InternalApi::post($this->base() . "/{$room}/images/{$image}/primary");
        return $res->failBack() ?? back()->with('success', 'Photo principale modifiée.');
    }

    public function deleteImage(int $room, int $image)
    {
        $res = InternalApi::delete($this->base() . "/{$room}/images/{$image}");
        return $res->failBack() ?? back()->with('success', 'Photo supprimée.');
    }

    public function calendar(Request $request, int $room)
    {
        $month = $this->month($request->query('month'));
        $cal = InternalApi::get($this->base() . "/{$room}/calendar", [
            'start_date' => $month->copy()->startOfMonth()->toDateString(),
            'end_date' => $month->copy()->endOfMonth()->toDateString(),
        ]);
        $periods = InternalApi::get($this->base() . "/{$room}/price-periods");

        return $this->page('rooms.calendar', [
            'room' => $this->room($room),
            'month' => $month,
            'days' => collect($cal->json('calendar', []))->keyBy('date')->all(),
            'periods' => $periods->json('periods', []),
        ]);
    }

    public function updateCalendar(Request $request, int $room)
    {
        $data = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:available,blocked,maintenance,occupied',
            'price_override' => 'nullable|numeric|min:0',
        ], ['start_date.after_or_equal' => 'La date de début ne peut pas être dans le passé.', 'end_date.after_or_equal' => 'La date de fin doit être après la date de début.']);

        $res = InternalApi::post($this->base() . "/{$room}/availability/bulk", array_filter($data, fn ($v) => $v !== null && $v !== ''));
        return $res->failBack() ?? back()->with('success', 'Calendrier mis à jour.');
    }

    public function storePeriod(Request $request, int $room)
    {
        $data = $request->validate([
            'label' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'price_per_night' => 'required|numeric|min:0',
        ], ['price_per_night.required' => 'Indiquez le prix par nuit pour cette période.']);

        $res = InternalApi::post($this->base() . "/{$room}/price-periods", $data);
        return $res->failBack() ?? back()->with('success', 'Tarif de période ajouté.');
    }

    public function destroyPeriod(int $room, int $period)
    {
        $res = InternalApi::delete($this->base() . "/{$room}/price-periods/{$period}");
        return $res->failBack() ?? back()->with('success', 'Tarif de période supprimé.');
    }

    private function month(?string $m): \Carbon\Carbon
    {
        return $m && preg_match('/^\d{4}-\d{2}$/', $m) ? \Carbon\Carbon::createFromFormat('Y-m-d', "$m-01")->startOfDay() : now()->startOfMonth();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'room_category' => 'required|in:' . implode(',', array_keys(self::CATEGORIES)),
            'description' => 'nullable|string|max:3000',
            'capacity' => 'required|integer|min:1|max:20',
            'price_per_night' => 'required|numeric|min:0',
            'single_occupancy_price' => 'nullable|numeric|min:0',
            'bedrooms' => 'required|integer|min:1|max:10',
            'bathrooms' => 'required|integer|min:1|max:10',
            'quantity' => 'required|integer|min:1|max:100',
            'surface_area' => 'nullable|numeric|min:0',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:60',
        ], [
            'name.required' => 'Donnez un nom à la chambre (exemple : Chambre double vue piscine).',
            'capacity.required' => 'Indiquez le nombre de personnes.',
            'price_per_night.required' => 'Indiquez le prix par nuit.',
            'quantity.required' => 'Indiquez le nombre de chambres de ce type.',
        ]);
        // L'API utilise « type » comme nom affiché de la chambre (et recopie type dans name).
        $data['type'] = $data['name'];
        $data['amenities'] = array_values($data['amenities'] ?? []);

        return $data;
    }
}
