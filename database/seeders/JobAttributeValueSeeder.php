<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\JobAttributeValue;
use App\Models\Attribute;
use Illuminate\Database\Seeder;

class JobAttributeValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $job = Job::find(1);  // Assuming job with id 1 exists

        $experienceLevel = Attribute::where('name', 'Experience Level')->first();
        $jobDuration = Attribute::where('name', 'Job Duration')->first();
        $isRemote = Attribute::where('name', 'Is it remote?')->first();

        JobAttributeValue::create([
            'job_id' => $job->id,
            'attribute_id' => $experienceLevel->id,
            'value' => 'Mid',
        ]);

        JobAttributeValue::create([
            'job_id' => $job->id,
            'attribute_id' => $jobDuration->id,
            'value' => '12',
        ]);

        JobAttributeValue::create([
            'job_id' => $job->id,
            'attribute_id' => $isRemote->id,
            'value' => '1',  // 1 for true, 0 for false
        ]);
    }
}
