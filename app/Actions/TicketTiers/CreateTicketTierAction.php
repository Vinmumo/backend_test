<?php

namespace App\Actions\TicketTiers;

use App\Data\CreateTicketTierData;
use App\Models\TicketTier;

class CreateTicketTierAction
{
    public function execute(CreateTicketTierData $data): TicketTier
    {
        return TicketTier::query()->create([
            'event_id' => $data->event_id,
            'name' => $data->name,
            'price' => $data->price,
            'quantity' => $data->quantity,
            'sales_channels' => $data->sales_channels,
        ]);
    }
}
