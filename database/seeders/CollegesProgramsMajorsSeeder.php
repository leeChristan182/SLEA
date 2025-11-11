<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CollegesProgramsMajorsSeeder extends Seeder
{
    public function run(): void
    {
        // Your same dataset (kept verbatim)
        $rows = [
            // COLLEGE OF APPLIED ECONOMICS
            ['college_name' => 'College of Applied Economics', 'program_name' => 'Bachelor of Science in Economics', 'major_name' => 'Development Economics'],
            ['college_name' => 'College of Applied Economics', 'program_name' => 'Bachelor of Science in Economics', 'major_name' => 'Monetary and Financial Economics'],
            ['college_name' => 'College of Applied Economics', 'program_name' => 'Bachelor of Science in Economics', 'major_name' => 'Resource and Environmental Economics'],

            // COLLEGE OF ARTS AND SCIENCES
            ['college_name' => 'College of Arts and Sciences', 'program_name' => 'Bachelor of Arts in Literature and Cultural Studies', 'major_name' => null],
            ['college_name' => 'College of Arts and Sciences', 'program_name' => 'Bachelor of Arts in English Language', 'major_name' => 'Applied Linguistics'],
            ['college_name' => 'College of Arts and Sciences', 'program_name' => 'Bachelor of Science in Biology', 'major_name' => 'Animal Biology'],
            ['college_name' => 'College of Arts and Sciences', 'program_name' => 'Bachelor of Science in Biology', 'major_name' => 'Plant Biology'],
            ['college_name' => 'College of Arts and Sciences', 'program_name' => 'Bachelor of Science in Mathematics', 'major_name' => null],
            ['college_name' => 'College of Arts and Sciences', 'program_name' => 'Bachelor of Science in Statistics', 'major_name' => null],

            // COLLEGE OF BUSINESS ADMINISTRATION
            ['college_name' => 'College of Business Administration', 'program_name' => 'Bachelor of Science in Business Administration', 'major_name' => 'Financial Management'],
            ['college_name' => 'College of Business Administration', 'program_name' => 'Bachelor of Science in Hospitality Management', 'major_name' => 'Culinary Arts Management'],
            ['college_name' => 'College of Business Administration', 'program_name' => 'Bachelor of Science in Hospitality Management', 'major_name' => 'Hotel Administration'],

            // COLLEGE OF EDUCATION
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Secondary Education', 'major_name' => 'English'],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Secondary Education', 'major_name' => 'Filipino'],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Secondary Education', 'major_name' => 'Mathematics'],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Secondary Education', 'major_name' => 'Science'],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Elementary Education', 'major_name' => null],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Early Childhood Education', 'major_name' => null],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Special Needs Education', 'major_name' => null],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Physical Education', 'major_name' => null],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technology and Livelihood Education', 'major_name' => 'Home Economics'],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Automotive Technology'],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Civil and Construction Technology'],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Computer Systems Servicing'],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Electrical Technology'],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Electronics Technology'],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Mechanical Technology'],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Heating, Ventilating, and Air-conditioning Technology'],

            // COLLEGE OF ENGINEERING
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Agricultural and Biosystems Engineering', 'major_name' => 'Land and Water Resource Engineering'],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Agricultural and Biosystems Engineering', 'major_name' => 'Machinery and Power Engineering'],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Agricultural and Biosystems Engineering', 'major_name' => 'Process Engineering'],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Agricultural and Biosystems Engineering', 'major_name' => 'Structures and Environment Engineering'],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Civil Engineering', 'major_name' => 'Geotechnical'],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Civil Engineering', 'major_name' => 'Structural'],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Civil Engineering', 'major_name' => 'Transportation'],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Electrical Engineering', 'major_name' => null],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Electronics Engineering', 'major_name' => null],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Geodetic Engineering', 'major_name' => null],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Geology', 'major_name' => null],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Mechanical Engineering', 'major_name' => null],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Mining Engineering', 'major_name' => null],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Sanitary Engineering', 'major_name' => null],

            // COLLEGE OF INFORMATION AND COMPUTING
            ['college_name' => 'College of Information and Computing', 'program_name' => 'Bachelor of Science in Information Technology', 'major_name' => 'Business Technology Management'],
            ['college_name' => 'College of Information and Computing', 'program_name' => 'Bachelor of Science in Information Technology', 'major_name' => 'Information Security'],
            ['college_name' => 'College of Information and Computing', 'program_name' => 'Bachelor of Science in Computer Science', 'major_name' => null],
            ['college_name' => 'College of Information and Computing', 'program_name' => 'Bachelor of Library and Information Science', 'major_name' => null],

            // COLLEGE OF TECHNOLOGY
            ['college_name' => 'College of Technology', 'program_name' => 'Bachelor of Science in Industrial Technology', 'major_name' => 'Electrical Technology'],
            ['college_name' => 'College of Technology', 'program_name' => 'Bachelor of Science in Industrial Technology', 'major_name' => 'Electronics Technology'],
            ['college_name' => 'College of Technology', 'program_name' => 'Bachelor of Science in Industrial Technology', 'major_name' => 'Mechanical Technology'],
            ['college_name' => 'College of Technology', 'program_name' => 'Bachelor of Science in Industrial Technology', 'major_name' => 'Automotive Technology'],
        ];

        DB::transaction(function () use ($rows) {
            $now = now();

            // Small caches to minimize DB round trips
            // colleges: name => ['id' => int, 'code' => ?string]
            $collegeCache = [];
            // programs: "college_id|program_name" => ['id' => int, 'code' => ?string]
            $programCache = [];
            // track taken codes
            $takenCollegeCodes = [];
            // per-college taken program codes
            $takenProgramCodesByCollege = [];

            // Preload existing colleges
            foreach (DB::table('colleges')->select('id', 'name', 'code')->get() as $c) {
                $collegeCache[$c->name] = ['id' => $c->id, 'code' => $c->code];
                if (!empty($c->code)) {
                    $takenCollegeCodes[] = $c->code;
                }
            }

            // Helper: make an initialism like "College of Arts and Sciences" -> "CAS"
            $makeCode = function (string $label, array $taken): string {
                $stop = ['of', 'and', 'the', 'in', 'for', '&'];
                $clean = Str::of($label)->replaceMatches('/[^A-Za-z0-9\s]/', '');
                $words = preg_split('/\s+/', (string) $clean, -1, PREG_SPLIT_NO_EMPTY);

                $letters = [];
                foreach ($words as $w) {
                    if (!in_array(strtolower($w), $stop, true)) {
                        $letters[] = Str::upper(Str::substr($w, 0, 1));
                    }
                }

                // Fallback if everything was a stopword
                $base = implode('', $letters) ?: Str::upper(Str::substr($clean, 0, 3));
                $base = Str::limit($base, 10, ''); // keep tidy

                // Ensure uniqueness with numeric suffix
                $code = $base;
                $n = 2;
                while (in_array($code, $taken, true)) {
                    $code = $base . $n++;
                }
                return $code;
            };

            foreach ($rows as $r) {
                // === 1) College ===
                if (!isset($collegeCache[$r['college_name']])) {
                    // create with generated code
                    $code = $makeCode($r['college_name'], $takenCollegeCodes);

                    DB::table('colleges')->updateOrInsert(
                        ['name' => $r['college_name']],
                        ['code' => $code, 'updated_at' => $now, 'created_at' => $now]
                    );

                    $collegeId = DB::table('colleges')->where('name', $r['college_name'])->value('id');
                    $collegeCache[$r['college_name']] = ['id' => $collegeId, 'code' => $code];
                    $takenCollegeCodes[] = $code;
                }

                $collegeId = $collegeCache[$r['college_name']]['id'];

                // initialize per-college taken program codes (from DB on first use)
                if (!isset($takenProgramCodesByCollege[$collegeId])) {
                    $takenProgramCodesByCollege[$collegeId] = DB::table('programs')
                        ->where('college_id', $collegeId)
                        ->whereNotNull('code')
                        ->pluck('code')
                        ->all();
                }

                // === 2) Program (scoped by college) ===
                $programKey = $collegeId . '|' . $r['program_name'];
                if (!isset($programCache[$programKey])) {
                    // Create program if missing; generate code unique within the college
                    $code = $makeCode($r['program_name'], $takenProgramCodesByCollege[$collegeId]);

                    DB::table('programs')->updateOrInsert(
                        ['college_id' => $collegeId, 'name' => $r['program_name']],
                        ['code' => $code, 'updated_at' => $now, 'created_at' => $now]
                    );

                    $programId = DB::table('programs')
                        ->where('college_id', $collegeId)
                        ->where('name', $r['program_name'])
                        ->value('id');

                    $programCache[$programKey] = ['id' => $programId, 'code' => $code];
                    $takenProgramCodesByCollege[$collegeId][] = $code;
                }

                $programId = $programCache[$programKey]['id'];

                // === 3) Major (optional; no codes unless you add a column) ===
                if (!empty($r['major_name'])) {
                    DB::table('majors')->updateOrInsert(
                        ['program_id' => $programId, 'name' => $r['major_name']],
                        ['updated_at' => $now, 'created_at' => $now]
                    );
                }
            }
        });
    }
}
