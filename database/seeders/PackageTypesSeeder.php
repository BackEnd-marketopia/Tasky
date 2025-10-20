<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PackageType;
use App\Models\PackageGoal;
use App\Models\Workspace;

class PackageTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all workspaces to create package types for each
        $workspaces = Workspace::all();

        // Default package types data
        $packageTypesData = [
            [
                'name' => 'Design',
                'icon' => 'bx bx-palette',
                'color' => '#e74c3c',
                'description' => 'UI/UX design, mockups, wireframes, and visual elements',
                'target_count' => 20
            ],
            [
                'name' => 'Development',
                'icon' => 'bx bx-code-alt',
                'color' => '#3498db',
                'description' => 'Frontend and backend development tasks',
                'target_count' => 50
            ],
            [
                'name' => 'Content Creation',
                'icon' => 'bx bx-edit',
                'color' => '#f39c12',
                'description' => 'Writing, blogging, copywriting, and content management',
                'target_count' => 30
            ],
            [
                'name' => 'Testing & QA',
                'icon' => 'bx bx-bug',
                'color' => '#9b59b6',
                'description' => 'Quality assurance, testing, and bug fixing',
                'target_count' => 25
            ],
            [
                'name' => 'Marketing',
                'icon' => 'bx bx-trending-up',
                'color' => '#2ecc71',
                'description' => 'Digital marketing, SEO, social media, and campaigns',
                'target_count' => 15
            ],
            [
                'name' => 'Research',
                'icon' => 'bx bx-search',
                'color' => '#34495e',
                'description' => 'Market research, competitor analysis, and data collection',
                'target_count' => 10
            ]
        ];

        foreach ($workspaces as $workspace) {
            foreach ($packageTypesData as $data) {
                // Create package type
                $packageType = PackageType::create([
                    'name' => $data['name'],
                    'icon' => $data['icon'],
                    'color' => $data['color'],
                    'description' => $data['description'],
                    'workspace_id' => $workspace->id,
                    'is_active' => true
                ]);

                // Create package goal
                PackageGoal::create([
                    'package_type_id' => $packageType->id,
                    'target_count' => $data['target_count'],
                    'workspace_id' => $workspace->id,
                    'description' => "Target goal for {$data['name']} tasks"
                ]);
            }
        }

        $this->command->info('Package types and goals seeded successfully for all workspaces!');
    }
}
