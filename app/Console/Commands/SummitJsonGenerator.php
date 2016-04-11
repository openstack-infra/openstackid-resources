<?php namespace App\Console\Commands;


use Illuminate\Console\Command;
use libs\utils\ICacheService;
use models\summit\ISummitRepository;
use services\model\ISummitService;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Config;

/**
 * Class SummitJsonGenerator
 * @package App\Console\Commands
 */
class SummitJsonGenerator extends Command {

	/**
	 * @var ISummitService
	 */
	private $service;

    /**
     * @var ISummitRepository
     */
    private $repository;

    /**
     * @var ICacheService
     */
    private $cache_service;

    public function __construct(
        ISummitRepository $repository,
        ISummitService $service,
        ICacheService $cache_service
    )
    {
        parent::__construct();
        $this->repository    = $repository;
        $this->service       = $service;
        $this->cache_service = $cache_service;
    }

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'summit:json:generator';


	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Regenerates Summit Initial Json';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
        $summit = $this->repository->getCurrent();
        if(is_null($this->repository)) return;
		$this->info(sprintf("processing summit id %s", $summit->ID));
        $start = time();
        $data  = $this->service->getSummitData($summit, $expand = 'locations,sponsors,summit_types,event_types,presentation_categories,schedule');
        if(is_null($data)) return;
        $end   = time();
        $delta = $end - $start;
        $this->info(sprintf("execution call %s seconds", $delta));
        $current_time = time();
        $key = '/api/v1/summits/current.expand=locations%2Csponsors%2Csummit_types%2Cevent_types%2Cpresentation_categories%2Cschedule';
        $cache_lifetime = intval(Config::get('server.response_cache_lifetime', 300));
        $this->cache_service->setSingleValue($key, json_encode($data), $cache_lifetime);
        $this->cache_service->setSingleValue($key.".generated", $current_time, $cache_lifetime);
        $this->info(sprintf("regenerated cache for summit id %s", $summit->ID));
	}

}
