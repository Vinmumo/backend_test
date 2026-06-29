<?php

namespace App\Data;

use App\Models\TicketTier;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class UpdateTicketTierData extends Data
{
    public function __construct(
        public Optional|string $name,
        public Optional|int $event_id,
        public Optional|float $price,
        public Optional|int $quantity,
        public Optional|array|null $sales_channels,
        public Optional|bool $is_active,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        $ticketTier = request()->route('ticket_tier');
        $eventId = $context->payload['event_id'] ?? $ticketTier?->event_id;

        return [
            'event_id' => ['sometimes', 'integer', 'exists:events,id'],
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('ticket_tiers', 'name')
                    ->where('event_id', $eventId)
                    ->ignore($ticketTier?->id),
            ],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'sales_channels' => ['sometimes', 'nullable', 'array'],
            'sales_channels.*' => ['string', Rule::in(TicketTier::SALES_CHANNELS)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
