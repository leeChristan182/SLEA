<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // Map existing clusters by NAME -> ID (from your ClusterSeeder)
        $clusterIds = DB::table('clusters')->pluck('id', 'name');

        // Helper to resolve cluster id by name
        $cid = function (string $clusterName) use ($clusterIds): ?int {
            return $clusterIds[$clusterName] ?? null;
        };

        // ---- Master list (your original list + councils) --------------------
        $orgs = [
            // Councils / governance
            ['name' => 'University Student Government (USG)', 'cluster' => 'Socio-Civic Cluster', 'domain' => 'campus', 'scope' => 'institutional'],
            ['name' => 'Obrero Student Council (OSC)',        'cluster' => 'Socio-Civic Cluster', 'domain' => 'campus', 'scope' => 'institutional'],
            ['name' => 'Local Council (LC)',                  'cluster' => 'Socio-Civic Cluster', 'domain' => 'college', 'scope' => 'institutional'],
            ['name' => 'League of Class Mayors (LCM)',        'cluster' => 'Socio-Civic Cluster', 'domain' => 'college', 'scope' => 'institutional'],
            ['name' => 'Council of Clubs and Organizations (CCO)', 'cluster' => 'Socio-Civic Cluster', 'domain' => 'campus', 'scope' => 'institutional'],
            ['name' => 'Local Government Unit (LGU)',         'cluster' => 'Socio-Civic Cluster', 'domain' => 'lgu',    'scope' => 'local'],

            // ---------------- Academic Cluster (Alphabetically Ordered) ----------------
            ['name' => 'Applied Economics Society (AEconS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Association of Automotive Technology Students (AATS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Association of Biology Students (ABS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Association of Early Childhood Education Students (AECEds)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Association of Electrical Technology Students (ASSETS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Association of Electronics Engineering Students (AECES)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Association of Geology Students (AGeoS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Association of IT Innovator Students (AITIS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Association of Mechanical Technology Students (AMeTS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Association of Technical-Vocational Education Students (ATVEdS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Civil Engineering Students Association (CESA)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Computer Science Society (CSS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Financial Management Students Society (FiMSS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Frontiers of Electronics Enthusiasts and Learners Society (FEELS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Geodetic Engineering Students Society (GESS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Guild of English Major Students (GEMS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Guild of Livelihood and Technology Education Students (GLiTES)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Guild of Young Information Specialist (GYIS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'HANDURAWAN', 'cluster' => 'Academic Cluster'],
            ['name' => 'Junior Philippine Institute of Accountants (JPIA)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Language Student Society (LSS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Mathematics Educators Society (MathEds)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Mathematics Students Organization (MSO)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Mining Engineering Society (MinES)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Philippine Society of Sanitary Engineers - Student Chapter (PSSE-SC)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Samahan ng Maka-Filipinong USePiano (SMFU)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Science Majors Society (SMS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Senior Students Organization (SSO)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Society of Electrical Engineering (SEES)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Society of Elementary Education Students (SEEdS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Society of Hospitality Management Students (SoHMS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Students\' Association of Mechanical Engineering (SAME)', 'cluster' => 'Academic Cluster'],
            ['name' => 'United Physical Education Major Students (UPEMS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'United Special Education Students (USpEdS)', 'cluster' => 'Academic Cluster'],
            ['name' => 'United Statistics Students Organization (USSO)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Young Entrepreneurs Society (YES)', 'cluster' => 'Academic Cluster'],
            ['name' => 'Young Marketers Society (YMS)', 'cluster' => 'Academic Cluster'],

            // ---------------- Campus Ministry Cluster (Alphabetically Ordered) ----------------
            ['name' => 'Basic Ecclesial Community (BEC)', 'cluster' => 'Campus Ministry Cluster'],
            ['name' => 'Catholic Faith Defenders (CFD)', 'cluster' => 'Campus Ministry Cluster'],
            ['name' => 'CFC Youth For Christ (YFC)', 'cluster' => 'Campus Ministry Cluster'],
            ['name' => 'Jesus Disciple Movement World Community (JWC)', 'cluster' => 'Campus Ministry Cluster'],
            ['name' => 'Lakas-Angkan Youth Fellowship (LAYF)', 'cluster' => 'Campus Ministry Cluster'],
            ['name' => 'Movement of the Adventist Students - Adventist Ministry to College and University Students (MAS-AMICUS)', 'cluster' => 'Campus Ministry Cluster'],
            ['name' => 'Philippine Student Alliance Lay Movement (PSALM)', 'cluster' => 'Campus Ministry Cluster'],
            ['name' => 'USeP-CRU', 'cluster' => 'Campus Ministry Cluster'],
            ['name' => 'USeP-Islamic Student Council (ISC)', 'cluster' => 'Campus Ministry Cluster'],

            // ---------------- Culture & Arts Cluster ----------------
            ['name' => 'LIKHA Production', 'cluster' => 'Culture and Arts Cluster'],

            // ---------------- Socio-Civic Cluster (Alphabetically Ordered) ----------------
            ['name' => 'Developer Student Community - USeP Obrero (DSC)', 'cluster' => 'Socio-Civic Cluster'],
            ['name' => 'Friends of Philippine Eagle (FPE)', 'cluster' => 'Socio-Civic Cluster'],
            ['name' => 'PARAGON', 'cluster' => 'Socio-Civic Cluster'],
            ['name' => 'Paths - Campus Inclusiveness Student Organization (PATHS)', 'cluster' => 'Socio-Civic Cluster'],
            ['name' => 'Society of Peer Facilitators (SPF)', 'cluster' => 'Socio-Civic Cluster'],
            ['name' => 'The Yanong Agila Organization (YANO)', 'cluster' => 'Socio-Civic Cluster'],
            ['name' => 'USeP-College Red Cross Youth (CRCY)', 'cluster' => 'Socio-Civic Cluster'],
            ['name' => 'USeP DOST-SEI Alliance of Agham Scholars (DOST-SEI)', 'cluster' => 'Socio-Civic Cluster'],
            ['name' => 'USeP-Junior JCI Club (JJC)', 'cluster' => 'Socio-Civic Cluster'],
            ['name' => 'USeP-LADANAG', 'cluster' => 'Socio-Civic Cluster'],
            ['name' => 'Yanode Blockchain Club (YBC)', 'cluster' => 'Socio-Civic Cluster'],
            ['name' => 'Youth Mappers Guild (YMG)', 'cluster' => 'Socio-Civic Cluster'],

            // ---------------- Sports Cluster ----------------
            ['name' => 'University Student-Athletes Organization (USAO)', 'cluster' => 'Sports Cluster'],
            ['name' => 'USeP-Agila E-Sports', 'cluster' => 'Sports Cluster'],
        ];

        // Delete the incorrect AITS entry before seeding
        DB::table('organizations')->where('name', 'Association of IT Innovators Students (AITS)')->delete();

        foreach ($orgs as $o) {
            $domain = $o['domain']   ?? 'campus';          // sensible defaults
            $scope  = $o['scope']    ?? 'institutional';
            $slug   = Str::slug($o['name']);

            // Check if an organization with the same slug exists but different name
            $existing = DB::table('organizations')->where('slug', $slug)->first();
            if ($existing && $existing->name !== $o['name']) {
                // Update the existing entry with the new name
                DB::table('organizations')
                    ->where('slug', $slug)
                    ->update([
                        'name' => $o['name'],
                        'cluster_id'  => $cid($o['cluster'] ?? '') ?: null,
                        'parent_id'   => null,
                        'domain'      => $domain,
                        'scope_level' => $scope,
                        'is_active'   => true,
                        'updated_at'  => now(),
                    ]);
            } else {
                // Use updateOrInsert for normal cases
                DB::table('organizations')->updateOrInsert(
                    ['name' => $o['name']],
                    [
                        'slug'        => $slug,
                        'cluster_id'  => $cid($o['cluster'] ?? '') ?: null,
                        'parent_id'   => null,
                        'domain'      => $domain,
                        'scope_level' => $scope,
                        'is_active'   => true,
                        'updated_at'  => now(),
                        'created_at'  => now(),
                    ]
                );
            }
        }

        $this->command?->info('✅ Organizations seeded/updated.');
    }
}
