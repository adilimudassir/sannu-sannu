<?php

namespace App\Console\Commands;

use App\Services\ProjectService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateProjectStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:update-statuses 
                            {--dry-run : Show what would be updated without making changes}
                            {--detailed : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update project statuses based on dates (complete expired projects, activate scheduled projects)';

    /**
     * Execute the console command.
     */
    public function handle(ProjectService $projectService): int
    {
        $isDryRun = $this->option('dry-run');
        $isVerbose = $this->option('detailed');

        $this->info('Starting project status updates...');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        try {
            if ($isDryRun) {
                $updatedCount = $this->performDryRun($projectService, $isVerbose);
            } else {
                $updatedCount = $projectService->updateProjectStatusByDate();
            }

            if ($updatedCount > 0) {
                $this->info("Successfully updated {$updatedCount} project(s)");
            } else {
                $this->info('No projects required status updates');
            }

            if ($isVerbose) {
                $this->line('');
                $this->info('Status update process completed successfully');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to update project statuses: '.$e->getMessage());

            Log::error('Project status update command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Perform a dry run to show what would be updated
     */
    private function performDryRun(ProjectService $projectService, bool $isVerbose): int
    {
        $count = 0;

        // Check for projects that would be completed
        $expiredProjects = \App\Models\Project::where('status', \App\Enums\ProjectStatus::ACTIVE)
            ->where('end_date', '<', now()->toDateString())
            ->with(['tenant'])
            ->get();

        if ($expiredProjects->count() > 0) {
            $this->line('');
            $this->info('Projects that would be COMPLETED:');

            foreach ($expiredProjects as $project) {
                $count++;
                $message = "  - {$project->name} (ID: {$project->id}, Tenant: {$project->tenant->name})";

                if ($isVerbose) {
                    $message .= " - End Date: {$project->end_date->format('Y-m-d')}";
                }

                $this->line($message);
            }
        }

        // Check for projects that would be activated
        $projectsToActivate = \App\Models\Project::where('status', \App\Enums\ProjectStatus::DRAFT)
            ->where('start_date', '<=', now()->toDateString())
            ->whereNotNull('start_date')
            ->with(['tenant'])
            ->get();

        $readyToActivate = $projectsToActivate->filter(function ($project) use ($projectService) {
            try {
                // Use reflection to access private method for dry run
                $reflection = new \ReflectionClass($projectService);
                $method = $reflection->getMethod('isProjectReadyForActivation');
                $method->setAccessible(true);

                return $method->invoke($projectService, $project);
            } catch (\Exception $e) {
                return false;
            }
        });

        if ($readyToActivate->count() > 0) {
            $this->line('');
            $this->info('Projects that would be ACTIVATED:');

            foreach ($readyToActivate as $project) {
                $count++;
                $message = "  - {$project->name} (ID: {$project->id}, Tenant: {$project->tenant->name})";

                if ($isVerbose) {
                    $message .= " - Start Date: {$project->start_date->format('Y-m-d')}";
                }

                $this->line($message);
            }
        }

        if ($projectsToActivate->count() > $readyToActivate->count()) {
            $notReady = $projectsToActivate->count() - $readyToActivate->count();
            $this->line('');
            $this->warn("Note: {$notReady} draft project(s) have reached their start date but are not ready for activation");
        }

        return $count;
    }
}
