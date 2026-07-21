<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

/**
 * Les 4 programmes phares réels de STF (cf. bilan 2016-2024) : Inspir'STEM ELLES,
 * STEM Challenge, Empow'Her Science Expo et Woman Impact Science Hub (WISH).
 *
 * Chaque entrée référence un ancien slug fictif (`old_slug`, hérité des premières
 * données de démonstration de la plateforme) : si une ligne existe encore sous cet
 * ancien slug, elle est renommée en place plutôt que dupliquée. Sinon, la ligne est
 * mise à jour ou créée directement sous son slug réel — sûr à rejouer, y compris en
 * production, quel que soit l'état de départ de la table `programs`.
 */
class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'old_slug' => 'mentorat-stim',
                'slug' => 'inspir-stem-elles',
                'name' => "Inspir'STEM ELLES",
                'audience' => 'Collège · Lycée · Université',
                'description' => "Programme phare de STF lancé en 2020 : sensibilisation, mentorat et accompagnement individuel des filles vers les filières scientifiques, technologiques, d'ingénierie et mathématiques (STIM).",
                'color' => 'blue',
                'status' => 'en_cours',
            ],
            [
                'old_slug' => 'decouverte-primaire',
                'slug' => 'stem-challenge',
                'name' => 'STEM Challenge',
                'audience' => 'Collège · Lycée · Université',
                'description' => 'Un concours qui met au défi les filles et jeunes femmes à travers des projets scientifiques et technologiques innovants, pour révéler et récompenser les talents STIM.',
                'color' => 'orange',
                'status' => 'en_cours',
            ],
            [
                'old_slug' => 'campus-numerique',
                'slug' => 'empowher-science-expo',
                'name' => "Empow'Her Science Expo",
                'audience' => 'Lycée · Université · Grand public',
                'description' => "La foire scientifique annuelle de STF : expositions, panels et sessions de mentorat pour mettre en lumière les réalisations des jeunes femmes en sciences. La 2e édition (novembre 2024) a réuni plus de 1500 visiteurs et 15 exposantes.",
                'color' => 'green',
                'status' => 'en_cours',
            ],
            [
                'old_slug' => 'leadership-jeunes-femmes',
                'slug' => 'wish',
                'name' => 'Woman Impact Science Hub (WISH)',
                'audience' => 'Université · Entrepreneuses',
                'description' => "Un incubateur qui accompagne des projets portés par des femmes dans les STIM — agriculture durable, énergies renouvelables, technologies — présentés chaque année à l'Empow'Her Science Expo.",
                'color' => 'blue',
                'status' => 'a_venir',
            ],
        ];

        foreach ($programs as $program) {
            $oldSlug = $program['old_slug'];
            $attributes = collect($program)->except('old_slug')->toArray();

            $existing = Program::where('slug', $oldSlug)->first();

            if ($existing) {
                $existing->update($attributes);
            } else {
                Program::updateOrCreate(['slug' => $attributes['slug']], $attributes);
            }
        }
    }
}
