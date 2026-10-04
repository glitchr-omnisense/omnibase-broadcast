<?php

namespace Base\Broadcast;

use Base\Bundle\AbstractBaseBundle;
use Base\Traits\SingletonTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * A broadcast's site: a programme, its episodes, and for each film of an
 * episode (omnibase/video) what it is - a performance: who (omnibase/music's
 * performers), which song, a cover or not. Written for one music show; no
 * more general than it needed.
 */
class BroadcastBundle extends AbstractBaseBundle
{
    use SingletonTrait;

    public function __construct()
    {
        parent::__construct();
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $this->setMapping($this->getPath().'/src/Entity', 'Base\Broadcast\Entity', 'App\Entity\Broadcast');
        $this->setMapping($this->getPath().'/src/Repository', 'Base\Broadcast\Repository', 'App\Repository\Broadcast');
    }
}
