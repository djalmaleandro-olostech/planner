<?php


namespace App\Helper;


use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;
use Psr\Container\ContainerInterface;

class MyConnectionPool extends Pool
{
    public function __construct(ContainerInterface $container, array $config = [])
    {
        $config['max_connections'] = 100;
        parent::__construct($container, $config);
    }

    public function createConnection(): ConnectionInterface
    {
        // print_r("\nCREATED CONNECTION \n");
        return new MyConnection();
    }
}