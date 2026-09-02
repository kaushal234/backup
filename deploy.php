<?php
namespace Deployer;

require 'recipe/symfony.php';
require __DIR__.'/vendor/autoload.php';

set('application', 'alvest');
set('repository', 'git@web-gitlab-01.tld-america.com:devteam/alvest-web-portals.git');
set('git_tty', false);
set('ssh_multiplexing', true);
set('allow_anonymous_stats', false);
set('composer_options', '{{composer_action}} --verbose --no-progress --no-interaction --no-dev --optimize-autoloader');

const USER = 'deployer';
const GROUP = 'php_executors';
const PHP_VERSION = '8.3';

$hosts = [
    'web-portals-01.tld-america.com' => 'production',
    'web-portals-02.tld-america.com' => 'production',
    'web-stag-portals-01.tld-america.com' => 'staging',
];

foreach ($hosts as $hostname => $stage) {
    host($hostname)
        ->set('labels', ['stage' => $stage])
        ->set('remote_user', USER)
        ->set('keep_releases', 3)
        ->set('deploy_path', '/var/www/alvest-web-portals')
        ->set('http_user', USER)
        ->set('http_group', GROUP)
        ->set('shared_files', [
            'shared/inc/config.credentials.inc.php',
            'shopfloor/.env',
            'intranet/.env',
            'dms/.env',
        ])
        ->set('shared_dirs', [
            'admin/var/logs',
            'intranet/uploads',
            'intranet/legacy/uploads',
            'intranet/var/log',
            'intranet/config/jwt',
            'shared/inc/templates_c',
            'shopfloor/templates_c',
            'shopfloor/var/log',
        ])
        ->set('writable_use_sudo', true)
        ->set('writable_mode', 'chmod')
        ->set('writable_dirs', [
            'admin/var/logs',
            'intranet/var/log',
            'intranet/var/cache',
            'intranet/legacy/templates_c',
            'shopfloor/templates_c',
            'shopfloor/var/log',
        ])
        ->set('bin/console', function () {
            return parse('{{release_path}}/intranet/bin/console');
        })
        ->set('vendors_tasks', [
            'cd {{release_path}}/admin && {{bin/composer}} {{composer_options}}',
            'cd {{release_path}}/dms && {{bin/composer}} {{composer_options}}',
            'cd {{release_path}}/intranet && {{bin/composer}} {{composer_options}}',
            'cd {{release_path}}/intranet && yarn install --frozen-lockfile --silent --no-progress',
            'cd {{release_path}}/shared/inc && {{bin/composer}} {{composer_options}}',
            'cd {{release_path}}/shopfloor && {{bin/composer}} {{composer_options}}',
            'cd {{release_path}}/shopfloor && yarn install --frozen-lockfile --silent --no-progress',
        ])
        ->set('build_tasks', [
            '{{bin/php}} {{bin/console}} ckeditor:install --no-interaction',
            '{{bin/php}} {{bin/console}} assets:install --no-interaction --symlink --relative public',
            'cd {{release_path}}/intranet && yarn encore production',
            'cd {{release_path}}/shopfloor && yarn run build',
        ])
        ->set('restart_tasks', [
            sprintf('sudo systemctl restart php%s-fpm', PHP_VERSION),
        ]);
}

$apiServers = [
    'web-api-01.tld-america.com' => 'production',
    'web-stag-api-01.tld-america.com' => 'staging',
];

foreach ($apiServers as $hostname => $stage) {
    host($hostname)
        ->set('labels', ['stage' => $stage])
        ->set('remote_user', USER)
        ->set('keep_releases', 3)
        ->set('deploy_path', '/var/www/alvest-web-portals')
        ->set('http_user', USER)
        ->set('http_group', GROUP)
        ->set('shared_files', [
            'api/.env',
        ])
        ->set('shared_dirs', [
            'api/var/log',
            'api/config/jwt',
            'api/uploads',
            'api/files',
        ])
        ->set('writable_use_sudo', true)
        ->set('writable_mode', 'chmod')
        ->set('writable_dirs', [
            'api/var/log',
            'api/var/cache',
        ])
        ->set('bin/console', function () {
            return parse('{{release_path}}/api/bin/console');
        })
        ->set('vendors_tasks', [
            'cd {{release_path}}/api && yarn install --frozen-lockfile --silent --no-progress',
            'mkdir -p {{release_path}}/api/public/build/css',
            'cd {{release_path}}/api && {{bin/composer}} {{composer_options}}',
        ])
        ->set('build_tasks', [
            'cd {{release_path}}/api && yarn encore production',
            '{{bin/php}} {{bin/console}} ion:wsdl -vv',
        ])
        ->set('after_tasks', [
            '{{bin/php}} -d memory_limit=1G {{bin/console}} api:openapi:export --no-interaction --spec-version=3 --output current/api/public/openapi.json',
            '{{deploy_path}}/releases/$(ls {{deploy_path}}/releases/|tail -n 2|head -n 1)/api/bin/console messenger:stop-workers',
            '{{bin/php}} {{bin/console}} doctrine:migrations:migrate --no-interaction',
        ])
        ->set('restart_tasks', [
            sprintf('sudo systemctl restart php%s-fpm', '8.4'),
        ])
    ;
}

$evendorsServers = [
    'web-stag-evendors-01.tld-america.com' => 'staging',
    'web-evendors-01.tld-america.com' => 'production',
];

foreach ($evendorsServers as $hostname => $stage) {
    host($hostname)
        ->set('labels', ['stage' => $stage])
        ->set('remote_user', 'deployer')
        ->set('keep_releases', 3)
        ->set('deploy_path', '/var/www/alvest-web-portals')
        ->set('http_user', 'deployer')
        ->set('http_group', 'php_executor')
        ->set('shared_files', [
            'evendors/.env'
        ])
        ->set('shared_dirs', [
            'evendors/var/log',
        ])
        ->set('writable_use_sudo', true)
        ->set('writable_mode', 'chmod')
        ->set('writable_dirs', [
            'evendors/var/log',
            'evendors/var/cache',
        ])
        ->set('bin/console', function () {
            return parse('{{release_path}}/evendors/bin/console');
        })
        ->set('vendors_tasks', [
            'cd {{release_path}}/evendors && {{bin/composer}} {{composer_options}}',
        ])
        ->set('build_tasks', [
            '{{bin/php}} {{bin/console}} asset-map:compile',
        ])
        ->set('after_tasks', [])
        ->set('restart_tasks', [
            sprintf('sudo systemctl restart php%s-fpm', '8.4'),
        ])
    ;
}

$extranetServers = [
    'web-stag-extranet-01.tld-america.com' => 'staging',
    'web-extranet-01.tld-america.com' => 'production',
];

foreach ($extranetServers as $hostname => $stage) {
    host($hostname)
        ->set('labels', ['stage' => $stage])
        ->set('remote_user', 'deployer')
        ->set('keep_releases', 3)
        ->set('deploy_path', '/var/www/alvest-web-portals')
        ->set('http_user', 'deployer')
        ->set('http_group', 'php_executor')
        ->set('shared_files', [
            'extranet-new/.env'
        ])
        ->set('shared_dirs', [
            'extranet-new/var/log',
        ])
        ->set('writable_use_sudo', true)
        ->set('writable_mode', 'chmod')
        ->set('writable_dirs', [
            'extranet-new/var/log',
            'extranet-new/var/cache',
        ])
        ->set('bin/console', function () {
            return parse('{{release_path}}/extranet-new/bin/console');
        })
        ->set('vendors_tasks', [
            'cd {{release_path}}/extranet-new && {{bin/composer}} {{composer_options}}',
        ])
        ->set('build_tasks', [
            '{{bin/php}} {{bin/console}} asset-map:compile',
        ])
        ->set('after_tasks', [])
        ->set('restart_tasks', [
            sprintf('sudo systemctl restart php%s-fpm', '8.4'),
        ])
    ;
}

$powerbiServers = [
    'web-stag-powerbi-01.tld-america.com' => 'staging',
    'web-powerbi-01.tld-america.com' => 'production',
];

foreach ($powerbiServers as $hostname => $stage) {
    host($hostname)
        ->set('labels', ['stage' => $stage])
        ->set('remote_user', 'deployer')
        ->set('keep_releases', 3)
        ->set('deploy_path', '/var/www/alvest-web-portals')
        ->set('http_user', 'deployer')
        ->set('http_group', 'php_executor')
        ->set('shared_files', [
            'powerbi/.env'
        ])
        ->set('vendors_tasks', [
            'cd {{release_path}}/powerbi && npm install',
            'cd {{release_path}}/powerbi && npm run build',
        ])
        ->set('writable_use_sudo', true)
        ->set('writable_mode', 'chmod')
        ->set('cache_clear', [])
        ->set('cache_warmup', [])
        ->set('restart_tasks', [
            'cd {{release_path}}/powerbi && pm2 delete all; pm2 start pm2.config.cjs',
        ]);
}

$mobileServers = [
    'web-stag-mobile-01.tld-america.com' => 'staging',
    'web-mobile-01.tld-america.com' => 'production',
];

foreach ($mobileServers as $hostname => $stage) {
    host($hostname)
        ->set('labels', ['stage' => $stage])
        ->set('remote_user', 'deployer')
        ->set('keep_releases', 3)
        ->set('deploy_path', '/var/www/alvest-web-portals')
        ->set('http_user', 'deployer')
        ->set('http_group', 'php_executor')
        ->set('shared_files', [
            'mobile/.env'
        ])
        ->set('vendors_tasks', [
            'cd {{release_path}}/mobile && yarn install --frozen-lockfile --silent --no-progress',
        ])
        ->set('build_tasks', [
            'cd {{release_path}}/mobile && yarn build',
        ]);
}

task('deploy:cache:clear', function () {
    foreach (get('cache_clear', []) as $task) {
        run($task);
    }
});

task('deploy:cache:warmup', function () {
    foreach (get('cache_warmup', []) as $task) {
        run($task);
    }
});

task('deploy:vendors', function () {
    foreach (get('vendors_tasks', []) as $task) {
        run($task);
    }
});

task('deploy:build', function () {
    foreach (get('build_tasks', []) as $task) {
        run($task, ['timeout' => 600]);
    }
});

task('deploy:restart', function () {
    foreach (get('restart_tasks', []) as $task) {
        run($task);
    }
});

task('deploy:after', function () {
    foreach (get('after_tasks', []) as $task) {
        run($task);
    }
});

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:build',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'deploy:symlink',
    'deploy:after',
    'deploy:restart',
    'deploy:unlock',
    'deploy:cleanup',
]);

after('deploy:failed', 'deploy:unlock');
after('deploy', 'deploy:success');
