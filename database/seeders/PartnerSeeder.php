<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

/**
 * Partenaires affichés côté vitrine (page /partenaires, bandeau accueil) avant
 * l'ajout de la gestion (CRUD + logo) en back-office. Sans logo par défaut :
 * à compléter depuis le back-office (Contenu > Partenaires).
 */
class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Fondation Numérique',
            'Institut des Sciences Ouest',
            'Coalition STIM Afrique',
            'Fonds Jeunes Talents',
            'Réseau Femmes Ingénieures',
            'Agence du Numérique',
        ];

        foreach ($names as $order => $name) {
            Partner::updateOrCreate(['name' => $name], ['order' => $order]);
        }
    }
}
