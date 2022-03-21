<?php
declare(strict_types = 1);

namespace App\Services\AppContainer;

interface AppContainerInterface
{
    /**
     * Returns the state of the application
     * @return string
     */
    public function handler(): string;
}