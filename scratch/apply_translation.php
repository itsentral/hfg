<?php
// Terapkan peta terjemahan UI (Indonesia -> Inggris) secara persis per file.
// Format JSON: { "path/file.php": [ ["teks lama", "teks baru", jumlah_kemunculan_opsional], ... ] }
// Setiap pasangan harus ditemukan persis sebanyak jumlah_kemunculan (default 1); bila tidak, file itu tidak diubah sama sekali.
// Pemakaian: php scratch/apply_translation.php [--dry] peta.json
$dry  = in_array('--dry', $argv, true);
$map  = json_decode(file_get_contents(end($argv)), true);
if (!is_array($map)) { fwrite(STDERR, "JSON tidak valid\n"); exit(1); }
$root = dirname(__DIR__) . '/';
$ok = 0; $fail = 0;
foreach ($map as $file => $pairs) {
    $path = $root . $file;
    if (!is_file($path)) { echo "TIDAK ADA  $file\n"; $fail++; continue; }
    $src = file_get_contents($path); $new = $src; $errs = [];
    foreach ($pairs as $p) {
        [$old, $rep] = $p; $want = $p[2] ?? 1;
        $n = substr_count($new, $old);
        if ($n !== $want) { $errs[] = "ditemukan $n (harap $want): " . substr($old, 0, 70); continue; }
        $new = str_replace($old, $rep, $new);
    }
    if ($errs) { echo "GAGAL  $file\n   - " . implode("\n   - ", $errs) . "\n"; $fail++; continue; }
    if (!$dry) file_put_contents($path, $new);
    echo ($dry ? '[dry] ' : '') . "OK  $file (" . count($pairs) . " teks)\n"; $ok++;
}
echo "$ok file OK, $fail file gagal\n";
exit($fail ? 1 : 0);
