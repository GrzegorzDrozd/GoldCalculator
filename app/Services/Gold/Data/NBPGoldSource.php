<?php
namespace App\Services\Gold;

use App\Contracts\DataSource;
use Illuminate\Support\Facades\Log;

/**
 * Class NBPSource
 *
 * Polish National Bank data source
 *
 * @package App\Services\Gold
 */
class NBPGoldSource implements DataSource {

    /**
     * @var string
     */
    protected $url = 'http://api.nbp.pl/api/cenyzlota/%s/%s/?format=json';

    /**
     * @var string
     */
    protected $outputDirectory = 'storage/app/prices/gold';

    /**
     * @param $start
     * @param $end
     *
     * @return mixed
     * @throws \Exception
     */
    public function get($start, $end) {
        // make sure that output directory is ok.
        $this->checkDirectory();

        // parse arguments
        try {
            $startDate  = new \DateTime($start);
            $endDate    = new \DateTime($end);
        } catch(\Exception $e) {
            Log::error('Unable to parse date range', ['message' =>$e->getMessage()]);
            throw new \InvalidArgumentException('Unable to parse date range', 1, $e);
        }

        // NBP api has gold prices since: Jan 2 2013 r.
        if ($startDate < new \DateTime('2013-01-02')) {
            Log::error('This provider works only for dates between 2013-01-02 and now');
            throw new \RangeException("This provider works only for dates between 2013-01-02 and now", 2);
        }

        // calculate difference between dates
        $dateDifference = $startDate->diff($endDate);

        // calculate number of request that we have to make
        // max number of days in one request is 93
        $requests = ceil($dateDifference->days/93);

        // prepare data for request
        $requestStartDate   = clone $startDate;
        $requestEndDate     = clone $startDate;

        $now                = new \DateTime();

        // add 93 to end date so that we have first span
        $requestEndDate->add(new \DateInterval('P93D'));
        $dataSet = [];

        // loop all requests
        for($i = 0; $i < $requests; $i++) {

            // we cannot provide dates in to the future, use today as max
            if ($requestEndDate > $now) {
                $requestEndDate = $now;
            }

            // get data
            $data = $this->makeRequest($requestStartDate, $requestEndDate);

            // append data to prev set
            $dataSet = array_merge($dataSet, $data);

            // add intervals
            $requestStartDate->add(new \DateInterval('P93D'));
            $requestEndDate->add(new \DateInterval('P93D'));
        }

        // store data for further analyse
        $path = base_path($this->outputDirectory).DIRECTORY_SEPARATOR.'%s.csv';

        foreach($dataSet as $date => $row) {
            // file name is : output path + / + year from date.
            $file = sprintf($path, substr($date, 0, 4));

            if (file_put_contents($file, 'NBP;'.$date.';'.$row."\n", FILE_APPEND) === false) {
                throw new \RuntimeException("Unable to save data in local storage");
            }
        }
    }

    /**
     * Get data from local files
     *
     * @return array
     */
    public function getData() {
        $path = base_path($this->outputDirectory);

        // search all csv files in output dir
        $files = glob($path.DIRECTORY_SEPARATOR.'*.csv');

        $return = [];

        // iterate over all files
        foreach ($files as $file) {
            $fp = fopen($file, 'rb');
            // read csv data
            while (($data = fgetcsv($fp, 1000, ";")) !== FALSE) {
                $data[2] = (float)$data[2];
                $return[] = $data;
            }
        }

        return $return;
    }

    /**
     * Make request to remote api and get data from it
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return array
     */
    protected function makeRequest(\DateTime $startDate, \DateTime $endDate) {

        // get data from remote source
        $result = file_get_contents(sprintf(
            $this->url,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ));

        // check results.
        return $this->validateResponse($result);
    }

    /**
     * Check if directory is there, clean it or create it if not.
     *
     * @return bool
     */
    protected function checkDirectory() {
        $path = base_path($this->outputDirectory);

        // make sure that directory exists
        if (is_dir($path) and !is_writable($path)) {
            Log::error('Unable to write into local cache directory', ['value'=>$path]);
            throw new \RuntimeException("Unable to write into local cache directory");
        }

        // clean up old data
        if (is_dir($path) and is_writable($path)) {
            // remove old data
            array_map('unlink', glob($path.DIRECTORY_SEPARATOR.'*.csv'));

            return true;
        }


        if (!mkdir($path, 0777, true) && !is_dir($path)) {
            throw new \RuntimeException(
                sprintf('Directory "%s" was not created', $path)
            );
        }


        return true;
    }

    /**
     * Handle response from remote source
     *
     * @param $result
     *
     * @return array
     */
    public function validateResponse($result) {
        // check result
        if (empty($result)) {
            throw new \RuntimeException('Unable to fetch date from NBP');
        }

        // decode response
        $array = json_decode($result, true);
        if (empty($array) AND json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Unable to parse date from NBP with message: ' . json_last_error_msg());
        }

        // we need only price keyed by date
        $array = array_column($array, 'cena', 'data');

        // check provided data
        foreach ($array as $date => $value) {
            // check returned price
            if (empty($value) OR !is_numeric($value)) {
                throw new \RuntimeException('Invalid data: ' . $value);
            }

            // make sure that key is a valid date.
            $dateComponents = explode('-', $date);
            if (
                count($dateComponents) !== 3 OR
                checkdate(
                    (int) $dateComponents[1], // m
                    (int) $dateComponents[2], // d
                    (int) $dateComponents[0]  // Y
                ) === false) {
                throw new \RuntimeException('Invalid date: ' . $date);
            }
        }

        return $array;
    }
}
