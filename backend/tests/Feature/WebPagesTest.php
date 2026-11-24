<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\User;
use Database\Seeders\EtablissementsSeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Toutes les pages du site Blade s'affichent (aucune erreur 500), pour chaque espace. */
class WebPagesTest extends TestCase
{
    use RefreshDatabase;

    private Accommodation $fdl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(PaymentMethodSeeder::class);
        $this->seed(EtablissementsSeeder::class);
        $this->fdl = Accommodation::where('slug', 'hotel-fleur-de-lys-tiassale')->firstOrFail();
    }

    private function client(): User
    {
        $user = User::factory()->create(['role' => 'user', 'first_name' => 'Awa', 'last_name' => 'Koné', 'email_verified_at' => now()]);
        Booking::factory()->create([
            'user_id' => $user->id,
            'accommodation_id' => $this->fdl->id,
            'room_id' => $this->fdl->rooms()->first()->id,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'check_in' => now()->addDays(10)->toDateString(),
            'check_out' => now()->addDays(12)->toDateString(),
            'total_price' => 50000,
        ]);
        return $user;
    }

    public function test_public_pages_render(): void
    {
        $room = $this->fdl->rooms()->first();
        foreach ([
            '/', '/accommodations', "/accommodations/{$this->fdl->id}", "/rooms/{$room->id}",
            '/faq', '/help', '/contact', '/cgu', '/retrouver-reservation',
            '/auth/login', '/auth/register', '/auth/forgot-password', '/auth/activate',
            "/bookings/new?room_id={$room->id}", '/review/booking/inconnu', '/bookings/inconnu',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_client_pages_render(): void
    {
        $user = $this->client();
        $this->actingAs($user);
        foreach ([
            'client.dashboard', 'client.reservations', 'client.trips', 'client.inbox', 'client.favorites',
            'client.programme', 'client.payments', 'client.credits', 'client.reviews', 'client.documents',
            'client.notifications', 'client.profile', 'client.settings', 'client.help',
        ] as $name) {
            $this->get(route($name))->assertOk()->assertSee(e($user->name), false);
        }
    }

    public function test_client_sees_own_booking_and_can_update_profile(): void
    {
        $user = $this->client();
        $booking = Booking::where('user_id', $user->id)->firstOrFail();

        $this->actingAs($user)->get(route('client.reservations'))->assertOk()->assertSee($booking->booking_number);
        $this->get(route('bookings.show', $booking->id))->assertOk()->assertSee('Réservation confirmée');

        $this->put(route('client.profile.update'), ['first_name' => 'Awa', 'last_name' => 'Traoré', 'phone' => '0700000001'])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSame('Traoré', $user->fresh()->last_name);
    }

    public function test_host_pages_render_for_the_establishment_manager(): void
    {
        $client = $this->client();
        $host = User::findOrFail($this->fdl->host_id);
        $room = $this->fdl->rooms()->first();
        $booking = Booking::where('accommodation_id', $this->fdl->id)->firstOrFail();
        $this->actingAs($host);

        foreach ([
            'host.dashboard', 'host.property', 'host.rooms', 'host.rooms.create', 'host.calendar', 'host.reservations',
            'host.requests', 'host.clients', 'host.inbox', 'host.reviews', 'host.promotions', 'host.finances',
            'host.stats', 'host.programme', 'host.staff', 'host.settings',
        ] as $name) {
            $this->get(route($name))->assertOk()->assertSee('Hôtel Fleur de Lys');
        }
        foreach (['host.rooms.edit', 'host.rooms.images', 'host.rooms.calendar'] as $name) {
            $this->get(route($name, $room->id))->assertOk()->assertSee(e($room->name), false);
        }
        $this->get(route('host.bookings.show', $booking->id))->assertOk()->assertSee(e($client->name), false)->assertSee($client->email);
        $this->get(route('host.reservations', ['status' => 'confirmed']))->assertOk()->assertSee(e($client->name), false);
    }

    public function test_host_can_update_a_room_and_create_a_promotion(): void
    {
        $host = User::findOrFail($this->fdl->host_id);
        $room = $this->fdl->rooms()->first();
        $this->actingAs($host);

        $this->from(route('host.rooms.edit', $room->id))->put(route('host.rooms.update', $room->id), [
            'name' => 'Chambre double confort', 'room_category' => 'double', 'capacity' => 2, 'price_per_night' => 30000,
            'bedrooms' => 1, 'bathrooms' => 1, 'quantity' => 3, 'is_active' => '1',
        ])->assertRedirect(route('host.rooms'))->assertSessionHas('success');
        $this->assertSame('Chambre double confort', $room->fresh()->name);
        $this->assertEquals(30000, (float) $room->fresh()->price_per_night);

        $this->from(route('host.promotions'))->post(route('host.promotions.store'), [
            'discount_type' => 'percent', 'discount_percent' => 15,
            'start_date' => now()->addDay()->toDateString(), 'end_date' => now()->addDays(10)->toDateString(),
            'description' => 'Offre de lancement',
        ])->assertRedirect(route('host.promotions'))->assertSessionHas('success');
        $this->assertDatabaseHas('promotions', ['accommodation_id' => $this->fdl->id, 'description' => 'Offre de lancement']);
    }

    public function test_admin_pages_render(): void
    {
        $client = $this->client();
        $booking = Booking::where('user_id', $client->id)->firstOrFail();
        $admin = User::where('email', 'admin@yeelenfleurdelys.com')->firstOrFail();
        $this->actingAs($admin);

        foreach ([
            'admin.dashboard', 'admin.analytics', 'admin.accommodations', 'admin.accommodations.create', 'admin.hosts',
            'admin.users', 'admin.users.create', 'admin.reservations', 'admin.reviews', 'admin.promotions', 'admin.programme',
            'admin.legal', 'admin.activity', 'admin.accounting', 'admin.payments', 'admin.settings',
        ] as $name) {
            $this->get(route($name))->assertOk();
        }
        foreach (['facturation', 'paiements', 'fidelite'] as $tab) {
            $this->get(route('admin.settings', $tab))->assertOk();
        }
        $this->get(route('admin.dashboard'))->assertSee('Hôtel Fleur de Lys')->assertSee('Résidences Yeelen');
        $this->get(route('admin.accommodations.show', $this->fdl->id))->assertOk()->assertSee('Hôtel Fleur de Lys');
        $this->get(route('admin.hosts.show', $this->fdl->host_id))->assertOk();
        $this->get(route('admin.users.show', $client->id))->assertOk()->assertSee($client->email);
        $this->get(route('admin.reservations.show', $booking->id))->assertOk()->assertSee(e($client->name), false);
        $this->get(route('admin.reservations', ['accommodation_id' => $this->fdl->id]))->assertOk()->assertSee(e($client->name), false);
    }

    public function test_admin_can_create_an_account_without_typing_a_password(): void
    {
        $this->actingAs(User::where('email', 'admin@yeelenfleurdelys.com')->firstOrFail());
        $this->post(route('admin.users.store'), ['name' => 'Koffi Yao', 'email' => 'koffi.yao@example.com', 'role' => 'host'])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'koffi.yao@example.com', 'role' => 'host']);
    }

    public function test_guests_are_sent_to_login_and_clients_cannot_open_other_spaces(): void
    {
        $this->get(route('client.dashboard'))->assertRedirect(route('login'));

        $this->actingAs($this->client());
        $this->get(route('host.dashboard'))->assertRedirect(route('client.dashboard'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('client.dashboard'));
    }

    public function test_web_login_opens_a_session_without_leaving_an_api_token(): void
    {
        $user = User::factory()->create(['role' => 'user', 'password' => bcrypt('motdepasse-test'), 'email_verified_at' => now()]);

        $this->post(route('login.submit'), ['email' => $user->email, 'password' => 'motdepasse-test'])
            ->assertRedirect(route('client.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_web_login_rejects_a_wrong_password_in_french(): void
    {
        $user = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $this->from(route('login'))->post(route('login.submit'), ['email' => $user->email, 'password' => 'mauvais'])
            ->assertRedirect(route('login'))->assertSessionHasErrors(['email' => 'E-mail ou mot de passe incorrect.']);
        $this->assertGuest();
    }
}
