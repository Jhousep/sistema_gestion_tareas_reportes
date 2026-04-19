<?php

namespace App\Controller;

use App\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks')]
class TaskController
{
    // Inyección de dependencias: Symfony me pasa TaskService ya creado,
    // así el controller no lo instancia directamente (desacopla y facilita mantenimiento)
    public function __construct(
        private TaskService $taskService
    ) {}

    // obtener todas las tareas
    #[Route('', name: 'task_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        // obtenemos los filtros desde la URL
        $filters = $request->query->all();

        $tasks = $this->taskService->getTasks($filters);

        // transformamos a array
        $data = array_map(function ($task) {
            return [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'status' => $task->getStatus()->value,
                'priority' => $task->getPriority()->value,
                'dueDate' => $task->getDueDate()?->format('Y-m-d'),
                'createdAt' => $task->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $task->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'assignedTo' => $task->getAssignedTo()->getEmail(),
            ];
        }, $tasks);

        return new JsonResponse($data);
    }

    // obtener una tarea por id
    #[Route('/{id}', name: 'task_get', methods: ['GET'])]
    public function getById(int $id): JsonResponse
    {
        try {
            $task = $this->taskService->getTaskById($id);

            return new JsonResponse([
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus()->value,
                'priority' => $task->getPriority()->value,
                'dueDate' => $task->getDueDate()?->format('Y-m-d'),
                'createdAt' => $task->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $task->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'assignedTo' => $task->getAssignedTo()->getEmail(),
                'categories' => array_map(
                    fn($c) => $c->getName(),
                    $task->getCategories()->toArray()
                )
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // crear una tarea
    #[Route('', name: 'task_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // convertimos el json a array
        $data = json_decode($request->getContent(), true);

        try {
            $task = $this->taskService->createTask($data);

            return new JsonResponse([
                'message' => 'Tarea creada correctamente',
                'id' => $task->getId()
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // actualizar tarea
    #[Route('/{id}', name: 'task_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $task = $this->taskService->updateTask($id, $data);

            return new JsonResponse([
                'message' => 'Tarea actualizada correctamente'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // eliminar tarea
    #[Route('/{id}', name: 'task_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->taskService->deleteTask($id);

            return new JsonResponse([
                'message' => 'Tarea eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
