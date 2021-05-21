<?php

namespace App\Controller;

use App\Entity\Outbound;
use App\Repository\InboundRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CallController extends AbstractController
{
    private const PARAM_PATTERN = '${param.%s}';

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

        $success = 0;
        $error = 0;
        foreach ($inbound->getOutbounds() as $outbound) {
            $result = $this->executeCall($request, $outbound);
            if ($result === true) {
                $success++;
            } else {
                $error++;
            }
        }

        $responseBody = json_encode(
            [
                'success' => $success,
                'error' => $error,
            ],
            JSON_THROW_ON_ERROR
        );

        $response = new Response();
        $response->setStatusCode(200);
        $response->setContent($responseBody);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    private function executeCall(Request $request, Outbound $outbound): bool
    {
        $url = $outbound->getUrl();
        $body = $outbound->getBody();
        $type = $outbound->getType();

        $method = 'GET';
        if ($body !== '') {
            $method = 'POST';
        }

        foreach($request->query->getIterator() as $key => $value) {
            $search = sprintf(self::PARAM_PATTERN, $key);
            $url = str_replace($search, $value, $url);
            $body = str_replace($search, $value, $body);
        }

        foreach($request->request->getIterator() as $key => $value) {
            $search = sprintf(self::PARAM_PATTERN, $key);
            $url = str_replace($search, $value, $url);
            $body = str_replace($search, $value, $body);
        }

        $header = [];
        if ($type === 'json') {
            $header['Content-Type'] = 'application/json';
        }

        try {
            $this->client->request(
                $method,
                $url,
                [
                    'timeout' => 1,
                    'body' => $body,
                    'headers' => $header,
                ]
            );
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }
}
