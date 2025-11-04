<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RubricSectionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rubric_sections')->truncate();

        DB::table('rubric_sections')->insert([
            // ---------------- Leadership ----------------
            [
                'category_id' => 1,
                'title' => 'A. Campus-Based Student Government',
                'evidence' => " Oath of Office\n Certification from the Organization Adviser/ Immediate Supervisor",
                'max_points' => 5,
                'notes' => " Credits are given for every academic year of full-time service.\n For the evidence of A. Campus Based: Certification from the Organization/ Council: The document must be signed by the adviser designated during their term as officer that year.",
                'order_no' => 1,
            ],
            [
                'category_id' => 1,
                'title' => 'B. Designation in Special Orders / Office Orders',
                'evidence' => " Certification from Chairperson/Committee\n Accomplishment Report",
                'max_points' => 5,
                'notes' => " Credits are given for every academic year of full-time service.",
                'order_no' => 2,
            ],
            [
                'category_id' => 1,
                'title' => 'C. Community-Based',
                'evidence' => " Oath of Office\n Certification from the Organization Adviser/Immediate Supervisor",
                'max_points' => 5,
                'notes' => " Credits are given for every academic year of full-time service.\n For C. Community Based: In cases where two or more positions are being held in a year, the applicant/nominee shall choose whichever is higher.",
                'order_no' => 3,
            ],
            [
                'category_id' => 1,
                'title' => 'D. Leadership Training / Seminars / Conferences Attended (max 5 points)',
                'evidence' => " Certificate of Attendance / Appreciation / Participation",
                'max_points' => 5,
                'notes' => " Credits are given for every academic year of full-time service.\n For D. Leadership Training/Seminars/ Conferences Attended: less than a day (half day) cannot be considered as seminar/training.\n Leadership seminars/trainings/conferences that are duly approved/endorsed/recognized/authorized by CHED, National Youth Commission, Department of Interior and Local Government, USeP, Local Government Units, and other SEC-registered organizations shall be accepted and given credit.",
                'order_no' => 4,
            ],

            // ---------------- Academic ----------------
            [
                'category_id' => 2,
                'title' => 'General Weighted Average (1st Semester)',
                'evidence' => " Certificate of Grades (Portal Generated) from first year to 1st Semester of the current Academic Year",
                'max_points' => 20,
                'notes' => " No Failing Grade/INC – 20 pts\n With INC – 17 pts\n With Failing Grade – 15 pts\n With Failing Grade & INC – 10 pts",
                'order_no' => 1,
            ],

            // ---------------- Awards ----------------
            [
                'category_id' => 3,
                'title' => 'Awards / Recognition Received',
                'evidence' => " Certificate of Recognition\n Selection Criteria/Guidelines from the award-giving body",
                'max_points' => 20,
                'notes' => " Only the top 3 awards or those with a selection process are credited.\n International – 20 pts\n National – 15 pts\n Regional – 10 pts\n Local – 5 pts",
                'order_no' => 1,
            ],

            // ---------------- Community Involvement ----------------
            [
                'category_id' => 4,
                'title' => 'Community Involvement',
                'evidence' => " Certificate of Recognition/Appreciation\n Activity Program/Invitation\n Photo Documentation",
                'max_points' => 20,
                'notes' => " Scoring per day:\n  - International – 0.8/day\n  - National – 0.6/day\n  - Regional – 0.4/day\n  - Local – 0.2/day\n Maximum of 20 points total.",
                'order_no' => 1,
            ],

            // ---------------- Conduct ----------------
            [
                'category_id' => 5,
                'title' => 'Good Conduct',
                'evidence' => " Taken charge of by the Office of Student Affairs and Services (OSAS)",
                'max_points' => 20,
                'notes' => " Deductions: -1.0 point for minor infractions; -2.0 points for major infractions.\n Based on OSAS official behavioral record.",
                'order_no' => 1,
            ],
        ]);
    }
}
