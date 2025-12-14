<?php

// Analysis script for Laravel migrations
$migrationsDir = __DIR__ . '/database/migrations';
$files = glob($migrationsDir . '/*.php');
sort($files);

$analysis = [
    'tables' => [],          // table_name => migration_file
    'foreign_keys' => [],    // [migration_file, from_table, column, to_table, to_column]
    'indexes' => [],         // [migration_file, table, index_name, type]
    'drops' => [],           // [migration_file, table, type, name]
    'alters' => []           // [migration_file, table, action]
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $filename = basename($file);

    // Extract table creations
    if (preg_match_all('/Schema::create\([\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
        foreach ($matches[1] as $table) {
            $analysis['tables'][$table] = $filename;
        }
    }

    // Extract table alterations
    if (preg_match_all('/Schema::table\([\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
        foreach ($matches[1] as $table) {
            $analysis['alters'][] = [$filename, $table];
        }
    }

    // Extract foreign keys - various patterns
    // Pattern 1: ->foreign('column')->references('id')->on('table')
    if (preg_match_all('/\-\>foreign\([\'"]([^\'"]+)[\'"]\)\s*\-\>references\([\'"]([^\'"]+)[\'"]\)\s*\-\>on\([\'"]([^\'"]+)[\'"]\)/i', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $analysis['foreign_keys'][] = [
                'migration' => $filename,
                'from_table' => '?', // Will be inferred
                'column' => $match[1],
                'to_table' => $match[3],
                'to_column' => $match[2]
            ];
        }
    }

    // Pattern 2: ->foreignId('user_id')->constrained('users')
    if (preg_match_all('/\-\>foreignId\([\'"]([^\'"]+)[\'"]\)\s*\-\>constrained\(\s*[\'"]?([^\'")\s]+)?[\'"]?\s*\)/i', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $column = $match[1];
            // If constrained() has argument, use it; otherwise infer from column name
            $toTable = !empty($match[2]) ? $match[2] : preg_replace('/_id$/', '', $column);
            $analysis['foreign_keys'][] = [
                'migration' => $filename,
                'from_table' => '?',
                'column' => $column,
                'to_table' => $toTable,
                'to_column' => 'id'
            ];
        }
    }

    // Extract index operations
    if (preg_match_all('/\-\>(unique|index)\([\'"]?([^\'")\s]+)?[\'"]?\s*,\s*[\'"]([^\'"]+)[\'"]\)/i', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $analysis['indexes'][] = [
                'migration' => $filename,
                'table' => '?',
                'name' => $match[3],
                'type' => $match[1]
            ];
        }
    }

    // Extract drop operations
    if (preg_match_all('/\-\>drop(Foreign|Index|Unique)\([\'"]([^\'"]+)[\'"]\)/i', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $analysis['drops'][] = [
                'migration' => $filename,
                'table' => '?',
                'type' => strtolower($match[1]),
                'name' => $match[2]
            ];
        }
    }
}

// Output as JSON
echo json_encode($analysis, JSON_PRETTY_PRINT);
