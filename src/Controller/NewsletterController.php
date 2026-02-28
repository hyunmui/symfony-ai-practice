<?php

namespace App\Controller;

use App\Entity\Newsletter;
use App\Form\NewsletterType;
use App\Repository\NewsletterRepository;
use App\Repository\SubscriberRepository;
use App\Service\NewsletterAiGenerator;
use App\Service\NewsletterSender;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/newsletter')]
class NewsletterController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NewsletterRepository $newsletterRepository,
        private readonly SubscriberRepository $subscriberRepository,
    ) {
    }

    #[Route('', name: 'newsletter_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('newsletter/index.html.twig', [
            'newsletters' => $this->newsletterRepository->findLatest(50),
        ]);
    }

    #[Route('/new', name: 'newsletter_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $newsletter = new Newsletter();
        $form = $this->createForm(NewsletterType::class, $newsletter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($newsletter);
            $this->em->flush();

            $this->addFlash('success', '뉴스레터가 저장되었습니다.');

            return $this->redirectToRoute('newsletter_show', ['id' => $newsletter->getId()]);
        }

        return $this->render('newsletter/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'newsletter_show', methods: ['GET'])]
    public function show(Newsletter $newsletter): Response
    {
        $subscriberCount = $this->subscriberRepository->countActive();

        return $this->render('newsletter/show.html.twig', [
            'newsletter' => $newsletter,
            'subscriber_count' => $subscriberCount,
        ]);
    }

    #[Route('/{id}/edit', name: 'newsletter_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Newsletter $newsletter): Response
    {
        if ($newsletter->isSent()) {
            $this->addFlash('error', '이미 발송된 뉴스레터는 수정할 수 없습니다.');

            return $this->redirectToRoute('newsletter_show', ['id' => $newsletter->getId()]);
        }

        $form = $this->createForm(NewsletterType::class, $newsletter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', '뉴스레터가 수정되었습니다.');

            return $this->redirectToRoute('newsletter_show', ['id' => $newsletter->getId()]);
        }

        return $this->render('newsletter/edit.html.twig', [
            'form' => $form,
            'newsletter' => $newsletter,
        ]);
    }

    #[Route('/{id}/delete', name: 'newsletter_delete', methods: ['POST'])]
    public function delete(Request $request, Newsletter $newsletter): Response
    {
        if ($this->isCsrfTokenValid('delete-newsletter-'.$newsletter->getId(), $request->getPayload()->getString('_token'))) {
            $this->em->remove($newsletter);
            $this->em->flush();
            $this->addFlash('success', '뉴스레터가 삭제되었습니다.');
        }

        return $this->redirectToRoute('newsletter_index');
    }

    #[Route('/{id}/send', name: 'newsletter_send', methods: ['POST'])]
    public function send(Request $request, Newsletter $newsletter, NewsletterSender $sender): Response
    {
        if ($newsletter->isSent()) {
            $this->addFlash('error', '이미 발송된 뉴스레터입니다.');

            return $this->redirectToRoute('newsletter_show', ['id' => $newsletter->getId()]);
        }

        if (!$this->isCsrfTokenValid('send-newsletter-'.$newsletter->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', '잘못된 요청입니다.');

            return $this->redirectToRoute('newsletter_show', ['id' => $newsletter->getId()]);
        }

        $result = $sender->send($newsletter);

        $newsletter->setStatus(Newsletter::STATUS_SENT);
        $newsletter->setSentAt(new \DateTime());
        $this->em->flush();

        $this->addFlash('success', sprintf(
            '뉴스레터가 발송되었습니다. 성공: %d건, 실패: %d건',
            $result['sent'],
            $result['failed']
        ));

        return $this->redirectToRoute('newsletter_show', ['id' => $newsletter->getId()]);
    }

    #[Route('/ai/generate', name: 'newsletter_ai_generate', methods: ['POST'])]
    public function aiGenerate(Request $request, NewsletterAiGenerator $generator): JsonResponse
    {
        $topic = $request->getPayload()->getString('topic');
        $tone = $request->getPayload()->getString('tone', '전문적인');
        $type = $request->getPayload()->getString('type', 'content');

        if (empty($topic)) {
            return $this->json(['error' => '주제를 입력해주세요.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            if ('subject' === $type) {
                $result = $generator->generateSubject($topic, $tone);
            } else {
                $result = $generator->generateContent($topic, $tone);
            }

            return $this->json(['content' => $result]);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'AI 생성 중 오류가 발생했습니다: '.$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
