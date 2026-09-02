set :stage, :production
set :log_level, :info
set :branch, :master

role :app, %w{deploy@webprod.tld-america.com}
role :web, %w{deploy@webprod.tld-america.com}
