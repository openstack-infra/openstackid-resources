<?php
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

class TestCase extends Illuminate\Foundation\Testing\TestCase {

    //services
    private $redis = null;
	/**
	 * Creates the application.
	 *
	 * @return \Illuminate\Foundation\Application
	 */
	public function createApplication()
	{
        //putenv('DB_DEFAULT=sqlite_testing');

		$app = require __DIR__.'/../bootstrap/app.php';

		$instance = $app->make('Illuminate\Contracts\Console\Kernel');
        $app->loadEnvironmentFrom('.env.testing');
        $instance->bootstrap();
    	return $app;
	}

    public function setUp()
    {
        parent::setUp();
        $this->redis  = Redis::connection();
        $this->redis->flushall();
        $this->prepareForTests();
    }

    protected function prepareForTests()
    {
        Artisan::call('migrate');
        Mail::pretend(true);
        $this->seed('TestSeeder');
    }

    public function tearDown()
    {

        parent::tearDown();
    }
}
