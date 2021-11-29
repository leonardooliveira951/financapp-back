<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Illuminate\Console\Command;


class MakePayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'makepayment:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to make scheduled payments';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(PaymentService $payment_service)
    {
        $this->info("Cron is working fine!");
        return $payment_service->make_scheduled_payment();
    }
}
