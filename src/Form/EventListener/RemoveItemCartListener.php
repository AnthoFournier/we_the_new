<?php

namespace App\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\ClickableInterface;

class RemoveItemCartListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    public function onPostSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $cart = $event->getData();

        if (!$cart) {
            return;
        }

        foreach ($form->get('orderItems')->all() as $child) {
            /** @var ClickableInterface $removeBtn */
            $removeBtn = $child->get('remove');

            if ($removeBtn->isClicked()) {
                $cart->removeOrderItem($child->getData());
                return;
            }
        }
    }
}
