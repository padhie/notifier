<?php

namespace App\Controller;

use App\Entity\Inbound;
use App\Repository\InboundRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class IndexController extends AbstractController
{
    private Security $security;
    private InboundRepository $inboundRepository;

    public function __construct(
        Security $security,
        InboundRepository $inboundRepository
    ) {
        $this->security = $security;
        $this->inboundRepository = $inboundRepository;
    }
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        $user = $this->security->getUser();
        assert($user instanceof UserInterface);

        $username = $user->getUsername();

        $inbounds = $this->inboundRepository->findBy([
            'username' => $username,
        ]);

        return $this->render('index/index.html.twig', [
            'username' => $username,
            'countInbound' => count($inbounds),
            'countOutbound' => array_sum(
                array_map(
                    static fn(Inbound $inbound): int => $inbound->getOutbounds()->count(),
                    $inbounds
                )
            ),
        ]);
    }
}
