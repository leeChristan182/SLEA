<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationSeeder extends Seeder
{
    public function run()
    {
        $organizations = [
            // Academic Cluster
            'Applied Economics Society (AEconS)',
            'Association of Biology Students (ABS)',
            'Association of Early Childhood Education Students (AECEds)',
            'Association of Electrical Technology Students (ASSETS)',
            'Association of Electronics Engineering Students (AECES)',
            'Association of Geology Students (AGeoS)',
            'Association of IT Innovators Students (AITS)',
            'Association of Mechanical Technology Students (AMeTS)',
            'Association of Technical-Vocational Education Students (ATVEdS)',
            'Civil Engineering Students Association (CESA)',
            'Computer Science Society (CSS)',
            'Financial Management Students Society (FiMSS)',
            'Frontiers of Electronics Enthusiasts and Learners Society (FEELS)',
            'Geodetic Engineering Students Society (GESS)',
            'Guild of English Major Students (GEMS)',
            'Guild of Livelihood and Technology Education Students (GLiTES)',
            'Guild of Young Information Specialist (GYIS)',
            'HANDURAWAN',
            'Junior Philippine Institute of Accountants (JPIA)',
            'Language Student Society (LSS)',
            'Mathematics Educators Society (MathEds)',
            'Mathematics Students Organization (MSO)',
            'Mining Engineering Society (MinES)',
            'Philippine Society of Sanitary Engineers - Student Chapter (PSSE-SC)',
            'Samahan ng Maka-Filipinong USePiano (SMFU)',
            'Science Majors Society (SMS)',
            'Senior Students Organization (SSO)',
            'Society of Electrical Engineering (SEES)',
            'Society of Elementary Education Students (SEEdS)',
            'Society of Hospitality Management Students (SoHMS)',
            'Students’ Association of Mechanical Engineering (SAME)',
            'United Physical Education Major Students (UPEMS)',
            'United Special Education Students (USpEdS)',
            'United Statistics Students Organization (USSO)',
            'Young Entrepreneurs Society (YES)',
            'Young Marketers Society (YMS)',
            'Association of Automotive Technology Students (AATS)',

            // Campus Ministry Cluster
            'Basic Ecclesial Community (BEC)',
            'CFC Youth For Christ (YFC)',
            'Catholic Faith Defenders (CFD)',
            'Lakas-Angkan Youth Fellowship (LAYF)',
            'Movement of the Adventist Students - Adventist Ministry to College and University Students (MAS-AMICUS)',
            'Philippine Student Alliance Lay Movement (PSALM)',
            'USeP-CRU',
            'Jesus Disciple Movement World Community (JWC)',
            'USeP-Islamic Student Council (ISC)',

            // Culture & Arts Cluster
            'LIKHA Production',

            // Socio-Civic Cluster
            'USeP-College Red Cross Youth (CRCY)',
            'Developer Student Community - USeP Obrero (DSC)',
            'USeP DOST-SEI Alliance of Agham Scholars (DOST-SEI)',
            'USeP-Junior JCI Club (JJC)',
            'Society of Peer Facilitators (SPF)',
            'Friends of Philippine Eagle (FPE)',
            'The Yanong Agila Organization (YANO)',
            'PARAGON',
            'USeP-LADANAG',
            'Yanode Blockchain Club (YBC)',
            'Paths - Campus Inclusiveness Student Organization (PATHS)',
            'Youth Mappers Guild (YMG)',

            // Sports Cluster
            'University Student-Athletes Organization (USAO)',
            'USeP-Agila E-Sports',
        ];

        foreach ($organizations as $org) {
            DB::table('organizations')->insert([
                'name' => $org,
            ]);
        }
    }
}
