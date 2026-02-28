<?php

namespace App\Service;

use App\Entity\Newsletter;
use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NewsletterSender
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly SubscriberRepository $subscriberRepository,
        private readonly string $fromEmail,
        private readonly string $fromName,
    ) {
    }

    /**
     * @return array{sent: int, failed: int}
     */
    public function send(Newsletter $newsletter): array
    {
        $subscribers = $this->subscriberRepository->findActive();

        $sent = 0;
        $failed = 0;

        foreach ($subscribers as $subscriber) {
            try {
                $this->sendToSubscriber($newsletter, $subscriber);
                ++$sent;
            } catch (\Throwable) {
                ++$failed;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    private function sendToSubscriber(Newsletter $newsletter, Subscriber $subscriber): void
    {
        $toName = $subscriber->getName() ?? '';
        $content = $this->buildEmailContent($newsletter, $subscriber);

        $email = (new Email())
            ->from(sprintf('%s <%s>', $this->fromName, $this->fromEmail))
            ->to($toName ? sprintf('%s <%s>', $toName, $subscriber->getEmail()) : $subscriber->getEmail())
            ->subject($newsletter->getSubject())
            ->html($content);

        $this->mailer->send($email);
    }

    private function buildEmailContent(Newsletter $newsletter, Subscriber $subscriber): string
    {
        $name = $subscriber->getName() ?? '구독자';

        return sprintf(
            '<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>%s</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f5f5f5; }
        .container { background: white; padding: 30px; margin: 20px auto; border-radius: 8px; }
        .header { background: #4A90E2; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; margin: -30px -30px 20px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #888; text-align: center; }
        h2 { color: #333; }
        p { line-height: 1.6; color: #555; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>%s</h1>
        </div>
        <p>안녕하세요, %s님!</p>
        %s
        <div class="footer">
            <p>본 이메일은 뉴스레터 구독자에게 발송됩니다.</p>
        </div>
    </div>
</body>
</html>',
            htmlspecialchars($newsletter->getSubject()),
            htmlspecialchars($newsletter->getTitle()),
            htmlspecialchars($name),
            $newsletter->getContent()
        );
    }
}
