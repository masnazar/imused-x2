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

Keluaran **HANYA** RAW JSON dengan **tepat 6 kunci** dalam urutan:
1. ringkasan            : string (satu paragraf ringkas, fokus efisiensi & efektivitas)
2. breakdown            : object {coa: array, platform: array}
3. risk_assessment      : array of strings (minimal 3 item)
4. mitigation_plan      : array of strings (minimal 3 item)
5. recommendations      : array of strings (minimal 3 item)
6. konklusi             : string (ringkasan akhir)

**Catatan**: `breakdown` terdiri dari dua objek:
  • `coa`: Top 5 COA  
  • `platform` (alias Sumber Pengeluaran): Top 5 sumber biaya

**TANPA** markdown, code fences, komentar, atau kunci tambahan.
SYS;

        $resp = $this->client->chat()->create([
            'model'       => 'gpt-4o-mini',
            'messages'    => [
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
            return 'Tolong ringkas modul "' . $module . '" sebagai JSON: ' . json_encode($p, JSON_UNESCAPED_UNICODE);
        }

        // 1) Hitung total fallback
        $total = (!empty($p['total']) && $p['total'] > 0)
            ? $p['total']
            : array_sum(array_column($p['breakdown_coa'], 'total'));

        // 2) Siapkan Top 5 COA
        $coa = $p['breakdown_coa'];
        usort($coa, fn($a,$b)=> $b['total'] <=> $a['total']);
        $topCoa = array_slice($coa, 0, 5);
        $arrCoa = array_map(fn($r)=> [
            'coa_name'   => $r['coa_name'],
            'total'      => round((float)$r['total'], 2),
            'percentage' => $total>0 ? round($r['total']/$total*100, 1) : 0,
        ], $topCoa);

        // 3) Siapkan Top 5 Platform
        $plat = $p['breakdown_platform'];
        usort($plat, fn($a,$b)=> $b['total'] <=> $a['total']);
        $topPlat = array_slice($plat, 0, 5);
        $arrPlat = array_map(fn($r)=> [
            'platform_name' => $r['platform_name'],
            'total'         => round((float)$r['total'], 2),
            'percentage'    => $total>0 ? round($r['total']/$total*100, 1) : 0,
        ], $topPlat);

        // 4) Metrics untuk ringkasan
        $metrics = [
            'total_transactions'  => (int)($p['count'] ?? 0),
            'total_spent'         => round((float)$total, 2),
            'avg_per_transaction' => round((float)($p['avg'] ?? 0), 2),
            'pct_tx_vs_prev'      => $p['pct_count']===null ? null : round((float)$p['pct_count'],1),
            'pct_spent_vs_prev'   => $p['pct_total']===null ? null : round((float)$p['pct_total'],1),
        ];

        $jsonCoa  = json_encode($arrCoa,  JSON_UNESCAPED_UNICODE);
        $jsonPlat = json_encode($arrPlat, JSON_UNESCAPED_UNICODE);
        $jsonMet  = json_encode($metrics, JSON_UNESCAPED_UNICODE);

        // 5) Kirim prompt dengan skeleton JSON kosong
        return <<<PROMPT
Data pengeluaran untuk konteks AI:
- Breakdown COA Top 5: $jsonCoa
- Breakdown Platform Top 5: $jsonPlat
- Metrics: $jsonMet

**Susun HANYA** satu JSON dengan struktur **tepat**:

{
  "ringkasan": "",
  "breakdown": {
    "coa": $jsonCoa,
    "platform": $jsonPlat
  },
  "risk_assessment": [],
  "mitigation_plan": [],
  "recommendations": [],
  "konklusi": ""
}
PROMPT;
    }
}
