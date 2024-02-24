<?php

namespace App\Console\Commands;

use App\Models\Todos;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteOverdueTodos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'todos:delete-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete todos that are overdue by 5 days or more';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $overdueTodos = Todos::where('due_date', '<=', Carbon::now()->subDays(5))->get();

        foreach ($overdueTodos as $todo) {
            $todo->delete();
        }

        $this->info('Overdue todos deleted successfully.');

        return 0;
    }
}
