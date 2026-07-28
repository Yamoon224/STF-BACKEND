<?php

namespace App\Console\Commands;

use App\Mail\CmsPageNewsletterMail;
use App\Models\CmsPage;
use App\Models\NewsletterSubscriber;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestNewsletterCommand extends Command
{
    protected $signature = 'newsletter:send-test
        {--page= : Slug de la page CMS à utiliser (par défaut : le dernier article publié)}
        {--to=* : Adresse(s) email à qui envoyer le test (par défaut : les 2 adresses de test STF)}';

    protected $description = "Envoie un email de newsletter de test (basé sur un vrai article) à une liste d'adresses de contrôle";

    private const DEFAULT_RECIPIENTS = [
        'armandebahi@sciencesaufeminin.org',
        'yamooon664@gmail.com',
    ];

    public function handle(): int
    {
        $slug = $this->option('page');

        $page = $slug
            ? CmsPage::where('slug', $slug)->first()
            : CmsPage::whereNotNull('category')->latest()->first();

        if (! $page) {
            $this->error($slug ? "Aucune page CMS trouvée pour le slug [{$slug}]." : 'Aucune page CMS disponible.');

            return self::FAILURE;
        }

        $recipients = $this->option('to') ?: self::DEFAULT_RECIPIENTS;

        foreach ($recipients as $email) {
            $subscriber = new NewsletterSubscriber([
                'email' => $email,
                'status' => 'actif',
            ]);
            $subscriber->id = 0;

            try {
                Mail::to($email)->send(new CmsPageNewsletterMail($page, $subscriber));
                $this->info("[OK] {$email}");
            } catch (\Throwable $e) {
                $this->error("[ERREUR] {$email} : {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
