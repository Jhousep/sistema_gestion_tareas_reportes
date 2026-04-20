<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\DTO\TaskReportFilterDTO;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    private Security $security; //esto es para poder usar el metodo isGranted

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Task::class);
        $this->security = $security;
    }

    //filtros personalizado para posterior getTasks
    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.assignedTo', 'u')
            ->leftJoin('t.categories', 'c')
            ->addSelect('u', 'c');


        //validar si es rol usuario para filtrar solo las de usuario
        if (
            $this->security->isGranted('ROLE_USER') &&
            !$this->security->isGranted('ROLE_ADMIN')
        ) {
            $user = $this->security->getUser();

            if ($user instanceof User) {
                $qb->andWhere('u.id = :user')
                    ->setParameter('user', $user->getId());
            }
        }

        // búsqueda por título
        if (!empty($filters['title'])) {
            $qb->andWhere('LOWER(t.title) LIKE :title')
            ->setParameter('title', '%' . strtolower($filters['title']) . '%');
        }

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
        if (!empty($filters['dueDateFrom'])) {
            $qb->andWhere('t.dueDate >= :dueDateFrom')
                ->setParameter('dueDateFrom', $filters['dueDateFrom']);
        }

        if (!empty($filters['dueDateTo'])) {
            $qb->andWhere('t.dueDate <= :dueDateTo')
                ->setParameter('dueDateTo', $filters['dueDateTo']);
        }

        // filtro por fecha de creación desde
        if (!empty($filters['createdFrom'])) {
            $qb->andWhere('t.createdAt >= :createdFrom')
                ->setParameter('createdFrom', $filters['createdFrom']);
        }

        // filtro por fecha de creación hasta
        if (!empty($filters['createdTo'])) {
            $qb->andWhere('t.createdAt <= :createdTo')
                ->setParameter('createdTo', $filters['createdTo']);
        }

        // filtro por fecha de actualización desde
        if (!empty($filters['updatedFrom'])) {
            $qb->andWhere('t.updatedAt >= :updatedFrom')
                ->setParameter('updatedFrom', $filters['updatedFrom']);
        }

        // filtro por fecha de actualización hasta
        if (!empty($filters['updatedTo'])) {
            $qb->andWhere('t.updatedAt <= :updatedTo')
                ->setParameter('updatedTo', $filters['updatedTo']);
        }

        // ordenamiento
        if (!empty($filters['orderBy'])) {
            $order = $filters['orderDir'] ?? 'ASC';
            $qb->orderBy('t.' . $filters['orderBy'], $order);
        }

        return $qb->getQuery()->getResult();
    }
    // filtros personalizado para reportería
    public function findByReportFilters(TaskReportFilterDTO $filters): array
    {
        // creamos el query builder sobre la entidad Task (alias t)
        $qb = $this->createQueryBuilder('t')
            ->distinct() // evitamos duplicados por categorías
            ->leftJoin('t.assignedTo', 'u')
            ->addSelect('u')
            ->leftJoin('t.categories', 'c')
            ->addSelect('c');


        //validar si es rol usuario para filtrar solo las de usuario
        if (
            $this->security->isGranted('ROLE_USER') &&
            !$this->security->isGranted('ROLE_ADMIN')
        ) {
            $user = $this->security->getUser();

            if ($user instanceof User) {
                $qb->andWhere('u.id = :user')
                    ->setParameter('user', $user->getId());
            }
        }

        // filtramos por fecha desde (creación)
        if ($filters->createdFrom) {
            $qb->andWhere('t.createdAt >= :createdFrom')
            ->setParameter('createdFrom', $filters->createdFrom);
        }

        // filtramos por fecha hasta (creación)
        if ($filters->createdTo) {
            $qb->andWhere('t.createdAt <= :createdTo')
            ->setParameter('createdTo', $filters->createdTo);
        }

        // filtramos por fecha de actualización desde
        if ($filters->updatedFrom) {
            $qb->andWhere('t.updatedAt >= :updatedFrom')
            ->setParameter('updatedFrom', $filters->updatedFrom);
        }

        // filtramos por fecha de actualización hasta
        if ($filters->updatedTo) {
            $qb->andWhere('t.updatedAt <= :updatedTo')
            ->setParameter('updatedTo', $filters->updatedTo);
        }

        // filtramos por fecha de vencimiento desde
        if ($filters->dueFrom) {
            $qb->andWhere('t.dueDate >= :dueFrom')
            ->setParameter('dueFrom', $filters->dueFrom);
        }

        // filtramos por fecha de vencimiento hasta
        if ($filters->dueTo) {
            $qb->andWhere('t.dueDate <= :dueTo')
            ->setParameter('dueTo', $filters->dueTo);
        }

        // filtramos por estado
        if ($filters->status) {
            $qb->andWhere('t.status = :status')
            ->setParameter('status', $filters->status->value);
        }

        // filtramos por prioridad
        if ($filters->priority) {
            $qb->andWhere('t.priority = :priority')
            ->setParameter('priority', $filters->priority->value);
        }

        // filtramos por usuario asignado
        if ($filters->userId) {
            $qb->andWhere('u.id = :userId')
            ->setParameter('userId', $filters->userId);
        }

        // ordenamos por fecha de creación
        $qb->orderBy('t.createdAt', 'DESC');

        /*
        // obtenemos la consulta para el explain
        $query = $qb->getQuery();
        $sql = $query->getSQL();
        $params = $query->getParameters();

        dd($sql, $params);
        */

        // ejecutamos la consulta
        return $qb->getQuery()->getResult();
    }
}
