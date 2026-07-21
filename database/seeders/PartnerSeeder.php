<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

/**
 * Partenaires réels de STF (bilan 2016-2024), affichés côté vitrine
 * (page /partenaires, bandeau accueil). Sans logo par défaut :
 * à compléter depuis le back-office (Contenu > Partenaires).
 */
class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            "Ministère de l'Enseignement Supérieur et de la Recherche Scientifique",
            "Ministère de l'Éducation Nationale et de l'Alphabétisation (MENA)",
            'République de Côte d\'Ivoire — Ministère de la Promotion de la Jeunesse',
            'Université Félix Houphouët-Boigny (UFHB)',
            'UNESCO',
            "Institut de Recherche pour le Développement (IRD) — République Française",
            "Agence Française de Développement (AFD)",
            'MTN Côte d\'Ivoire',
            'Conseil National des Jeunes de Côte d\'Ivoire (CNJCI)',
            'Université Jean Lorougnon Guédé',
            'ENSEA',
            'Université de Man (U-MAN)',
            "Université Virtuelle de Côte d'Ivoire (UVCI)",
            'Université Alassane Ouattara (UAO)',
            'Université Peleforo Gon Coulibaly (UPGC)',
            'Université de San Pedro (USP)',
            'Université Nangui Abrogoua (UNA)',
            'Contrat de Désendettement et de Développement (C2D)',
        ];

        foreach ($names as $order => $name) {
            Partner::updateOrCreate(['name' => $name], ['order' => $order]);
        }
    }
}
