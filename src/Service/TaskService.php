<?php

namespace App\Service;

use App\Entity\Task;
use App\Enum\TaskStatus;
use App\Enum\TaskPriority;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class TaskService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TaskRepository $taskRepository,
        private UserRepository $userRepository,
        private CategoryRepository $categoryRepository
    ) {}

    public function getTasks(array $filters): array
    {
        return $this->taskRepository->findByFilters($filters);
    }

    public function getTaskById(int $taskId): Task
    {
        // buscamos la tarea por id
        $task = $this->taskRepository->find($taskId);

        // validamos que exista
        if (!$task) {
            throw new \InvalidArgumentException('Tarea no encontrada');
        }

        return $task;
    }

    public function createTask(array $data): Task
    {
        $task = new Task();

        // seteamos los siguientes datos
        $task->setTitle($data['title']);
        $task->setDescription($data['description'] ?? null);

        // seteamos nuestro status del task validando con :from para evitar errores
        $task->setStatus(
            isset($data['status'])
                ? TaskStatus::from($data['status'])
                : TaskStatus::PENDING
        );

        // seteamos nuestro priority del task validando con :from para evitar errores
        $task->setPriority(
            isset($data['priority'])
                ? TaskPriority::from($data['priority'])
                : TaskPriority::MEDIUM
        );

        // seteamos fecha de vencimiento
        if (!empty($data['dueDate'])) {
            $task->setDueDate(new \DateTimeImmutable($data['dueDate']));
        }

        // aquí capturamos el id del usuario
        $user = $this->userRepository->find($data['assignedTo']);

        if (!$user) {
            throw new \InvalidArgumentException('Usuario no encontrado');
        }

        // asignamos el usuario
        $task->setAssignedTo($user);

        // asignamos las categorias
        if (!empty($data['categories'])) {
            foreach ($data['categories'] as $categoryId) {

                // buscamos la categoria que exista
                $category = $this->categoryRepository->find($categoryId);

                // si la encontramos la agregamos
                if ($category) {
                    $task->addCategory($category);
                }
            }
        }

        // este objeto $task sea gestionado y eventualmente guardado en la base de datos
        $this->em->persist($task);
        //ejecutar todos los cambios pendientes en la base de datos (en laravel se usa ->save())
        $this->em->flush();

        return $task;
    }

    public function updateTask(int $taskId, array $data): Task
    {
        // buscamos la tarea por id
        $task = $this->taskRepository->find($taskId);

        // validamos que exista
        if (!$task) {
            throw new \InvalidArgumentException('Tarea no encontrada');
        }

        // seteamos los datos básicos solo si vienen en el request

        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }

        // usamos array_key_exists para permitir null explícito
        if (array_key_exists('description', $data)) {
            $task->setDescription($data['description']);
        }

        // seteamos status validando con enum
        if (isset($data['status'])) {
            $task->setStatus(TaskStatus::from($data['status']));
        }

        // seteamos priority validando con enum
        if (isset($data['priority'])) {
            $task->setPriority(TaskPriority::from($data['priority']));
        }

        // seteamos fecha de vencimiento (puede ser null)
        if (array_key_exists('dueDate', $data)) {
            $task->setDueDate(
                $data['dueDate']
                    ? new \DateTimeImmutable($data['dueDate'])
                    : null
            );
        }

        // actualizamos usuario asignado si viene
        if (isset($data['assignedTo'])) {
            $user = $this->userRepository->find($data['assignedTo']);

            if (!$user) {
                throw new \InvalidArgumentException('Usuario no encontrado');
            }

            $task->setAssignedTo($user);
        }

        // actualizamos categorias (reemplazo completo)
        if (isset($data['categories'])) {

            // eliminamos las categorias actuales
            foreach ($task->getCategories() as $category) {
                $task->removeCategory($category);
            }

            // agregamos las nuevas categorias
            foreach ($data['categories'] as $categoryId) {

                // buscamos la categoria
                $category = $this->categoryRepository->find($categoryId);

                // si existe la agregamos
                if ($category) {
                    $task->addCategory($category);
                }
            }
        }

        // guardamos cambios en la base de datos
        $this->em->flush();

        return $task;
    }

    public function deleteTask(int $taskId): void
    {
        // buscamos la tarea por id
        $task = $this->taskRepository->find($taskId);

        // validamos que exista
        if (!$task) {
            throw new \InvalidArgumentException('Tarea no encontrada');
        }

        // marcamos la entidad para eliminación (Doctrine trabaja de manera Unit of Work por eso la necesidad de marcar)
        $this->em->remove($task);

        // ejecutamos los cambios en la base de datos
        $this->em->flush();
    }
}
