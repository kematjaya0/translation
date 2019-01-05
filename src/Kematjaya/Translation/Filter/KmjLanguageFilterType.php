<?php

/**
 * Description of MsLanguageFilterType
 *
 * @author NUR HIDAYAT
 */

namespace Kematjaya\Translation\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

class KmjLanguageFilterType extends AbstractType{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('key', Filters\TextFilterType::class, array(
               'attr' => ['class' => 'form-control', 'placeholder' => 'key']
            ))
            ->add('translated', Filters\TextFilterType::class, array(
               'attr' => ['class' => 'form-control', 'placeholder' => 'translated']
            ));
    }
    
    public function getBlockPrefix()
    {
        return 'ms_language_filter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //'csrf_protection'   => false,
            'validation_groups' => array('filtering')
        ));
    }
}
