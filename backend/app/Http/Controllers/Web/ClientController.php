<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\InternalApi;
use Illuminate\Http\Request;

/** Espace client (/dashboard/user) : réservations, messages, fidélité, profil. */
class ClientController extends Controller
{
    public function dashboard(Request $request)
    {
        $stats = InternalApi::get('analytics/traveler');
        $loyalty = InternalApi::get('me/loyalty');
        $notifications = InternalApi::get('me/notifications');

        return view('web.client.dashboard', [
            'stats' => $stats->ok() ? $stats->json() : [],
            'loyalty' => $loyalty->json('data', []),
            'credit' => (float) InternalApi::get('credits/balance')->json('balance', 0),
            'unread' => (int) $notifications->json('unread_count', 0),
            'notifications' => array_slice($notifications->json('data', []), 0, 5),
        ]);
    }

    public function reservations(Request $request)
    {
        $status = $request->query('status');
        $res = InternalApi::get('bookings', array_filter(['page' => $request->integer('page') ?: null, 'status' => $status]));

        return view('web.client.reservations', [
            'bookings' => $res->items(),
            'meta' => $res->json(),
            'status' => $status,
        ]);
    }

    public function trips()
    {
        $stats = InternalApi::get('analytics/traveler');
        $all = InternalApi::get('bookings', ['per_page' => 100])->items();
        $past = array_values(array_filter($all, fn ($b) => in_array($b['status'], ['completed', 'confirmed'], true) && substr($b['check_out'], 0, 10) < now()->toDateString()));

        return view('web.client.trips', [
            'upcoming' => $stats->json('upcoming_bookings', []),
            'past' => $past,
        ]);
    }

    public function inbox()
    {
        $res = InternalApi::get('user/inbox');
        return view('web.client.inbox', ['threads' => $res->items(), 'me' => auth()->id()]);
    }

    public function inboxReply(Request $request)
    {
        $request->validate(['parent_id' => 'required|integer', 'body' => 'required|string|max:2000'], ['body.required' => 'Écrivez votre réponse.']);
        $res = InternalApi::post('user/inbox', $request->only('parent_id', 'body'));
        return $res->failBack() ?? back()->with('success', 'Réponse envoyée.');
    }

    public function favorites()
    {
        $res = InternalApi::get('favorites');
        $items = collect($res->json('data', []))->map(fn ($f) => $f['accommodation'] ?? $f)->filter(fn ($a) => isset($a['id']))->values()->all();
        return view('web.client.favorites', ['items' => $items]);
    }

    public function programme()
    {
        return view('web.client.programme', [
            'loyalty' => InternalApi::get('me/loyalty')->json('data', []),
            'history' => InternalApi::get('me/loyalty/history')->json('data', []),
        ]);
    }

    public function claimVoucher(Request $request)
    {
        $request->validate(['reward_tier_id' => 'required|integer']);
        $res = InternalApi::post('me/loyalty/claim-voucher', $request->only('reward_tier_id'));
        if ($fail = $res->failBack()) {
            return $fail;
        }
        return back()->with('success', 'Bon de réduction créé : ' . $res->json('data.code') . '. Saisissez-le comme code promo lors de votre prochaine réservation.');
    }

    public function payments()
    {
        $res = InternalApi::get('me/payments');
        return view('web.client.payments', ['payments' => $res->json('data', []), 'total' => (float) $res->json('total_paid', 0)]);
    }

    public function credits()
    {
        $res = InternalApi::get('credits');
        return view('web.client.credits', ['balance' => (float) $res->json('balance', 0), 'credits' => $res->json('credits.data', [])]);
    }

    public function reviews(Request $request)
    {
        $res = InternalApi::get('me/reviews');
        return view('web.client.reviews', [
            'submitted' => $res->json('submitted', []),
            'pending' => $res->json('pending', []),
            'focus' => $request->integer('booking') ?: null,
        ]);
    }

    public function storeReview(Request $request)
    {
        $request->validate([
            'accommodation_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ], ['rating.required' => 'Choisissez une note de 1 à 5.', 'comment.required' => 'Écrivez quelques mots sur votre séjour.']);

        $res = InternalApi::post('reviews', $request->only('accommodation_id', 'booking_id', 'rating', 'comment'));
        if (!$res->ok()) {
            $msg = $res->message();
            return back()->withErrors(['general' => $msg === 'You must have a completed booking to review' ? 'Vous pourrez donner votre avis après votre séjour.' : $msg])->withInput();
        }
        return back()->with('success', 'Merci pour votre avis ! Il sera publié après relecture.');
    }

    public function documents()
    {
        return view('web.client.documents', ['identity' => InternalApi::get('me/identity')->json()]);
    }

    public function uploadIdentity(Request $request)
    {
        $request->validate([
            'id_type' => 'required|in:CNI,Passeport,Permis',
            'id_number' => 'required|string|max:255',
            'id_document_recto' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            'id_document_verso' => 'required_if:id_type,CNI,Permis|nullable|image|mimes:jpeg,jpg,png|max:5120',
        ], [
            'id_document_recto.required' => 'Ajoutez la photo du recto.',
            'id_document_verso.required_if' => 'Ajoutez la photo du verso.',
        ]);

        $res = InternalApi::post('me/identity', $request->only('id_type', 'id_number'), array_filter([
            'id_document_recto' => $request->file('id_document_recto'),
            'id_document_verso' => $request->file('id_document_verso'),
        ]));
        return $res->failBack() ?? back()->with('success', "Pièce d'identité enregistrée.");
    }

    public function notifications()
    {
        $res = InternalApi::get('me/notifications');
        return view('web.client.notifications', ['items' => $res->json('data', []), 'unread' => (int) $res->json('unread_count', 0)]);
    }

    public function readAllNotifications()
    {
        InternalApi::post('me/notifications/read-all');
        return back()->with('success', 'Toutes les notifications sont marquées comme lues.');
    }

    public function profile()
    {
        return view('web.client.profile', ['me' => InternalApi::get('me')->json()]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:255',
            'residence_country' => 'nullable|string|max:255',
            'residence_city' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before:today',
        ]);
        $res = InternalApi::put('me/profile', $data);
        return $res->failBack() ?? back()->with('success', 'Profil mis à jour.');
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|mimes:jpeg,jpg,png|max:2048'], ['avatar.max' => 'La photo ne doit pas dépasser 2 Mo.']);
        $res = InternalApi::post('me/avatar', [], ['avatar' => $request->file('avatar')]);
        return $res->failBack() ?? back()->with('success', 'Photo mise à jour.');
    }

    public function settings()
    {
        return view('web.client.settings', ['twoFactor' => InternalApi::get('two-factor/status')->json()]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], ['password.confirmed' => 'Les deux mots de passe ne correspondent pas.', 'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.']);
        $res = InternalApi::post('me/password', $request->only('current_password', 'password', 'password_confirmation'));
        return $res->failBack() ?? back()->with('success', 'Mot de passe modifié.');
    }

    public function help()
    {
        $support = json_decode(file_get_contents(resource_path('data/support.json')), true);
        return view('web.client.help', ['support' => $support['support'], 'groups' => $support['faq']]);
    }
}
