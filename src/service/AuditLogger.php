<?php

namespace App\service;

use App\Entity\AuditLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AuditLogger
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function log(User $user, string $action, string $details = null): void
    {
        $auditLog = new AuditLog();
        $auditLog->setUser($user);
        $auditLog->setAction($action);
        $auditLog->setDetails($details);
        $auditLog->setTimestamp(new \DateTime());

        $this->em->persist($auditLog);
        $this->em->flush();
    }
}
