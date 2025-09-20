<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private string $fromEmail = 'noreply@mypos.com'
    ) {
    }

    public function sendPasswordResetEmail(User $user, string $token): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($user->getEmail())
            ->subject('Password Reset Request')
            ->html($this->twig->render('emails/password_reset.html.twig', [
                'user' => $user,
                'token' => $token,
            ]));

        $this->mailer->send($email);
    }

    public function sendWelcomeEmail(User $user): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($user->getEmail())
            ->subject('Welcome to MyPOS!')
            ->html($this->twig->render('emails/welcome.html.twig', [
                'user' => $user,
            ]));

        $this->mailer->send($email);
    }
}
