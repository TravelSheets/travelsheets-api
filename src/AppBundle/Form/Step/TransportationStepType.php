<?php
/**
 * Created by PhpStorm.
 * User: quentinmachard
 * Date: 09/08/2017
 * Time: 12:20
 */

namespace AppBundle\Form\Step;


use AppBundle\Entity\Step\TransportationStep;
use AppBundle\Form\AbstractStepType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransportationStepType extends AbstractStepType
{
    public function addCustomFields(FormBuilderInterface $builder, array $options)
    {
//        $builder->add('from', EntityType::class, array(
//            'class'
//            'label' => 'Lieu de départ',
//        ));
//
//        $builder->add('to', EntityType::class, array(
//            'label' => 'Lieu d\'arrivée',
//        ));

        $builder->add('type', ChoiceType::class, array(
            'choices' => array(
                'Avion' => 'plane',
                'Bateau' => 'boat',
                'Voiture' => 'car',
                'Train' => 'train',
                'Taxi' => 'taxi',
                'Vélo' => 'bike',
                'Transports en communs' => 'subway',
                'Autre' => 'other',
            ),
            'label' => 'Moyen de transport',
            'placeholder' => 'Choisir un moyen de transport'
        ));

        $builder->add('company', TextType::class, array(
            'required' => false,
        ));

        $builder->add('bookingNumber', TextType::class, array(
            'required' => false,
        ));

        $builder->add('flightNumber', TextType::class, array(
            'required' => false,
        ));

        $builder->add('openingLuggage', TimeType::class, array(
            'required' => false,
        ));

        $builder->add('closingLuggage', TimeType::class, array(
            'required' => false,
        ));

        $builder->add('seat', TextType::class, array(
            'required' => false,
        ));
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => TransportationStep::class,
        ));
    }
}