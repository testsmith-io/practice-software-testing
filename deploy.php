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
task('upload', function () {
    $apps = [
//gf
        ['source'=> 'sprint5', 'web_destination' => '', 'api_destination' => ''],
        ['source'=> 'sprint4', 'web_destination' => 'v4.', 'api_destination' => '-v4'],
        ['source'=> 'sprint3', 'web_destination' => 'v3.', 'api_destination' => '-v3'],
        ['source'=> 'sprint2', 'web_destination' => 'v2.', 'api_destination' => '-v2'],
        ['source'=> 'sprint1', 'web_destination' => 'v1.', 'api_destination' => '-v1'],
        ['source'=> 'sprint5-with-bugs', 'web_destination' => 'with-bugs.', 'api_destination' => '-with-bugs']
    ];
    $deployPath = get('deploy_path');

    foreach ($apps as $app)
    {
        runLocally("cd {$app['source']}/API/ && composer update --no-dev --prefer-dist --optimize-autoloader");
        run("cd /var/www/ && mkdir -p api{$app['api_destination']}.practicesoftwaretesting.com_tmp");
        upload(__DIR__ . "/{$app['source']}/API/", "/var/www/api{$app['api_destination']}.practicesoftwaretesting.com_tmp");
        run("sudo mv /var/www/api{$app['api_destination']}.practicesoftwaretesting.com /var/www/api{$app['api_destination']}.practicesoftwaretesting.com_bak");
        run("sudo mv /var/www/api{$app['api_destination']}.practicesoftwaretesting.com_tmp /var/www/api{$app['api_destination']}.practicesoftwaretesting.com");
        run("sudo rm -f -R /var/www/api{$app['api_destination']}.practicesoftwaretesting.com_tmp");
        run("sudo rm -f -R /var/www/api{$app['api_destination']}.practicesoftwaretesting.com_bak");
        run("curl https://api{$app['api_destination']}.practicesoftwaretesting.com");
        run("/usr/bin/php /var/www/api{$app['api_destination']}.practicesoftwaretesting.com/artisan migrate:fresh --force -vvvv");
        run("/usr/bin/php /var/www/api{$app['api_destination']}.practicesoftwaretesting.com/artisan db:seed --force -vvvv");
//        run("/usr/bin/php /var/www/api{$app['api_destination']}.practicesoftwaretesting.com/artisan vendor:publish --provider \"L5Swagger\L5SwaggerServiceProvider\"");
        run("/usr/bin/php /var/www/api{$app['api_destination']}.practicesoftwaretesting.com/artisan l5-swagger:generate");
        run("sudo chmod -R 777 /var/www/api{$app['api_destination']}.practicesoftwaretesting.com/storage");
        run("sudo chown -R www-data:www-data /var/www/api{$app['api_destination']}.practicesoftwaretesting.com/storage/framework");

        runLocally("cd {$app['source']}/UI/ && npm install --legacy-peer-deps && npm run build");
        run("cd /var/www/ && mkdir -p {$app['web_destination']}practicesoftwaretesting.com_tmp/public_html");
        upload(__DIR__ . "/{$app['source']}/UI/dist/toolshop/", "/var/www/{$app['web_destination']}practicesoftwaretesting.com_tmp/public_html");
        run("sudo mv /var/www/{$app['web_destination']}practicesoftwaretesting.com /var/www/{$app['web_destination']}practicesoftwaretesting.com_bak");
        run("sudo mv /var/www/{$app['web_destination']}practicesoftwaretesting.com_tmp /var/www/{$app['web_destination']}practicesoftwaretesting.com");
        run("sudo rm -f -R /var/www/{$app['web_destination']}practicesoftwaretesting.com_tmp");
        run("sudo rm -f -R /var/www/{$app['web_destination']}practicesoftwaretesting.com_bak");
    }
});

