<?php
namespace App\Console\Commands;

/**
 * Class CalculateBestDay
 *
 * Calculate best day of the month to do gold investment.
 *
 * This algorithm is very naive and does not work for 31 day of the month and does not account for February.
 *
 * @package App\Console\Commands
 */
class CalculateBestDay extends CalculateMonthly
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gold:best_day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate best day of the month to buy gold';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = $this->calculator->getData();

        // set since as first date of available data
        $this->setSince($data[0][1]);

        // to calculate we need amount of money we want to use
        $this->setAmount(10000);

        $weightByDay = array_fill(1, 30, 0);

        for($i = 1; $i <= 30; $i++) {
            $this->setDayOfMonth($i);
            list($weight) = $this->calculateForDay($data);
            // sum all weight by day
            $weightByDay[$i] += $weight;
        }

        // this will give us value
        $minValue = min($weightByDay);
        $maxValue = max($weightByDay);

        // to get day (key) of $weightByDay we can use regular key search
        $this->info("Best day of the month to buy gold is: ".array_search($maxValue, $weightByDay));
        $this->info("Worst day of the month to buy gold is: ".array_search($minValue, $weightByDay));

        $this->info("Note: this algorithm is naive and does not include 31 day of the month. It also does not acount for Ferbuary.");
    }
}
