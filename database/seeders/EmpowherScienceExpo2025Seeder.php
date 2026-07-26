<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Enregistre le compte-rendu réel de la 3e édition de l'Empow'Her Science Expo
 * (2-3 octobre 2025, Université Nangui Abrogoua), d'après le rapport officiel de STF.
 */
class EmpowherScienceExpo2025Seeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@stf-organisation.org')->first();

        CmsPage::updateOrCreate(
            ['slug' => 'empowher-science-expo-2025'],
            [
                'title' => "Empow'Her Science Expo 2025 : plus de 1500 participants à l'Université Nangui Abrogoua",
                'type' => 'article',
                'category' => 'Événement',
                'excerpt' => "Les 2 et 3 octobre 2025, l'amphithéâtre de la présidence de l'Université Nangui Abrogoua (Abobo) a accueilli la 3e édition de l'Empow'Her Science Expo sur le thème « Jeunes femmes et STIM au cœur de l'innovation pour une transition énergétique durable ». 23 stands, plus de 1500 participants et plus de 1000 élèves ont pris part à l'événement.",
                'body' => "Organisée par Sciences & Technologies au Féminin, la 3e édition de l'Empow'Her Science Expo s'est tenue les 2 et 3 octobre 2025 à l'amphithéâtre de la présidence de l'Université Nangui Abrogoua d'Abobo, sur le thème « Jeunes femmes et STIM au cœur de l'innovation pour une transition énergétique durable ». Deux panels ont rythmé l'événement — « Briser les barrières : Genre & accès aux filières STIM » et « Jeunes femmes innovatrices : parcours inspirants, solutions locales en énergie propre » — aux côtés d'une exposition scientifique de 23 projets (entomologie, agriculture durable, biologie, maison connectée), de sessions de mentorat et d'un concours de pitchs. Le 1er prix est revenu au projet « Néco et Astou » (pesticides biologiques) de Krysley Tracey Kolman (UFR Biosciences, UFHB), le 2e prix à Amoin Gervaise Kouamé (UNA) pour un complément alimentaire phytoestrogénique à base de feuilles de manioc, et le 3e prix à Priscille Grâce Noubiwe pour le projet Doc@Dom. Plus de 1500 participants et 1000 élèves ont été touchés, en présence des Directions Régionales de l'Éducation Nationale (DREN) Abidjan 1 et 2.",
                'status' => 'publie',
                'author_id' => $admin?->id,
                'published_at' => '2025-10-03',
            ]
        );
    }
}
