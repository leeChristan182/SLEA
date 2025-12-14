# Laravel Migration Integrity Audit Report

Generated: 2025-12-14

## Executive Summary

Analyzed 48 migrations in chronological order for MySQL migration integrity issues.

---

## 1. Migration Timeline (Chronological Order)

| #   | Timestamp         | Migration File                        | Tables Created/Modified                |
| --- | ----------------- | ------------------------------------- | -------------------------------------- |
| 1   | 2025_01_10_052643 | create_enum_code_tables               | 14 enum tables                         |
| 2   | 2025_08_11_032525 | create_log_in_table                   | log_in                                 |
| 3   | 2025_08_11_084213 | create_users_table                    | users, password_changes, assessor_info |
| 4   | 2025_08_12_074509 | create_rubric_categories_table        | rubric_categories                      |
| 5   | 2025_08_12_074509 | create_rubric_sections_table          | rubric_sections                        |
| 6   | 2025_08_13_021817 | create_rubric_subsections_table       | rubric_subsections                     |
| 7   | 2025_08_13_034150 | create_rubric_options_table           | rubric_options                         |
| 8   | 2025_08_13_064542 | create_submissions_table              | submissions                            |
| 9   | 2025_09_15_080422 | create_leadership_types_table         | leadership_types                       |
| 10  | 2025_09_15_080447 | create_clusters_table                 | clusters                               |
| 11  | 2025_11_03_045320 | create_positions_table                | positions                              |
| 12  | 2025_11_05_071259 | create_organizations_table            | organizations                          |
| 13  | 2025_11_06_084822 | create_colleges_table                 | colleges                               |
| 14  | 2025_11_06_084951 | create_programs_table                 | programs                               |
| 15  | 2025_11_06_085009 | create_majors_table                   | majors                                 |
| 16  | 2025_11_07_004626 | create_student_leaderships_table      | student_leaderships                    |
| 17  | 2025_11_07_030741 | create_submission_reviews_table       | submission_reviews                     |
| 18  | 2025_11_09_180008 | create_assessor_compiled_scores_table | assessor_compiled_scores               |
| 19  | 2025_11_09_180031 | create_assessor_final_reviews_table   | assessor_final_reviews                 |
| 20  | 2025_11_09_180414 | create_submission_history_table       | submission_history                     |
| 21  | 2025_11_09_191717 | create_organization_position_table    | organization_position                  |
| 22  | 2025_11_10_010157 | create_final_reviews_table            | final_reviews                          |
| 23  | 2025_11_10_015549 | create_user_documents_table           | user_documents                         |
| 24  | 2025_11_11_000901 | create_student_academic_table         | student_academic                       |
| 25+ | ...               | Various ALTER migrations              | (See detailed list below)              |

---

## 2. CRITICAL ISSUES FOUND

### Issue Type A: Foreign Keys Referencing Future Tables

**CRITICAL ISSUE #1**: Migration timestamps are BEFORE table dependencies

The `2025_01_10_052643_create_enum_code_tables.php` migration creates enum tables that are referenced by earlier migrations!

**Problem**: Timestamp 2025*01_10 is AFTER 2025_08_11, 2025_08_12, 2025_08_13, 2025_09_15, and 2025_11*\* migrations, but those migrations create foreign keys to the enum tables.

**Affected Foreign Keys**:

| Migration                                    | FK Column         | References Table            | Issue                                        |
| -------------------------------------------- | ----------------- | --------------------------- | -------------------------------------------- |
| 2025_08_11_084213 (users)                    | role              | user_roles                  | ❌ user_roles created LATER                  |
| 2025_08_11_084213 (users)                    | status            | user_statuses               | ❌ user_statuses created LATER               |
| 2025_08_12_074509 (rubric_categories)        | aggregation       | rubric_aggregations         | ❌ rubric_aggregations created LATER         |
| 2025_08_12_074509 (rubric_sections)          | aggregation       | rubric_aggregations         | ❌ rubric_aggregations created LATER         |
| 2025_08_13_021817 (rubric_subsections)       | scoring_method    | scoring_methods             | ❌ scoring_methods created LATER             |
| 2025_08_13_064542 (submissions)              | status            | submission_statuses         | ❌ submission_statuses created LATER         |
| 2025_11_05_071259 (organizations)            | domain            | organization_domains        | ❌ organization_domains created LATER        |
| 2025_11_05_071259 (organizations)            | scope_level       | scope_levels                | ❌ scope_levels created LATER                |
| 2025_11_07_004626 (student_leaderships)      | leadership_status | student_leadership_statuses | ❌ student_leadership_statuses created LATER |
| 2025_11_07_030741 (submission_reviews)       | score_source      | review_score_sources        | ❌ review_score_sources created LATER        |
| 2025_11_07_030741 (submission_reviews)       | decision          | category_results            | ❌ category_results created LATER            |
| 2025_11_09_180008 (assessor_compiled_scores) | category_result   | category_results            | ❌ category_results created LATER            |
| 2025_11_09_180031 (assessor_final_reviews)   | qualification     | qualifications              | ❌ qualifications created LATER              |
| 2025_11_09_180031 (assessor_final_reviews)   | status            | final_review_statuses       | ❌ final_review_statuses created LATER       |
| 2025_11_10_010157 (final_reviews)            | decision          | final_review_decisions      | ❌ final_review_decisions created LATER      |

**Root Cause**: The enum tables file has a timestamp of `2025_01_10` (January 10) but should be `2025_08_10` or earlier (before August 11).

---

### Issue Type B: DropUnique/DropIndex Conflicts with Foreign Keys

**ISSUE #2**: Migration `2025_11_23_074025_remove_unique_name_from_positions_add_composite_unique.php`

```php
Schema::table('positions', function (Blueprint $table) {
    $table->dropUnique(['name']); // ❌ DANGEROUS
    $table->unique(['leadership_type_id', 'name'], 'positions_leadership_type_name_unique');
});
```

**Problem**: Drops unique index on `name` column without checking if it's used by foreign keys.

**Status**: ⚠️ Currently safe (no FKs reference positions.name), but fragile pattern.

---

**ISSUE #3**: Migration `2025_11_23_120000_fix_student_leaderships_table.php`

```php
if ($this->indexExists('student_leaderships', 'uniq_org_position_per_term')) {
    Schema::table('student_leaderships', function (Blueprint $table) {
        $table->dropUnique('uniq_org_position_per_term'); // Properly guarded!
    });
}
```

**Status**: ✅ This one is CORRECT - it adds standalone indexes BEFORE dropping unique, preventing FK dependency issues.

---

**ISSUE #4**: Migration `2025_12_14_072225_fix_student_leaderships_uniques_add_user_id.php`

```php
Schema::table('student_leaderships', function (Blueprint $table) {
    $table->dropUnique('uniq_non_org_position_per_term'); // ⚠️ No guard check
    $table->dropUnique('uniq_org_position_per_term');     // ⚠️ No guard check
});
```

**Problem**: No idempotent checks using `information_schema` before dropping unique constraints.

---

### Issue Type C: Duplicate Index Names

**ISSUE #5**: Potential duplicate index name collisions

Migration `2025_08_13_064542_create_submissions_table.php` creates:

```php
$table->index(
    ['rubric_category_id', 'rubric_section_id', 'rubric_subsection_id'],
    'submissions_rubric_combo_idx'  // ✅ Named index
);
$table->index(['status', 'submitted_at']); // ⚠️ Auto-generated name
```

**Status**: ⚠️ Mixed approach - some named, some auto. Could lead to conflicts during rollback/re-run.

---

### Issue Type D: Missing Idempotent Guards

**ISSUE #6**: Multiple migrations lack idempotent checks

Examples of migrations WITHOUT information_schema guards:

1. `2025_11_23_073935_add_leadership_type_id_to_positions_table.php` - No column existence check
2. `2025_12_14_052357_add_unique_non_org_leadership_constraint.php` - No index existence check
3. `2025_12_14_052411_add_unique_org_leadership_constraint.php` - No index existence check

**Pattern**: Most "add\_\*" migrations don't check if column/constraint already exists.

---

### Issue Type E: Seeder Truncate Issues

**STATUS**: ✅ **NO ISSUES FOUND**

Scanned all seeders - none use `truncate()` on FK-referenced tables.

---

## 3. RECOMMENDED FIXES

### Fix #1: Rename Enum Tables Migration (CRITICAL)

**Action**: Rename the enum tables migration to have an earlier timestamp.

**File**: `2025_01_10_052643_create_enum_code_tables.php`  
**New Name**: `2025_08_10_000000_create_enum_code_tables.php`

**Commands**:

```bash
mv database/migrations/2025_01_10_052643_create_enum_code_tables.php \
   database/migrations/2025_08_10_000000_create_enum_code_tables.php
```

**Alternative**: Create a "late FK migration" to add all enum FK constraints after the tables are created.

---

### Fix #2: Add Idempotent Guards to All ALTER Migrations

Add `information_schema` checks before:

-   Adding columns
-   Adding foreign keys
-   Dropping indexes/constraints
-   Modifying columns

**Example Pattern**:

```php
// Before adding FK
$fkExists = DB::select("
    SELECT CONSTRAINT_NAME
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'positions'
    AND CONSTRAINT_NAME = 'positions_leadership_type_id_foreign'
");

if (empty($fkExists)) {
    Schema::table('positions', function (Blueprint $table) {
        $table->foreign('leadership_type_id')
            ->references('id')
            ->on('leadership_types')
            ->onDelete('cascade');
    });
}
```

---

### Fix #3: Create a Late FK Migration for Enum Tables

**New Migration**: `2025_12_15_000000_add_enum_foreign_keys.php`

This migration will add all enum table FKs AFTER all tables are created.

---

## 4. SUMMARY OF MIGRATIONS NEEDING EDITS

### High Priority (Critical)

1. ✅ `2025_01_10_052643_create_enum_code_tables.php` - Rename to earlier timestamp
2. ✅ `2025_11_23_073935_add_leadership_type_id_to_positions_table.php` - Add column existence check
3. ✅ `2025_12_14_052357_add_unique_non_org_leadership_constraint.php` - Add idempotent guards
4. ✅ `2025_12_14_052411_add_unique_org_leadership_constraint.php` - Add idempotent guards
5. ✅ `2025_12_14_072225_fix_student_leaderships_uniques_add_user_id.php` - Add constraint existence checks

### Medium Priority (Recommended)

6. ⚠️ `2025_11_23_074025_remove_unique_name_from_positions_add_composite_unique.php` - Add existence checks
7. ⚠️ All ALTER migrations - Add column existence checks before adding columns

### Low Priority (Best Practice)

8. 📝 Add explicit index names to all unnamed indexes
9. 📝 Standardize FK naming conventions

---

## 5. ADDITIONAL OBSERVATIONS

### Positive Findings

-   ✅ No truncate() calls in seeders
-   ✅ Migration `2025_11_23_120000_fix_student_leaderships_table.php` demonstrates proper index handling pattern
-   ✅ Consistent use of `cascade` and `nullOnDelete` for FK actions
-   ✅ Good use of named indexes in many places

### Technical Debt

-   Mixed timestamp format (2025_01 vs 2025_08)
-   Some migrations have guard checks, others don't (inconsistent pattern)
-   No automated testing for migration integrity

---

## Next Steps

1. Implement Fix #1 (rename enum migration) - **CRITICAL**
2. Create late FK migration template
3. Add idempotent guards to high-priority migrations
4. Review and test on fresh database
5. Document migration best practices for team

---

**Report Generated By**: MySQL Migration Integrity Audit Tool
**Date**: December 14, 2025
