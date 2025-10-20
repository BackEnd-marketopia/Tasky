# Package Goals Integration - API Usage Examples

## Overview
تم إضافة ربط المهام (Tasks) بأهداف الحزم (Package Goals) لتتبع التقدم في كل مهمة.

## جداول البيانات المضافة
- تم إضافة `package_goal_id` و `progress_count` لجدول `tasks`
- `package_goal_id`: مربوط بالهدف المحدد (nullable)
- `progress_count`: عدد المكتمل من الهدف (افتراضي 0)

## APIs الجديدة

### 1. جلب أنواع الحزم
```
GET /api/tasks/package-types
```

**Response:**
```json
{
    "error": false,
    "message": "Package types retrieved successfully.",
    "data": [
        {
            "id": 1,
            "name": "Design Package",
            "icon": "design-icon",
            "color": "#FF5722",
            "description": "Package for design tasks"
        }
    ]
}
```

### 2. جلب أهداف الحزم حسب النوع
```
GET /api/tasks/package-goals-by-type?package_type_id=1
```

**Response:**
```json
{
    "error": false,
    "message": "Package goals retrieved successfully.",
    "data": [
        {
            "id": 1,
            "title": "Design 15 Logos",
            "description": "Create 15 unique logo designs",
            "target_count": 15,
            "completed_count": 5,
            "remaining_count": 10,
            "completion_percentage": 33.33,
            "package_type": "Design Package"
        }
    ]
}
```

### 3. إنشاء مهمة مع Package Goal
```
POST /api/tasks/store
```

**Request Body:**
```json
{
    "title": "Design Logo for Client A",
    "description": "Create a modern logo design",
    "status_id": 1,
    "project": 1,
    "start_date": "2025-10-20",
    "due_date": "2025-10-25",
    "package_goal_id": 1,
    "progress_count": 2,
    "user_id": [1, 2]
}
```

### 4. تحديث التقدم في المهمة
```
PATCH /api/tasks/update-progress
```

**Request Body:**
```json
{
    "task_id": 123,
    "progress_count": 5
}
```

**Response:**
```json
{
    "error": false,
    "message": "Progress count updated successfully.",
    "data": {
        "task_id": 123,
        "progress_count": 5,
        "progress_percentage": 33.33,
        "remaining_count": 10,
        "is_goal_completed": false
    }
}
```

### 5. جلب معلومات المهمة مع التحليلات
```
GET /api/tasks/123
```

**Response (إضافة للبيانات الموجودة):**
```json
{
    "error": false,
    "data": {
        "id": 123,
        "title": "Design Logo for Client A",
        "package_goal_id": 1,
        "progress_count": 5,
        "package_goal": {
            "id": 1,
            "title": "Design 15 Logos",
            "target_count": 15,
            "package_type": "Design Package",
            "description": "Create 15 unique logo designs"
        },
        "progress_analysis": {
            "progress_percentage": 33.33,
            "remaining_count": 10,
            "is_goal_completed": false,
            "target_count": 15,
            "completed_count": 5
        }
    }
}
```

## Models والعلاقات المضافة

### Task Model
```php
// العلاقة مع PackageGoal
public function packageGoal()
{
    return $this->belongsTo(PackageGoal::class);
}

// حساب النسبة المئوية
public function getProgressPercentageAttribute()
{
    if (!$this->packageGoal || $this->packageGoal->target_count == 0) {
        return 0;
    }
    return round(($this->progress_count / $this->packageGoal->target_count) * 100, 2);
}

// حساب المتبقي
public function getRemainingCountAttribute()
{
    if (!$this->packageGoal) {
        return 0;
    }
    return max(0, $this->packageGoal->target_count - $this->progress_count);
}

// التحقق من اكتمال الهدف
public function getIsGoalCompletedAttribute()
{
    if (!$this->packageGoal) {
        return false;
    }
    return $this->progress_count >= $this->packageGoal->target_count;
}
```

### PackageGoal Model
```php
// العلاقة مع Tasks
public function tasks()
{
    return $this->hasMany(Task::class);
}

// حساب مجموع التقدم من جميع المهام
public function getCompletedTasksCountAttribute()
{
    return $this->tasks()->sum('progress_count');
}
```

## استخدام Frontend

### 1. في Create Task Form
1. اختيار Package Type من dropdown
2. عند الاختيار، جلب Package Goals للنوع المختار
3. اختيار Package Goal من dropdown
4. إدخال Progress Count (اختياري)

### 2. في Task Details Page
- عرض معلومات Package Goal
- عرض التقدم الحالي والنسبة المئوية
- إمكانية تحديث Progress Count
- عرض الإحصائيات (المكتمل، المتبقي، النسبة المئوية)

### 3. في Task List
- عرض Progress Bar للمهام المرتبطة بـ Package Goals
- عرض النسبة المئوية والحالة

## ملاحظات مهمة

1. **Package Goal اختياري**: يمكن إنشاء مهام بدون ربطها بـ Package Goal
2. **Progress Count منفصل**: كل مهمة لها progress count منفصل
3. **Validation**: Progress Count لا يمكن أن يتجاوز target_count للهدف
4. **Multiple Tasks**: يمكن ربط عدة مهام بنفس Package Goal
5. **Workspace Isolation**: Package Goals مفصولة حسب Workspace
6. **Real-time Updates**: التحديثات تظهر فوراً في جميع المهام المرتبطة

## المرحلة التالية
1. إضافة Frontend UI للـ Package Goal selection
2. إضافة Progress tracking في Task details
3. إضافة Dashboard للإحصائيات العامة لجميع Package Goals
4. إضافة Notifications عند اكتمال الأهداف