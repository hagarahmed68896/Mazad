<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:activate-upcoming-ads')]
#[Description('Command description')]
class ActivateUpcomingAds extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = \App\Models\Ad::where('status', 'upcoming')
            ->where(function($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->update(['status' => 'active']);

        $this->info("Activated {$count} upcoming ads.");
    }
}
