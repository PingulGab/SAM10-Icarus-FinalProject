<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sentiment extends Model
{
    use HasFactory;

    // Allow mass assignment for these attributes
    protected $fillable = ['text', 'sentiment', 'compound', 'group_id', 'created_at'];

    /**
     * Define the relationship to the group.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
