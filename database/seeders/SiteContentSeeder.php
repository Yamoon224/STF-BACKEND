<?php

namespace Database\Seeders;

use App\Models\PageSection;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

/**
 * Seeds the "site settings" (contact/social) and the editable content sections
 * of every static public page, with exactly the copy that used to be hardcoded
 * in the site's React components. Backoffice edits then take over from here.
 */
class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedSections();
    }

    private function seedSettings(): void
    {
        $settings = [
            'address' => 'Siège : Angré 7ème tranche, rue L130, près du complexe sportif',
            'phone' => '(+225) 27 22 30 61 38',
            'email_primary' => 'sciencesaufeminin@gmail.com',
            'email_secondary' => 'contact@scitechfeminin.org',
            'site_url' => 'https://sciencesaufeminin.org',
            'social_linkedin' => 'https://www.linkedin.com/company/sciences-et-technologies-au-féminin/',
            'social_facebook' => 'https://www.facebook.com/FemmesenSTIM',
            'social_instagram' => '#',
            'social_youtube' => '#',
            'social_x' => '#',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function seedSections(): void
    {
        $sections = [
            // --- a-propos ---------------------------------------------------
            ['a-propos', 'hero', 'hero', [
                'eyebrow' => 'À propos',
                'title' => 'Sciences & Technologies au Féminin',
                'description' => "STF est une organisation dédiée à la promotion des STIM auprès des filles et des jeunes femmes, à travers le mentorat, l'éducation et le plaidoyer.",
            ]],
            ['a-propos', 'histoire', 'text', [
                'eyebrow' => 'Notre histoire',
                'title' => 'Pourquoi STF existe',
                'body' => "Créée en 2016 par un groupe d'étudiantes de l'Université Félix Houphouët-Boigny à Abidjan, Sciences & Technologies au Féminin (STF) est née d'une conviction simple : les filles et les jeunes femmes doivent occuper toute leur place dans les sciences, la technologie, l'ingénierie et les mathématiques (STIM). Après des campagnes de sensibilisation, des programmes de mentorat et des formations en leadership scientifique, l'association a obtenu en 2020 la reconnaissance officielle du Ministère de l'Enseignement Supérieur et de la Recherche Scientifique. Huit ans après sa création, STF a accompagné plus de 11 000 bénéficiaires à travers ses tournées nationales (SciTech-Tour, Caravane STEM for Her), ses sections universitaires et ses clubs scolaires implantés dans 8 villes de Côte d'Ivoire.",
            ]],
            ['a-propos', 'mission', 'text', [
                'eyebrow' => 'Notre mission',
                'title' => 'Ce que nous visons',
                'body' => "Conjuguer les Sciences, Technologies, Ingénierie et Mathématiques (STIM) au féminin : soutenir l'orientation académique et professionnelle des jeunes filles, renforcer chaque année les compétences de centaines de filles et femmes à travers ateliers pratiques, stages et formations certifiantes, élargir la visibilité des STIM via des événements phares comme l'Empow'Her Science Expo, et favoriser le mentorat ainsi que l'accès à des bourses d'études.",
            ]],
            ['a-propos', 'values', 'list_title_description', [
                'items' => [
                    ['title' => 'Audace', 'description' => 'Oser porter les filles vers des filières où elles sont sous-représentées.'],
                    ['title' => 'Union', 'description' => 'Construire un réseau solidaire de mentores, partenaires et bénéficiaires.'],
                    ['title' => 'Intégrité', 'description' => 'Protéger les bénéficiaires et garantir la confiance dans chaque interaction.'],
                    ['title' => 'Résultat', 'description' => "Mesurer l'impact réel et rendre compte aux partenaires et bailleurs."],
                ],
            ]],
            ['a-propos', 'governance', 'list_role_mission', [
                'items' => [
                    ['role' => 'Christelle Ogo — Présidente & Fondatrice', 'mission' => "Vision stratégique, représentation institutionnelle et internationale de STF, pilotage général de l'organisation."],
                    ['role' => "Conseil d'Administration", 'mission' => "Armande Bahi, Arsène Adingra Kouassi, Assi Leaticia et Alexandra Kouame : validation stratégique et gouvernance de l'association."],
                    ['role' => 'Direction Exécutive', 'mission' => "Kouadio Désiré N'Guessan, avec Uchenna Okere, Serena Djama et Andrea Dembele : pilotage opérationnel des programmes et des équipes."],
                    ['role' => 'Coordination Nationale', 'mission' => "Marcelle Niamkey coordonne les antennes régionales de STF et anime le réseau des ambassadrices STIM à travers la Côte d'Ivoire."],
                ],
            ]],

            // --- politiques ---------------------------------------------------
            ['politiques', 'hero', 'hero', [
                'eyebrow' => 'Politiques',
                'title' => 'Protection, confidentialité et conformité',
                'description' => 'La protection des filles et jeunes femmes est une exigence non négociable de la plateforme STF.',
            ]],
            ['politiques', 'policies', 'list_title_text', [
                'items' => [
                    ['title' => 'Protection des filles (Safeguarding)', 'text' => "STF applique des mesures strictes de protection contre les abus, le harcèlement et les risques. Toute mentore est validée avant tout échange avec une mentée, et chaque conversation dispose d'un bouton de signalement."],
                    ['title' => 'Confidentialité', 'text' => "Les profils des mentées sont privés par défaut. Les mentores n'accèdent qu'aux informations pédagogiques des mentées qui leur sont officiellement affectées, jamais aux coordonnées privées ou aux données d'autres bénéficiaires."],
                    ['title' => 'Code de conduite', 'text' => "Toutes les utilisatrices — mentées, mentores, collaboratrices et partenaires — s'engagent à respecter un cadre de bienveillance, de respect et de non-discrimination."],
                    ['title' => 'Gestion des données', 'text' => "Seules les informations nécessaires au fonctionnement du service sont collectées. Une tranche d'âge est utilisée plutôt qu'une date de naissance complète lorsque cela est possible."],
                    ['title' => 'Consentement média', 'text' => "Aucune photo, vidéo ou témoignage n'est publiée sans autorisation explicite de la bénéficiaire ou de son tuteur légal."],
                    ['title' => 'Traçabilité', 'text' => 'Les actions sensibles (connexion, consultation, validation, suspension, suppression, signalement) sont journalisées et auditables à tout moment.'],
                ],
            ]],

            // --- impact ---------------------------------------------------
            ['impact', 'hero', 'hero', [
                'eyebrow' => 'Impact',
                'title' => 'Des résultats mesurés, des rapports fiables',
                'description' => 'STF publie des indicateurs consolidés par programme, cohorte, niveau, pays et période — pour ses équipes, ses partenaires et ses bailleurs.',
            ]],
            ['impact', 'indicators', 'list_label_value', [
                'items' => [
                    ['label' => 'Bénéficiaires accompagnées (2016-2024)', 'value' => '11 022'],
                    ['label' => 'Filles et femmes parmi les bénéficiaires', 'value' => '75%'],
                    ['label' => 'Étudiantes accompagnées vers une bourse', 'value' => '155'],
                    ['label' => 'Ambassadrices STIM formées', 'value' => '300'],
                    ['label' => "Villes couvertes en Côte d'Ivoire", 'value' => '8'],
                    ['label' => 'Sections STF en universités', 'value' => '8'],
                    ['label' => 'Sections en grandes écoles', 'value' => '4'],
                    ['label' => 'Clubs STF en collèges et lycées', 'value' => '8'],
                ],
            ]],

            // --- mentorat ---------------------------------------------------
            ['mentorat', 'hero', 'hero', [
                'eyebrow' => 'Mentorat',
                'title' => 'Un dispositif structuré, sécurisé et suivi',
                'description' => 'Inscription, validation des mentores, matching par critères objectifs, sessions, messagerie sécurisée et bilan de cycle : chaque étape est pensée pour protéger les mentées et créer un impact réel.',
            ]],
            ['mentorat', 'mentee_path', 'list_text', [
                'items' => [
                    'Créer un compte : niveau, intérêts, objectifs et langue.',
                    'Recevoir une proposition de mentore selon le matching STF.',
                    'Échanger via la messagerie sécurisée et planifier les sessions.',
                    'Suivre ses objectifs, projets, badges et son bilan de cycle.',
                ],
            ]],
            ['mentorat', 'mentor_path', 'list_text', [
                'items' => [
                    'Créer un profil professionnel : expertise, langues, disponibilités.',
                    "Attendre la validation du compte par l'équipe STF.",
                    'Recevoir les mentées affectées et consulter leur profil pédagogique.',
                    'Animer les sessions, rédiger des notes et proposer les prochaines étapes.',
                ],
            ]],
            ['mentorat', 'security', 'list_text', [
                'items' => [
                    'Profil privé par défaut, en particulier pour les mineures',
                    'Aucune coordonnée privée visible sans consentement',
                    'Bouton de signalement dans chaque conversation',
                    'Toute consultation des mentores est tracée et auditable',
                ],
            ]],

            // --- partenaires ---------------------------------------------------
            ['partenaires', 'hero', 'hero', [
                'eyebrow' => 'Partenaires',
                'title' => 'Ils rendent nos programmes possibles',
                'description' => "Ministères, universités, agences des Nations Unies et entreprises soutiennent STF depuis 2016, financièrement, techniquement ou en mettant à disposition des mentores.",
            ]],
            ['partenaires', 'cta', 'text', [
                'title' => 'Devenir partenaire de STF',
                'body' => "En 2024, les contrats et partenariats ont représenté 77% des 82 920 247 FCFA reçus par STF. Financement de bourses, mise à disposition de mentores, accès à des rapports d'impact agrégés : plusieurs formes de partenariat sont possibles.",
            ]],

            // --- programmes ---------------------------------------------------
            ['programmes', 'hero', 'hero', [
                'eyebrow' => 'Programmes',
                'title' => 'Des parcours pour chaque étape',
                'description' => "De la découverte en primaire à la préparation à l'insertion professionnelle, chaque programme STF a des objectifs, une cible et des modalités de participation claires.",
            ]],

            // --- bourses ---------------------------------------------------
            ['bourses', 'hero', 'hero', [
                'eyebrow' => 'Bourses',
                'title' => 'Des bourses pour poursuivre vos études en STIM',
                'description' => "STF accompagne les filles et jeunes femmes dans leurs démarches de candidature aux bourses d'études scientifiques, technologiques, d'ingénierie et mathématiques. En 2024, 155 étudiantes ont été accompagnées vers l'obtention d'une bourse.",
            ]],

            // --- blog ---------------------------------------------------
            ['blog', 'hero', 'hero', [
                'eyebrow' => 'Blog & actualités',
                'title' => 'Les nouvelles de STF',
                'description' => "Articles, annonces, retours d'expérience, médias et galerie photos et vidéos autour des programmes STF.",
            ]],

            // --- experiences-virtuelles ---------------------------------------------------
            ['experiences-virtuelles', 'hero', 'hero', [
                'eyebrow' => 'Expériences virtuelles',
                'title' => 'Découvrir les STIM à son rythme',
                'description' => "Des cours de renforcement, un labo virtuel d'expériences et des sessions en direct — choisis ton niveau pour commencer, comme un cours à domicile en ligne.",
            ]],

            // --- contact ---------------------------------------------------
            ['contact', 'hero', 'hero', [
                'eyebrow' => 'Contact',
                'title' => 'Parlons de votre demande',
                'description' => 'Choisissez le formulaire correspondant à votre profil pour que l\'équipe STF vous réponde plus rapidement.',
            ]],
        ];

        foreach ($sections as $order => [$pageKey, $sectionKey, $type, $payload]) {
            PageSection::updateOrCreate(
                ['page_key' => $pageKey, 'section_key' => $sectionKey],
                ['type' => $type, 'payload' => $payload, 'order' => $order]
            );
        }
    }
}
