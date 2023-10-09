<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $photos = [
            "AgACAgQAAxUAAWUe2inCTgavSU03iFB_eKZ2cF2_AAIvqTEbZ1iLFzVVBufbuJARAQADAgADYgADMAQ",
            "AgACAgQAAxUAAWUe2ikqH1YLqSHmX-G7B-ML9zN2AAIuqTEbZ1iLF0_3nIUVvnsoAQADAgADYgADMAQ",
            "AgACAgQAAxUAAWUgcBa2huDIxS8gQUGUveLjLw72AAKppzEb7ZP_PplFjuu-nfa9AQADAgADYgADMAQ",
            "AgACAgQAAxUAAWUgK_-b5TmD7wABRS2HjiajWK_UGgACd6oxG1FyFRbYY695FPCmMQEAAwIAA2IAAzAE",
            "AgACAgQAAxUAAWUgK_96hqOWx18brZvXiAHXuvgPAAJ2qjEbUXIVFj_OfhdBXfoFAQADAgADYgADMAQ",
            "AgACAgQAAxUAAWUgK_9sv0WoUVddhOYKeIy6oScVAAJ1qjEbUXIVFpUD9vaTGxnuAQADAgADYgADMAQ"
        ];

        foreach (range(1, 50) as $index) {
            $name = $faker->name;
            $tgId = $faker->randomNumber(9);
            $genderId = $faker->randomElement([1, 2]);
            $interestedIn = ($genderId == 1) ? 2 : 1;

            $numPhotos = $faker->numberBetween(1, 3);
            $selectedPhotos = $faker->randomElements($photos, $numPhotos);

            DB::table('users')->insert([
                'name' => $name,
                'tg_id' => $tgId,
                'gender_id' => $genderId,
                'email' => $faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => bcrypt('password123'),
                'age' => $faker->numberBetween(18, 99),
                'tg_username' => $faker->userName,
                'bio' => $faker->text,
                'photos' => json_encode($selectedPhotos),
                'has_telegram_premium' => $faker->boolean,
                'is_pro_user' => $faker->boolean,
                'user_type' => 1,
                'status' => $faker->boolean,
                'remember_token' => null,
                'interested_in' => $interestedIn
            ]);
        }
    }
}
