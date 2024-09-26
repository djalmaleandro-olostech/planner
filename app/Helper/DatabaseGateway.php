<?php
declare(strict_types=1);

namespace App\Helper;

class DatabaseGateway extends Database
{
    public function __construct()
    {
        parent::__construct('db_gw');
    }
}