<?php

namespace Deployer;

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
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', []);

// Hosts
host('production')
    ->hostname('54.229.94.142')
    ->user('ubuntu')
    ->forwardAgent()
    ->stage('production')
    ->set('deploy_path', '/var/www/api.practicesoftwaretesting.com');

// Tasks
task('upload-sprint1', function () {
    $deployPath = get('deploy_path');
    deploy('sprint1', 'v1.', '-v1');
});

task('upload-sprint2', function () {
    $deployPath = get('deploy_path');
    deploy('sprint2', 'v2.', '-v2');
});

task('upload-sprint3', function () {
    $deployPath = get('deploy_path');
    deploy('sprint3', 'v3.', '-v3');
});

task('upload-sprint4', function () {
    $deployPath = get('deploy_path');
    deploy('sprint4', 'v4.', '-v4');
});

task('upload-sprint5', function () {
    $deployPath = get('deploy_path');
    deploy('sprint5', '', '');
});

task('upload-sprint5-with-bugs', function () {
    $deployPath = get('deploy_path');
    deploy('sprint5-with-bugs', 'with-bugs.', '-with-bugs');
});

function deploy($source, $webDestination, $apiDestination) {
    //runLocally("cd {$source}/API/ && composer update --no-dev --prefer-dist --optimize-autoloader");
    run("cd /var/www/ && mkdir -p api{$apiDestination}.practicesoftwaretesting.com_tmp");
    upload(__DIR__ . "/{$source}/API/", "/var/www/api{$apiDestination}.practicesoftwaretesting.com_tmp");
    run("sudo mv /var/www/api{$apiDestination}.practicesoftwaretesting.com /var/www/api{$apiDestination}.practicesoftwaretesting.com_bak");
    run("sudo mv /var/www/api{$apiDestination}.practicesoftwaretesting.com_tmp /var/www/api{$apiDestination}.practicesoftwaretesting.com");
    run("sudo rm -f -R /var/www/api{$apiDestination}.practicesoftwaretesting.com_tmp");
    run("sudo rm -f -R /var/www/api{$apiDestination}.practicesoftwaretesting.com_bak");
    run("curl https://api{$apiDestination}.practicesoftwaretesting.com");
    run("/usr/bin/php /var/www/api{$apiDestination}.practicesoftwaretesting.com/artisan migrate:fresh --force -vvvv");
    run("/usr/bin/php /var/www/api{$apiDestination}.practicesoftwaretesting.com/artisan db:seed --force -vvvv");
    run("/usr/bin/php /var/www/api{$apiDestination}.practicesoftwaretesting.com/artisan route:cache");
    run("/usr/bin/php /var/www/api{$apiDestination}.practicesoftwaretesting.com/artisan config:clear");
//        run("/usr/bin/php /var/www/api{$app['api_destination']}.practicesoftwaretesting.com/artisan vendor:publish --provider \"L5Swagger\L5SwaggerServiceProvider\"");
    run("/usr/bin/php /var/www/api{$apiDestination}.practicesoftwaretesting.com/artisan l5-swagger:generate");
    run("sudo chmod -R 777 /var/www/api{$apiDestination}.practicesoftwaretesting.com/storage");
    run("sudo chown -R www-data:www-data /var/www/api{$apiDestination}.practicesoftwaretesting.com/storage/framework");

    runLocally("cd {$source}/UI/ && npm install --legacy-peer-deps && npm run build");
    run("cd /var/www/ && mkdir -p {$webDestination}practicesoftwaretesting.com_tmp/public_html");
    upload(__DIR__ . "/{$source}/UI/dist/toolshop/", "/var/www/{$webDestination}practicesoftwaretesting.com_tmp/public_html");
    run("sudo mv /var/www/{$webDestination}practicesoftwaretesting.com /var/www/{$webDestination}practicesoftwaretesting.com_bak");
    run("sudo mv /var/www/{$webDestination}practicesoftwaretesting.com_tmp /var/www/{$webDestination}practicesoftwaretesting.com");
    run("sudo rm -f -R /var/www/{$webDestination}practicesoftwaretesting.com_tmp");
    run("sudo rm -f -R /var/www/{$webDestination}practicesoftwaretesting.com_bak");
}
