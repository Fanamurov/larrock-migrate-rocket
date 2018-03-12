<?php

namespace Larrock\ComponentMigrateRocket\Commands;

use Illuminate\Console\Command;
use Larrock\ComponentMigrateRocket\Helpers\BlocksMigrate;
use Larrock\ComponentMigrateRocket\Helpers\CartMigrate;
use Larrock\ComponentMigrateRocket\Helpers\CatalogMigrate;
use Larrock\ComponentMigrateRocket\Helpers\CategoryMigrate;
use Larrock\ComponentMigrateRocket\Helpers\FeedMigrate;
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
    protected $signature = 'migrateRocket:import {--process= : name migration process} {--clearDBLog= : clear logDB} {--silence= : dont show dialogs}';

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
        $process = $this->option('process');
        $clearDBLog = $this->option('clearDBLog');
        $silence = $this->option('silence');
        $options = [];
        if($silence > 0){
            $options['--silence'] = $silence;
        }
        $options['--process'] = $process;
        $options['--clearDBLog'] = $clearDBLog;

        if($silence > 0){
            $this->process($options);
        }else{
            if ($this->confirm('Start Migrate?', 'yes')) {
                if(empty($options['--clearDBLog'])){
                    if($this->confirm('Clear MigrateDB?')) {
                        $options['--clearDBLOG'] = true;
                    }
                }
                $this->process($options);
            }
        }
    }

    protected function process($options)
    {
        $this->info('Start migrate process');

        if( !empty($options['--process'])){
            $name = $options['--process'];
        }else{
            $packages = [
                'UsersMigrate', 'CategoryMigrate', 'BlocksMigrate', 'CatalogMigrate', 'CatalogMigrateLinks', 'CartMigrate',
                'FeedMigrate', 'MenuMigrate', 'PagesMigrate', 'ReviewsMigrate', 'all'
            ];

            $name = $this->choice('What to migrate?', $packages);
        }

        $this->clearDBLog($name, $options);

        if($name === 'UsersMigrate' || $name === 'all'){
            $process_list[] = new UsersMigrate();
        }elseif($name === 'CategoryMigrate' || $name === 'all'){
            $process_list[] = new CategoryMigrate();
        }elseif($name === 'BlocksMigrate' || $name === 'all'){
            $process_list[] = new BlocksMigrate();
        }elseif($name === 'CatalogMigrate' || $name === 'all'){
            $process_list[] = new CatalogMigrate();
        }elseif($name === 'CatalogMigrateLinks' || $name === 'all'){
            $process_list[] = new CatalogMigrate();
        }elseif($name === 'CartMigrate' || $name === 'all'){
            $process_list[] = new CartMigrate();
        }elseif($name === 'FeedMigrate' || $name === 'all'){
            $process_list[] = new FeedMigrate();
        }elseif($name === 'MenuMigrate' || $name === 'all'){
            $process_list[] = new MenuMigrate();
        }elseif($name === 'PagesMigrate' || $name === 'all'){
            $process_list[] = new PagesMigrate();
        }elseif($name === 'ReviewsMigrate' || $name === 'all'){
            $process_list[] = new ReviewsMigrate();
        }

        if( !isset($process_list)){
            $this->error('Не выбран конкретный процесс');
            return TRUE;
        }

        foreach ($process_list as $process){
            $this->info('Process '. \get_class($process) .' started');
            $process->import();
            $this->info('Process '. \get_class($process) .' successful imported');
        }

        $this->info('Process migrate ended');
        $this->call('cache:clear');
    }

    protected function clearDBLog($name, $options)
    {
        $MigrateDBLog = new MigrateDBLog();

        if($name === 'all' && !empty($options['--clearDBLog'])){
            $MigrateDBLog->clearAll();
        }
        if($name === 'UsersMigrate' && !empty($options['--clearDBLog'])){
            $MigrateDBLog->clearByTable('users');
        }
        if($name === 'CategoryMigrate' && !empty($options['--clearDBLog'])){
            $MigrateDBLog->clearByTable('category');
        }
        if($name === 'BlocksMigrate' && !empty($options['--clearDBLog'])){
            $MigrateDBLog->clearByTable('blocks');
        }
        if($name === 'CatalogMigrate' && !empty($options['--clearDBLog'])){
            $MigrateDBLog->clearByTable('catalog');
        }
        if($name === 'CartMigrate' && !empty($options['--clearDBLog'])){
            $MigrateDBLog->clearByTable('cart');
        }
        if($name === 'FeedMigrate' && !empty($options['--clearDBLog'])){
            $MigrateDBLog->clearByTable('feed');
        }
        if($name === 'MenuMigrate' && !empty($options['--clearDBLog'])){
            $MigrateDBLog->clearByTable('menu');
        }
        if($name === 'PagesMigrate' && !empty($options['--clearDBLog'])){
            $MigrateDBLog->clearByTable('page');
        }
        if($name === 'ReviewsMigrate' && !empty($options['--clearDBLog'])){
            $MigrateDBLog->clearByTable('reviews');
        }
    }
}