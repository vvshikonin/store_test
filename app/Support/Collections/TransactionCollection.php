<?php
namespace App\Support\Collections;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class TransactionCollection extends Collection
{
    function __construct($models)
    {
        parent::__construct($models);
    }

    /**
     * Вызывает loadDeepRelations из модели для каждого Item
     * 
     * @param array $relations - Параметр, который ожидает loadDeepRelations в модели
     */
    public function loadDeepRelations(array $relations = [])
    {
        foreach($this->items as $item)
        {
            $item->loadDeepRelations($relations);
        }

        return new static ($this->items);
    }
}