<?php

namespace Larrock\ComponentMigrateRocket\Commands;

use Illuminate\Console\Command;
use Larrock\ComponentMigrateRocket\Helpers\BlocksMigrate;
use Larrock\ComponentMigrateRocket\Helpers\CatalogMigrate;
use Larrock\ComponentMigrateRocket\Helpers\CategoryMigrate;
use Larrock\ComponentMigrateRocket\Helpers\FeedMigrate;
use Larrock\ComponentMigrateRocket\Helpers\MediaMigrate;
use Larrock\ComponentMigrateRocket\Helpers\MenuMigrate;
use Larrock\ComponentMigrateRocket\Helpers\MigrateDBLog;
use Larrock\ComponentMigrateRocket\Helpers\PagesMigrate;
use Larrock\ComponentMigrateRocket\Helpers\ReviewsMigrate;
use Larrock\ComponentMigrateRocket\Helpers\UsersMigrate;

class MigrateRocketCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrateRocket:import {--sleep= : sleep process in seconds after 1s} {--silence= : dont show dialogs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate database rocket to LarrockCMS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sleep = $this->option('sleep');
        $silence = $this->option('silence');
        $options = [];
        if($sleep && $sleep > 0){
            $options['--sleep'] = $sleep;
        }
        if($silence > 0){
            $options['--silence'] = $silence;
        }

        if($silence > 0){
            $this->process($options);
        }else{
            if ($this->confirm('Start Migrate?', 'yes')) {
                if ($this->confirm('Clear MigrateDB?')) {
                    $MigrateDBLog = new MigrateDBLog();
                    $MigrateDBLog->clearAll();
                }
                $this->process($options);
            }
        }
    }

    protected function process($options)
    {
        $this->info('Start migrate process');

        $packages = [
            'UsersMigrate', 'CategoryMigrate', 'BlocksMigrate', 'CatalogMigrate', 'FeedMigrate',
            'MenuMigrate', 'PagesMigrate', 'ReviewsMigrate', 'all'
        ];

        $name = $this->choice('What to migrate?', $packages);

        if($name === 'UsersMigrate' || $name === 'all'){
            $process_list[] = new UsersMigrate();
        }elseif($name === 'CategoryMigrate' || $name === 'all'){
            $process_list[] = new UsersMigrate();
        }elseif($name === 'BlocksMigrate' || $name === 'all'){
            $process_list[] = new UsersMigrate();
        }elseif($name === 'CatalogMigrate' || $name === 'all'){
            $process_list[] = new UsersMigrate();
        }elseif($name === 'FeedMigrate' || $name === 'all'){
            $process_list[] = new UsersMigrate();
        }elseif($name === 'MenuMigrate' || $name === 'all'){
            $process_list[] = new UsersMigrate();
        }elseif($name === 'PagesMigrate' || $name === 'all'){
            $process_list[] = new UsersMigrate();
        }elseif($name === 'ReviewsMigrate' || $name === 'all'){
            $process_list[] = new UsersMigrate();
        }

        if( !isset($process_list)){
            $this->error('Не выбран конкретный процесс');
            return TRUE;
        }

        foreach ($process_list as $process){
            $this->info('Process '. get_class($process) .' started');
            $process->import();
            $this->info('Process '. get_class($process) .' successful imported');
        }

        $this->info('Process migrate ended');
        $this->call('cache:clear');
    }
}