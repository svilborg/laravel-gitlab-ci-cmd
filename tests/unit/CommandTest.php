<?php
namespace Tests\Unit;

use GitlabCi\ServiceProviders\GitlabCiServiceProvider;
use Illuminate\Support\Facades\Artisan;

class CommandTest extends \Orchestra\Testbench\TestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            GitlabCiServiceProvider::class
        ];
    }

    public function testSampleTest()
    {
        $this->mockConsoleOutput = false;

        // $cmd = $this->artisan('gitlab-ci', [
        // // '--pipeline' => '98575'
        // '--pipeline' => '98555'
        // ]);

        $cmd = $this->artisan('gitlab-ci', [
            // '--pipeline' => '98575'
            '--pipeline' => '98555'
        ]);

        $resultAsText = Artisan::output();
        echo $resultAsText;
    }
}