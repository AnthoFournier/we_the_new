<?php

namespace App\Controller\Frontend;

use App\Form\CartType;
use App\Manager\CartManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/panier', name: 'app.cart.show')]
    public function index(CartManager $cartManager, Request $request): Response
    {
        $cart = $cartManager->getCurrentCart();

        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cartManager->save($cart);

            $this->addFlash('success', 'Votre panier a bien été enregistré.');

            return $this->redirectToRoute('app.cart.show'); //pour afficher le message flash
        }

        return $this->render('Frontend/Cart/index.html.twig', [
            'cart' => $cart,
            'form' => $form,
        ]);
    }
}
