<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Map old values in student_academic → latest scheme
        if (DB::getSchemaBuilder()->hasTable('student_academic')) {
            DB::table('student_academic')
                ->where('slea_application_status', 'not_ready')
                ->update(['slea_application_status' => 'incomplete']);

            DB::table('student_academic')
                ->where('slea_application_status', 'ready_for_assessor')
                ->update(['slea_application_status' => 'pending_assessor_evaluation']);

            DB::table('student_academic')
                ->where('slea_application_status', 'for_admin_review')
                ->update(['slea_application_status' => 'pending_administrative_validation']);

            DB::table('student_academic')
                ->where('slea_application_status', 'awarded')
                ->update(['slea_application_status' => 'qualified']);

            // Old "rejected" now becomes "not_qualified"
            DB::table('student_academic')
                ->where('slea_application_status', 'rejected')
                ->update(['slea_application_status' => 'not_qualified']);
        }

        // 2) Clean up old enum keys (we now fully adhere to the new ones)
        if (DB::getSchemaBuilder()->hasTable('slea_application_statuses')) {
            DB::table('slea_application_statuses')
                ->whereIn('key', [
                    'not_ready',
                    'ready_for_assessor',
                    'for_admin_review',
                    'awarded',
                    'rejected',
                ])->delete();
        }
    }

    public function down(): void
    {
        // Only needed if you ever rollback, but keep it for symmetry

        if (DB::getSchemaBuilder()->hasTable('student_academic')) {
            DB::table('student_academic')
                ->where('slea_application_status', 'incomplete')
                ->update(['slea_application_status' => 'not_ready']);

            DB::table('student_academic')
                ->where('slea_application_status', 'pending_assessor_evaluation')
                ->update(['slea_application_status' => 'ready_for_assessor']);

            DB::table('student_academic')
                ->where('slea_application_status', 'pending_administrative_validation')
                ->update(['slea_application_status' => 'for_admin_review']);

            DB::table('student_academic')
                ->where('slea_application_status', 'qualified')
                ->update(['slea_application_status' => 'awarded']);

            DB::table('student_academic')
                ->where('slea_application_status', 'not_qualified')
                ->update(['slea_application_status' => 'rejected']);
        }

        if (DB::getSchemaBuilder()->hasTable('slea_application_statuses')) {
            $oldStatuses = [
                ['key' => 'not_ready'],
                ['key' => 'ready_for_assessor'],
                ['key' => 'for_admin_review'],
                ['key' => 'awarded'],
                ['key' => 'rejected'],
            ];

            $existing = DB::table('slea_application_statuses')
                ->whereIn('key', array_column($oldStatuses, 'key'))
                ->pluck('key')
                ->all();

            $toInsert = array_values(array_filter($oldStatuses, function ($row) use ($existing) {
                return !in_array($row['key'], $existing, true);
            }));

            if (!empty($toInsert)) {
                DB::table('slea_application_statuses')->insert($toInsert);
            }
        }
    }
};
