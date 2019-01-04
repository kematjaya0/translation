<?php

/**
 * Description of Configuration
 *
 * @author NUR HIDAYAT
 */

namespace Kematjaya\Translation\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface{
    
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kematjaya');
        
        $rootNode
                ->children()
                    ->arrayNode('translation')
                ->end()
            ->end();
        
        return $treeBuilder;
    }
}
