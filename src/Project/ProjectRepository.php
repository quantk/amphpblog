<?php


namespace App\Project;


use Amp\Promise;
use QuantFrame\Database\Repository;

class ProjectRepository extends Repository
{
    /**
     * @param int $id
     * @return Promise
     */
    public function find(int $id): Promise
    {
        return Project::find($id);
    }
}