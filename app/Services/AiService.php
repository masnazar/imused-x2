<?php namespace App\Services;

use OpenAI;

class AiService
{
    protected \OpenAI\Client $client;

    public function __construct()
    {
        $this->client = OpenAI::client(getenv('OPENAI_API_KEY'));
    }

    /**
     * Panggil OpenAI Chat API dan kembalikan JSON mentah
     */
    public function analyze(string $module, array $p): string
    {
        $system = <<<SYS
Anda adalah konsultan bisnis & keuangan senior, ahli dalam:
- Analisis keuangan
- Penyusunan anggaran
- Peramalan
- Manajemen risiko

Keluaran WAJIB berupa JSON mentah dengan tepat 6 kunci:
1. breakdown_coa
2. breakdown_platform
3. executive_summary
4. risk_assessment
5. mitigation_plan
6. recommendations

Tanpa Markdown, tanpa code fences, tanpa kunci tambahan.
SYS;

        $resp = $this->client->chat()->create([
            'model'    => 'gpt-4o-mini',
            'messages' => [
                ['role'=>'system', 'content'=> $system],
                ['role'=>'user',   'content'=> $this->buildPrompt($module, $p)],
            ],
            'temperature' => 0.7,
        ]);

        return trim($resp->choices[0]->message->content);
    }

    /**
     * Bangun prompt untuk modul “expenses”
     */
    protected function buildPrompt(string $module, array $p): string
    {
        if ($module !== 'expenses') {
            return 'Tolong ringkas modul "' . $module . '" sebagai JSON: ' . json_encode($p);
        }

        // Total pengeluaran (fallback)
        $total = isset($p['total']) && $p['total'] > 0
            ? $p['total']
            : array_sum(array_column($p['breakdown_coa'], 'total'));

        //
        // 1) BREAKDOWN COA (urut desc, Top 5)
        //
        $coa = $p['breakdown_coa'];
        usort($coa, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        $topCoa = array_slice($coa, 0, 5);
        $arrCoa = array_map(function($r) use ($total) {
            return [
                'coa_name'   => $r['coa_name'],
                'total'      => round((float)$r['total'], 2),
                'percentage' => $total > 0 ? round($r['total'] / $total * 100, 1) : 0,
            ];
        }, $topCoa);

        //
        // 2) BREAKDOWN PLATFORM
        //
        $plat = $p['breakdown_platform'];
        usort($plat, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        $topPlat = array_slice($plat, 0, 5);
        $arrPlat = array_map(function($r) use ($total) {
            return [
                'platform_name' => $r['platform_name'],
                'total'         => round((float)$r['total'], 2),
                'percentage'    => $total > 0 ? round($r['total'] / $total * 100, 1) : 0,
            ];
        }, $topPlat);

        //
        // 3) METRICS
        //
        $metrics = [
            'total_transactions'     => isset($p['count']) ? (int)$p['count'] : 0,
            'total_spent'            => round((float)$total, 2),
            'avg_per_transaction'    => isset($p['avg']) ? round((float)$p['avg'], 2) : 0,
            'pct_tx_vs_prev'         => $p['pct_count']  === null ? null : round((float)$p['pct_count'], 1),
            'pct_spent_vs_prev'      => $p['pct_total'] === null ? null : round((float)$p['pct_total'], 1),
        ];

        // Encode ke JSON tanpa escape unicode
        $jsonCoa     = json_encode($arrCoa, JSON_UNESCAPED_UNICODE);
        $jsonPlat    = json_encode($arrPlat, JSON_UNESCAPED_UNICODE);
        $jsonMetrics = json_encode($metrics, JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Berikut data untuk di-isi ke JSON sesuai instruksi sistem:

breakdown_coa: $jsonCoa
breakdown_platform: $jsonPlat
metrics: $jsonMetrics

PROMPT;
    }
}
