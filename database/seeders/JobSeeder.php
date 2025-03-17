<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\Language;
use App\Models\Location;
use App\Models\Category;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $job = Job::create([
            'title' => 'Senior Laravel Developer',
            'description' => 'Develop advanced Laravel applications.',
            'company_name' => 'TechCorp',
            'salary_min' => 5000,
            'salary_max' => 8000,
            'is_remote' => true,
            'job_type' => 'full-time',
            'status' => 'published'
        ]);

        // Get or create related models
        $language = Language::firstOrCreate(['name' => 'PHP']);
        $location = Location::firstOrCreate(['city' => 'New York', 'state' => 'NY', 'country' => 'USA']);
        $category = Category::firstOrCreate(['name' => 'Software Development']);

        // Attach relationships manually
        \DB::table('job_language')->insert([
            'job_id' => $job->id,
            'language_id' => $language->id,
        ]);

        \DB::table('job_location')->insert([
            'job_id' => $job->id,
            'location_id' => $location->id,
        ]);

        \DB::table('job_category')->insert([
            'job_id' => $job->id,
            'category_id' => $category->id,
        ]);


    }
}
