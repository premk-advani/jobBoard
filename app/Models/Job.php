<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;
    protected $table = 'bjobs';


    protected $fillable = [
        'title', 'description', 'company_name', 'salary_min', 'salary_max', 
        'is_remote', 'job_type', 'status', 'published_at'
    ];

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'job_language', 'job_id', 'language_id');
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'job_location', 'job_id', 'location_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'job_category', 'job_id', 'category_id');
    }

    public function jobAttributes()
    {
        return $this->hasMany(JobAttributeValue::class, 'job_id');
    }

}
