<?php
namespace App\Contracts;

/**
 * Interface DataSource
 *
 * Common data source interface
 *
 * @package App\Contracts
 */
interface DataSource {

    /**
     * @param $start
     * @param $end
     *
     * @return mixed
     */
    public function get($start, $end);

    /**
     * @return mixed
     */
    public function getData();
}
