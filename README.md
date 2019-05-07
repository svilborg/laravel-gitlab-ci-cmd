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
      -x, --stats                Statistics
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

Statistics

    $ artisan gitlab-ci -x

    +-----------+------+---------+------------+------------+
    | Status    | Jobs | Jobs %  | Duration   | Duration % |
    +-----------+------+---------+------------+------------+
    | ✔ success | 517  | 76.59 % | 14 hours   | 95.84 %    |
    | ⏵ running | 12   | 1.78 %  | 23 minutes | 2.53 %     |
    | ⏸ created | 28   | 4.15 %  | 1 second   | 0 %        |
    | ⚙ manual  | 77   | 11.41 % | 1 second   | 0 %        |
    | ✖ failed  | 6    | 0.89 %  | 15 minutes | 1.63 %     |
    | ⏸ skipped | 35   | 5.19 %  | 1 second   | 0 %        |
    +-----------+------+---------+------------+------------+
    |  ∑        | 675  |         | 15 hours   |            |
    +-----------+------+---------+------------+------------+


    +---------------+------+---------+------------+------------+
    | Runner        | Jobs | Jobs %  | Duration   | Duration % |
    +---------------+------+---------+------------+------------+
    | s01.ci.server | 12   | 20.74 % | 1 second   | 0 %        |
    | s02.ci.server | 37   | 5.48 %  | 40 minutes | 4.35 %     |
    | s03.ci.server | 43   | 6.37 %  | 1 hour     | 7.9 %      |
    | s04.ci.server | 27   | 4 %     | 25 minutes | 2.77 %     |
    | s08.ci.server | 16   | 2.37 %  | 52 minutes | 5.66 %     |
    | s09.ci.server | 35   | 5.19 %  | 1 hour     | 6.75 %     |
    | s10.ci.server | 32   | 4.74 %  | 1 hour     | 6.58 %     |
    +---------------+------+---------+------------+------------+
    |  ∑            | 675  |         | 15 hours   |            |
    +---------------+------+---------+------------+------------+

    +------------------+------+---------+-----------+------------+
    | Stage            | Jobs | Jobs %  | Duration  | Duration % |
    +------------------+------+---------+-----------+------------+
    | build            | 15   | 2.22 %  | 1 hour    | 8.44 %     |
    | inspection       | 15   | 2.22 %  | 8 minutes | 0.95 %     |
    | unit_tests       | 330  | 48.89 % | 6 hours   | 42.82 %    |
    | acceptance_tests | 93   | 13.78 % | 5 hours   | 36.57 %    |
    | api_tests        | 117  | 17.33 % | 1 hour    | 11.22 %    |
    | code_coverage    | 15   | 2.22 %  | 1 second  | 0 %        |
    | deploy           | 90   | 13.33 % | 1 second  | 0 %        |
    +------------------+------+---------+-----------+------------+
    |  ∑               | 675  |         | 15 hours  |            |
    +------------------+------+---------+-----------+------------+

