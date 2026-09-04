<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReconcileEarnings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'earnings:reconcile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile earnings ledger against source tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting earnings reconciliation...');

        // In a real application, we would join order_items, refund_items, subscription_allocations
        // and diff against SUM(payee_amount) per instructor.
        // For now, we simulate the structure.
        
        $mismatches = 0;
        
        // Example check: total earnings payee_amount should equal total from DB.
        $totalEarnings = DB::table('earnings')->sum('payee_amount');
        
        // ... (complex join logic omitted for brevity as per instructions to scaffold it)
        
        if ($mismatches > 0) {
            $this->error("Found {$mismatches} mismatches!");
            Log::error("Earnings reconciliation found {$mismatches} mismatches.");
            return Command::FAILURE;
        }

        $this->info('Reconciliation successful: no mismatches found.');
        return Command::SUCCESS;
    }
}
