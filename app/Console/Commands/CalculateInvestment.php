<?php
namespace App\Console\Commands;

use App\Services\Gold\Calculator;
use Illuminate\Console\Command;

/**
 * Class CalculateInvestment
 *
 * Calculate max amount of gold one can buy having specified amount of money. It also shows some statistics.
 *
 * @package App\Console\Commands
 */
class CalculateInvestment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gold:calculate {--number=3 : Top number of days to calculate for} {--amount=600000 : Amount to invest}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate best investment date';

    /**
     * @var Calculator
     */
    protected $calculator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Calculator $calculator)
    {
        parent::__construct();
        $this->calculator = $calculator;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // arguments
        $number = $this->option('number');
        $amount = $this->option('amount');

        // get best values
        $best   = $this->calculator->calculate(
            $amount,
            $number
        );

        // prepare table header
        $header = [
            'Date',
            'Price for 1g',
            'Weight of gold (in g)',
            'Max profit with that price',
            'Sell Date',
            'Max price for 1g',
            'Profit with current price'
        ];

        // format numbers for display
        foreach($best as $index => $row){
            $best[$index]['max profit']      = $this->calculator->format($row['max profit']);
            $best[$index]['current profit']  = $this->calculator->format($row['current profit']);
        }
        
        $this->info("Best times to buy gold:");
        $this->table($header, $best);
    }
}
