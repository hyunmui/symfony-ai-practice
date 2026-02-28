<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Form\SubscriberType;
use App\Repository\SubscriberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/subscriber')]
class SubscriberController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SubscriberRepository $subscriberRepository,
    ) {
    }

    #[Route('', name: 'subscriber_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('subscriber/index.html.twig', [
            'subscribers' => $this->subscriberRepository->findBy([], ['subscribedAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'subscriber_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $subscriber = new Subscriber();
        $subscriber->setActive(true);
        $form = $this->createForm(SubscriberType::class, $subscriber);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->em->persist($subscriber);
                $this->em->flush();
                $this->addFlash('success', '구독자가 추가되었습니다.');

                return $this->redirectToRoute('subscriber_index');
            } catch (\Exception) {
                $this->addFlash('error', '이미 등록된 이메일입니다.');
            }
        }

        return $this->render('subscriber/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'subscriber_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Subscriber $subscriber): Response
    {
        $form = $this->createForm(SubscriberType::class, $subscriber);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', '구독자 정보가 수정되었습니다.');

            return $this->redirectToRoute('subscriber_index');
        }

        return $this->render('subscriber/edit.html.twig', [
            'form' => $form,
            'subscriber' => $subscriber,
        ]);
    }

    #[Route('/{id}/delete', name: 'subscriber_delete', methods: ['POST'])]
    public function delete(Request $request, Subscriber $subscriber): Response
    {
        if ($this->isCsrfTokenValid('delete-subscriber-'.$subscriber->getId(), $request->getPayload()->getString('_token'))) {
            $this->em->remove($subscriber);
            $this->em->flush();
            $this->addFlash('success', '구독자가 삭제되었습니다.');
        }

        return $this->redirectToRoute('subscriber_index');
    }

    #[Route('/{id}/toggle', name: 'subscriber_toggle', methods: ['POST'])]
    public function toggle(Request $request, Subscriber $subscriber): Response
    {
        if ($this->isCsrfTokenValid('toggle-subscriber-'.$subscriber->getId(), $request->getPayload()->getString('_token'))) {
            $subscriber->setActive(!$subscriber->isActive());
            $this->em->flush();
            $status = $subscriber->isActive() ? '활성화' : '비활성화';
            $this->addFlash('success', "구독자가 {$status}되었습니다.");
        }

        return $this->redirectToRoute('subscriber_index');
    }
}
