<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AcademySeeder::class,
            AlturamedixDemoSeeder::class,
            AlturamedixFinalDesignSeeder::class,
            ReferencePublicDesignSeeder::class,
            MirrorAzContentSeeder::class,
            LocalizedPublicContentSeeder::class,
        ]);
    }
}
