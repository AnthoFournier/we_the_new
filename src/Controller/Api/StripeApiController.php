<?php

namespace App\Controller\Api;

use App\Factory\StripeFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/payment/stripe', name: 'api.payment.stripe',)]
class StripeApiController extends AbstractController
{
    #[Route('/notify', name: '.notify', methods: ['POST'])]
    public function notify(Request $request, StripeFactory $stripeFactory): JsonResponse
    {
        //On récupère la clef public dans l'entête de la requête
        $signature = $request->headers->get('stripe-signature');

        if (!$signature) {
            throw new \InvalidArgumentException('Stripe signature is missing');
        }

        return $stripeFactory->handleStripeRequest($signature, $request->getContent());
    }
}
