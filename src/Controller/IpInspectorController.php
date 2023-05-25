<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IpInspectorController extends AbstractController
{

	public HttpClientInterface $httpClient;

	#[Route('/ip-inspector', name: 'ip_inspector', methods: 'get')]
	public function checkIp() {
		return $this->render('ip-inspector.html.twig');
	}

	#[Route('/ip-info', name: 'ip_info', methods: 'POST')]
	public function getIpInfo(Request $request): Response
	{
		$ip = 'http://ip-api.com/json/' . $request->request->get('id');
		$response = $this->httpClient->request('GET', $ip);
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

	public function __construct(HttpClientInterface $httpClient)
	{
		$this->httpClient = $httpClient;
	}
}
