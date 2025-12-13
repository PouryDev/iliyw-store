<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\ProductionSeeder;

class RunProductionSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:production {--fresh : Run fresh migration before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run production seeder to add real products from site owner data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏭 Starting Production Seeder...');
        
        if ($this->option('fresh')) {
            $this->info('🔄 Running fresh migration...');
            $this->call('migrate:fresh');
        }
        
        $this->info('🌱 Seeding production data...');
        $this->call('db:seed', ['--class' => ProductionSeeder::class]);
        
        $this->info('✅ Production seeding completed successfully!');
        $this->info('📊 Added 22 products with variants to the database');
        $this->info('🎨 Created colors, sizes, and categories');
        $this->info('🛍️ Ready for production use!');
    }
}