<?php

/**
 * Description of TranslationBundle
 *
 * @author NUR HIDAYAT
 */

namespace Kematjaya\Translation;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TranslationBundle extends Bundle {
    
    public function build(ContainerBuilder $container)
    {
        //$container->addCompilerPass(new SerializerConfigurationPass());
    }
    
}
