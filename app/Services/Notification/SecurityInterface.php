<?php

declare(strict_types = 1);

namespace App\Services\Notification;

interface SecurityInterface
{
    public function getToken(): string;
}

