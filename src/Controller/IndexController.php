<?php

namespace App\Controller;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index()
    {
        $username = $this->getUser()->getName();
        $key = $this->getParameter("mercure_secret_key");

        $configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($key)
        );

        $builder = $configuration->builder()
            ->withClaim("mercure", ["subscribe" => [sprintf("/%s", $username)]]);

        $token = $builder->getToken($configuration->signer(), $configuration->signingKey());

        $token = $token->toString();

        $response = $this->render("index/index.html.twig", ['controller_name' => "Index"]);

        $response->headers->setCookie(
            new Cookie(
                "mercureAuthorization",
                $token,
                (new \DateTime())
                    ->add(new \DateInterval('PT2H')),
                './well-known/mercure',
                null,
                false,
                true,
                false,
                'strict'
            )
        );

        return $response;
    }
}
