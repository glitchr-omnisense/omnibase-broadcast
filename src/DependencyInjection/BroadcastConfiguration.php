<?php

namespace Base\Broadcast\DependencyInjection;

use Base\Bundle\AbstractBaseConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class BroadcastConfiguration extends AbstractBaseConfiguration
{
    private bool $childrenDeclared = false;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = $this->getTreeBuilder();
        if ($this->childrenDeclared) {
            return $treeBuilder;
        }
        $this->childrenDeclared = true;

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('programme')->defaultNull()
                    ->info('The slug of the site\'s programme (a site is one show\'s); null: the first one.')->end()
                ->integerNode('anniversary')->defaultValue(30)
                    ->info('"Il y a trente ans cette semaine": how many years back the home page looks.')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
