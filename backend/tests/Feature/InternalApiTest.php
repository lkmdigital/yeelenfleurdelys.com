<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\InternalApi;
use Database\Seeders\EtablissementsSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class InternalApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(EtablissementsSeeder::class);
        Route::middleware('web')->get('/_t/public', fn () => response()->json(InternalApi::get('accommodations')->items()));
        Route::middleware('web')->get('/_t/my', function () {
            $r = InternalApi::get('accommodations/my');
            return response()->json(['status' => $r->status(), 'items' => $r->items(), 'path' => request()->path()]);
        });
    }

    public function test_public_endpoint_is_reachable_from_a_web_route(): void
    {
        $this->get('/_t/public')->assertOk()->assertJsonCount(4);
    }

    public function test_session_user_is_authenticated_on_the_api_and_request_is_restored(): void
    {
        $host = User::where('email', 'fleurdelys@yeelenfleurdelys.com')->first();
        $res = $this->actingAs($host, 'web')->get('/_t/my')->assertOk();
        $this->assertSame(200, $res->json('status'));
        $this->assertCount(1, $res->json('items'));
        $this->assertSame('_t/my', $res->json('path'));
    }

    public function test_guest_gets_401_from_protected_endpoint(): void
    {
        $this->get('/_t/my')->assertOk()->assertJsonPath('status', 401);
    }
}
