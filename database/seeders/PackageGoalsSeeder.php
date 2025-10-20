<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PackageGoal;
use App\Models\PackageType;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;

class PackageGoalsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // التأكد من وجود الجدول أولاً
        if (!DB::getSchemaBuilder()->hasTable('package_goals')) {
            // إنشاء الجدول إذا لم يكن موجوداً
            DB::statement("
                CREATE TABLE IF NOT EXISTS package_goals (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    package_type_id BIGINT UNSIGNED NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    target_count INT DEFAULT 0,
                    workspace_id BIGINT UNSIGNED NOT NULL,
                    description TEXT NULL,
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    FOREIGN KEY (package_type_id) REFERENCES package_types(id) ON DELETE CASCADE,
                    FOREIGN KEY (workspace_id) REFERENCES workspaces(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_workspace_package_goal_title (package_type_id, workspace_id, title)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $this->command->info('✅ تم إنشاء جدول package_goals');
        }

        // الحصول على workspace الأول (أو إنشاؤه)
        $workspace = Workspace::first();
        if (!$workspace) {
            $this->command->error('❌ لا يوجد workspace. يرجى إنشاء workspace أولاً');
            return;
        }

        // الحصول على أنواع الحزم
        $packageTypes = PackageType::where('workspace_id', $workspace->id)->get();
        
        if ($packageTypes->isEmpty()) {
            $this->command->error('❌ لا توجد أنواع حزم. يرجى تشغيل PackageTypesSeeder أولاً');
            return;
        }

        // إنشاء أهداف نموذجية لكل نوع حزمة
        $goals = [
            'Design' => [
                ['title' => 'تصميم الواجهات الأساسية', 'target_count' => 15, 'description' => 'تصميم الصفحات الرئيسية وواجهات المستخدم'],
                ['title' => 'تصميم الأيقونات والشعارات', 'target_count' => 25, 'description' => 'إنشاء مجموعة شاملة من الأيقونات والعناصر البصرية'],
                ['title' => 'تصميم المحتوى المرئي', 'target_count' => 10, 'description' => 'إنشاء الصور والرسومات للمحتوى']
            ],
            'Development' => [
                ['title' => 'تطوير الباك إند', 'target_count' => 30, 'description' => 'برمجة الخادم وقواعد البيانات والـ APIs'],
                ['title' => 'تطوير الفرونت إند', 'target_count' => 25, 'description' => 'برمجة واجهة المستخدم والتفاعلات'],
                ['title' => 'اختبارات النظام', 'target_count' => 20, 'description' => 'كتابة الاختبارات والتأكد من جودة الكود']
            ],
            'Content Creation' => [
                ['title' => 'كتابة المحتوى التسويقي', 'target_count' => 12, 'description' => 'إنشاء النصوص الترويجية والإعلانية'],
                ['title' => 'المحتوى التقني', 'target_count' => 8, 'description' => 'كتابة التوثيق والأدلة التقنية'],
                ['title' => 'محتوى وسائل التواصل', 'target_count' => 20, 'description' => 'إنشاء منشورات وحملات السوشال ميديا']
            ],
            'Testing & QA' => [
                ['title' => 'اختبار الوظائف', 'target_count' => 15, 'description' => 'اختبار جميع وظائف النظام والتأكد من عملها'],
                ['title' => 'اختبار الأداء', 'target_count' => 10, 'description' => 'قياس سرعة واستجابة النظام'],
                ['title' => 'اختبار الأمان', 'target_count' => 8, 'description' => 'فحص الثغرات الأمنية والحماية']
            ],
            'Marketing' => [
                ['title' => 'حملات السوشال ميديا', 'target_count' => 20, 'description' => 'إنشاء وإدارة الحملات التسويقية'],
                ['title' => 'تحليل السوق', 'target_count' => 12, 'description' => 'دراسة المنافسين وتحليل السوق'],
                ['title' => 'إعلانات مدفوعة', 'target_count' => 15, 'description' => 'إنشاء وإدارة الإعلانات المدفوعة']
            ],
            'Research' => [
                ['title' => 'بحث المستخدمين', 'target_count' => 10, 'description' => 'دراسة احتياجات وسلوك المستخدمين'],
                ['title' => 'بحث تقني', 'target_count' => 8, 'description' => 'البحث عن التقنيات والحلول المناسبة'],
                ['title' => 'تحليل البيانات', 'target_count' => 12, 'description' => 'تحليل البيانات واستخراج الإحصائيات']
            ]
        ];

        foreach ($packageTypes as $packageType) {
            if (isset($goals[$packageType->name])) {
                foreach ($goals[$packageType->name] as $goalData) {
                    // التحقق من عدم وجود الهدف مسبقاً
                    $existingGoal = PackageGoal::where('package_type_id', $packageType->id)
                        ->where('workspace_id', $workspace->id)
                        ->where('title', $goalData['title'])
                        ->first();

                    if (!$existingGoal) {
                        PackageGoal::create([
                            'package_type_id' => $packageType->id,
                            'title' => $goalData['title'],
                            'target_count' => $goalData['target_count'],
                            'description' => $goalData['description'],
                            'workspace_id' => $workspace->id,
                            'is_active' => true
                        ]);

                        $this->command->info("✅ تم إنشاء هدف: {$goalData['title']} لنوع: {$packageType->name}");
                    } else {
                        $this->command->warn("⚠️ الهدف موجود مسبقاً: {$goalData['title']} لنوع: {$packageType->name}");
                    }
                }
            }
        }

        $this->command->info('🎉 تم إنشاء أهداف الحزم بنجاح!');
    }
}
