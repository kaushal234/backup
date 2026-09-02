# config valid only for current version of Capistrano
lock '3.4.0'

set :application, 'survey-app'
set :repo_url, 'git@webdevtool.tld-america.com:devteam/survey-app.git'
set :pty, true

set :yarn_flags, '--silent --no-progress'

after 'deploy:updated', 'tld_yarn:run_build'
