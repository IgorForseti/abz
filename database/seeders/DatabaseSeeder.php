<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Sequence;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //Add name in the positons table
        if (Position::count() == 0) {
            $positions = [
                'Lawyer',
                'Content manager',
                'Security',
                'Designer',
            ];

            foreach ($positions as $position) {
                Position::create(['name' => $position]);
            }
        }

        //Create 45 random users
         User::factory(45)->state(new Sequence(
             fn ($sequence) => ['position_id' => Position::all()->random()],
         ))->create();
    }
}
