<?php

namespace Database\Seeders;

// use App\Models\User;
use Database\Seeders\PageHomeSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\TemplateSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PageHomeSeeder::class);
        $this->call(SettingSeeder::class); 
        $this->call(TemplateSeeder::class); 
        
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}