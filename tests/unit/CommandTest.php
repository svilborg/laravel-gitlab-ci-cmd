<?php
namespace Tests\Unit;

use Gitlab\Client;
use GitlabCi\ServiceProviders\GitlabCiServiceProvider;

class ClassConstantStub
{

    const AUTH_URL_TOKEN = 'url_token';
}

class CommandTest extends \Orchestra\Testbench\TestCase
{

    private $mock;

    public function setUp()
    {
        parent::setUp();

        $mock = \Mockery::namedMock(Client::class, ClassConstantStub::class);
        $mock->shouldReceive('create')->andReturnSelf();
        $mock->shouldReceive('authenticate')->andReturnSelf();

        $this->mock = $mock;
    }

    protected function getPackageProviders($app)
    {
        return [
            GitlabCiServiceProvider::class
        ];
    }

    public function testPipelines()
    {
        $this->mock->shouldReceive('api')->andReturnSelf();
        $this->mock->shouldReceive('pipelines')->andReturn([
            [
                'id' => 1,
                'name' => 'test',
                'status' => 'success',
                'ref' => 'master'
            ]
        ]);

        $cmd = $this->artisan('gitlab-ci', []);

        $cmd->assertExitCode(0);
        $cmd->expectsOutput('1 success [master]');
    }
}