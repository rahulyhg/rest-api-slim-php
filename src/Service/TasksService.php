<?php

/**
 * Tasks Service.
 */
class TasksService extends Base
{
    private $database;

    /**
     * Constructor of the class.
     *
     * @param type $database
     */
    public function __construct($database = null)
    {
        $this->database = $database;
    }

    /**
     * Get all tasks.
     *
     * @return array
     */
    public function getTasks()
    {
        $repository = new TasksRepository;
        $query = $repository->getTasksQuery();
        $statement = $this->database->prepare($query);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Get one task by id.
     *
     * @param int $taskId
     * @return array
     */
    public function getTask($taskId)
    {
        $task = self::checkTask($this->database, $taskId);

        return $task;
    }

    /**
     * Search tasks by name.
     *
     * @param string $tasksName
     * @return array
     * @throws Exception
     */
    public function searchTasks($tasksName)
    {
        $repository = new TasksRepository;
        $query = $repository->searchTasksQuery();
        $statement = $this->database->prepare($query);
        $query = '%'.$tasksName.'%';
        $statement->bindParam('query', $query);
        $statement->execute();
        $tasks = $statement->fetchAll();
        if (!$tasks) {
            throw new Exception(self::TASK_NOT_FOUND, 404);
        }

        return $tasks;
    }

    /**
     * Create task.
     *
     * @param mixed $database
     * @param mixed $request
     * @return array
     * @throws Exception
     */
    public static function createTask($database, $request)
    {
        $input = $request->getParsedBody();
        if (empty($input['task'])) {
            throw new Exception(self::TASK_NAME_REQUIRED, 400);
        }
        $repository = new TasksRepository;
        $query = $repository->createTaskQuery();
        $statement = $database->prepare($query);
        $statement->bindParam('task', $input['task']);
        $statement->execute();
        $task = self::checkTask($database, $database->lastInsertId());

        return $task;
    }

    /**
     * Update task.
     *
     * @param mixed $database
     * @param mixed $request
     * @param int $taskId
     * @return array
     * @throws Exception
     */
    public static function updateTask($database, $request, $taskId)
    {
        $task = self::checkTask($database, $taskId);
        $input = $request->getParsedBody();
        if (empty($input['task']) && empty($input['status'])) {
            throw new Exception(self::TASK_INFO_REQUIRED, 400);
        }
        $taskname = isset($input['task']) ? $input['task'] : $task->task;
        $status = isset($input['status']) ? $input['status'] : $task->status;
        $repository = new TasksRepository;
        $query = $repository->updateTaskQuery();
        $statement = $database->prepare($query);
        $statement->bindParam('id', $taskId);
        $statement->bindParam('task', $taskname);
        $statement->bindParam('status', $status);
        $statement->execute();

        return self::checkTask($database, $taskId);
    }

    /**
     * Delete task.
     *
     * @param mixed $database
     * @param int $taskId
     * @return array
     */
    public static function deleteTask($database, $taskId)
    {
        self::checkTask($database, $taskId);
        $repository = new TasksRepository;
        $query = $repository->deleteTaskQuery();
        $statement = $database->prepare($query);
        $statement->bindParam('id', $taskId);
        $statement->execute();

        return self::TASK_DELETED;
    }
}