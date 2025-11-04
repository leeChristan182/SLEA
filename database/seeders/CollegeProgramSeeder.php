<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CollegeProgram;

class CollegeProgramSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('college_programs')->insert([
            // COLLEGE OF APPLIED ECONOMICS
            ['college_name' => 'College of Applied Economics', 'program_name' => 'Bachelor of Science in Economics', 'major_name' => 'Development Economics', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Applied Economics', 'program_name' => 'Bachelor of Science in Economics', 'major_name' => 'Monetary and Financial Economics', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Applied Economics', 'program_name' => 'Bachelor of Science in Economics', 'major_name' => 'Resource and Environmental Economics', 'created_at' => now(), 'updated_at' => now()],

            // COLLEGE OF ARTS AND SCIENCES
            ['college_name' => 'College of Arts and Sciences', 'program_name' => 'Bachelor of Arts in Literature and Cultural Studies', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Arts and Sciences', 'program_name' => 'Bachelor of Arts in English Language', 'major_name' => 'Applied Linguistics', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Arts and Sciences', 'program_name' => 'Bachelor of Science in Biology', 'major_name' => 'Animal Biology', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Arts and Sciences', 'program_name' => 'Bachelor of Science in Biology', 'major_name' => 'Plant Biology', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Arts and Sciences', 'program_name' => 'Bachelor of Science in Mathematics', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Arts and Sciences', 'program_name' => 'Bachelor of Science in Statistics', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],

            // COLLEGE OF BUSINESS ADMINISTRATION
            ['college_name' => 'College of Business Administration', 'program_name' => 'Bachelor of Science in Business Administration', 'major_name' => 'Financial Management', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Business Administration', 'program_name' => 'Bachelor of Science in Hospitality Management', 'major_name' => 'Culinary Arts Management', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Business Administration', 'program_name' => 'Bachelor of Science in Hospitality Management', 'major_name' => 'Hotel Administration', 'created_at' => now(), 'updated_at' => now()],

            // COLLEGE OF EDUCATION
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Secondary Education', 'major_name' => 'English', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Secondary Education', 'major_name' => 'Filipino', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Secondary Education', 'major_name' => 'Mathematics', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Secondary Education', 'major_name' => 'Science', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Elementary Education', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Early Childhood Education', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Special Needs Education', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Physical Education', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technology and Livelihood Education', 'major_name' => 'Home Economics', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Automotive Technology', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Civil and Construction Technology', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Computer Systems Servicing', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Electrical Technology', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Electronics Technology', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Mechanical Technology', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Education', 'program_name' => 'Bachelor of Technical-Vocational Teacher Education', 'major_name' => 'Heating, Ventilating, and Air-conditioning Technology', 'created_at' => now(), 'updated_at' => now()],

            // COLLEGE OF ENGINEERING
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Agricultural and Biosystems Engineering', 'major_name' => 'Land and Water Resource Engineering', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Agricultural and Biosystems Engineering', 'major_name' => 'Machinery and Power Engineering', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Agricultural and Biosystems Engineering', 'major_name' => 'Process Engineering', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Agricultural and Biosystems Engineering', 'major_name' => 'Structures and Environment Engineering', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Civil Engineering', 'major_name' => 'Geotechnical', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Civil Engineering', 'major_name' => 'Structural', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Civil Engineering', 'major_name' => 'Transportation', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Electrical Engineering', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Electronics Engineering', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Geodetic Engineering', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Geology', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Mechanical Engineering', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Mining Engineering', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Engineering', 'program_name' => 'Bachelor of Science in Sanitary Engineering', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],

            // COLLEGE OF INFORMATION AND COMPUTING
            ['college_name' => 'College of Information and Computing', 'program_name' => 'Bachelor of Science in Information Technology', 'major_name' => 'Business Technology Management', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Information and Computing', 'program_name' => 'Bachelor of Science in Information Technology', 'major_name' => 'Information Security', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Information and Computing', 'program_name' => 'Bachelor of Science in Computer Science', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Information and Computing', 'program_name' => 'Bachelor of Library and Information Science', 'major_name' => null, 'created_at' => now(), 'updated_at' => now()],

            // COLLEGE OF TECHNOLOGY
            ['college_name' => 'College of Technology', 'program_name' => 'Bachelor of Science in Industrial Technology', 'major_name' => 'Electrical Technology', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Technology', 'program_name' => 'Bachelor of Science in Industrial Technology', 'major_name' => 'Electronics Technology', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Technology', 'program_name' => 'Bachelor of Science in Industrial Technology', 'major_name' => 'Mechanical Technology', 'created_at' => now(), 'updated_at' => now()],
            ['college_name' => 'College of Technology', 'program_name' => 'Bachelor of Science in Industrial Technology', 'major_name' => 'Automotive Technology', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
