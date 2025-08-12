<?php

namespace App\Console\Commands;

use App\Services\SessionService;
use Illuminate\Console\Command;

class CleanupExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired sessions from the database';

    public function __construct(
        private SessionService $sessionService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Cleaning up expired sessions...');
        
        $deletedCount = $this->sessionService->cleanupExpiredSessions();
        
        if ($deletedCount > 0) {
            $this->info("Cleaned up {$deletedCount} expired sessions.");
        } else {
            $this->info('No expired sessions found.');
        }
        
        return Command::SUCCESS;
    }
}
