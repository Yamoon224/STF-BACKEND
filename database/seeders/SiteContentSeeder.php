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
                'body' => "Face au faible nombre de filles et de femmes dans les filières scientifiques, technologiques, d'ingénierie et mathématiques, STF a été créée pour offrir un accompagnement concret : des mentores professionnelles, des expériences virtuelles adaptées et des espaces sécurisés pour progresser en confiance.",
            ]],
            ['a-propos', 'mission', 'text', [
                'eyebrow' => 'Notre mission',
                'title' => 'Ce que nous visons',
                'body' => "Accompagner chaque bénéficiaire depuis la découverte des STIM jusqu'à l'insertion professionnelle, en s'appuyant sur des données fiables et un dispositif de protection strict, en particulier pour les mineures.",
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
                    ['role' => 'Direction / PCA', 'mission' => "Validation stratégique et arbitrage final de l'organisation."],
                    ['role' => 'Administratrice STF', 'mission' => 'Pilotage opérationnel, sécurité et gestion des accès de la plateforme.'],
                    ['role' => 'Responsables programmes', 'mission' => 'Conception et suivi des parcours bénéficiaires et du mentorat.'],
                    ['role' => 'Responsable contenus', 'mission' => 'Animation éditoriale du site et cohérence de la communication.'],
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
                    ['label' => 'Mentées inscrites', 'value' => '2 400'],
                    ['label' => 'Mentées actives', 'value' => '1 860'],
                    ['label' => 'Mentores validées', 'value' => '180'],
                    ['label' => 'Binômes créés', 'value' => '950'],
                    ['label' => 'Sessions réalisées', 'value' => '6 240'],
                    ['label' => 'Taux de rétention', 'value' => '84%'],
                    ['label' => 'Modules complétés', 'value' => '3 100'],
                    ['label' => 'Badges délivrés', 'value' => '1 420'],
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
                'description' => 'Institutions, fondations et entreprises partenaires soutiennent STF financièrement, techniquement ou en mettant à disposition des mentores.',
            ]],
            ['partenaires', 'cta', 'text', [
                'title' => 'Devenir partenaire de STF',
                'body' => "Financement de bourses, mise à disposition de mentores, accès à des rapports d'impact agrégés : plusieurs formes de partenariat sont possibles.",
            ]],

            // --- programmes ---------------------------------------------------
            ['programmes', 'hero', 'hero', [
                'eyebrow' => 'Programmes',
                'title' => 'Des parcours pour chaque étape',
                'description' => "De la découverte en primaire à la préparation à l'insertion professionnelle, chaque programme STF a des objectifs, une cible et des modalités de participation claires.",
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
