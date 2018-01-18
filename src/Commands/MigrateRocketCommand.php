<?php

namespace Larrock\ComponentMigrateRocket\Commands;

use Illuminate\Console\Command;
use Larrock\ComponentMigrateRocket\Helpers\BlocksMigrate;
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

        $process_list = [
            //new UsersMigrate(),
            new CategoryMigrate(),
            //new BlocksMigrate(),
            new CatalogMigrate(),
            /*new FeedMigrate(),
            new MenuMigrate(),
            new PagesMigrate(),
            new ReviewsMigrate()*/
        ];

        foreach ($process_list as $process){
            $this->info('Process '. get_class($process) .' started');
            //$bar = $this->output->createProgressBar(count($data));
            $process->import();
            //$bar->finish();
            //\Log::info('Sheet #'. $sheet .' successful imported.');
            $this->info('Process '. get_class($process) .' successful imported');
        }

        $this->info('Process migrate ended');
        $this->call('cache:clear');


        /*$sheet = (int)$this->option('sheet');
        $adminWizard = new AdminWizard();
        $data = \Cache::remember('ImportSheet'. $sheet, 1440, function() use ($sheet, $adminWizard){
            return \Excel::selectSheetsByIndex($sheet)->load($adminWizard->findXLSX(), function($reader) {})->get();
        });

        $bar = $this->output->createProgressBar(count($data));
        $adminWizard->artisanSheetImport($sheet, $bar, $data, $this->option('sleep'), $this->option('withoutimage'));
        $bar->finish();
        \Log::info('Sheet #'. $sheet .' successful imported.');
        $this->info('Sheet #'. $sheet .' successful imported.');*/
    }
}