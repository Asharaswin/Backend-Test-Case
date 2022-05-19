<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $title = $this->faker->sentence($nbWords = 3, $variableNbWords = true);
        $code = $this->faker->swiftBicNumber();

        return [
            'title'     => $title,
            'code'      => $code,
            'author'    => $this->faker->name,
            'quantity'  => rand(3,5),
        ];
    }
}
