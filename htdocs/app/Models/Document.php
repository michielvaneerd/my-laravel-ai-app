<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Table('documents')]
#[Fillable(['title', 'path'])]
class Document extends Model
{
    public function chunks()
    {
        return $this->hasMany(Chunk::class, foreignKey: 'document_id', localKey: 'id');
    }
}
