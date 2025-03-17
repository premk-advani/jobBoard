<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'options'];

    protected $casts = [
        'options' => 'array',  // Cast options as an array
    ];

    public function jobAttributes()
    {
        return $this->hasMany(JobAttributeValue::class, 'attribute_id');
    }
}