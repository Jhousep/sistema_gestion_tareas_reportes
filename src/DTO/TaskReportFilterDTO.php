<?php
namespace App\DTO;

use App\Enum\TaskStatus;
use App\Enum\TaskPriority;

class TaskReportFilterDTO
{
    public ?\DateTimeInterface $createdFrom = null;
    public ?\DateTimeInterface $createdTo = null;

    public ?\DateTimeInterface $dueFrom = null;
    public ?\DateTimeInterface $dueTo = null;

    public ?\DateTimeInterface $updatedFrom = null;
    public ?\DateTimeInterface $updatedTo = null;

    public ?TaskStatus $status = null;
    public ?TaskPriority $priority = null;

    public ?int $userId = null;
}
