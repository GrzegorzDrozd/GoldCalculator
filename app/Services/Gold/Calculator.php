<?php
namespace App\Services\Gold;

use App\Contracts\DataSource;

/**
 * Class Calculator
 *
 * Main calculator class for the project
 *
 * @package App\Services\Gold
 */
class Calculator {

    /**
     * @var DataSource
     */
    protected $source;

    /**
     * Calculator constructor.
     *
     * @param DataSource $source
     */
    public function __construct(\App\Contracts\DataSource $source) {
        $this->source = $source;
    }

    /**
     * Get best dates
     *
     * @param $amount
     * @param $number
     *
     * @return array
     */
    public function calculate($amount, $number){
        
        $data           = $this->getData();
        if (empty($data)) {
            throw new \RuntimeException("Local cache empty. Please run 'artisan gold:fetch' first.");
        }
        $bestTimes      = $this->getLowestValues($data, $number);
        $highestValue   = current($this->getHighestValues($data, 1));

        // current element will be last element of the array
        $currentValue   = $data[count($data)-1];

        $ret = [];
        foreach($bestTimes as $i => $bestTime) {
            $n = $amount/$bestTime[2];
            $ret[] = [
                'date'              => $bestTime[1],
                'price'             => $bestTime[2],
                'bought'            => $n,
                'max profit'        => ($highestValue[2]*$n),
                'max profit date'   => $highestValue[1],
                'max price for 1g'  => $highestValue[2],
                'current profit'    => ($currentValue[2]*$n),
            ];
        }
        return $ret;
    }

    /**
     * @param $amount
     *
     * @return string
     */
    public function format($amount) {
        return number_format($amount, 2, ',', ' ').' PLN';
    }

    /**
     * Get data sorted from lowest value first.
     *
     * @param $data
     * @param $n
     *
     * @return array
     */
    public function getLowestValues($data, $n = 1) {
        uasort ($data, function ($a, $b) {
            return $a[2] > $b[2];
        });
        return array_slice($data, 0, $n);
    }

    /**
     * Get data sorted from highest value first.
     * 
     * @param $data
     * @param $n
     *
     * @return array
     */
    public function getHighestValues($data, $n = 1) {
        uasort ($data, function ($a, $b) {
            return $a[2] < $b[2];
        });
        return array_slice($data, 0, $n);
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->source->getData();
    }
}
