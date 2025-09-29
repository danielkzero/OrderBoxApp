<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProdutosImagens;
use App\Models\Produtos;
use App\Models\Empresas;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProdutosImagens>
 */
class ProdutosImagensFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $produto = Produtos::inRandomOrder()->first() ?? Produtos::factory()->create();

        // Cria uma imagem 640x480 em memória
        $width = 640;
        $height = 480;
        $image = imagecreatetruecolor($width, $height);

        // Preenche com uma cor aleatória
        $color = imagecolorallocate($image, rand(0,255), rand(0,255), rand(0,255));
        imagefill($image, 0, 0, $color);

        // Captura a imagem em buffer
        ob_start();
        imagejpeg($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        // Converte em Base64
        $imagemBase64 = 'data:image/jpeg;base64,' . base64_encode($imageData);

        return [
            'empresa_id' => $produto->empresa_id,
            'produto_id' => $produto->id,
            'imagem_base64' => $imagemBase64,
            'ordem' => $this->faker->numberBetween(0, 10),
        ];
    }
}
