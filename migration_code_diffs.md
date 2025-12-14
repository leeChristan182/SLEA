# Migration Code Diffs - Minimal Changes Required

This document contains the minimal code changes needed to fix migration integrity issues.

---

## Strategy

1. **Remove enum FK constraints from original migrations** (they'll be added in the late FK migration)
2. **Add idempotent guards** to all ALTER migrations
3. **Add index existence checks** before dropping indexes/constraints

---

## Fix 1: Remove Enum FK from users table migration

**File**: `2025_08_11_084213_create_users_table.php`

### REMOVE these foreign key definitions from the up() method:

```diff
         // Foreign keys to enum tables
-        $table->foreign('role')
-            ->references('key')
-            ->on('user_roles')
-            ->cascadeOnUpdate();
-
-        $table->foreign('status')
-            ->references('key')
-            ->on('user_statuses')
-            ->cascadeOnUpdate();
     });
```

**Location**: Inside `Schema::create('users', ...)` around lines 45-54

**Reason**: These FKs reference tables that don't exist yet. They'll be added by the late FK migration.

---

## Fix 2: Remove Enum FK from rubric_categories migration

**File**: `2025_08_12_074509_create_rubric_categories_table.php`

### REMOVE this foreign key:

```diff
         $table->string('aggregation', 20)->default('capped_sum');
-        $table->foreign('aggregation')->references('key')->on('rubric_aggregations');
         $table->json('aggregation_params')->nullable();
```

**Location**: Line 19 inside `Schema::create('rubric_categories', ...)`

---

## Fix 3: Remove Enum FK from rubric_sections migration

**File**: `2025_08_12_074509_create_rubric_sections_table.php`

### REMOVE this foreign key:

```diff
         // Optional aggregation config (but no max points here)
         $table->string('aggregation', 20)->default('sum');
-        $table->foreign('aggregation')->references('key')->on('rubric_aggregations');
         $table->json('aggregation_params')->nullable();
```

**Location**: Line 21 inside `Schema::create('rubric_sections', ...)`

---

## Fix 4: Remove Enum FK from rubric_subsections migration

**File**: `2025_08_13_021817_create_rubric_subsections_table.php`

### REMOVE this foreign key:

```diff
         $table->string('scoring_method', 10)->default('fixed');
-        $table->foreign('scoring_method')->references('key')->on('scoring_methods');
         $table->string('unit', 50)->nullable();
```

**Location**: Line 24 inside `Schema::create('rubric_subsections', ...)`

---

## Fix 5: Remove Enum FK from submissions migration

**File**: `2025_08_13_064542_create_submissions_table.php`

### REMOVE this foreign key:

```diff
         $table->string('status', 20)->default('pending');
-        $table->foreign('status')->references('key')->on('submission_statuses');

         $table->text('remarks')->nullable();
```

**Location**: Line 32 inside `Schema::create('submissions', ...)`

---

## Fix 6: Remove Enum FKs from organizations migration

**File**: `2025_11_05_071259_create_organizations_table.php`

### REMOVE these foreign keys:

```diff
         $table->string('domain', 20)->default('campus');
         $table->string('scope_level', 20)->default('institutional');
-        $table->foreign('domain')->references('key')->on('organization_domains');
-        $table->foreign('scope_level')->references('key')->on('scope_levels');

         $table->boolean('is_active')->default(true);
```

**Location**: Lines 21-22 inside `Schema::create('organizations', ...)`

---

## Fix 7: Remove Enum FK from student_leaderships migration

**File**: `2025_11_07_004626_create_student_leaderships_table.php`

### REMOVE this foreign key:

```diff
         // Leadership status (Active / Inactive) referencing enum table
         $table->string('leadership_status', 20)->nullable();
-        $table->foreign('leadership_status')
-            ->references('key')
-            ->on('student_leadership_statuses');

         $table->string('issued_by', 191)->nullable();
```

**Location**: Lines 45-47 inside `Schema::create('student_leaderships', ...)`

---

## Fix 8: Remove Enum FKs from submission_reviews migration

**File**: `2025_11_07_030741_create_submission_reviews_table.php`

### REMOVE these foreign keys:

```diff
         $table->string('score_source', 10)->default('auto');
-        $table->foreign('score_source')->references('key')->on('review_score_sources');
         $table->text('override_reason')->nullable();

         $table->string('decision', 20)->nullable();
-        $table->foreign('decision')->references('key')->on('category_results'); // or its own table if you want reuse
         $table->text('comments')->nullable();
```

**Location**: Lines 25 and 28 inside `Schema::create('submission_reviews', ...)`

---

## Fix 9: Remove Enum FK from assessor_compiled_scores migration

**File**: `2025_11_09_180008_create_assessor_compiled_scores_table.php`

### REMOVE this foreign key:

```diff
         $table->string('category_result', 20)->nullable();
-        $table->foreign('category_result')->references('key')->on('category_results');

         $table->timestamps();
```

**Location**: Line 20 inside `Schema::create('assessor_compiled_scores', ...)`

---

## Fix 10: Remove Enum FKs from assessor_final_reviews migration

**File**: `2025_11_09_180031_create_assessor_final_reviews_table.php`

### REMOVE these foreign keys:

```diff
         $table->string('qualification', 20)->nullable();
-        $table->foreign('qualification')->references('key')->on('qualifications');

         $table->text('remarks')->nullable();

         $table->string('status', 20)->default('finalized');
-        $table->foreign('status')->references('key')->on('final_review_statuses');

         $table->timestamp('reviewed_at')->useCurrent();
```

**Location**: Lines 18 and 22 inside `Schema::create('assessor_final_reviews', ...)`

---

## Fix 11: Remove Enum FK from final_reviews migration

**File**: `2025_11_10_010157_create_final_reviews_table.php`

### REMOVE this foreign key:

```diff
         $table->string('decision', 20)->nullable();
-        $table->foreign('decision')->references('key')->on('final_review_decisions');
         $table->text('remarks')->nullable();
```

**Location**: Line 18 inside `Schema::create('final_reviews', ...)`

---

## Fix 12: Add idempotent guard to positions FK migration

**File**: `2025_11_23_073935_add_leadership_type_id_to_positions_table.php`

### REPLACE the entire up() method:

```diff
 public function up(): void
 {
+    // Check if column already exists
+    if (Schema::hasColumn('positions', 'leadership_type_id')) {
+        return;
+    }
+
     Schema::table('positions', function (Blueprint $table) {
         $table->unsignedBigInteger('leadership_type_id')->nullable()->after('id');
         $table->foreign('leadership_type_id')
             ->references('id')
             ->on('leadership_types')
             ->onDelete('cascade');
     });
 }
```

---

## Fix 13: Add idempotent guard to unique constraint drop

**File**: `2025_11_23_074025_remove_unique_name_from_positions_add_composite_unique.php`

### ADD helper method and update up():

```diff
+use Illuminate\Support\Facades\DB;
+
 return new class extends Migration
 {
+    private function indexExists(string $table, string $indexName): bool
+    {
+        $result = DB::select("
+            SELECT INDEX_NAME
+            FROM information_schema.STATISTICS
+            WHERE TABLE_SCHEMA = DATABASE()
+            AND TABLE_NAME = ?
+            AND INDEX_NAME = ?
+        ", [$table, $indexName]);
+
+        return !empty($result);
+    }
+
     public function up(): void
     {
         Schema::table('positions', function (Blueprint $table) {
-            $table->dropUnique(['name']);
+            // Only drop if it exists
+            if ($this->indexExists('positions', 'positions_name_unique')) {
+                $table->dropUnique(['name']);
+            }
+
+            // Only add if it doesn't exist
+            if (!$this->indexExists('positions', 'positions_leadership_type_name_unique')) {
+                $table->unique(['leadership_type_id', 'name'], 'positions_leadership_type_name_unique');
+            }
-            $table->unique(['leadership_type_id', 'name'], 'positions_leadership_type_name_unique');
         });
     }
```

---

## Fix 14: Add idempotent guards to unique constraint migrations

**File**: `2025_12_14_052357_add_unique_non_org_leadership_constraint.php`

### ADD helper method and update up():

```diff
+use Illuminate\Support\Facades\DB;
+
 return new class extends Migration {
+    private function indexExists(string $table, string $indexName): bool
+    {
+        $result = DB::select("
+            SELECT INDEX_NAME
+            FROM information_schema.STATISTICS
+            WHERE TABLE_SCHEMA = DATABASE()
+            AND TABLE_NAME = ?
+            AND INDEX_NAME = ?
+        ", [$table, $indexName]);
+
+        return !empty($result);
+    }
+
     public function up(): void
     {
+        if ($this->indexExists('student_leaderships', 'uniq_non_org_position_per_term')) {
+            return; // Already exists
+        }
+
         Schema::table('student_leaderships', function (Blueprint $table) {
             $table->unique(
                 ['leadership_type_id', 'position_id', 'term'],
                 'uniq_non_org_position_per_term'
             );
         });
     }
```

---

## Fix 15: Add idempotent guards to org unique constraint migration

**File**: `2025_12_14_052411_add_unique_org_leadership_constraint.php`

### ADD helper method and update up():

```diff
+use Illuminate\Support\Facades\DB;
+
 return new class extends Migration {
+    private function indexExists(string $table, string $indexName): bool
+    {
+        $result = DB::select("
+            SELECT INDEX_NAME
+            FROM information_schema.STATISTICS
+            WHERE TABLE_SCHEMA = DATABASE()
+            AND TABLE_NAME = ?
+            AND INDEX_NAME = ?
+        ", [$table, $indexName]);
+
+        return !empty($result);
+    }
+
     public function up(): void
     {
+        if ($this->indexExists('student_leaderships', 'uniq_org_position_per_term')) {
+            return; // Already exists
+        }
+
         Schema::table('student_leaderships', function (Blueprint $table) {
             // ... rest of code
         });
     }
```

---

## Fix 16: Add idempotent guards to final unique fix migration

**File**: `2025_12_14_072225_fix_student_leaderships_uniques_add_user_id.php`

### ADD helper method and update up():

```diff
+use Illuminate\Support\Facades\DB;
+
 return new class extends Migration {
+    private function indexExists(string $table, string $indexName): bool
+    {
+        $result = DB::select("
+            SELECT INDEX_NAME
+            FROM information_schema.STATISTICS
+            WHERE TABLE_SCHEMA = DATABASE()
+            AND TABLE_NAME = ?
+            AND INDEX_NAME = ?
+        ", [$table, $indexName]);
+
+        return !empty($result);
+    }
+
     public function up(): void
     {
         Schema::table('student_leaderships', function (Blueprint $table) {
-            // Drop old global uniques
-            $table->dropUnique('uniq_non_org_position_per_term');
-            $table->dropUnique('uniq_org_position_per_term');
-
-            // Add correct per-user uniques
-            $table->unique(['user_id', 'leadership_type_id', 'position_id', 'term'], 'uniq_user_non_org_position_per_term');
-            $table->unique(['user_id', 'leadership_type_id', 'organization_id', 'position_id', 'term'], 'uniq_user_org_position_per_term');
+            // Drop old global uniques (only if they exist)
+            if ($this->indexExists('student_leaderships', 'uniq_non_org_position_per_term')) {
+                $table->dropUnique('uniq_non_org_position_per_term');
+            }
+            if ($this->indexExists('student_leaderships', 'uniq_org_position_per_term')) {
+                $table->dropUnique('uniq_org_position_per_term');
+            }
+
+            // Add correct per-user uniques (only if they don't exist)
+            if (!$this->indexExists('student_leaderships', 'uniq_user_non_org_position_per_term')) {
+                $table->unique(['user_id', 'leadership_type_id', 'position_id', 'term'], 'uniq_user_non_org_position_per_term');
+            }
+            if (!$this->indexExists('student_leaderships', 'uniq_user_org_position_per_term')) {
+                $table->unique(['user_id', 'leadership_type_id', 'organization_id', 'position_id', 'term'], 'uniq_user_org_position_per_term');
+            }
         });
     }
```

---

## Summary

### Files to Modify (11 files)

1. ✅ `2025_08_11_084213_create_users_table.php` - Remove 2 enum FKs
2. ✅ `2025_08_12_074509_create_rubric_categories_table.php` - Remove 1 enum FK
3. ✅ `2025_08_12_074509_create_rubric_sections_table.php` - Remove 1 enum FK
4. ✅ `2025_08_13_021817_create_rubric_subsections_table.php` - Remove 1 enum FK
5. ✅ `2025_08_13_064542_create_submissions_table.php` - Remove 1 enum FK
6. ✅ `2025_11_05_071259_create_organizations_table.php` - Remove 2 enum FKs
7. ✅ `2025_11_07_004626_create_student_leaderships_table.php` - Remove 1 enum FK
8. ✅ `2025_11_07_030741_create_submission_reviews_table.php` - Remove 2 enum FKs
9. ✅ `2025_11_09_180008_create_assessor_compiled_scores_table.php` - Remove 1 enum FK
10. ✅ `2025_11_09_180031_create_assessor_final_reviews_table.php` - Remove 2 enum FKs
11. ✅ `2025_11_10_010157_create_final_reviews_table.php` - Remove 1 enum FK

### Files to Add Idempotent Guards (5 files)

12. ✅ `2025_11_23_073935_add_leadership_type_id_to_positions_table.php` - Add column check
13. ✅ `2025_11_23_074025_remove_unique_name_from_positions_add_composite_unique.php` - Add index checks
14. ✅ `2025_12_14_052357_add_unique_non_org_leadership_constraint.php` - Add index check
15. ✅ `2025_12_14_052411_add_unique_org_leadership_constraint.php` - Add index check
16. ✅ `2025_12_14_072225_fix_student_leaderships_uniques_add_user_id.php` - Add index checks

### New File to Create (1 file)

17. ✅ `2025_12_15_000000_add_enum_foreign_keys_late.php` - Already created

---

## Testing Strategy

After applying all fixes:

1. Drop all tables: `php artisan migrate:fresh`
2. Run migrations: `php artisan migrate`
3. Verify no errors
4. Run again (idempotency test): `php artisan migrate`
5. Verify "Nothing to migrate" message
6. Test rollback: `php artisan migrate:rollback --step=5`
7. Re-run migrations: `php artisan migrate`

All steps should complete without errors.

---

**Total Changes**: 16 files modified/created
**Lines Changed**: ~100 lines (mostly deletions and guards)
**Risk Level**: Low (only removing problematic FKs and adding safety checks)
