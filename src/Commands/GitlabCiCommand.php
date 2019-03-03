<?php
namespace GitlabCi\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;
use Symfony\Component\Console\Input\InputOption;
use Gitlab\Client;

/**
 * Gitlab CI
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
     * @var Config
     */
    protected $config;

    /**
     *
     * @var Client
     */
    private $client;

    /**
     *
     * @var integer
     */
    private $projectId;

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @return void
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        parent::__construct();
    }

    /**
     * Intialize client
     *
     * @throws \Exception
     */
    public function init()
    {
        if (! $this->config->get('gitlab_ci')) {
            throw new \Exception('Missing configuration file gitlab_ci');
        }

        $url = $this->config->get('gitlab_ci')['url'];
        $token = $this->config->get('gitlab_ci')['token'];

        $this->client = Client::create($url)->authenticate($token, Client::AUTH_URL_TOKEN);
        $this->projectId = $this->config->get('gitlab_ci')['project_id'];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->init();

        if ($this->option('pipeline')) {
            $pipelineId = (int) $this->option('pipeline');
            $retryFailed = ($this->option('retry') !== false) ? true : false;

            if ($this->option('stop') !== false) {
                $this->stopPipeline($pipelineId);
            } else {
                $this->listPipelineJobs($pipelineId, $retryFailed);
            }
        } elseif ($this->option('job')) {

            if ($this->option('artifacts') !== false) {
                $this->getJobArtifacts($this->option('job'));
            } elseif ($this->option('retry') !== false) {
                $this->retryJob($this->option('job'));
            } elseif ($this->option('trace') !== false) {
                $this->getJobTrace($this->option('job'));
            } else {
                $this->getJob($this->option('job'));
            }
        } else {
            $this->listPipelines($this->option('limit'));
        }

        $this->line('');
    }

    /**
     *
     * List recient Pipelines
     *
     * @param int $perPage
     */
    protected function listPipelines(int $perPage = 10)
    {
        $params = [
            'per_page' => $perPage
        ];

        if ($this->option('branch')) {
            $params['ref'] = $this->option('branch');
        }

        if ($this->option('current-branch')) {
            $params['ref'] = $this->getCurrentBranch();
        }

        $pipelines = $this->client->api('projects')->pipelines($this->projectId, $params);

        $this->title('Pipelines ' . count($pipelines));

        foreach ($pipelines as $pipeline) {
            $status = $pipeline['status'];
            $sha = substr($pipeline['sha'], 0, 7);

            $verboseInfo = '';
            if ($this->output->isVerbose()) {
                $commit = $this->getCommit($pipeline['sha']);

                if ($commit) {
                    $verboseInfo = " \n ";
                    $verboseInfo .= $commit['author_name'] . ' [' . $commit['title'] . ']';
                    $verboseInfo .= " \n ";
                }
            }

            $info = $pipeline['id'] . ' ' . $pipeline['status'] . ' [' . $pipeline['ref'] . '] (' . $sha . ') ' . $verboseInfo;

            $this->output($status, $info);
        }
    }

    /**
     * Get Commit Info
     *
     * @param string $sha
     * @return array
     */
    protected function getCommit(string $sha)
    {
        return $this->client->api('repositories')->commit($this->projectId, $sha);
    }

    /**
     * Get Pipline's Jobs
     *
     * @param string $pipelineId
     * @param bool $retryFailed
     */
    protected function listPipelineJobs(int $pipelineId, bool $retryFailed = false)
    {
        $params = [
            'per_page' => 100
        ];

        if ($retryFailed) {
            $jobs = $this->client->api('jobs')->pipelineJobs($this->projectId, $pipelineId, $params);

            $jobsLastStatus = $this->jobsLastStatus($jobs);

            foreach ($jobsLastStatus as $job) {
                if ($job['status'] == 'failed') {
                    $this->retryJob($job['id']);
                }
            }
        }

        $jobs = $this->client->api('jobs')->pipelineJobs($this->projectId, $pipelineId, $params);

        $this->title('Pipeline #' . $pipelineId . ' Jobs');

        foreach ($jobs as $job) {
            $status = $job['status'];

            $info = $job['id'] . ' ' . $job['status'] . ' [' . $job['stage'] . '] ' . $job['name'];

            $this->output($status, $info);
        }
    }

    /**
     * Stop Pipeline
     *
     * @param int $pipelineId
     */
    protected function stopPipeline(int $pipelineId)
    {
        $this->client->api('projects')->cancelPipeline($this->projectId, $pipelineId, []);

        $this->warn('Pipeline #' . $pipelineId . ' Stopped');
    }

    /**
     * Get a Job
     *
     * @param integer $jobId
     */
    protected function getJob($jobId)
    {
        $job = $this->client->api('jobs')->show($this->projectId, $jobId, []);

        $duration = $job['duration'] ?? '';
        $status = $job['status'];

        $info = $job['id'] . ' ' . $job['status'] . ' [' . $job['stage'] . '] ' . $job['name'] . ' (' . $duration . ')';

        $this->title('Job #' . $jobId);
        $this->output($status, $info);
        $this->line("    " . $job['web_url']);
    }

    /**
     * Get Job's Trace
     *
     * @param integer $jobId
     */
    protected function getJobTrace(int $jobId)
    {
        $trace = $this->client->api('jobs')->trace($this->projectId, $jobId, []);

        $this->title('Job #' . $jobId . ' Trace');
        $this->info($trace);
    }

    /**
     * Get Job's Artifacts
     *
     * @param integer $jobId
     */
    protected function getJobArtifacts(int $jobId)
    {
        $artifacts = $this->client->api('jobs')->artifacts($this->projectId, $jobId, []);

        $this->title('Job #' . $jobId . ' Artifacts');

        $this->downloadJobArtifact($jobId, $artifacts);
    }

    /**
     * Dowload and unzip Artifacts
     *
     * @param integer $jobId
     * @param \GuzzleHttp\Psr7\Stream $artifacts
     */
    private function downloadJobArtifact(int $jobId, \GuzzleHttp\Psr7\Stream $artifacts)
    {
        $buffer = '';
        while (! $artifacts->eof()) {
            $buf = $artifacts->read(1048576);
            // Using a loose equality here to match on '' and false.
            if ($buf == null) {
                break;
            }
            $buffer .= $buf;
        }

        $path = '/tmp/';

        $archive = $path . 'job_' . $jobId . '.zip';

        file_put_contents($archive, $buffer);

        $this->info('Downloaded to ' . $archive);

        $unzippedPath = $path . 'job_' . $jobId;

        $zip = new \ZipArchive();
        $res = $zip->open($archive);

        if ($res === TRUE) {
            // extract it to the path we determined above
            $zip->extractTo($unzippedPath);
            $zip->close();

            $this->info('Extracted to ' . $unzippedPath);
        }
    }

    /**
     * Retry a Job
     *
     * @param integer $jobId
     */
    protected function retryJob($jobId)
    {
        $this->client->api('jobs')->retry($this->projectId, $jobId, []);

        $this->warn('Job # ' . $jobId . ' has been retried.');
    }

    /**
     * Title
     *
     * @param string $status
     * @param string $info
     */
    private function title($title)
    {
        $this->line('');
        $this->line($title);
        $this->line('');
    }

    /**
     * Output based on pipline/job status
     *
     * @param string $status
     * @param string $info
     */
    private function output($status, $info)
    {
        if ($status == 'success') {
            $this->info('✔ ' . $info);
        } elseif ($status == 'running' || $status == 'pending') {
            $this->warn('⏵ ' . $info);
        } elseif ($status == 'canceled') {
            $this->warn('⏹ ' . $info);
        } elseif ($status == 'manual') {
            $this->line('⚙ ' . $info);
        } elseif ($status == 'skipped' || $status == 'created' || $status == 'pending') {
            $this->line('⏸ ' . $info);
        } else {
            $this->error('✖ ' . $info);
        }
    }

    /**
     * Get current branch
     *
     * @throws \Exception
     * @return string
     */
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
     *
     * @param array $jobs
     */
    private function jobsLastStatus($jobs)
    {
        $jobsLastStatus = [];
        foreach ($jobs as $job) {
            $jobsLastStatus[$job['name']] = [
                'id' => $job['id'],
                'status' => $job['status']
            ];
        }

        return $jobsLastStatus;
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
                InputOption::VALUE_NONE,
                'Show pipelines for the current git branch'
            ],
            [
                'pipeline',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Show pipeline\'s jobs. Pipeline id.',
                false
            ],
            [
                'job',
                'j',
                InputOption::VALUE_OPTIONAL,
                'Show job.',
                false
            ],
            [
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Per page limit for pipelines.',
                15
            ],
            [
                'trace',
                't',
                InputOption::VALUE_NONE,
                'Show job trace.'
            ],
            [
                'artifacts',
                'a',
                InputOption::VALUE_NONE,
                'Show job\'s artifacts.'
            ],
            [
                'retry',
                'r',
                InputOption::VALUE_NONE,
                'Retry a job.'
            ],
            [
                'stop',
                's',
                InputOption::VALUE_NONE,
                'Stop/Cancel a pipeline.'
            ]
        ];
    }
}