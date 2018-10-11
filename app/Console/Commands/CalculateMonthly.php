<?php
namespace App\Console\Commands;

use App\Services\Gold\Calculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class CalculateInvestment
 *
 * Calculate how much money one could profit or lose buying gold for specific amount of money since specific date on specific day of the month
 *
 * @package App\Console\Commands
 */
class CalculateMonthly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gold:monthly 
    {--s|since=2015-01-01 : Date since calculation is made. Y-m-d format} 
    {--d|day_of_month=10 : Day of the month when gold is bought} 
    {--a|amount=1000 : Amount to invest}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate total profit for amount of gold bought on day of the month since specific date';

    /**
     * @var int
     */
    private $dayOfMonth;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var \DateTime
     */
    private $since;

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
        $this->setDayOfMonth($this->option('day_of_month'));
        $this->setAmount($this->option('amount'));
        $this->setSince($this->option('since'));

        // get all data
        $data = $this->calculator->getData();
        // get last row
        $currentRow   = $data[count($data)-1];

        // calculate amount of gold and total money invested
        list($weight, $investedSum) = $this->calculateForDay($data);

        $currentValue = $weight * $currentRow[2];
        $this->info('Total amount invested: '.$this->calculator->format($investedSum));
        $this->info('Total gold weight: '.$weight);
        $this->info('Total value with current gold price: '. $this->calculator->format($currentValue));
        $this->info('Earnings/loss: '.$this->calculator->format($currentValue-$investedSum));
    }

    /**
     * @param $since
     * @param $dayOfMonth
     *
     * @return bool
     */
    public function filter($current) {

        // make sure that date is in correct format
        try {
            $recordDate = \DateTime::createFromFormat('Y-m-d', $current[1]);
        } catch (\Exception $e) {
            Log::error('Invalid date', ['message' =>$e->getMessage()]);
            throw new \RuntimeException('Invalid date');
        }

        if (empty($recordDate)) {
            return false;
        }

        // filter all records before specific date
        if ($recordDate < $this->getSince()) {
            return false;
        }

        // select specific day of the month
        if ((int)$recordDate->format('d') != $this->getDayOfMonth()) {
            return false;
        }

        return true;
    }

    /**
     * @param $data
     *
     * @return array
     */
    protected function calculateForDay($data) {
        $filter = new \CallbackFilterIterator(new \ArrayIterator($data), array($this, 'filter'));

        $weight      = 0;
        $investedSum = 0;
        foreach ($filter as $row) {
            // row[2] contains price for 1g on that day. For specific amount we can buy specific weight of gold.
            $weight      += $this->getAmount() / $row[2];
            $investedSum += $this->getAmount();
        }

        return array($weight, $investedSum);
    }

    /**
     * @return int
     */
    public function getDayOfMonth() {
        return $this->dayOfMonth;
    }

    /**
     * @param int $dayOfMonth
     */
    public function setDayOfMonth($dayOfMonth) {
        if (!is_numeric($dayOfMonth) OR $dayOfMonth > 31 OR $dayOfMonth < 0) {
            Log::error('You need to use positive integer number for day of the month. And less than 31.', ['value' => $dayOfMonth]);
            throw new \InvalidArgumentException('You need to use positive integer number for day of the month. And less than 31.');
        }
        $this->dayOfMonth = $dayOfMonth;
    }

    /**
     * @return int
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount) {
        if (!is_numeric($amount) OR $amount < 0) {
            Log::error('You need to use positive integer for amount', ['value' => $amount]);
            throw new \InvalidArgumentException('You need to use positive integer for amount');
        }
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getSince() {
        return $this->since;
    }

    /**
     * @param string $since
     */
    public function setSince($since) {
        try {
            $date = \DateTime::createFromFormat('Y-m-d', $since);
        } catch (\Exception $e) {
            Log::error('You need to use Y-m-d date format', ['value' => $since]);
            throw new \InvalidArgumentException('You need to use Y-m-d date format');
        }
        $this->since = $date;
    }
}
