# Laravel Migration Best Practices Checklist

Use this checklist when creating or reviewing migrations to ensure MySQL integrity.

---

## ✅ Before Creating a Migration

-   [ ] Verify the timestamp is appropriate (earlier migrations should have earlier timestamps)
-   [ ] Check if referenced tables already exist or will exist before this migration runs
-   [ ] Identify all foreign key dependencies

---

## ✅ When Creating Tables

-   [ ] Define table schema without foreign keys first
-   [ ] Add regular columns and indexes
-   [ ] Consider adding foreign keys in a separate "late FK" migration if:
    -   The referenced table might not exist yet
    -   The referenced table is an enum/lookup table
    -   The migration order is uncertain

### Example Pattern:

```php
// In table creation migration
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('role', 32);  // Column only, no FK yet
    $table->timestamps();
});

// In late FK migration (runs after all tables created)
if (!foreignKeyExists('users', 'users_role_foreign')) {
    Schema::table('users', function (Blueprint $table) {
        $table->foreign('role')->references('key')->on('user_roles');
    });
}
```

---

## ✅ When Adding Foreign Keys

-   [ ] Verify the referenced table exists or will exist before this migration
-   [ ] Specify `onDelete()` behavior (cascade, nullOnDelete, restrict)
-   [ ] Specify `onUpdate()` behavior if needed (cascadeOnUpdate, restrictOnUpdate)
-   [ ] Use descriptive constraint names for complex FKs
-   [ ] Add idempotent guard check

### FK Naming Convention:

```php
// Good: explicit name
$table->foreign('user_id', 'posts_user_id_foreign')
    ->references('id')
    ->on('users')
    ->onDelete('cascade');

// Also good: Laravel auto-naming
$table->foreignId('user_id')
    ->constrained('users')
    ->onDelete('cascade');
```

---

## ✅ When Altering Tables

-   [ ] Add column existence check before adding columns
-   [ ] Add FK existence check before adding foreign keys
-   [ ] Add index existence check before adding indexes
-   [ ] Add constraint existence check before dropping constraints

### Idempotent Guard Pattern:

```php
use Illuminate\Support\Facades\DB;

// Helper method (add to migration class)
private function columnExists(string $table, string $column): bool
{
    return Schema::hasColumn($table, $column);
}

private function foreignKeyExists(string $table, string $constraintName): bool
{
    $result = DB::select("
        SELECT CONSTRAINT_NAME
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = ?
        AND CONSTRAINT_NAME = ?
        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ", [$table, $constraintName]);

    return !empty($result);
}

private function indexExists(string $table, string $indexName): bool
{
    $result = DB::select("
        SELECT INDEX_NAME
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = ?
        AND INDEX_NAME = ?
    ", [$table, $indexName]);

    return !empty($result);
}

// Usage in up() method
public function up(): void
{
    if (!$this->columnExists('users', 'profile_completed')) {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('profile_completed')->default(false);
        });
    }
}
```

---

## ✅ When Dropping Constraints

-   [ ] Check if constraint exists before dropping
-   [ ] Verify no other constraints depend on this constraint
-   [ ] For unique indexes: check if any FK uses it as supporting index
-   [ ] Add standalone indexes BEFORE dropping unique constraints that FKs depend on

### Safe Constraint Drop Pattern:

```php
public function up(): void
{
    // Step 1: Add standalone indexes for FK support
    if (!$this->indexExists('student_leaderships', 'sl_user_id_idx')) {
        Schema::table('student_leaderships', function (Blueprint $table) {
            $table->index('user_id', 'sl_user_id_idx');
        });
    }

    // Step 2: Now safe to drop unique constraint
    if ($this->indexExists('student_leaderships', 'uniq_old_constraint')) {
        Schema::table('student_leaderships', function (Blueprint $table) {
            $table->dropUnique('uniq_old_constraint');
        });
    }

    // Step 3: Add new constraint
    if (!$this->indexExists('student_leaderships', 'uniq_new_constraint')) {
        Schema::table('student_leaderships', function (Blueprint $table) {
            $table->unique(['user_id', 'other_col'], 'uniq_new_constraint');
        });
    }
}
```

---

## ✅ When Creating Indexes

-   [ ] Use explicit names for important indexes
-   [ ] Use descriptive names that indicate purpose
-   [ ] Check for name conflicts with existing indexes
-   [ ] Consider if index should be unique

### Index Naming Convention:

```php
// Composite index with explicit name
$table->index(
    ['user_id', 'created_at'],
    'posts_user_created_idx'
);

// Unique constraint with explicit name
$table->unique(
    ['email', 'organization_id'],
    'users_email_org_unique'
);
```

---

## ✅ Migration File Organization

### Recommended Migration Order:

1. **Early (before tables)**: Enum/lookup tables

    - `xxxx_xx_xx_000000_create_enum_tables.php`

2. **Core**: Main entity tables (without FKs to enums)

    - `xxxx_xx_xx_xxxxxx_create_users_table.php`
    - `xxxx_xx_xx_xxxxxx_create_organizations_table.php`

3. **Relations**: Junction/pivot tables

    - `xxxx_xx_xx_xxxxxx_create_organization_user_table.php`

4. **Late**: Foreign key constraints
    - `xxxx_xx_xx_999999_add_enum_foreign_keys.php`
    - `xxxx_xx_xx_999999_add_cross_table_foreign_keys.php`

---

## ✅ Testing Checklist

After creating/modifying migrations:

-   [ ] Test fresh migration: `php artisan migrate:fresh`
-   [ ] Test idempotency: `php artisan migrate` (should show "Nothing to migrate")
-   [ ] Test rollback: `php artisan migrate:rollback --step=1`
-   [ ] Test re-migration: `php artisan migrate`
-   [ ] Test with seeded data: `php artisan migrate:fresh --seed`
-   [ ] Verify foreign key constraints work: Try inserting invalid FK values
-   [ ] Verify cascade deletes work if configured

---

## ✅ Common Pitfalls to Avoid

### ❌ DON'T:

```php
// Don't reference tables that don't exist yet
Schema::create('posts', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained('users');
    // ❌ What if users table migrates after posts?
});

// Don't drop indexes without checking FK dependencies
$table->dropUnique(['email']);
// ❌ FK might use this as supporting index

// Don't add columns without existence check in ALTER migration
Schema::table('users', function (Blueprint $table) {
    $table->string('phone');
    // ❌ Will fail if migration runs twice
});
```

### ✅ DO:

```php
// Do: Create column first, add FK later
Schema::create('posts', function (Blueprint $table) {
    $table->unsignedBigInteger('user_id'); // Column only
});

// Do: Add standalone index before dropping unique
$table->index('email', 'users_email_idx');
$table->dropUnique(['email']);

// Do: Check existence before adding
if (!Schema::hasColumn('users', 'phone')) {
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone');
    });
}
```

---

## 🔍 Code Review Checklist

When reviewing migration PRs, verify:

-   [ ] Timestamp is appropriate and doesn't conflict
-   [ ] All referenced tables exist or will exist before this migration
-   [ ] Foreign keys have proper `onDelete`/`onUpdate` actions
-   [ ] ALTER migrations have idempotent guards
-   [ ] Index/constraint drops are safe (no FK dependencies)
-   [ ] Migration is reversible (down() method works)
-   [ ] No hardcoded database names or schemas
-   [ ] Proper use of transaction-safe operations

---

## 📚 Reference

### Laravel Schema Builder Methods:

-   `Schema::create()` - Create new table
-   `Schema::table()` - Modify existing table
-   `Schema::hasTable()` - Check if table exists
-   `Schema::hasColumn()` - Check if column exists
-   `Schema::dropIfExists()` - Safe drop

### Foreign Key Methods:

-   `->foreign('column')` - Create FK
-   `->references('column')` - Referenced column
-   `->on('table')` - Referenced table
-   `->onDelete('action')` - Delete action (cascade, nullOnDelete, restrict)
-   `->onUpdate('action')` - Update action (cascade, restrictOnUpdate)
-   `->dropForeign(['column'])` - Drop FK by column
-   `->dropForeign('constraint_name')` - Drop FK by name

### Index Methods:

-   `->index('column')` - Create regular index
-   `->unique('column')` - Create unique constraint
-   `->dropIndex('index_name')` - Drop index
-   `->dropUnique('index_name')` - Drop unique constraint

---

## 🚀 Quick Reference: Common Scenarios

### Scenario 1: Adding a nullable FK to existing table

```php
public function up(): void
{
    if (!Schema::hasColumn('posts', 'category_id')) {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('user_id')
                ->constrained('categories')
                ->nullOnDelete();
        });
    }
}
```

### Scenario 2: Changing unique constraint composition

```php
public function up(): void
{
    // Add supporting indexes first
    if (!$this->indexExists('posts', 'posts_user_id_idx')) {
        Schema::table('posts', function (Blueprint $table) {
            $table->index('user_id', 'posts_user_id_idx');
        });
    }

    // Drop old unique
    if ($this->indexExists('posts', 'posts_slug_unique')) {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique('posts_slug_unique');
        });
    }

    // Add new composite unique
    if (!$this->indexExists('posts', 'posts_user_slug_unique')) {
        Schema::table('posts', function (Blueprint $table) {
            $table->unique(['user_id', 'slug'], 'posts_user_slug_unique');
        });
    }
}
```

### Scenario 3: Creating enum-referenced table

```php
// Option A: Don't add FK in creation
Schema::create('users', function (Blueprint $table) {
    $table->string('status', 32)->default('pending');
    // No FK here - will be added in late FK migration
});

// Option B: Check if enum table exists
if (Schema::hasTable('user_statuses')) {
    Schema::table('users', function (Blueprint $table) {
        $table->foreign('status')
            ->references('key')
            ->on('user_statuses');
    });
}
```

---

**Last Updated**: December 14, 2025  
**Version**: 1.0
