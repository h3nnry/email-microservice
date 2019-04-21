<?php

namespace App\Repositories;

/**
 * Class EmailRepository
 * @package App\Repositories
 */
class EmailRepository extends Repository
{
    public function model()
    {
        return 'App\Email';
    }
}