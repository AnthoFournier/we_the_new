<?php

namespace App\Manager;

use App\Entity\Order\Order;
use App\Factory\OrderFactory;
use App\Storage\CartSessionStorage;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Order\OrderRepository;
use Symfony\Bundle\SecurityBundle\Security;

class CartManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private OrderFactory $orderFactory,
        private CartSessionStorage $cartSessionStorage,
        private Security $security,
        private OrderRepository $orderRepo,
    ) {
    }
    /**
     * Get the current cart of the user
     *
     * @return Order
     */
    public function getCurrentCart(): Order
    {
        $cart = $this->cartSessionStorage->getCart();

        //On vérifie si un utilusateur est connecté actuellement
        $user = $this->security->getUser();
        if (!$cart) {
            if ($user) {
                //Si l'utilisateur est connecté et qu'il n'a pas de panier en session, on récupère son panier en base de données
                //On récupère le dernier panier de l'utilisateur
                $cart = $this->orderRepo->findLastCartByUser($user);
            }
            //Si on a un panier en SESSION sans utilisateur connecté
            //Et qu'on a un utilisateur de connecté
            // On veut rattacher le panier à l'utilisateur connecté
        } else if (!$cart->getUser() && $user) {
            $cart->setUser($user);
            $cartDb = $this->orderRepo->findLastCartByUser($user);

            if ($cartDb) {
                $cart = $this->mergeCart($cartDb, $cart);
            }
        }
        return $cart ?? $this->orderFactory->create();
    }

    /**
     * Save the cart in the database
     *
     * @param Order $order
     * @return void
     */
    public function save(Order $order): void
    {
        $this->cartSessionStorage->setCart($order);
        $this->em->persist($order);
        $this->em->flush();
    }

    private function mergeCart(Order $cartDb, Order $cartSession): Order
    {
        foreach ($cartDb->getOrderItems() as $item) {
            $cartSession->addOrderItem($item);
        }

        $this->em->remove($cartDb);
        $this->em->flush();

        return $cartSession;
    }
}
