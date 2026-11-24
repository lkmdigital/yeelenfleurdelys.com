<?php

namespace App\Http\Controllers\Web\Host;

use App\Support\InternalApi;
use Illuminate\Http\Request;

class ReviewController extends HostController
{
    public function index(Request $request)
    {
        $this->property();
        $reply = in_array($request->query('reply_status'), ['replied', 'not_replied'], true) ? $request->query('reply_status') : null;
        $res = InternalApi::get('host/reviews', array_filter(['reply_status' => $reply, 'page' => $request->integer('page') ?: null]));

        return $this->page('reviews', [
            'reviews' => $res->items(),
            'meta' => $res->json(),
            'reply' => $reply,
            'acc' => $this->property(),
        ]);
    }

    public function reply(Request $request, int $review)
    {
        $request->validate(['host_reply' => 'required|string|max:2000'], ['host_reply.required' => 'Écrivez votre réponse.']);
        $res = InternalApi::patch("host/reviews/{$review}/reply", $request->only('host_reply'));
        return $res->failBack() ?? back()->with('success', 'Réponse publiée sous l’avis.');
    }
}
