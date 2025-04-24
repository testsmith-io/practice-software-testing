<?php

namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'my_project');
set('writable_mode', 'chmod');
set('keep_releases', 5);

set('writable_dirs', [
    'bootstrap/cache',
    'storage',
    'storage/',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
]);

set('log_files', 'storage/logs/*.log');

// Project repository
set('repository', 'git@github.com:testsmith-io/practice-software-testing.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
set('shared_dirs', []);
set('shared_files', []);

// Writable dirs by web server
set('writable_dirs', []);

// Hosts
host('production')
    ->setHostname('142.93.137.65')
    ->set('remote_user', 'root')
    ->setForwardAgent(true)
    ->set('stage', 'production')
    ->set('deploy_path', '/var/www/api.practicesoftwaretesting.com');

// Tasks for each sprint version
task('deploy:sprint1', function () {
    deploy('sprint1', 'v1.', '-v1');
});

task('deploy:sprint2', function () {
    deploy('sprint2', 'v2.', '-v2');
});

task('deploy:sprint3', function () {
    deploy('sprint3', 'v3.', '-v3');
});

task('deploy:sprint4', function () {
    deploy('sprint4', 'v4.', '-v4');
});

task('deploy:sprint5', function () {
    deploy('sprint5', '', '');
});

task('deploy:sprint5-with-bugs', function () {
    deploy('sprint5-with-bugs', 'with-bugs.', '-with-bugs');
});

// Function to handle deployment logic
function deploy($source, $webDestination, $apiDestination) {
    // API deployment
    run("cd /var/www/ && mkdir -p api{$apiDestination}.practicesoftwaretesting.com_tmp");
    upload(__DIR__ . "/{$source}/API/", "/var/www/api{$apiDestination}.practicesoftwaretesting.com_tmp");
    run("sudo mv /var/www/api{$apiDestination}.practicesoftwaretesting.com /var/www/api{$apiDestination}.practicesoftwaretesting.com_bak");
    run("sudo mv /var/www/api{$apiDestination}.practicesoftwaretesting.com_tmp /var/www/api{$apiDestination}.practicesoftwaretesting.com");
    run("sudo rm -rf /var/www/api{$apiDestination}.practicesoftwaretesting.com_tmp");
    run("sudo rm -rf /var/www/api{$apiDestination}.practicesoftwaretesting.com_bak");
    run("curl https://api{$apiDestination}.practicesoftwaretesting.com/status");

    // Running Artisan commands
    run("/usr/bin/php /var/www/api{$apiDestination}.practicesoftwaretesting.com/artisan migrate:fresh --force -vvvv");
    run("/usr/bin/php /var/www/api{$apiDestination}.practicesoftwaretesting.com/artisan db:seed --force -vvvv");
    run("/usr/bin/php /var/www/api{$apiDestination}.practicesoftwaretesting.com/artisan optimize:clear");
    run("/usr/bin/php /var/www/api{$apiDestination}.practicesoftwaretesting.com/artisan config:cache");
    run("/usr/bin/php /var/www/api{$apiDestination}.practicesoftwaretesting.com/artisan route:cache");
    run("/usr/bin/php /var/www/api{$apiDestination}.practicesoftwaretesting.com/artisan config:clear");
    run("/usr/bin/php /var/www/api{$apiDestination}.practicesoftwaretesting.com/artisan l5-swagger:generate");
    run("sudo chmod -R 777 /var/www/api{$apiDestination}.practicesoftwaretesting.com/storage");
    run("sudo chown -R www-data:www-data /var/www/api{$apiDestination}.practicesoftwaretesting.com/storage");

    // UI Deployment
    runLocally("rm -rf {$source}/UI/node_modules/esbuild/");
    runLocally("rm -rf {$source}/UI/node_modules");
    runLocally("rm -f {$source}/UI/package-lock.json");
    runLocally("npm cache clean --force");
    runLocally("cd {$source}/UI/ && npm cache verify && npm install --include=optional --legacy-peer-deps && npm run build");

    run("cd /var/www/ && mkdir -p {$webDestination}practicesoftwaretesting.com_tmp/public_html");
    upload(__DIR__ . "/{$source}/UI/dist/toolshop/", "/var/www/{$webDestination}practicesoftwaretesting.com_tmp/public_html");
    run("sudo mv /var/www/{$webDestination}practicesoftwaretesting.com /var/www/{$webDestination}practicesoftwaretesting.com_bak");
    run("sudo mv /var/www/{$webDestination}practicesoftwaretesting.com_tmp /var/www/{$webDestination}practicesoftwaretesting.com");
    run("sudo rm -rf /var/www/{$webDestination}practicesoftwaretesting.com_tmp");
    run("sudo rm -rf /var/www/{$webDestination}practicesoftwaretesting.com_bak");
}
