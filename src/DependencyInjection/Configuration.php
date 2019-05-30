<?php

namespace Deozza\PhilarmonyUserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('philarmony_user');

        $rootNode
            ->children()
                ->arrayNode('user')
                    ->children()
                        ->scalarNode('config')
                            ->isRequired()
                            ->treatNullLike("/var/Philarmony/user")
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;

    }
}