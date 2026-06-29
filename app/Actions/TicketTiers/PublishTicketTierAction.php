<?php

namespace App\Actions\TicketTiers;

use App\Models\TicketTier;

class PublishTicketTierAction
{
    public function execute(TicketTier $ticketTier): TicketTier
    {
        $ticketTier->update([
            'is_published' => true,
        ]);

        return $ticketTier->refresh();
    }
}
