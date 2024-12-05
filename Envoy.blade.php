@servers(['web' => ['phil@10.126.0.2']])

@task('deploy', ['on' => 'web'])
    cd /var/www/philstephens.com
    git pull
    composer install
    npm run build
@endtask
