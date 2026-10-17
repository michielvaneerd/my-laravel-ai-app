<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\AsVector;

#[Table('chunks')]
#[Fillable(['document_id', 'content', 'embedding', 'token_count'])]
class Chunk extends Model
{
    protected function casts(): array
    {
        return [
            'embedding' => AsVector::class,
        ];
    }

    public function document()
    {
        return $this->belongsTo(Document::class, foreignKey: 'document_id', ownerKey: 'id');
    }
}
