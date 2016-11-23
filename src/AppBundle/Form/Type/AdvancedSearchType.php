<?php

namespace AppBundle\Form\Type;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdvancedSearchType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('search_fields');
        $resolver->setRequired('translator');
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var array $searchFields */
        $searchFields = $options['search_fields'];
        /** @var Translator $translator */
        $translator = $options['translator'];

        foreach ($searchFields as $searchField) {
            $builder->add(
                $searchField,
                TextType::class,
                [
                    'label' => $translator->transChoice('fields.'.$searchField, 1),
                    'required' => false,
                ]
            );
        }

        $builder->add('search', SubmitType::class);
    }
}
