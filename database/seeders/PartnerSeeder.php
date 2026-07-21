<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

/**
 * Partenaires réels de STF (bilan 2016-2024), affichés côté vitrine sur 2 sections
 * de la page /partenaires : « Ils nous font confiance » (institutions, bailleurs et
 * reconnaissance officielle) et « Partenaires » (universités et structures relais
 * de terrain). Sans logo par défaut : à compléter depuis le back-office
 * (Contenu > Partenaires).
 */
class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        // Ministères, bailleurs, agences des Nations Unies et de coopération.
        $trusted = [
            "Ministère de l'Enseignement Supérieur et de la Recherche Scientifique",
            "Ministère de l'Éducation Nationale et de l'Alphabétisation (MENA)",
            'République de Côte d\'Ivoire — Ministère de la Promotion de la Jeunesse',
            'UNESCO',
            "Institut de Recherche pour le Développement (IRD) — République Française",
            'Agence Française de Développement (AFD)',
            'MTN Côte d\'Ivoire',
            'Contrat de Désendettement et de Développement (C2D)',
        ];

        // Universités, grandes écoles et structures relais des programmes STF.
        $partners = [
            'Université Félix Houphouët-Boigny (UFHB)',
            'Conseil National des Jeunes de Côte d\'Ivoire (CNJCI)',
            'Université Jean Lorougnon Guédé',
            'ENSEA',
            'Université de Man (U-MAN)',
            "Université Virtuelle de Côte d'Ivoire (UVCI)",
            'Université Alassane Ouattara (UAO)',
            'Université Peleforo Gon Coulibaly (UPGC)',
            'Université de San Pedro (USP)',
            'Université Nangui Abrogoua (UNA)',
        ];

        $order = 0;
        foreach ($trusted as $name) {
            Partner::updateOrCreate(['name' => $name], ['order' => $order++, 'type' => 'confiance']);
        }
        foreach ($partners as $name) {
            Partner::updateOrCreate(['name' => $name], ['order' => $order++, 'type' => 'partenaire']);
        }
    }
}
