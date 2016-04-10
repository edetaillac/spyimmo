<?php

namespace SpyimmoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class OfferVisitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('visitDate', DateTimeType::class, array('data' => new \DateTime('now')));
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'SpyimmoBundle\Entity\Offer',
        );
    }

    public function getName()
    {
        return 'Offer';
    }
}