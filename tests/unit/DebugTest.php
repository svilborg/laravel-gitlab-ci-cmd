<?php
namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use GitlabCi\ServiceProviders\GitlabCiServiceProvider;

class DebugTest extends \Orchestra\Testbench\TestCase
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

    public function testDebug()
    {
        $this->mockConsoleOutput = false;

        $cmd = $this->artisan('gitlab-ci', [ // '--pipeline' => '98575'
                                              // '--pipeline' => '98555'
                                              // '--pipeline' => '98340',
                                              // '--job' => '755369'
                                              // '-r'=>'',
                                              // '-t'=>'',
                                              // '-a'=>'x',
                                              // '--help'=>'',
        ]);

        $resultAsText = Artisan::output();
        echo "\n\n";
        echo $resultAsText;

        $this->assertTrue(true);
    }
}