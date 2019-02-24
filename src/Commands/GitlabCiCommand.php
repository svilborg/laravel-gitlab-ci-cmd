<?php
namespace GitlabCi\Commands;

use InvalidArgumentException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;
use Symfony\Component\Console\Input\InputOption;
use Gitlab\Client;

/**
 */
class GitlabCiCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'gitlab-ci';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gitlab CI Intregration.';

    /**
     * Configuration repository.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     *
     * @var Client
     */
    private $client;

    private $projectId;

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @return void
     */
    public function __construct(Config $config)
    {
        // Set configuration repository.
        $this->config = $config;

        $url = $this->config->get('gitlab-ci')['url'];
        $token = $this->config->get('gitlab-ci')['token'];

        $this->client = Client::create($url)->authenticate($token, Client::AUTH_URL_TOKEN);
        $this->projectId = $this->config->get('gitlab-ci')['project_id'];

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->getOutput()->setDecorated(true);

        if ($this->option('pipeline')) {
            $this->listPipelineJobs();
        } else {
            $this->listPipelines();
        }
    }

    protected function listPipelines()
    {
        $params = [];

        if ($this->option('branch')) {
            $params = [
                'ref' => $this->option('branch')
            ];
        }

        if ($this->option('current-branch')) {
            $params = [
                'ref' => $this->option('current-branch')
            ];
        }

        $pipelines = $this->client->api('projects')->pipelines($this->projectId, $params);

        foreach ($pipelines as $pipeline) {
            $status = $pipeline['status'];
            $info = $pipeline['id'] . ' ' . $pipeline['status'] . ' [' . $pipeline['ref'] . ']';

            $this->output($status, $info);
        }
    }

    protected function listPipelineJobs()
    {
        $params = [
            'per_page' => 100
        ];

        $pipelineId = $this->option('pipeline');

        $pipelines = $this->client->api('jobs')->pipelineJobs($this->projectId, $pipelineId, $params);

        foreach ($pipelines as $pipeline) {
            // $duration = $pipeline['duration'] ?? '';
            $status = $pipeline['status'];

            $info = $pipeline['id'] . ' ' . $pipeline['status'] . ' [' . $pipeline['stage'] . '] ' . $pipeline['name'];
            // ' (' . $duration . ')'

            $this->output($status, $info);
        }
    }

    private function output($status, $info)
    {
        if ($status == 'success') {
            $this->info($info);
        } elseif ($status == 'running') {
            $this->warn($info);
        } elseif ($status == 'manual' || $status == 'skipped' || $status == 'created') {
            $this->line($info);
        } else {
            $this->error($info);
        }
    }

    private function getCurrentBranch()
    {
        if (! is_file('.git/HEAD')) {
            throw new \Exception('No git found. Use --branch option');
        }

        $head = file('.git/HEAD', FILE_USE_INCLUDE_PATH);

        $parts = explode("/", $head[0], 3);

        return trim($parts[2]);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'branch',
                'b',
                InputOption::VALUE_OPTIONAL,
                'Show pipelines for specific Branch/Ref',
                false
            ],
            [
                'current-branch',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Show pipelines for specific the current git branch',
                false
            ],
            [
                'pipeline',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Show pipeline. Pipeline id.',
                false
            ]
        ];
    }
}