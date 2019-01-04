<?php

/**
 * Description of TranslationExtension
 *
 * @author NUR HIDAYAT
 */

namespace Kematjaya\Translation\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class TranslationExtension extends Extension{
    
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $container->setParameter('kematjaya.translation', $config);
        
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        //$loader->load('all.xml');
       
        //$api = $container->getDefinition('Pti\Security\Api\UserProvider');
       // $api->replaceArgument(0, $config['pti_auth']);
        //dump($config);exit;
        //$user = $container->getDefinition('Pti\Security\User\UserProvider');
        //$user->replaceArgument(0, $config['pti_auth']);
    }
}
