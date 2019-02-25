# Laravel GitlabCI Command

A command to list Gitlab CI Pipelines, Jobs, Traces, Artifacts.

### Configuration

Available in config/gitlab_ci.php

    url         - Gitlab Url
    token       - Gitlab User's Token
    project_id  - Project id

### Usage 

    Description:
      Gitlab CI Intregration.

    Usage:
      gitlab-ci [options]

    Options:
      -b, --branch[=BRANCH]      Show pipelines for specific Branch/Ref [default: false]
      -c, --current-branch       Show pipelines for the current git branch
      -p, --pipeline[=PIPELINE]  Show pipeline's jobs. Pipeline id. [default: false]
      -j, --job[=JOB]            Show job. [default: false]
      -t, --trace                Show job trace.
      -a, --artifacts            Show job's artifacts.
      -r, --retry                Retry a job.
      -h, --help                 Display this help message

