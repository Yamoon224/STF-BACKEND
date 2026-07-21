<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Badge;
use App\Models\Certificate;
use App\Models\CmsPage;
use App\Models\Cohort;
use App\Models\Conversation;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\Experiment;
use App\Models\Faq;
use App\Models\Group;
use App\Models\Level;
use App\Models\LiveSession;
use App\Models\MenteeProfile;
use App\Models\MentorProfile;
use App\Models\MentorshipPairing;
use App\Models\MentorshipSession;
use App\Models\Module;
use App\Models\ModuleProgress;
use App\Models\Program;
use App\Models\Project;
use App\Models\Report;
use App\Models\Subject;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Mirrors apps/site & apps/backoffice mock-data.ts so the frontends have
 * realistic data to switch to once wired to this API.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administratrice STF',
            'email' => 'admin@stf-organisation.org',
            'password' => 'password',
            'status' => 'active',
            'country' => 'Sénégal',
        ]);
        $admin->assignRole('admin');

        $staff = User::create([
            'name' => 'Collaboratrice STF',
            'email' => 'staff@stf-organisation.org',
            'password' => 'password',
            'status' => 'active',
            'country' => 'Sénégal',
        ]);
        $staff->assignRole('staff');

        // --- Programs ---------------------------------------------------
        // Les 4 programmes phares réels de STF (cf. bilan 2016-2024). Les clés de
        // tableau ci-dessous restent celles d'origine : elles ne sont que des
        // identifiants PHP internes utilisés plus bas pour lier cohortes, modules,
        // témoignages, etc. — elles n'apparaissent jamais côté site.
        $programs = [
            'mentorat-stim' => Program::create([
                'name' => "Inspir'STEM ELLES",
                'slug' => 'inspir-stem-elles',
                'audience' => 'Collège · Lycée · Université',
                'description' => "Programme phare de STF lancé en 2020 : sensibilisation, mentorat et accompagnement individuel des filles vers les filières scientifiques, technologiques, d'ingénierie et mathématiques (STIM).",
                'color' => 'blue',
                'status' => 'en_cours',
                'cycle_start' => '2026-01-01',
                'cycle_end' => '2026-12-31',
            ]),
            'decouverte-primaire' => Program::create([
                'name' => 'STEM Challenge',
                'slug' => 'stem-challenge',
                'audience' => 'Collège · Lycée · Université',
                'description' => 'Un concours qui met au défi les filles et jeunes femmes à travers des projets scientifiques et technologiques innovants, pour révéler et récompenser les talents STIM.',
                'color' => 'orange',
                'status' => 'en_cours',
                'cycle_start' => '2026-02-01',
                'cycle_end' => '2026-06-30',
            ]),
            'campus-numerique' => Program::create([
                'name' => "Empow'Her Science Expo",
                'slug' => 'empowher-science-expo',
                'audience' => 'Lycée · Université · Grand public',
                'description' => "La foire scientifique annuelle de STF : expositions, panels et sessions de mentorat pour mettre en lumière les réalisations des jeunes femmes en sciences. La 2e édition (novembre 2024) a réuni plus de 1500 visiteurs et 15 exposantes.",
                'color' => 'green',
                'status' => 'en_cours',
                'cycle_start' => '2026-03-01',
                'cycle_end' => '2026-08-31',
            ]),
            'leadership-jeunes-femmes' => Program::create([
                'name' => 'Woman Impact Science Hub (WISH)',
                'slug' => 'wish',
                'audience' => 'Université · Entrepreneuses',
                'description' => "Un incubateur qui accompagne des projets portés par des femmes dans les STIM — agriculture durable, énergies renouvelables, technologies — présentés chaque année à l'Empow'Her Science Expo.",
                'color' => 'blue',
                'status' => 'a_venir',
                'cycle_start' => '2026-09-01',
                'cycle_end' => '2026-12-31',
            ]),
        ];

        $cohortStim = Cohort::create([
            'program_id' => $programs['mentorat-stim']->id,
            'name' => 'Cohorte Lycée 2026',
            'start_date' => '2026-01-15',
            'end_date' => '2026-12-15',
            'status' => 'en_cours',
        ]);

        // --- Mentors ------------------------------------------------------
        $fatou = User::create([
            'name' => 'Fatou Konaté',
            'email' => 'fatou.konate@example.org',
            'password' => 'password',
            'status' => 'active',
            'country' => 'Sénégal',
        ]);
        $fatou->assignRole('mentor');
        MentorProfile::create([
            'user_id' => $fatou->id,
            'expertise' => 'Ingénieure logiciel',
            'bio' => "Accompagner une jeune fille dans son projet STIM est l'une des expériences les plus concrètes en tant que professionnelle.",
            'capacity' => 4,
            'validated_at' => now()->subMonths(6),
            'validated_by' => $admin->id,
        ]);

        $kadidia = User::create([
            'name' => 'Kadidia Traoré',
            'email' => 'kadidia.traore@example.org',
            'password' => 'password',
            'status' => 'active',
            'country' => 'Mali',
        ]);
        $kadidia->assignRole('mentor');
        MentorProfile::create([
            'user_id' => $kadidia->id,
            'expertise' => 'Cybersécurité',
            'capacity' => 3,
            'validated_at' => now()->subMonth(),
            'validated_by' => $admin->id,
        ]);

        $sarah = User::create([
            'name' => "Sarah N'Guessan",
            'email' => 'sarah.nguessan@example.org',
            'password' => 'password',
            'status' => 'pending',
            'country' => "Côte d'Ivoire",
        ]);
        $sarah->assignRole('mentor');
        MentorProfile::create(['user_id' => $sarah->id, 'expertise' => 'Data science', 'capacity' => 3]);

        $julie = User::create([
            'name' => 'Julie Amoussou',
            'email' => 'julie.amoussou@example.org',
            'password' => 'password',
            'status' => 'pending',
            'country' => 'Bénin',
        ]);
        $julie->assignRole('mentor');
        MentorProfile::create(['user_id' => $julie->id, 'expertise' => 'Génie civil', 'capacity' => 3]);

        // --- Mentees --------------------------------------------------
        $aicha = User::create([
            'name' => 'Aïcha Diallo',
            'email' => 'aicha.diallo@example.org',
            'password' => 'password',
            'status' => 'active',
            'country' => "Côte d'Ivoire",
        ]);
        $aicha->assignRole('mentee');
        MenteeProfile::create(['user_id' => $aicha->id, 'level' => 'Terminale scientifique', 'school' => 'Lycée Abidjan']);

        $mariam = User::create([
            'name' => 'Mariam Sow',
            'email' => 'mariam.sow@example.org',
            'password' => 'password',
            'status' => 'active',
            'country' => 'Mali',
        ]);
        $mariam->assignRole('mentee');
        MenteeProfile::create(['user_id' => $mariam->id, 'level' => 'Licence 1']);

        $ndeye = User::create([
            'name' => 'Ndeye Fall',
            'email' => 'ndeye.fall@example.org',
            'password' => 'password',
            'status' => 'pending',
            'country' => 'Sénégal',
        ]);
        $ndeye->assignRole('mentee');
        MenteeProfile::create(['user_id' => $ndeye->id, 'level' => '1ère S']);

        // --- Donor ------------------------------------------------------
        $donor = User::create([
            'name' => 'Fondation Numérique',
            'email' => 'contact@fondation-numerique.org',
            'password' => 'password',
            'status' => 'active',
        ]);
        $donor->assignRole('donor');

        // --- Pairings & sessions -----------------------------------------
        $pairingAicha = MentorshipPairing::create([
            'mentee_id' => $aicha->id,
            'mentor_id' => $fatou->id,
            'program_id' => $programs['mentorat-stim']->id,
            'cohort_id' => $cohortStim->id,
            'status' => 'actif',
            'match_score' => 92,
            'matched_at' => now()->subMonths(6),
        ]);

        $pairingMariam = MentorshipPairing::create([
            'mentee_id' => $mariam->id,
            'mentor_id' => $fatou->id,
            'program_id' => $programs['campus-numerique']->id,
            'status' => 'actif',
            'match_score' => 88,
            'matched_at' => now()->subMonths(3),
        ]);

        MentorshipPairing::create([
            'mentee_id' => $ndeye->id,
            'program_id' => $programs['mentorat-stim']->id,
            'cohort_id' => $cohortStim->id,
            'status' => 'en_attente',
        ]);

        MentorshipSession::create([
            'pairing_id' => $pairingAicha->id,
            'scheduled_at' => '2026-07-18 16:00:00',
            'topic' => 'Préparation du projet de fin de cycle',
            'status' => 'confirmee',
            'created_by' => $fatou->id,
        ]);
        MentorshipSession::create([
            'pairing_id' => $pairingAicha->id,
            'scheduled_at' => '2026-07-25 16:00:00',
            'topic' => "Point d'étape mensuel",
            'status' => 'en_attente',
            'created_by' => $fatou->id,
        ]);
        $pastSession = MentorshipSession::create([
            'pairing_id' => $pairingAicha->id,
            'scheduled_at' => '2026-07-04 16:00:00',
            'topic' => 'Introduction aux algorithmes',
            'status' => 'realisee',
            'created_by' => $fatou->id,
        ]);
        $pastSession->notes()->create([
            'author_id' => $fatou->id,
            'content' => 'Aïcha progresse bien sur les bases algorithmiques, à revoir : complexité.',
            'visibility' => 'partagee',
        ]);

        MentorshipSession::create([
            'pairing_id' => $pairingMariam->id,
            'scheduled_at' => '2026-07-20 10:00:00',
            'topic' => 'Suivi de projet data',
            'status' => 'confirmee',
            'created_by' => $fatou->id,
        ]);

        // --- Modules, progress, quiz --------------------------------------
        $moduleFondations = Module::create([
            'program_id' => $programs['mentorat-stim']->id,
            'title' => 'Fondations STIM',
            'order' => 1,
            'status' => 'publie',
        ]);
        $moduleProg = Module::create([
            'program_id' => $programs['mentorat-stim']->id,
            'title' => 'Bases de la programmation',
            'order' => 2,
            'status' => 'publie',
        ]);
        $moduleIa = Module::create([
            'program_id' => $programs['mentorat-stim']->id,
            'title' => "Introduction à l'intelligence artificielle",
            'order' => 3,
            'status' => 'publie',
        ]);
        Module::create([
            'program_id' => $programs['mentorat-stim']->id,
            'title' => 'Data & statistiques appliquées',
            'order' => 4,
            'status' => 'brouillon',
        ]);

        ModuleProgress::create(['user_id' => $aicha->id, 'module_id' => $moduleFondations->id, 'progress' => 100, 'completed_at' => now()->subMonths(4)]);
        ModuleProgress::create(['user_id' => $aicha->id, 'module_id' => $moduleProg->id, 'progress' => 70]);
        ModuleProgress::create(['user_id' => $aicha->id, 'module_id' => $moduleIa->id, 'progress' => 25]);

        $quiz = $moduleFondations->quizzes()->create(['title' => 'Quiz Fondations STIM', 'passing_score' => 70]);
        $q1 = $quiz->questions()->create(['question' => 'STIM signifie sciences, technologies, ingénierie et mathématiques ?', 'type' => 'unique', 'order' => 1]);
        $q1->options()->createMany([
            ['label' => 'Vrai', 'is_correct' => true],
            ['label' => 'Faux', 'is_correct' => false],
        ]);

        // --- Cours de renforcement, labo virtuel & sessions live ----------
        $levels = [
            '6e-5e' => Level::create(['name' => '6e & 5e', 'slug' => '6e-5e', 'order' => 1]),
            '4e-3e' => Level::create(['name' => '4e & 3e', 'slug' => '4e-3e', 'order' => 2]),
            '2nde-1re' => Level::create(['name' => '2nde & 1re', 'slug' => '2nde-1re', 'order' => 3]),
            'terminale-c-d' => Level::create(['name' => 'Terminale C & D', 'slug' => 'terminale-c-d', 'order' => 4]),
        ];

        $subjects = [
            'mathematiques' => Subject::create(['name' => 'Mathématiques', 'slug' => 'mathematiques']),
            'physique' => Subject::create(['name' => 'Physique', 'slug' => 'physique']),
            'chimie' => Subject::create(['name' => 'Chimie', 'slug' => 'chimie']),
            'svt' => Subject::create(['name' => 'SVT', 'slug' => 'svt']),
        ];

        $courseFonctions = Course::create([
            'level_id' => $levels['terminale-c-d']->id,
            'subject_id' => $subjects['mathematiques']->id,
            'title' => 'Fonctions et dérivées',
            'description' => 'Étude de fonctions, calcul de dérivées et applications.',
            'order' => 1,
            'status' => 'publie',
        ]);
        $courseMecanique = Course::create([
            'level_id' => $levels['terminale-c-d']->id,
            'subject_id' => $subjects['physique']->id,
            'title' => 'Mécanique du point',
            'description' => "Cinématique et dynamique du point matériel.",
            'order' => 1,
            'status' => 'publie',
        ]);
        $courseElectricite = Course::create([
            'level_id' => $levels['2nde-1re']->id,
            'subject_id' => $subjects['physique']->id,
            'title' => 'Électricité — circuits en courant continu',
            'description' => "Lois de tension et d'intensité dans les circuits série et dérivation.",
            'order' => 1,
            'status' => 'publie',
        ]);
        $courseAcideBase = Course::create([
            'level_id' => $levels['2nde-1re']->id,
            'subject_id' => $subjects['chimie']->id,
            'title' => 'Réactions acide-base',
            'order' => 1,
            'status' => 'publie',
        ]);
        $courseReproduction = Course::create([
            'level_id' => $levels['4e-3e']->id,
            'subject_id' => $subjects['svt']->id,
            'title' => 'Reproduction et hérédité',
            'order' => 1,
            'status' => 'publie',
        ]);
        $courseFractions = Course::create([
            'level_id' => $levels['6e-5e']->id,
            'subject_id' => $subjects['mathematiques']->id,
            'title' => 'Nombres et fractions',
            'description' => 'Comparer, additionner et simplifier des fractions.',
            'order' => 1,
            'status' => 'publie',
        ]);
        $courseVivant = Course::create([
            'level_id' => $levels['6e-5e']->id,
            'subject_id' => $subjects['svt']->id,
            'title' => 'Le vivant et son environnement',
            'description' => 'Découvrir les besoins et le développement des êtres vivants.',
            'order' => 2,
            'status' => 'publie',
        ]);

        CourseProgress::create(['user_id' => $aicha->id, 'course_id' => $courseFonctions->id, 'progress' => 40]);

        Experiment::create([
            'subject_id' => $subjects['chimie']->id,
            'level_id' => $levels['2nde-1re']->id,
            'course_id' => $courseAcideBase->id,
            'title' => 'Dosage acide-base',
            'description' => "Déterminer la concentration d'une solution par titrage.",
            'instructions' => "1. Préparer la burette. 2. Verser l'indicateur coloré. 3. Titrer goutte à goutte jusqu'au virage.",
            'order' => 1,
            'status' => 'publie',
        ]);
        Experiment::create([
            'subject_id' => $subjects['physique']->id,
            'level_id' => $levels['terminale-c-d']->id,
            'course_id' => $courseMecanique->id,
            'title' => 'Chute libre et frottements',
            'description' => "Comparer la chute d'objets avec et sans frottement de l'air.",
            'order' => 1,
            'status' => 'publie',
        ]);
        Experiment::create([
            'subject_id' => $subjects['svt']->id,
            'level_id' => $levels['4e-3e']->id,
            'course_id' => $courseReproduction->id,
            'title' => 'Observation de cellules au microscope',
            'description' => "Observer et légender des cellules végétales et animales.",
            'instructions' => "1. Préparer une lamelle avec l'échantillon. 2. Régler le microscope du grossissement le plus faible au plus fort. 3. Dessiner et légender ce qui est observé.",
            'order' => 1,
            'status' => 'publie',
        ]);
        Experiment::create([
            'subject_id' => $subjects['physique']->id,
            'level_id' => $levels['2nde-1re']->id,
            'course_id' => $courseElectricite->id,
            'title' => 'Mesure de tension et d\'intensité',
            'description' => "Mesurer tension et intensité dans un circuit série et un circuit en dérivation.",
            'instructions' => "1. Monter le circuit avec une pile, une lampe et un interrupteur. 2. Brancher le voltmètre en dérivation puis l'ampèremètre en série. 3. Relever et comparer les mesures.",
            'order' => 1,
            'status' => 'publie',
        ]);
        Experiment::create([
            'subject_id' => $subjects['svt']->id,
            'level_id' => $levels['6e-5e']->id,
            'course_id' => $courseVivant->id,
            'title' => 'Germination des graines',
            'description' => "Observer les conditions nécessaires à la germination d'une graine.",
            'instructions' => "1. Placer des graines dans plusieurs pots (eau, lumière, terre variables). 2. Observer chaque jour pendant deux semaines. 3. Comparer les conditions de germination.",
            'order' => 1,
            'status' => 'publie',
        ]);

        LiveSession::create([
            'course_id' => $courseFonctions->id,
            'title' => 'Session de révision — Fonctions et dérivées',
            'scheduled_at' => '2026-07-24 18:00:00',
            'duration_minutes' => 60,
            'status' => 'a_venir',
            'created_by' => $staff->id,
        ]);
        LiveSession::create([
            'course_id' => $courseElectricite->id,
            'title' => 'Atelier questions-réponses — Électricité',
            'scheduled_at' => '2026-07-28 17:00:00',
            'duration_minutes' => 45,
            'status' => 'a_venir',
            'created_by' => $staff->id,
        ]);
        LiveSession::create([
            'course_id' => $courseMecanique->id,
            'title' => 'Session live — Mécanique du point',
            'scheduled_at' => '2026-07-26 18:00:00',
            'duration_minutes' => 60,
            'status' => 'a_venir',
            'created_by' => $staff->id,
        ]);
        LiveSession::create([
            'course_id' => $courseAcideBase->id,
            'title' => 'Atelier questions-réponses — Réactions acide-base',
            'scheduled_at' => '2026-07-30 17:00:00',
            'duration_minutes' => 45,
            'status' => 'a_venir',
            'created_by' => $staff->id,
        ]);
        LiveSession::create([
            'course_id' => $courseReproduction->id,
            'title' => 'Session live — Reproduction et hérédité',
            'scheduled_at' => '2026-08-02 17:00:00',
            'duration_minutes' => 60,
            'status' => 'a_venir',
            'created_by' => $staff->id,
        ]);
        LiveSession::create([
            'course_id' => $courseFractions->id,
            'title' => 'Session live — Nombres et fractions',
            'scheduled_at' => '2026-08-04 16:00:00',
            'duration_minutes' => 45,
            'status' => 'a_venir',
            'created_by' => $staff->id,
        ]);
        LiveSession::create([
            'course_id' => $courseVivant->id,
            'title' => 'Session live — Le vivant et son environnement',
            'scheduled_at' => '2026-08-06 16:00:00',
            'duration_minutes' => 45,
            'status' => 'a_venir',
            'created_by' => $staff->id,
        ]);

        // --- Badges & certificates ------------------------------------
        $badgeFondations = Badge::create(['title' => 'Fondations STIM', 'description' => 'Module Fondations STIM complété']);
        $badgeEquipe = Badge::create(['title' => "Esprit d'équipe", 'description' => 'Participation active en groupe']);
        $badgeProjet = Badge::create(['title' => 'Premier projet déposé', 'description' => 'Premier projet soumis pour validation']);

        $badgeFondations->users()->attach($aicha->id, ['awarded_at' => '2026-03-10', 'awarded_by' => $admin->id]);
        $badgeEquipe->users()->attach($aicha->id, ['awarded_at' => '2026-04-22', 'awarded_by' => $admin->id]);
        $badgeProjet->users()->attach($aicha->id, ['awarded_at' => '2026-05-14', 'awarded_by' => $admin->id]);

        Certificate::create([
            'user_id' => $aicha->id,
            'program_id' => $programs['mentorat-stim']->id,
            'title' => 'Certificat — Fondations STIM',
            'serial_number' => 'STF-'.strtoupper(Str::random(8)),
            'issued_at' => '2026-03-10',
        ]);

        // --- Projects -----------------------------------------------------
        Project::create([
            'mentee_id' => $aicha->id,
            'pairing_id' => $pairingAicha->id,
            'title' => 'Application de suivi des devoirs',
            'status' => 'en_validation',
        ]);
        Project::create([
            'mentee_id' => $aicha->id,
            'pairing_id' => $pairingAicha->id,
            'title' => 'Capteur de qualité de l\'air (maquette)',
            'status' => 'valide',
        ]);

        // --- Groups ---------------------------------------------------
        $groupCohorte = Group::create([
            'name' => 'Cohorte Lycée Abidjan 2026',
            'type' => 'automatique',
            'program_id' => $programs['mentorat-stim']->id,
            'status' => 'actif',
            'created_by' => $admin->id,
        ]);
        $groupCohorte->members()->attach([
            $aicha->id => ['role_in_group' => 'membre', 'joined_at' => now()->subMonths(5)],
            $ndeye->id => ['role_in_group' => 'membre', 'joined_at' => now()->subMonths(5)],
            $fatou->id => ['role_in_group' => 'animatrice', 'joined_at' => now()->subMonths(5)],
        ]);
        $post = $groupCohorte->posts()->create([
            'author_id' => $aicha->id,
            'content' => 'Merci à toutes pour les conseils sur le projet de fin de cycle !',
        ]);
        $post->comments()->create([
            'author_id' => $fatou->id,
            'content' => 'Bravo à toute la cohorte pour cette dynamique !',
        ]);

        Group::create([
            'name' => 'Atelier Robotique — Projet pilote',
            'type' => 'travail',
            'status' => 'actif',
            'created_by' => $admin->id,
        ]);
        Group::create([
            'name' => 'Mentorat collectif — Data science',
            'type' => 'mentorat',
            'status' => 'en_validation',
            'created_by' => $admin->id,
        ]);

        // --- Reports (signalements) -----------------------------------
        Report::create([
            'reporter_id' => $aicha->id,
            'context_type' => 'messagerie_pairing',
            'context_id' => $pairingAicha->id,
            'description' => 'Message inapproprié reçu dans la messagerie du binôme.',
            'status' => 'en_cours',
        ]);
        Report::create([
            'reporter_id' => $mariam->id,
            'context_type' => 'groupe',
            'context_id' => $groupCohorte->id,
            'description' => "Comportement à signaler dans l'atelier robotique.",
            'status' => 'resolu',
            'resolved_by' => $admin->id,
            'resolved_at' => now()->subDays(7),
        ]);

        // --- CMS content ------------------------------------------------
        // Actualités réelles de STF (bilan 2016-2024), classées de la plus récente
        // à la plus ancienne.
        CmsPage::create([
            'title' => 'Accueil',
            'slug' => 'accueil',
            'type' => 'page',
            'body' => 'Bienvenue sur le site de Sciences & Technologies au Féminin (STF).',
            'status' => 'publie',
            'author_id' => $admin->id,
            'published_at' => now()->subMonths(8),
        ]);
        CmsPage::create([
            'title' => "Empow'Her Science Expo 2024 : plus de 1500 visiteurs à Abidjan",
            'slug' => 'empowher-science-expo-2024',
            'type' => 'article',
            'category' => 'Événement',
            'excerpt' => "Du 28 au 30 novembre 2024, l'Ivoire Trade Center d'Abidjan a accueilli la 2e édition de l'Empow'Her Science Expo sur le thème « STEM pour un impact social ». Plus de 1500 visiteurs et 15 exposantes ont pris part à l'événement.",
            'body' => "Organisée par Sciences & Technologies au Féminin, la 2e édition de l'Empow'Her Science Expo s'est tenue du 28 au 30 novembre 2024 à l'Ivoire Trade Center d'Abidjan Cocody, sur le thème « STEM pour un impact social : Révolutionner l'accès à la science, à la technologie et à l'éducation ». Quatre panels ont rythmé l'événement, aux côtés d'une exposition scientifique (agriculture durable, entomologie, énergies renouvelables) et de sessions de mentorat. Plus de 1500 visiteurs et 21 établissements secondaires et universitaires ont été touchés, pour 15 exposantes reçues dans les domaines des STIM.",
            'status' => 'publie',
            'author_id' => $admin->id,
            'published_at' => '2024-11-30',
        ]);
        CmsPage::create([
            'title' => 'STF représente la Côte d\'Ivoire au YALI Expo et à l\'Africa Tech Festival',
            'slug' => 'yali-expo-afrique-du-sud-2024',
            'type' => 'article',
            'category' => 'International',
            'excerpt' => "Du 11 au 14 novembre 2024, la présidente de STF a représenté la Côte d'Ivoire en Afrique du Sud, présentant sa solution en énergies renouvelables devant un public panafricain.",
            'body' => "En tant qu'Alumni du programme Young African Leaders Initiative (YALI) Dakar, la présidente du Conseil d'Administration de STF a représenté la Côte d'Ivoire lors du YALI Expo et de l'Africa Tech Festival, organisés en Afrique du Sud du 11 au 14 novembre 2024. Elle y a présenté sa solution en énergies renouvelables, mettant en avant le savoir-faire ivoirien et l'engagement de STF dans la promotion des STIM et des énergies durables.",
            'status' => 'publie',
            'author_id' => $admin->id,
            'published_at' => '2024-11-14',
        ]);
        CmsPage::create([
            'title' => 'STF invitée à la Cité des Sciences et de l\'Industrie de Paris',
            'slug' => 'cite-des-sciences-paris-2024',
            'type' => 'article',
            'category' => 'International',
            'excerpt' => "Du 4 au 7 octobre 2024, STF a représenté la Côte d'Ivoire lors de la Francophonie en France, animant un atelier sur la durabilité écologique suivi par plus de 3500 personnes.",
            'body' => "Invitée par la Cité des sciences et de l'industrie de Paris sur recommandation de l'ambassade de France en Côte d'Ivoire, STF a représenté le pays lors de la Francophonie en France du 3 au 6 octobre 2024. L'organisation y a animé un atelier sur la durabilité écologique durant trois jours, une activité qui a accueilli plus de 3500 personnes.",
            'status' => 'publie',
            'author_id' => $admin->id,
            'published_at' => '2024-10-07',
        ]);
        CmsPage::create([
            'title' => 'STF récompensée du prix Impacts Jeunes 2024 par le CNJCI',
            'slug' => 'prix-impacts-jeunes-2024',
            'type' => 'article',
            'category' => 'Distinction',
            'excerpt' => "Le 14 août 2024, Sciences & Technologies au Féminin a remporté le prix de la « Meilleure activité de promotion du genre et protection des jeunes femmes », lors du concours Impacts Jeunes organisé par le CNJCI.",
            'body' => "Le 14 août 2024, Sciences & Technologies au Féminin a remporté le prix de la « Meilleure activité de promotion du genre et protection des jeunes femmes » lors du concours Impacts Jeunes, organisé par le Conseil National des Jeunes de Côte d'Ivoire (CNJCI). Ce prix reconnaît l'engagement de l'organisation en faveur de l'empowerment des jeunes femmes dans les domaines des sciences, technologies, ingénierie et mathématiques (STEM).",
            'status' => 'publie',
            'author_id' => $admin->id,
            'published_at' => '2024-08-14',
        ]);
        CmsPage::create([
            'title' => 'Immersion technologique en Allemagne avec la GIZ',
            'slug' => 'immersion-technologique-allemagne-2024',
            'type' => 'article',
            'category' => 'International',
            'excerpt' => "En juin 2024, STF a participé à une immersion dans les centres technologiques de Berlin, organisée avec la coopération allemande (GIZ), autour des énergies renouvelables et de l'innovation durable.",
            'body' => "En juin 2024, STF a eu l'opportunité de participer à une immersion dans le monde de la technologie en Allemagne, organisée en coopération avec la GIZ (coopération allemande au développement). Cette mission visait à découvrir les innovations technologiques et les meilleures pratiques dans les secteurs des énergies renouvelables, de la gestion de l'énergie et de l'innovation durable, et à explorer des partenariats potentiels pour le développement durable en Afrique.",
            'status' => 'publie',
            'author_id' => $admin->id,
            'published_at' => '2024-06-15',
        ]);
        CmsPage::create([
            'title' => 'La Reine Mathilde de Belgique échange avec de jeunes talents aux côtés de STF',
            'slug' => 'reine-mathilde-belgique-2024',
            'type' => 'article',
            'category' => 'Événement',
            'excerpt' => "En avril 2024, la Présidente du Conseil d'Administration de STF a coanimé un panel aux côtés de la Reine Mathilde de Belgique à l'Université Félix Houphouët-Boigny, sur l'éducation, les sciences et les technologies en Afrique.",
            'body' => "Lors de la visite officielle de la Reine Mathilde de Belgique en Côte d'Ivoire en avril 2024, la Présidente du Conseil d'Administration de STF a coanimé un panel aux côtés de la Reine à l'Université Félix Houphouët-Boigny. L'événement, en présence du Ministre de l'Enseignement Supérieur et de la Recherche Scientifique et de Madame Nialé Kaba, a mis en lumière l'importance de l'éducation, des sciences et des technologies pour les femmes dans le développement de l'Afrique.",
            'status' => 'publie',
            'author_id' => $admin->id,
            'published_at' => '2024-04-15',
        ]);
        CmsPage::create([
            'title' => 'Christelle Ogo honorée par la JCI pour son engagement scientifique',
            'slug' => 'prix-jci-christelle-ogo-2024',
            'type' => 'article',
            'category' => 'Distinction',
            'excerpt' => "Le 4 avril 2024, la présidente de STF a reçu le prix « Développement Scientifique et/ou Technologique » de la Junior Chamber International (JCI).",
            'body' => "Le 4 avril 2024, la Présidente du Conseil d'Administration de Sciences & Technologies au Féminin a été honorée du prix dans la catégorie « Développement Scientifique et/ou Technologique » par la JCI (Junior Chamber International), en reconnaissance de son engagement exceptionnel à promouvoir l'accès des femmes et des jeunes filles aux domaines scientifiques et technologiques.",
            'status' => 'publie',
            'author_id' => $admin->id,
            'published_at' => '2024-04-04',
        ]);
        CmsPage::create([
            'title' => "Journée des Éclaireuses : l'IRD et STF réunissent 39 lycéennes autour de la recherche",
            'slug' => 'journee-eclaireuses-2024',
            'type' => 'article',
            'category' => 'Sensibilisation',
            'excerpt' => "Le 23 mars 2024, l'Institut de Recherche pour le Développement (IRD) a organisé pour la première fois en Côte d'Ivoire la Journée des Éclaireuses, en partenariat avec STF.",
            'body' => "Le 23 mars 2024, l'Institut de Recherche pour le Développement (IRD) a initié pour la première fois en Côte d'Ivoire la Journée des Éclaireuses, en partenariat avec STF, reconnue pour son expertise dans la sensibilisation et la formation des jeunes femmes aux STIM. Cette journée a réuni trente-neuf lycéennes issues de divers établissements, à travers des discussions en petits groupes, des ateliers thématiques et des témoignages de mentors scientifiques.",
            'status' => 'publie',
            'author_id' => $admin->id,
            'published_at' => '2024-03-23',
        ]);

        // --- Partners / testimonials / faqs -------------------------------
        $this->call(PartnerSeeder::class);
        $this->call(SiteContentSeeder::class);

        Testimonial::create([
            'name' => 'Aïcha D.',
            'role' => 'Mentée — Programme Mentorat STIM',
            'quote' => "Ma mentore m'a aidée à croire que l'ingénierie était possible pour moi. Aujourd'hui je suis en licence de génie informatique.",
            'program_id' => $programs['mentorat-stim']->id,
            'order' => 1,
        ]);
        Testimonial::create([
            'name' => 'Fatou K.',
            'role' => 'Mentore bénévole, ingénieure logiciel',
            'quote' => "Accompagner une jeune fille dans son projet STIM est l'une des expériences les plus concrètes que j'ai eues en tant que professionnelle.",
            'program_id' => $programs['mentorat-stim']->id,
            'order' => 2,
        ]);
        Testimonial::create([
            'name' => 'Mariam S.',
            'role' => 'Mentée — Campus numérique',
            'quote' => 'Les modules et badges m\'ont permis d\'avancer à mon rythme, même avec une connexion limitée.',
            'program_id' => $programs['campus-numerique']->id,
            'order' => 3,
        ]);

        Faq::create(['question' => 'Qui peut devenir mentée ?', 'answer' => 'Toute fille ou jeune femme en primaire, collège, lycée ou université intéressée par les sciences, technologies, l\'ingénierie ou les mathématiques.', 'category' => 'mentorat', 'order' => 1]);
        Faq::create(['question' => 'Comment devient-on mentore ?', 'answer' => 'Les candidates créent un profil professionnel détaillé. Leur compte est ensuite examiné et validé par l\'équipe STF avant tout échange avec une mentée.', 'category' => 'mentorat', 'order' => 2]);
        Faq::create(['question' => 'Comment se déroule le matching ?', 'answer' => "STF propose des binômes sur la base du domaine d'intérêt, de la langue, du niveau, des objectifs et des disponibilités. L'équipe STF confirme ou ajuste chaque affectation.", 'category' => 'mentorat', 'order' => 3]);
        Faq::create(['question' => 'La messagerie est-elle sécurisée ?', 'answer' => 'Oui. Les coordonnées privées ne sont jamais visibles par défaut et chaque conversation dispose d\'un bouton de signalement.', 'category' => 'mentorat', 'order' => 4]);

        // --- Messaging ----------------------------------------------------
        $conversation = Conversation::create(['subject' => 'Suivi mentorat', 'context_type' => 'pairing', 'context_id' => $pairingAicha->id]);
        $conversation->participants()->attach([$aicha->id, $fatou->id]);
        $conversation->messages()->create(['sender_id' => $fatou->id, 'body' => 'Bravo pour ta présentation, on en reparle jeudi.']);
        $conversation->messages()->create(['sender_id' => $aicha->id, 'body' => 'Merci beaucoup, à jeudi !']);

        // --- Audit trail --------------------------------------------------
        AuditLog::record($admin, 'mentore.validee', $fatou);
        AuditLog::record($fatou, 'profil.consulte', $aicha);
        AuditLog::record($admin, 'signalement.cree', null, ['context' => 'binome #'.$pairingAicha->id]);
    }
}
