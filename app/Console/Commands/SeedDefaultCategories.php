<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\User;
use Illuminate\Console\Command;

class SeedDefaultCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:seed-defaults';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed default expense and income categories for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding default categories for all users...');

        $defaultExpenseCategories = ['Food', 'Transport', 'Utilities'];
        $defaultIncomeCategories = ['Salary', 'Interest'];

        $users = User::all();
        $totalUsers = $users->count();

        if ($totalUsers === 0) {
            $this->warn('No users found in the database.');
            return;
        }

        $this->info("Found {$totalUsers} user(s). Creating default categories...");

        $bar = $this->output->createProgressBar($totalUsers);
        $bar->start();

        foreach ($users as $user) {
            // Seed expense categories
            foreach ($defaultExpenseCategories as $categoryName) {
                Category::firstOrCreate(
                    [
                        'name' => $categoryName,
                        'type' => 'expense',
                        'created_by' => $user->id,
                    ],
                    [
                        'name' => $categoryName,
                        'type' => 'expense',
                        'created_by' => $user->id,
                    ]
                );
            }

            // Seed income categories
            foreach ($defaultIncomeCategories as $categoryName) {
                Category::firstOrCreate(
                    [
                        'name' => $categoryName,
                        'type' => 'income',
                        'created_by' => $user->id,
                    ],
                    [
                        'name' => $categoryName,
                        'type' => 'income',
                        'created_by' => $user->id,
                    ]
                );
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Default categories seeded successfully for all users!');
    }
}
