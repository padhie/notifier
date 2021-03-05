<?php

namespace App\Controller;

use App\Entity\Outbound;
use App\Repository\InboundRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallController extends AbstractController
{
    private HttpClientInterface $client;
    private InboundRepository $inboundRepository;

    public function __construct(
        HttpClientInterface $client,
        InboundRepository $inboundRepository
    ) {
        $this->client = $client;
        $this->inboundRepository = $inboundRepository;
    }

    /**
     * @Route("/call/{hash}", name="call")
     */
    public function index(Request $request, string $hash): Response
    {
        $inbound = $this->inboundRepository->findOneBy([
            'hash' => $hash,
        ]);

        if ($inbound === null) {
            return (new Response())
                ->setStatusCode(404);
        }

        foreach ($inbound->getOutbounds() as $outbound) {
            $this->executeCall($request, $outbound);
        }

        return (new Response())
            ->setStatusCode(200);
    }

    private function executeCall(Request $request, Outbound $outbound): void
    {
        $url = $outbound->getUrl();
        $body = $outbound->getBody();

        $method = 'GET';
        if ($body !== '') {
            $method = 'POST';
        }

        foreach($request->query->getIterator() as $key => $value) {
            $url = str_replace($key, $value, $url);
            $body = str_replace($key, $value, $body);
        }

        foreach($request->request->getIterator() as $key => $value) {
            $url = str_replace($key, $value, $url);
            $body = str_replace($key, $value, $body);
        }

        try {
            $this->client->request(
                $method,
                $url,
                [
                    'timeout' => 1,
                    'body' => $body,
                ]
            );
        } catch (TransportExceptionInterface $e) {
            // do nothing
        }
    }
}
