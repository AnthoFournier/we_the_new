<?php

namespace App\Controller\Frontend;

use App\Entity\User;
use App\Entity\Address;
use App\Form\AddressType;
use App\Manager\CartManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/checkout', name: 'app.checkout')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private CartManager $cartManager,
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/address', name: '.address', methods: ['POST', 'GET'])]
    public function index(Request $request): Response
    {
        $cart = $this->cartManager->getCurrentCart();

        if ($cart->getOrderItems()->isEmpty()) {
            $this->addFlash('danger', 'Votre panier est vide, veuillez ajouter des produits');
            return $this->redirectToRoute('app.cart.show');
        }


        /** @var User $user */
        $user = $this->getUser();

        if ($user->getDefaultAddress()) {
            $address = clone $user->getDefaultAddress();
        } else if (!$user->getAddresses()->isEmpty()) {
            $address = clone $user->getAddresses()->first();
        } else {
            $address = new Address();
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$user->hasAddress($address)) {
                $user->addAddress($address);

                $this->em->persist($address);
                $this->em->flush();
            }

            return $this->redirectToRoute('app.checkout.shipping');
        }

        return $this->render('Frontend/Checkout/address.html.twig', [
            'cart' => $cart,
            'form' => $form,
            'addresses' => $user->getAddresses(),
        ]);
    }

    #[Route('/shipping', name: '.shipping', methods: ['POST', 'GET'])]
    public function shipping(): Response
    {
        dd('shipping');

        //TODO: Implement shipping method selection

    }
}
