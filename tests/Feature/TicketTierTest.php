<?php

use App\Models\Event;
use App\Models\TicketTier;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    $permissions = [
        'ticket-tiers.view-any',
        'ticket-tiers.view',
        'ticket-tiers.create',
        'ticket-tiers.update',
        'ticket-tiers.delete',
        'ticket-tiers.publish',
    ];

    foreach ($permissions as $permission) {
        Permission::query()->create(['name' => $permission]);
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo($permissions);
});

it('stores a ticket tier', function (): void {
    $event = Event::factory()->create();

    $response = $this->actingAs($this->user)->postJson('/api/ticket-tiers', [
        'event_id' => $event->id,
        'name' => 'Early Bird',
        'price' => 25.50,
        'quantity' => 100,
        'sales_channels' => ['web', 'box_office'],
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('message', 'Ticket tier created successfully.')
        ->assertJsonPath('data.name', 'Early Bird')
        ->assertJsonPath('data.event_id', $event->id);

    $this->assertDatabaseHas('ticket_tiers', [
        'event_id' => $event->id,
        'name' => 'Early Bird',
        'price' => 25.50,
        'quantity' => 100,
    ]);
});

it('enforces name uniqueness per event and allows the same name across events', function (): void {
    $firstEvent = Event::factory()->create();
    $secondEvent = Event::factory()->create();

    TicketTier::factory()->create([
        'event_id' => $firstEvent->id,
        'name' => 'VIP',
    ]);

    $this->actingAs($this->user)->postJson('/api/ticket-tiers', [
        'event_id' => $firstEvent->id,
        'name' => 'VIP',
        'price' => 100,
        'quantity' => 10,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    $this->actingAs($this->user)->postJson('/api/ticket-tiers', [
        'event_id' => $secondEvent->id,
        'name' => 'VIP',
        'price' => 100,
        'quantity' => 10,
    ])->assertCreated()
        ->assertJsonPath('data.event_id', $secondEvent->id)
        ->assertJsonPath('data.name', 'VIP');
});

it('filters tiers available on a sales channel', function (): void {
    $event = Event::factory()->create();

    $allChannels = TicketTier::factory()->create([
        'event_id' => $event->id,
        'name' => 'General Admission',
        'sales_channels' => null,
    ]);
    $webOnly = TicketTier::factory()->create([
        'event_id' => $event->id,
        'name' => 'Web Only',
        'sales_channels' => ['web'],
    ]);
    $boxOfficeOnly = TicketTier::factory()->create([
        'event_id' => $event->id,
        'name' => 'Box Office Only',
        'sales_channels' => ['box_office'],
    ]);

    $response = $this->actingAs($this->user)->getJson('/api/ticket-tiers?filter[channel]=web');

    $response->assertOk();

    expect(collect($response->json('data'))->pluck('id')->all())
        ->toContain($allChannels->id, $webOnly->id)
        ->not->toContain($boxOfficeOnly->id);
});

it('publishes a ticket tier', function (): void {
    $ticketTier = TicketTier::factory()->create([
        'is_published' => false,
    ]);

    $this->actingAs($this->user)
        ->postJson("/api/ticket-tiers/{$ticketTier->id}/publish")
        ->assertOk()
        ->assertJsonPath('message', 'Ticket tier published successfully.')
        ->assertJsonPath('data.is_published', true);

    $this->assertDatabaseHas('ticket_tiers', [
        'id' => $ticketTier->id,
        'is_published' => true,
    ]);
});

it('soft deletes a ticket tier and excludes it from the index', function (): void {
    $deletedTier = TicketTier::factory()->create(['name' => 'Deleted tier']);
    $visibleTier = TicketTier::factory()->create(['name' => 'Visible tier']);

    $this->actingAs($this->user)
        ->deleteJson("/api/ticket-tiers/{$deletedTier->id}")
        ->assertOk()
        ->assertJsonPath('message', 'Ticket tier deleted successfully.');

    $this->assertSoftDeleted('ticket_tiers', [
        'id' => $deletedTier->id,
    ]);

    $response = $this->actingAs($this->user)->getJson('/api/ticket-tiers');

    $response->assertOk();

    expect(collect($response->json('data'))->pluck('id')->all())
        ->toContain($visibleTier->id)
        ->not->toContain($deletedTier->id);
});
