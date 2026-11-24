<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\User;
use Database\Seeders\EtablissementsSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEstablishmentsOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_four_establishments_with_one_manager_each(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(EtablissementsSeeder::class);
        $this->seed(EtablissementsSeeder::class); // idempotent

        $this->assertSame(4, Accommodation::count());
        $fdl = Accommodation::where('slug', 'hotel-fleur-de-lys-tiassale')->firstOrFail();
        $this->assertEqualsCanonicalizing([25000.0, 30000.0], $fdl->rooms()->pluck('price_per_night')->map(fn ($p) => (float) $p)->all());
        $this->assertSame(4, Accommodation::distinct('host_id')->count('host_id'));
        $this->assertTrue(User::where('email', 'admin@yeelenfleurdelys.com')->value('role') === 'admin');
        // Résidences sans tarif connu : chambre non réservable
        $this->assertSame(0, Accommodation::where('slug', 'residences-yeelen-angre-mahou')->first()->rooms()->where('is_active', true)->count());
    }

    public function test_overview_returns_one_row_per_establishment_for_admin_only(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(EtablissementsSeeder::class);
        $admin = User::where('email', 'admin@yeelenfleurdelys.com')->firstOrFail();
        $fdl = Accommodation::where('slug', 'hotel-fleur-de-lys-tiassale')->firstOrFail();

        Booking::factory()->create([
            'accommodation_id' => $fdl->id,
            'room_id' => $fdl->rooms()->first()->id,
            'status' => 'confirmed',
            'check_in' => now()->startOfMonth()->addDay(),
            'check_out' => now()->startOfMonth()->addDays(2),
            'total_price' => 25000,
        ]);

        $res = $this->actingAs($admin)->getJson('/api/admin/accommodations/overview')->assertOk();
        $this->assertCount(4, $res->json('data'));
        $row = collect($res->json('data'))->firstWhere('id', $fdl->id);
        $this->assertSame(1, $row['bookings_month']);
        $this->assertEquals(25000, $row['revenue_month']);

        $manager = User::find($fdl->host_id);
        $this->actingAs($manager)->getJson('/api/admin/accommodations/overview')->assertForbidden();
    }

    public function test_public_registration_cannot_create_a_manager_account(): void
    {
        $this->postJson('/api/auth/register-partner-light', [])->assertNotFound();
    }
}
