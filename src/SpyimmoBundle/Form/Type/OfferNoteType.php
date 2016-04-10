<?php

namespace SpyimmoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class OfferNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('note');
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