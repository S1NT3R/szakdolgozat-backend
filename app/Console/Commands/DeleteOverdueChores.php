<?php

namespace App\Console\Commands;

use App\Models\Chores;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOverdueChores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chores:delete-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete chores that are overdue by 5 days or more';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $overdueChores = Chores::where('due_date', '<=', Carbon::now()->subDays(5))->get();

        foreach ($overdueChores as $chore) {
            $chore->delete();
        }

        $this->info('Overdue chores deleted successfully.');

        return 0;
    }
}
