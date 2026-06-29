<?php

namespace App\Data;

use App\Models\TicketTier;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class CreateTicketTierData extends Data
{
    public function __construct(
        public int $event_id,
        public string $name,
        public float $price,
        public int $quantity,
        public ?array $sales_channels = null,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ticket_tiers', 'name')->where('event_id', $context->payload['event_id'] ?? null),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'sales_channels' => ['nullable', 'array'],
            'sales_channels.*' => ['string', Rule::in(TicketTier::SALES_CHANNELS)],
        ];
    }
}
