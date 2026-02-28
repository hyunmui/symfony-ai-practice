<?php

namespace App\Controller;

use App\Repository\NewsletterRepository;
use App\Repository\SubscriberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(
        NewsletterRepository $newsletterRepository,
        SubscriberRepository $subscriberRepository,
    ): Response {
        return $this->render('dashboard/index.html.twig', [
            'newsletters' => $newsletterRepository->findLatest(5),
            'total_newsletters' => $newsletterRepository->count(),
            'total_subscribers' => $subscriberRepository->count(),
            'active_subscribers' => $subscriberRepository->countActive(),
        ]);
    }
}
