<?php
namespace App\Services;

use App\Models\Restaurant;
use App\Models\RestaurantEmbedding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-exp-03-07:embedContent';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /** -------------  API   ------------- */
    private function callGemini(string $text): array
    {
        $res = Http::withBody(json_encode([
            'model'   => 'models/gemini-embedding-exp-03-07',
            'content' => ['parts' => [['text' => $text]]]
        ]), 'application/json')
        ->post("{$this->apiUrl}?key={$this->apiKey}");

        if (!$res->successful()) {
            Log::error('Gemini embedding error → '.$res->body());
            throw new \Exception("Gemini error ".$res->status());
        }

        $vec = $res->json('predictions.0.embedding.values');   // ← ruta correcta
        if (!is_array($vec)) {
            throw new \Exception('Malformed embedding response');
        }
        return $vec;
    }

    /** -------------  CRUD Embeddings   ------------- */
    public function createRestaurantEmbedding(Restaurant $r): array
    {
        return Cache::remember("emb_$r->id", 86400, function() use($r){
            $text = "$r->name. $r->description. $r->address";
            $vec  = app()->isProduction()
                    ? $this->callGemini($text)
                    : $this->dummyVector();                    // 768-d también
            RestaurantEmbedding::updateOrCreate(
                ['restaurant_id'=>$r->id],
                ['embedding'=>$vec,'text_content'=>$text]
            );
            return $vec;
        });
    }

    public function findSimilarRestaurants(string $query, int $limit = 5): array
    {
        $qVec = app()->isProduction() ? $this->callGemini($query) : $this->dummyVector();
        $all  = RestaurantEmbedding::with('restaurant')->get();
        $scored = $all->map(fn($e)=>[
            'score'=>$this->cosine($qVec, $e->embedding),
            'r'    =>$e->restaurant
        ])->sortByDesc('score')->take($limit);

        return $scored->map(fn($i)=>[
            'id'=>$i['r']->id,
            'name'=>$i['r']->name,
            'description'=>$i['r']->description,
            'address'=>$i['r']->address,
            'image'=>$i['r']->image,
            'categories'=>$i['r']->categories->pluck('name')->toArray()
        ])->values()->toArray();
    }

    /** -------------  Utils ------------- */
    private function cosine(array $a, array $b): float
    {
        $dot=$mA=$mB=0;
        foreach ($a as $i=>$v){ $dot+=$v*$b[$i]; $mA+=$v*$v; $mB+=$b[$i]*$b[$i]; }
        return ($mA&&$mB)? $dot/(sqrt($mA)*sqrt($mB)) : 0;
    }
    private function dummyVector(): array
    {   // 768-dim para desarrollo
        return array_map(fn()=>round(mt_rand()/mt_getrandmax(),4), range(1,768));
    }
}
