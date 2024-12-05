<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    // Allow mass assignment for these attributes
    protected $fillable = ['name', 'user_id'];

    /**
     * Define the relationship to the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define the relationship to sentiments.
     */
    public function sentiments()
    {
        return $this->hasMany(Sentiment::class);
    }
}
