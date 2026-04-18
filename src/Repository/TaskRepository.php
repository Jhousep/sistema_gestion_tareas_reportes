<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    //filtros personalizados
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.assignedTo', 'u')
            ->leftJoin('t.categories', 'c')
            ->addSelect('u', 'c');

        // filtro por status
        if (!empty($filters['status'])) {
            $qb->andWhere('t.status = :status')
                ->setParameter('status', $filters['status']);
        }

        // filtro por prioridad
        if (!empty($filters['priority'])) {
            $qb->andWhere('t.priority = :priority')
                ->setParameter('priority', $filters['priority']);
        }

        // filtro por usuario
        if (!empty($filters['assignedTo'])) {
            $qb->andWhere('u.id = :user')
                ->setParameter('user', $filters['assignedTo']);
        }

        // rango de fechas
        if (!empty($filters['fromDate'])) {
            $qb->andWhere('t.dueDate >= :fromDate')
                ->setParameter('fromDate', $filters['fromDate']);
        }

        if (!empty($filters['toDate'])) {
            $qb->andWhere('t.dueDate <= :toDate')
                ->setParameter('toDate', $filters['toDate']);
        }

        // ordenamiento
        if (!empty($filters['orderBy'])) {
            $order = $filters['orderDir'] ?? 'ASC';
            $qb->orderBy('t.' . $filters['orderBy'], $order);
        }

        return $qb->getQuery()->getResult();
    }
}
