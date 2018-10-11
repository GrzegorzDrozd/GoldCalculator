<?php

namespace App\Console\Commands;

use App\Contracts\DataSource;
use Illuminate\Console\Command;

/**
 * Class FetchData
 *
 * Fetch data from the provider
 *
 * @package App\Console\Commands
 */
class FetchData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gold:fetch {--from=2013-01-02 : Format Y-m-d} {--to=now : Format Y-m-d or now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch historic gold price data';

    /**
     * @var DataSource
     */
    protected $source;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DataSource $source)
    {
        parent::__construct();
        $this->source = $source;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->source->get(
                $this->option('from'),
                $this->option('to')
            );
        } catch(\Exception $e) {
            $this->error("Unable to fetch data into local cache", ['message'=>$e->getMessage()]);
        }
    }
}
