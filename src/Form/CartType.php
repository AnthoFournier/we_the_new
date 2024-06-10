<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Order\Order;
use App\Form\EventListener\ClearCartListener;
use App\Form\EventListener\RemoveItemCartListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderItems', CollectionType::class, [
                'label' => false,
                'entry_type' => CartItemType::class,
            ])
            ->add('clear', SubmitType::class, [
                'label' => 'Vider le panier',
                'attr' => [
                    'class' => 'btn btn-danger',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Valider la modification de votre panier',
                'attr' => [
                    'class' => 'btn btn-secondary',
                ],
            ]);

        $builder
            ->addEventSubscriber(new ClearCartListener)
            ->addEventSubscriber(new RemoveItemCartListener);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
