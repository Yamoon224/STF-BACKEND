<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Actualités réelles de STF (bilan 2016-2024), pour la page /blog du site public.
 * Retire au passage les 2 articles fictifs de démonstration (lancement de cohorte,
 * partenariat "Fondation Numérique") s'ils existent encore, sans toucher aux autres
 * contenus déjà ajoutés depuis le back-office.
 */
class BlogSeeder extends Seeder
{
    public function run(): void
    {
        CmsPage::whereIn('slug', ['lancement-cohorte-2026', 'partenariat-fondation'])->delete();

        CmsPage::updateOrCreate(
            ['slug' => 'accueil'],
            [
                'title' => 'Accueil',
                'type' => 'page',
                'body' => 'Bienvenue sur le site de Sciences & Technologies au Féminin (STF).',
                'status' => 'publie',
                'published_at' => now()->subMonths(8),
            ]
        );

        $admin = User::where('email', 'admin@stf-organisation.org')->first();

        $articles = [
            [
                'title' => "Empow'Her Science Expo 2024 : plus de 1500 visiteurs à Abidjan",
                'slug' => 'empowher-science-expo-2024',
                'category' => 'Événement',
                'excerpt' => "Du 28 au 30 novembre 2024, l'Ivoire Trade Center d'Abidjan a accueilli la 2e édition de l'Empow'Her Science Expo sur le thème « STEM pour un impact social ». Plus de 1500 visiteurs et 15 exposantes ont pris part à l'événement.",
                'body' => "Organisée par Sciences & Technologies au Féminin, la 2e édition de l'Empow'Her Science Expo s'est tenue du 28 au 30 novembre 2024 à l'Ivoire Trade Center d'Abidjan Cocody, sur le thème « STEM pour un impact social : Révolutionner l'accès à la science, à la technologie et à l'éducation ». Quatre panels ont rythmé l'événement, aux côtés d'une exposition scientifique (agriculture durable, entomologie, énergies renouvelables) et de sessions de mentorat. Plus de 1500 visiteurs et 21 établissements secondaires et universitaires ont été touchés, pour 15 exposantes reçues dans les domaines des STIM.",
                'published_at' => '2024-11-30',
            ],
            [
                'title' => "STF représente la Côte d'Ivoire au YALI Expo et à l'Africa Tech Festival",
                'slug' => 'yali-expo-afrique-du-sud-2024',
                'category' => 'International',
                'excerpt' => "Du 11 au 14 novembre 2024, la présidente de STF a représenté la Côte d'Ivoire en Afrique du Sud, présentant sa solution en énergies renouvelables devant un public panafricain.",
                'body' => "En tant qu'Alumni du programme Young African Leaders Initiative (YALI) Dakar, la présidente du Conseil d'Administration de STF a représenté la Côte d'Ivoire lors du YALI Expo et de l'Africa Tech Festival, organisés en Afrique du Sud du 11 au 14 novembre 2024. Elle y a présenté sa solution en énergies renouvelables, mettant en avant le savoir-faire ivoirien et l'engagement de STF dans la promotion des STIM et des énergies durables.",
                'published_at' => '2024-11-14',
            ],
            [
                'title' => "STF invitée à la Cité des Sciences et de l'Industrie de Paris",
                'slug' => 'cite-des-sciences-paris-2024',
                'category' => 'International',
                'excerpt' => "Du 4 au 7 octobre 2024, STF a représenté la Côte d'Ivoire lors de la Francophonie en France, animant un atelier sur la durabilité écologique suivi par plus de 3500 personnes.",
                'body' => "Invitée par la Cité des sciences et de l'industrie de Paris sur recommandation de l'ambassade de France en Côte d'Ivoire, STF a représenté le pays lors de la Francophonie en France du 3 au 6 octobre 2024. L'organisation y a animé un atelier sur la durabilité écologique durant trois jours, une activité qui a accueilli plus de 3500 personnes.",
                'published_at' => '2024-10-07',
            ],
            [
                'title' => 'STF récompensée du prix Impacts Jeunes 2024 par le CNJCI',
                'slug' => 'prix-impacts-jeunes-2024',
                'category' => 'Distinction',
                'excerpt' => "Le 14 août 2024, Sciences & Technologies au Féminin a remporté le prix de la « Meilleure activité de promotion du genre et protection des jeunes femmes », lors du concours Impacts Jeunes organisé par le CNJCI.",
                'body' => "Le 14 août 2024, Sciences & Technologies au Féminin a remporté le prix de la « Meilleure activité de promotion du genre et protection des jeunes femmes » lors du concours Impacts Jeunes, organisé par le Conseil National des Jeunes de Côte d'Ivoire (CNJCI). Ce prix reconnaît l'engagement de l'organisation en faveur de l'empowerment des jeunes femmes dans les domaines des sciences, technologies, ingénierie et mathématiques (STEM).",
                'published_at' => '2024-08-14',
            ],
            [
                'title' => 'Immersion technologique en Allemagne avec la GIZ',
                'slug' => 'immersion-technologique-allemagne-2024',
                'category' => 'International',
                'excerpt' => "En juin 2024, STF a participé à une immersion dans les centres technologiques de Berlin, organisée avec la coopération allemande (GIZ), autour des énergies renouvelables et de l'innovation durable.",
                'body' => "En juin 2024, STF a eu l'opportunité de participer à une immersion dans le monde de la technologie en Allemagne, organisée en coopération avec la GIZ (coopération allemande au développement). Cette mission visait à découvrir les innovations technologiques et les meilleures pratiques dans les secteurs des énergies renouvelables, de la gestion de l'énergie et de l'innovation durable, et à explorer des partenariats potentiels pour le développement durable en Afrique.",
                'published_at' => '2024-06-15',
            ],
            [
                'title' => 'La Reine Mathilde de Belgique échange avec de jeunes talents aux côtés de STF',
                'slug' => 'reine-mathilde-belgique-2024',
                'category' => 'Événement',
                'excerpt' => "En avril 2024, la Présidente du Conseil d'Administration de STF a coanimé un panel aux côtés de la Reine Mathilde de Belgique à l'Université Félix Houphouët-Boigny, sur l'éducation, les sciences et les technologies en Afrique.",
                'body' => "Lors de la visite officielle de la Reine Mathilde de Belgique en Côte d'Ivoire en avril 2024, la Présidente du Conseil d'Administration de STF a coanimé un panel aux côtés de la Reine à l'Université Félix Houphouët-Boigny. L'événement, en présence du Ministre de l'Enseignement Supérieur et de la Recherche Scientifique et de Madame Nialé Kaba, a mis en lumière l'importance de l'éducation, des sciences et des technologies pour les femmes dans le développement de l'Afrique.",
                'published_at' => '2024-04-15',
            ],
            [
                'title' => 'Christelle Ogo honorée par la JCI pour son engagement scientifique',
                'slug' => 'prix-jci-christelle-ogo-2024',
                'category' => 'Distinction',
                'excerpt' => "Le 4 avril 2024, la présidente de STF a reçu le prix « Développement Scientifique et/ou Technologique » de la Junior Chamber International (JCI).",
                'body' => "Le 4 avril 2024, la Présidente du Conseil d'Administration de Sciences & Technologies au Féminin a été honorée du prix dans la catégorie « Développement Scientifique et/ou Technologique » par la JCI (Junior Chamber International), en reconnaissance de son engagement exceptionnel à promouvoir l'accès des femmes et des jeunes filles aux domaines scientifiques et technologiques.",
                'published_at' => '2024-04-04',
            ],
            [
                'title' => "Journée des Éclaireuses : l'IRD et STF réunissent 39 lycéennes autour de la recherche",
                'slug' => 'journee-eclaireuses-2024',
                'category' => 'Sensibilisation',
                'excerpt' => "Le 23 mars 2024, l'Institut de Recherche pour le Développement (IRD) a organisé pour la première fois en Côte d'Ivoire la Journée des Éclaireuses, en partenariat avec STF.",
                'body' => "Le 23 mars 2024, l'Institut de Recherche pour le Développement (IRD) a initié pour la première fois en Côte d'Ivoire la Journée des Éclaireuses, en partenariat avec STF, reconnue pour son expertise dans la sensibilisation et la formation des jeunes femmes aux STIM. Cette journée a réuni trente-neuf lycéennes issues de divers établissements, à travers des discussions en petits groupes, des ateliers thématiques et des témoignages de mentors scientifiques.",
                'published_at' => '2024-03-23',
            ],
        ];

        foreach ($articles as $article) {
            CmsPage::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'title' => $article['title'],
                    'type' => 'article',
                    'category' => $article['category'],
                    'excerpt' => $article['excerpt'],
                    'body' => $article['body'],
                    'status' => 'publie',
                    'author_id' => $admin?->id,
                    'published_at' => $article['published_at'],
                ]
            );
        }
    }
}
