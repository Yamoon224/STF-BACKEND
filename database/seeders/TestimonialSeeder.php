<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Témoignages réels, sourcés du bilan 2016-2024 de STF : chaque citation est
 * attribuée exactement à sa source (aucune parole n'est prêtée à une personne
 * nommée sans que le bilan ne la cite explicitement).
 *
 * Retire au passage les 3 témoignages fictifs de démonstration (Aïcha D., Fatou K.,
 * Mariam S.) s'ils existent encore, sans toucher aux autres témoignages déjà
 * ajoutés depuis le back-office.
 */
class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        Testimonial::whereIn('name', ['Aïcha D.', 'Fatou K.', 'Mariam S.'])->delete();

        $empowher = Program::where('slug', 'empowher-science-expo')->first();

        $testimonials = [
            [
                'name' => 'Christelle Ogo',
                'role' => 'Présidente & Fondatrice de STF',
                'quote' => "Bien que nous n'ayons pas remporté de prix lors de cet événement à Johannesburg, cette expérience a été enrichissante. En tant que seule représentante de la Côte d'Ivoire et de l'Afrique francophone, j'ai constaté que nos efforts sont reconnus à l'échelle continentale et mondiale. Cette reconnaissance nous motive à intensifier nos actions et à encourager davantage de filles à s'engager dans les STIM.",
                'program_id' => null,
                'order' => 1,
            ],
            [
                'name' => 'UNESCO Paris',
                'role' => 'Reconnaissance internationale, 2023',
                'quote' => "Christelle Ogo est une figure emblématique de la promotion des études STEM pour les jeunes filles en Côte d'Ivoire et représente le leadership scientifique d'une nouvelle génération de femmes africaines.",
                'program_id' => null,
                'order' => 2,
            ],
            [
                'name' => 'Sciences & Technologies au Féminin',
                'role' => "Retour des participantes, Empow'Her Science Expo 2024",
                'quote' => "Les jeunes esprits brillent de mille feux après avoir participé à l'Empow'Her Science Expo. Plus que de simples informations, ces expériences les ont nourries de confiance en leurs propres capacités à façonner l'avenir.",
                'program_id' => $empowher?->id,
                'order' => 3,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(
                ['name' => $testimonial['name'], 'role' => $testimonial['role']],
                $testimonial
            );
        }
    }
}
