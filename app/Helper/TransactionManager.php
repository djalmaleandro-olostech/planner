<?php
declare(strict_types=1);

namespace App\Helper;

use Hyperf\Contract\StdoutLoggerInterface;

class TransactionManager
{
    private Database $db;
    private StdoutLoggerInterface $logger;


    public function __construct(Database $db, StdoutLoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }
    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }
    public function commit(): void
    {
        $this->db->commit();
    }
    public function rollBack(): void
    {
        $this->db->rollBack();
        $this->logger->warning("ROLLBACK OCORRIDO NO PROCESSO");
    }
}

