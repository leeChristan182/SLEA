<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Update existing status keys in student_academic table
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
        }

        // Now update the enum table if it exists
        if (DB::getSchemaBuilder()->hasTable('slea_application_statuses')) {
            DB::table('slea_application_statuses')->where('key', 'not_ready')->delete();
            DB::table('slea_application_statuses')->where('key', 'ready_for_assessor')->delete();
            DB::table('slea_application_statuses')->where('key', 'for_admin_review')->delete();
            DB::table('slea_application_statuses')->where('key', 'awarded')->delete();

            // Insert new status keys
            DB::table('slea_application_statuses')->insert([
                ['key' => 'incomplete'],
                ['key' => 'pending_assessor_evaluation'],
                ['key' => 'pending_administrative_validation'],
                ['key' => 'qualified'],
                // 'not_qualified' stays the same
            ]);
        }
    }

    public function down(): void
    {
        // Revert the changes
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
        }

        // Restore old enum keys if table exists
        if (DB::getSchemaBuilder()->hasTable('slea_application_statuses')) {
            DB::table('slea_application_statuses')->where('key', 'incomplete')->delete();
            DB::table('slea_application_statuses')->where('key', 'pending_assessor_evaluation')->delete();
            DB::table('slea_application_statuses')->where('key', 'pending_administrative_validation')->delete();
            DB::table('slea_application_statuses')->where('key', 'qualified')->delete();

            DB::table('slea_application_statuses')->insert([
                ['key' => 'not_ready'],
                ['key' => 'ready_for_assessor'],
                ['key' => 'for_admin_review'],
                ['key' => 'awarded'],
            ]);
        }
    }
};

        }

        // Now update the enum table if it exists
        if (DB::getSchemaBuilder()->hasTable('slea_application_statuses')) {
            DB::table('slea_application_statuses')->where('key', 'not_ready')->delete();
            DB::table('slea_application_statuses')->where('key', 'ready_for_assessor')->delete();
            DB::table('slea_application_statuses')->where('key', 'for_admin_review')->delete();
            DB::table('slea_application_statuses')->where('key', 'awarded')->delete();

            // Insert new status keys
            DB::table('slea_application_statuses')->insert([
                ['key' => 'incomplete'],
                ['key' => 'pending_assessor_evaluation'],
                ['key' => 'pending_administrative_validation'],
                ['key' => 'qualified'],
                // 'not_qualified' stays the same
            ]);
        }
    }

    public function down(): void
    {
        // Revert the changes
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
        }

        // Restore old enum keys if table exists
        if (DB::getSchemaBuilder()->hasTable('slea_application_statuses')) {
            DB::table('slea_application_statuses')->where('key', 'incomplete')->delete();
            DB::table('slea_application_statuses')->where('key', 'pending_assessor_evaluation')->delete();
            DB::table('slea_application_statuses')->where('key', 'pending_administrative_validation')->delete();
            DB::table('slea_application_statuses')->where('key', 'qualified')->delete();

            DB::table('slea_application_statuses')->insert([
                ['key' => 'not_ready'],
                ['key' => 'ready_for_assessor'],
                ['key' => 'for_admin_review'],
                ['key' => 'awarded'],
            ]);
        }
    }
};
