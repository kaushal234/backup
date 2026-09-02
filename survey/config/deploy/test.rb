set :stage, :test
set :log_level, :info
set :branch, :dev

role :app, %w{deploy@webtest.tld-america.com}
role :web, %w{deploy@webtest.tld-america.com}
