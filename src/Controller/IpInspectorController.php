<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IpInspectorController extends AbstractController
{

	public HttpClientInterface $httpClient;

	private string $steamUrl = 'http://api.steampowered.com/IDOTA2Match_570';
	private string $steamKey = 'xxx';

	#[Route('/ip-inspector', name: 'ip_inspector', methods: 'get')]
	public function checkIp() {
		return $this->render('ip-inspector.html.twig');
	}

	#[Route('/ip-info', name: 'ip_info', methods: 'POST')]
	public function getIpInfo(Request $request): Response
	{
		$url = 'http://ip-api.com/json/' . $request->request->get('ip');

		$response = $this->httpClient->request('GET', $url);
		$statusCode = $response->getStatusCode();

		if($statusCode == 200) {
			$content = $response->toArray();
		}

		return $this->render('ip-inspector.html.twig', [
			'content' => $content
		]);
	}

	#[Route('/json/ip-inspector/{ip}', methods: 'GET')]
	public function getIpInformation(string $ip): JsonResponse
	{
		$url = 'http://ip-api.com/json/' . $ip;
		$response = $this->httpClient->request('GET', $url);
		$statusCode = $response->getStatusCode();

		if($statusCode == 200) {
			$content = $response->toArray();
		}

		return $this->json($content);
	}

	#[Route('/dota', name: 'dota', methods: 'get')]
	public function dotaInfo(int $retryCount = 3, int $retryDelay = 5000): JsonResponse
	{
		$httpClient = HttpClient::create();

		$url = $this->steamUrl . '/GetTopLiveGame/v1/?key=' . $this->steamKey . '&partner=0';
		$urlChina = $this->steamUrl . '/GetTopLiveGame/v1/?key=' . $this->steamKey . '&partner=1';

		$mergedResults = [];

		for ($retry = 0; $retry < $retryCount; $retry++) {
			try {
				$response = $httpClient->request('GET', $url);
				$proMatchesLiveEast = $response->toArray()['game_list'];

				$responseChina = $httpClient->request('GET', $urlChina);
				$proMatchesLiveChina = $responseChina->toArray()['game_list'];

				$mergedResults = array_merge($proMatchesLiveEast, $proMatchesLiveChina);
				break;
			} catch (TransportException $exception) {
				if ($retry === $retryCount - 1) {
					throw $exception;
				}

				usleep($retryDelay);
			}
		}

		return $this->json($mergedResults);
	}

	public function __construct(HttpClientInterface $httpClient)
	{
		$this->httpClient = $httpClient;
	}
}
