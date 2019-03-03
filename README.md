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
      -l, --limit[=LIMIT]        Per page limit for pipelines. [default: 15]
      -t, --trace                Show job trace.
      -a, --artifacts            Show job's artifacts.
      -r, --retry                Retry a job.
      -s, --stop                 Stop/Cancel a pipeline.
      -h, --help                 Display this help message

### Examples

Listing Pipelines

    $ php artisan gitlab-ci -c

    Pipelines

    ⏵ 690 running [master]
    ✔ 661 success [master]
    ✔ 640 success [master]
    ✖ 639 failed [master]
    ✔ 635 success [master]
    ✔ 631 success [master]

Pipeline's Jobs

    $ artisan gitlab-ci -p 701

    Pipeline #865 Jobs

    ✔ 799 success [build] Build
    ✔ 800 success [unit_tests] UnitTests
    ✖ 801 failed [acceptance_tests] AcceptanceTests
    ⏹ 803 canceled [functional_tests] FunctionalTests
    ⚙ 804 manual [code_coverage] CodeCoverage






