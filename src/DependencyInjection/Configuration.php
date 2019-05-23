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
                        ->scalarNode('profile')
                            ->isRequired()
                            ->treatNullLike("/var/Philarmony/user_profile")
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;

    }
}