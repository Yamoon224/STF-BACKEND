<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use Illuminate\Database\Seeder;

/**
 * Bourses et portails de bourses réels, issus du « Manuel sur les opportunités et
 * métiers liés aux STIM pour les femmes et les filles » (Projet NEDA, CI-Energies /
 * Ministère des Mines, du Pétrole et de l'Énergie / Banque mondiale). Aucun montant
 * ni échéance n'est fourni par la source pour ces portails officiels : laissés vides
 * jusqu'à ce que STF dispose d'une information précise.
 */
class ScholarshipSeeder extends Seeder
{
    public function run(): void
    {
        $scholarships = [
            [
                'title' => 'Bourses de coopération internationale',
                'provider' => "Ministère des Affaires Étrangères, de l'Intégration Africaine et des Ivoiriens de l'Extérieur",
                'description' => "Site de référence des bourses de coopération internationale accessibles aux élèves et étudiant(e)s ivoirien(ne)s, selon critères.",
                'audience' => "Élèves et étudiant(e)s ivoirien(ne)s (selon critères)",
                'application_url' => 'https://bourses.diplomatie.gouv.ci',
                'order' => 0,
            ],
            [
                'title' => "Bourses d'études à l'étranger — procédure officielle",
                'provider' => 'République de Côte d\'Ivoire — Portail National des Services Publics',
                'description' => "Démarche officielle pour les étudiant(e)s souhaitant poursuivre des études à l'étranger.",
                'audience' => "Étudiant(e)s souhaitant poursuivre des études à l'étranger",
                'application_url' => 'https://www.servicepublic.gouv.ci/accueil/detaildemarcheparticulier/1/457/7',
                'order' => 1,
            ],
            [
                'title' => "Informations officielles sur l'enseignement supérieur",
                'provider' => "Ministère des Affaires Étrangères, de l'Intégration Africaine et des Ivoiriens de l'Extérieur",
                'description' => "Portail d'information officielle sur les bourses liées à l'enseignement supérieur.",
                'audience' => "Élèves et étudiant(e)s",
                'application_url' => 'https://www.bourses.enseignement.gouv.ci',
                'order' => 2,
            ],
            [
                'title' => "Opportunités d'insertion et programmes jeunes",
                'provider' => 'Agence Emploi Jeunes (République de Côte d\'Ivoire)',
                'description' => "Opportunités d'insertion professionnelle et programmes dédiés aux jeunes étudiant(e)s, diplômé(e)s et en insertion.",
                'audience' => "Jeunes (étudiant(e)s, diplômé(e)s, en insertion)",
                'application_url' => 'https://agenceemploijeunes.ci/',
                'order' => 3,
            ],
            [
                'title' => "Bourses d'études à l'étranger — portail gouvernemental",
                'provider' => 'Gouvernement de la République de Côte d\'Ivoire',
                'description' => "Portail gouvernemental général, point d'entrée vers les démarches de bourses d'études à l'étranger.",
                'audience' => 'Grand public',
                'application_url' => 'https://www.gouv.ci',
                'order' => 4,
            ],
        ];

        foreach ($scholarships as $scholarship) {
            Scholarship::updateOrCreate(
                ['title' => $scholarship['title']],
                $scholarship + ['status' => 'ouverte']
            );
        }
    }
}
