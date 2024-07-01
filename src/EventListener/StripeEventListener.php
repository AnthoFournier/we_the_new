<?php

namespace App\EventListener;

use App\Entity\Order\Order;
use App\Event\StripeEvent;
use Psr\Log\LoggerInterface;
use App\Entity\Order\Payment;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Order\OrderRepository;
use App\Repository\Order\PaymentRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'payment_intent.succeeded', method: 'onPaymentSucceed')]
#[AsEventListener(event: 'payment_intent.payment_failed', method: 'onPaymentFailed')]
class StripeEventListener
{
    public function __construct(
        private EntityManagerInterface $em,
        private OrderRepository $orderRepository,
        private PaymentRepository $paymentRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function onPaymentSucceed(StripeEvent $stripeEvent): void
    {
        $ressource = $stripeEvent->getRessource();

        if (!$ressource) {
            throw new \InvalidArgumentException('The event resource is missing');
        }

        //On récupère le payment associé en BDD
        $payment = $this->paymentRepository->find($ressource->metadata->payment_id);
        //On récupère la commande associée en BDD
        $order = $this->orderRepository->find($ressource->metadata->order_id);

        if (!$payment || !$order) {
            throw new \InvalidArgumentException('The payment or the order is missing');
        }

        //On met à jour le statut du paiement
        $payment->setStatus(Payment::STATUS_PAID);
        $order->setStatus(Order::STATUS_SHIPPING);

        $this->em->persist($payment);
        $this->em->persist($order);
        $this->em->flush();
    }

    public function onPaymentFailed(StripeEvent $stripeEvent): void
    {
        $ressource = $stripeEvent->getRessource();

        if (!$ressource) {
            throw new \InvalidArgumentException('The event resource is missing');
        }

        //On récupère le payment associé en BDD
        $payment = $this->paymentRepository->find($ressource->metadata->payment_id);
        //On récupère la commande associée en BDD
        $order = $this->orderRepository->find($ressource->metadata->order_id);

        if (!$payment || !$order) {
            throw new \InvalidArgumentException('The payment or the order is missing');
        }

        //On met à jour le statut du paiement
        $payment->setStatus(Payment::STATUS_FAILED);
        $order->setStatus(Order::STATUS_AWAITING_PAYMENT);
        $order->setNumber('ORD-' . $order->getId() . '-' . date('YmdHis'));

        $this->em->persist($payment);
        $this->em->persist($order);
        $this->em->flush();
    }
}
