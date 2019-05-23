<?php

namespace Deozza\PhilarmonyUserBundle;

use Deozza\PhilarmonyUserBundle\DependencyInjection\DeozzaPhilarmonyUserExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DeozzaPhilarmonyUserBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new DeozzaPhilarmonyUserExtension();
        }
        return $this->extension;
    }
}