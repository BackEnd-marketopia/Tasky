<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PackageType;
use App\Models\PackageGoal;
use App\Models\Task;
use App\Models\Project;
use App\Models\Status;

class PackageGoalsDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first available workspace
        $workspace = \App\Models\Workspace::first();
        if (!$workspace) {
            $this->command->error('No workspace found. Please create a workspace first.');
            return;
        }
        $workspaceId = $workspace->id;
        
        // Create Package Types (or get existing ones)
        $webDevType = PackageType::firstOrCreate([
            'name' => 'Web Development',
            'workspace_id' => $workspaceId,
        ], [
            'description' => 'Web development projects and tasks',
            'color' => '#3b82f6',
            'icon' => 'bx bx-code-alt',
            'is_active' => true,
        ]);

        $designType = PackageType::firstOrCreate([
            'name' => 'Design',
            'workspace_id' => $workspaceId,
        ], [
            'description' => 'UI/UX Design tasks',
            'color' => '#8b5cf6',
            'icon' => 'bx bx-paint',
            'is_active' => true,
        ]);

        $marketingType = PackageType::firstOrCreate([
            'name' => 'Marketing',
            'workspace_id' => $workspaceId,
        ], [
            'description' => 'Marketing and promotion activities',
            'color' => '#ef4444',
            'icon' => 'bx bx-trending-up',
            'is_active' => true,
        ]);

        // Create Package Goals (or get existing ones)
        $webGoal1 = PackageGoal::firstOrCreate([
            'package_type_id' => $webDevType->id,
            'title' => 'Frontend Development Tasks',
            'workspace_id' => $workspaceId,
        ], [
            'description' => 'Complete frontend development milestones',
            'target_count' => 50,
        ]);

        $webGoal2 = PackageGoal::firstOrCreate([
            'package_type_id' => $webDevType->id,
            'title' => 'Backend API Development',
            'workspace_id' => $workspaceId,
        ], [
            'description' => 'Complete backend API endpoints',
            'target_count' => 30,
        ]);

        $designGoal = PackageGoal::firstOrCreate([
            'package_type_id' => $designType->id,
            'title' => 'UI Component Design',
            'workspace_id' => $workspaceId,
        ], [
            'description' => 'Design reusable UI components',
            'target_count' => 25,
        ]);

        $marketingGoal = PackageGoal::firstOrCreate([
            'package_type_id' => $marketingType->id,
            'title' => 'Content Creation',
            'workspace_id' => $workspaceId,
        ], [
            'description' => 'Create marketing content pieces',
            'target_count' => 20,
        ]);

        // Get or create a project
        $project = Project::where('workspace_id', $workspaceId)->first();
        if (!$project) {
            $project = Project::create([
                'title' => 'Demo Project',
                'description' => 'Demo project for package goals',
                'workspace_id' => $workspaceId,
                'start_date' => now(),
                'end_date' => now()->addDays(30),
            ]);
        }

        // Get default status
        $status = Status::first();
        if (!$status) {
            $status = Status::create([
                'title' => 'In Progress',
                'color' => 'primary',
            ]);
        }

        // Create demo tasks with progress
        $tasks = [
            [
                'title' => 'Create Homepage Layout',
                'package_goal_id' => $webGoal1->id,
                'progress_count' => 15,
            ],
            [
                'title' => 'Develop Contact Form',
                'package_goal_id' => $webGoal1->id,
                'progress_count' => 8,
            ],
            [
                'title' => 'Setup User Authentication API',
                'package_goal_id' => $webGoal2->id,
                'progress_count' => 12,
            ],
            [
                'title' => 'Create Product API Endpoints',
                'package_goal_id' => $webGoal2->id,
                'progress_count' => 6,
            ],
            [
                'title' => 'Design Button Components',
                'package_goal_id' => $designGoal->id,
                'progress_count' => 18,
            ],
            [
                'title' => 'Design Form Elements',
                'package_goal_id' => $designGoal->id,
                'progress_count' => 5,
            ],
            [
                'title' => 'Write Blog Posts',
                'package_goal_id' => $marketingGoal->id,
                'progress_count' => 12,
            ],
            [
                'title' => 'Create Social Media Content',
                'package_goal_id' => $marketingGoal->id,
                'progress_count' => 7,
            ],
        ];

        // Get first user
        $user = \App\Models\User::first();
        if (!$user) {
            $this->command->error('No user found. Please create a user first.');
            return;
        }

        foreach ($tasks as $taskData) {
            Task::create([
                'title' => $taskData['title'],
                'description' => 'Demo task for ' . $taskData['title'],
                'project_id' => $project->id,
                'status_id' => $status->id,
                'package_goal_id' => $taskData['package_goal_id'],
                'progress_count' => $taskData['progress_count'],
                'workspace_id' => $workspaceId,
                'created_by' => $user->id,
                'start_date' => now(),
                'due_date' => now()->addDays(7),
            ]);
        }

        $this->command->info('Demo data created successfully!');
        $this->command->info('Created:');
        $this->command->info('- 3 Package Types');
        $this->command->info('- 4 Package Goals'); 
        $this->command->info('- 8 Tasks with progress');
    }
}
