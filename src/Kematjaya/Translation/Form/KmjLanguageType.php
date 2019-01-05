<?php

/**
 * Description of MsLanguageType
 *
 * @author NUR HIDAYAT
 */

namespace Kematjaya\Translation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class KmjLanguageType extends AbstractType{
    
    private $container;
    
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('key', TextType::class, array(
               'attr' => ['class' => 'form-control', 'placeholder' => 'key', 'required' => true]
            ));
        
        foreach($this->container->getParameter('locale_supported') as $v) {
            $builder->add($v, TextType::class, array(
               'attr' => ['class' => 'form-control', 'placeholder' => strtolower(ucwords($v)), 'required' => true]
            ));
        }
    }
}
