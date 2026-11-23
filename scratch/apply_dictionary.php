<?php
// Terapkan kamus frasa tampilan (Indonesia -> Inggris) ke file PHP/view, hanya pada teks tampil.
//   - Diproses: isi string PHP ('...', "..."), teks HTML/JS di luar tag PHP.
//   - Frasa 1-2 kata: hanya bila menjadi seluruh isi string / seluruh teks node HTML / seluruh literal JS.
//   - Frasa 3+ kata: juga di dalam kalimat (batas kata).
//   - Dilewati: baris write_log()/log_message(), string yang dibandingkan (== != case in_array),
//     kunci array ('x' =>), argumen query builder (where, get_where, like, order_by, select, ...), atribut value="...".
// Pemakaian: php scratch/apply_dictionary.php [--dry] [--report=file.tsv] kamus.json modul1 modul2 ...
$args = array_slice($argv, 1);
$dry = false; $report = null; $rest = [];
foreach ($args as $a) {
    if ($a === '--dry') $dry = true;
    elseif (strpos($a, '--report=') === 0) $report = substr($a, 9);
    else $rest[] = $a;
}
$dict = json_decode(file_get_contents(array_shift($rest)), true);
// "_protected": nilai yang tersimpan di database / dipakai lintas query (mis. jenis_transaksi = 'Saldo Awal'),
// tidak boleh diterjemahkan sebagai literal walau tidak terdeteksi sebagai pembanding.
$manualProtected = $dict['_protected'] ?? [];
unset($dict['_catatan'], $dict['_protected']);
uksort($dict, fn($a, $b) => strlen($b) <=> strlen($a));
$words = fn($s) => count(preg_split('/\s+/', trim($s)));
$root = dirname(__DIR__) . '/';
$dbFns = ['where', 'or_where', 'where_in', 'where_not_in', 'get_where', 'like', 'or_like', 'not_like', 'order_by', 'group_by', 'select', 'select_sum', 'select_max', 'having', 'join', 'from', 'get', 'set', 'query', 'in_array', 'array_search', 'array_key_exists', 'list_help', 'get_name', 'getAcccesmenu', 'has_permission', 'restrict', 'header', 'redirect', 'site_url', 'base_url', 'library', 'model', 'view', 'helper', 'item', 'post', 'userdata', 'set_userdata', 'flashdata', 'define', 'sprintf_key', 'field', 'str_replace', 'preg_replace', 'preg_match', 'explode', 'implode'];
$log = [];
$changedFiles = 0; $total = 0;

// Literal yang dipakai sebagai NILAI logika di mana pun di aplikasi (dibandingkan, case, kunci array, argumen query,
// in_array, JS ===) tidak boleh diterjemahkan di file mana pun, karena nilai yang sama bisa di-assign di satu tempat
// dan dibandingkan / dicari di tempat lain.
$protected = array_fill_keys($manualProtected, 1);
$scan = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . 'application', FilesystemIterator::SKIP_DOTS));
foreach ($scan as $fi) {
    if ($fi->getExtension() !== 'php' || strpos($fi->getPathname(), '/logs/') !== false) continue;
    $c = file_get_contents($fi->getPathname());
    $tk = @token_get_all($c); $cnt = count($tk);
    $sig = function ($i, $dir) use ($tk, $cnt) { for ($k = $i + $dir; $k >= 0 && $k < $cnt; $k += $dir) { if (is_array($tk[$k]) && in_array($tk[$k][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) continue; return $tk[$k]; } return null; };
    for ($i = 0; $i < $cnt; $i++) {
        $x = $tk[$i];
        if (!is_array($x)) continue;
        if ($x[0] === T_CONSTANT_ENCAPSED_STRING) {
            $p = $sig($i, -1); $nx = $sig($i, 1);
            $ps = is_array($p) ? $p[1] : $p; $ns = is_array($nx) ? $nx[1] : $nx;
            $cmp = in_array($ps, ['==', '===', '!=', '!==', '<>', 'case'], true) || in_array($ns, ['==', '===', '!=', '!==', '<>', '=>'], true)
                || (is_array($p) && $p[0] === T_CASE);
            // argumen fungsi query / pencarian nilai
            if (!$cmp && $ps === ',' || $ps === '(') {
                $d = 0;
                for ($k = $i - 1; $k >= 0; $k--) {
                    $s2 = is_array($tk[$k]) ? $tk[$k][1] : $tk[$k];
                    if ($s2 === ')') $d++; elseif ($s2 === '(') { if ($d === 0) { for ($j = $k - 1; $j >= 0; $j--) { if (is_array($tk[$j]) && $tk[$j][0] === T_WHITESPACE) continue; $fn = is_array($tk[$j]) ? strtolower($tk[$j][1]) : ''; break; }
                        if (in_array($fn ?? '', ['where', 'or_where', 'where_in', 'where_not_in', 'get_where', 'like', 'or_like', 'not_like', 'having', 'in_array', 'array_search', 'array_keys', 'set', 'strpos', 'stripos', 'str_contains', 'preg_match'], true)) $cmp = true;
                        break; } $d--; }
                    elseif ($s2 === ';' || $s2 === '{' || $s2 === '}') break;
                }
            }
            if ($cmp) $protected[substr($x[1], 1, -1)] = 1;
        } elseif ($x[0] === T_INLINE_HTML) {
            if (preg_match_all('/(?:[=!]==?|case)\s*([\'"])((?:(?!\1).){1,120})\1|([\'"])((?:(?!\3).){1,120})\3\s*[=!]==?/', $x[1], $m, PREG_SET_ORDER)) {
                foreach ($m as $y) { $v = $y[2] !== '' ? $y[2] : ($y[4] ?? ''); if ($v !== '') $protected[$v] = 1; }
            }
        }
    }
}
// Nilai array (... => 'teks') hanya diterjemahkan bila kuncinya kunci tampilan; selebihnya bisa tersimpan ke database.
$displayKeys = ['pesan', 'msg', 'message', 'title', 'text', 'placeholder', 'label', 'content', 'html', 'confirmbuttontext',
    'cancelbuttontext', 'info', 'caption', 'heading', 'subtitle', 'tooltip', 'alert', 'notif', 'error', 'errors', 'warning', 'success',
    'header', 'judul', 'nama_menu_tampil', 'button', 'btn', 'hint', 'note_tampil', 'emptytable', 'zerorecords', 'lengthmenu', 'search',
    'infoempty', 'infofiltered', 'loadingrecords', 'processing', 'previous', 'next', 'first', 'last', 'reason', 'status_text_tampil'];

$wordBoundary = function ($text, $key) {
    // jangan menyentuh nama permission, mis. 'Approval Kasbon Management.View'
    return '/(?<![A-Za-z0-9_])' . preg_quote($key, '/') . '(?![A-Za-z0-9_])(?![^\'"\n]*\.(?:View|Add|Manage|Delete|Approve|Edit)\b)/u';
};
// Terapkan kamus pada teks bebas (isi string atau HTML). $mode: 'php' (isi literal PHP) | 'html'
$applyText = function ($text, $isWholeLiteral, $quote, &$hits) use ($dict, $words, $wordBoundary, &$protected) {
    $out = $text;
    // 1. seluruh literal sama dengan frasa
    if ($isWholeLiteral) {
        $t = trim($out);
        if ($t !== '' && (isset($protected[$out]) || isset($protected[$t]))) return $out;
        if ($t !== '' && isset($dict[$t])) {
            $rep = $dict[$t];
            if ($quote === "'") $rep = str_replace("'", "\\'", $rep);
            $hits[] = $t;
            return str_replace($t, $rep, $out);
        }
    }
    foreach ($dict as $k => $v) {
        if (strpos($out, $k) === false) continue;
        $isProt = isset($protected[$k]);
        $rep = $v;
        if ($quote === "'") $rep = str_replace("'", "\\'", $rep);
        // 2. teks node HTML: >  frasa  <
        $re = '/(>\s*)' . preg_quote($k, '/') . '(\s*<)/u';
        $out = preg_replace_callback($re, function ($m) use ($rep, $k, &$hits) { $hits[] = $k; return $m[1] . $rep . $m[2]; }, $out);
        // 2b. teks node di awal/akhir isi literal: "...</i> Simpan" / "Tahun</td>"
        if ($quote !== null) {
            $out = preg_replace_callback('/(>\s*)' . preg_quote($k, '/') . '(\s*)$/u', function ($m) use ($rep, $k, &$hits) { $hits[] = $k; return $m[1] . $rep . $m[2]; }, $out);
            $out = preg_replace_callback('/^(\s*)' . preg_quote($k, '/') . '(\s*<)/u', function ($m) use ($rep, $k, &$hits) { $hits[] = $k; return $m[1] . $rep . $m[2]; }, $out);
        }
        // 2d. HTML di dalam literal JS (teks inline): '...</i> Simpan' / "<b>Tambah</b>"
        if ($quote === null) {
            $out = preg_replace_callback('/(>\s*)' . preg_quote($k, '/') . '(\s*)(?=[\'"])/u', function ($m) use ($rep, $k, &$hits) { $hits[] = $k; return $m[1] . $rep . $m[2]; }, $out);
        }
        // 2c. atribut tampil: title / placeholder / alt / data-original-title / aria-label
        $out = preg_replace_callback('/\b(title|placeholder|alt|data-original-title|aria-label|data-bs-original-title)=([\'"])' . preg_quote($k, '/') . '\2/u', function ($m) use ($v, $k, &$hits) {
            $r = str_replace(["'", '"'], ['&#39;', '&quot;'], $v);
            $hits[] = $k; return $m[1] . '=' . $m[2] . $r . $m[2];
        }, $out);
        if ($isProt) continue;
        // 3. literal JS/HTML ber-kutip: 'frasa' / "frasa" (bukan value="...", bukan pembanding, bukan kunci objek)
        if ($quote === null) {
            $re = '/(?<!value=)(?<![=!]=)(?<![=!]==)(?<![=!]= )(?<!case )([\'"])(\s*)' . preg_quote($k, '/') . '(\s*)\1(?!\s*:)(?!\s*=>)(?!\s*[=!]=)/u';
            $out = preg_replace_callback($re, function ($m) use ($v, $k, &$hits) {
                $r = $v; if ($m[1] === "'") $r = str_replace("'", "\\'", $r); else $r = str_replace('"', '\\"', $r);
                $hits[] = $k; return $m[1] . $m[2] . $r . $m[3] . $m[1];
            }, $out);
        }
        // 4. frasa panjang di dalam kalimat
        if ($words($k) >= 3 && strpos($out, $k) !== false) {
            $out = preg_replace_callback($wordBoundary($out, $k), function ($m) use ($rep, $k, &$hits) { $hits[] = $k; return $rep; }, $out);
        }
    }
    return $out;
};

foreach ($rest as $mod) {
    $dir = $root . 'application/modules/' . $mod;
    if (!is_dir($dir)) { fwrite(STDERR, "modul tidak ada: $mod\n"); continue; }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $fi) {
        if ($fi->getExtension() !== 'php') continue;
        $path = $fi->getPathname(); $rel = substr($path, strlen($root));
        if (preg_match('#/(migrations|tests|backup_old|language)/#', $rel)) continue;
        $src = file_get_contents($path);
        $lines = explode("\n", $src);
        $t = token_get_all($src); $n = count($t);
        $out = ''; $fileHits = 0;
        $prevSig = function ($i) use ($t) { for ($k = $i - 1; $k >= 0; $k--) { if (is_array($t[$k]) && in_array($t[$k][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) continue; return $t[$k]; } return null; };
        $nextSig = function ($i) use ($t, $n) { for ($k = $i + 1; $k < $n; $k++) { if (is_array($t[$k]) && in_array($t[$k][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) continue; return $t[$k]; } return null; };
        // nama fungsi pembungkus terdekat untuk token ke-$i
        $enclosingFn = function ($i) use ($t) {
            $d = 0;
            for ($k = $i - 1; $k >= 0; $k--) {
                $s = is_array($t[$k]) ? $t[$k][1] : $t[$k];
                if ($s === ')' || $s === ']') $d++;
                elseif ($s === '(' || $s === '[') {
                    if ($d === 0) {
                        if ($s === '[') return '[';
                        for ($j = $k - 1; $j >= 0; $j--) { if (is_array($t[$j]) && $t[$j][0] === T_WHITESPACE) continue; return is_array($t[$j]) && $t[$j][0] === T_STRING ? strtolower($t[$j][1]) : ''; }
                        return '';
                    }
                    $d--;
                } elseif ($s === ';' || $s === '{' || $s === '}') return '';
            }
            return '';
        };
        $curLine = 1;
        for ($i = 0; $i < $n; $i++) {
            $tok = $t[$i];
            if (!is_array($tok)) { $out .= $tok; continue; }
            [$id, $text, $line] = $tok;
            $lineText = $lines[$line - 1] ?? '';
            // pesan log tidak diterjemahkan: write_log, log_message, history, simpan_aktifitas, dan variabel $keterangan (dipakai log)
            $skipLine = (bool) preg_match('/\b(write_log|log_message|history|simpan_aktifitas)\s*\(|\$(keterangan|status_text)\s*=/i', $lineText);
            $hits = [];
            if (!$skipLine && $id === T_INLINE_HTML) {
                $text = $applyText($text, false, null, $hits);
            } elseif (!$skipLine && ($id === T_CONSTANT_ENCAPSED_STRING || $id === T_ENCAPSED_AND_WHITESPACE)) {
                $p = $prevSig($i); $nx = $nextSig($i);
                $ps = is_array($p) ? $p[1] : $p; $ns = is_array($nx) ? $nx[1] : $nx;
                $isCmp = in_array($ps, ['==', '===', '!=', '!==', 'case', '<>'], true) || in_array($ns, ['==', '===', '!=', '!==', '=>'], true)
                    || (is_array($p) && in_array($p[0], [T_IS_EQUAL, T_IS_IDENTICAL, T_IS_NOT_EQUAL, T_IS_NOT_IDENTICAL, T_CASE], true))
                    || (is_array($nx) && in_array($nx[0], [T_IS_EQUAL, T_IS_IDENTICAL, T_IS_NOT_EQUAL, T_IS_NOT_IDENTICAL, T_DOUBLE_ARROW], true));
                $fn = $enclosingFn($i);
                // nilai array: hanya untuk kunci tampilan
                if (!$isCmp && $ps === '=>') {
                    $kt = null;
                    for ($k2 = $i - 1, $seenArrow = false; $k2 >= 0; $k2--) {
                        if (is_array($t[$k2]) && $t[$k2][0] === T_WHITESPACE) continue;
                        $s3 = is_array($t[$k2]) ? $t[$k2][1] : $t[$k2];
                        if (!$seenArrow) { $seenArrow = ($s3 === '=>'); continue; }
                        $kt = $t[$k2]; break;
                    }
                    $kname = (is_array($kt) && $kt[0] === T_CONSTANT_ENCAPSED_STRING) ? strtolower(substr($kt[1], 1, -1)) : '';
                    $isExcelCol = (is_array($kt) && $kt[0] === T_CONSTANT_ENCAPSED_STRING && preg_match('/^[A-Z]{1,2}[0-9]*$/', substr($kt[1], 1, -1)));
                    if (!$isExcelCol && !in_array($kname, $displayKeys, true)) $isCmp = true;
                }
                if (preg_match('/\.(View|Add|Manage|Delete|Approve|Edit)[\'"]?$/', $text)) $isCmp = true; // nama permission
                // literal di dalam ekspresi nilai array ('k' => $a ?: 'teks' . $b) atau assignment ke variabel deskripsi
                // ($Keterangan_INV = 'teks' . $x) -> bisa tersimpan ke DB
                if (!$isCmp) {
                    $d = 0;
                    for ($k = $i - 1; $k >= 0; $k--) {
                        $tk = $t[$k]; $s = is_array($tk) ? $tk[1] : $tk;
                        if (is_array($tk) && in_array($tk[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) continue;
                        if ($s === ')' || $s === ']') { $d++; continue; }
                        if ($s === '(' || $s === '[') { if ($d === 0) break; $d--; continue; }
                        if ($d > 0) continue;
                        if ($s === ',' || $s === ';' || $s === '{' || $s === '}') break;
                        if ($s === '=>') {
                            for ($j = $k - 1; $j >= 0; $j--) { if (is_array($t[$j]) && $t[$j][0] === T_WHITESPACE) continue; break; }
                            $kt = $t[$j] ?? null;
                            $kn = (is_array($kt) && $kt[0] === T_CONSTANT_ENCAPSED_STRING) ? strtolower(substr($kt[1], 1, -1)) : '';
                            $isCol = (is_array($kt) && $kt[0] === T_CONSTANT_ENCAPSED_STRING && preg_match('/^[A-Z]{1,2}[0-9]*$/', substr($kt[1], 1, -1)));
                            if (!$isCol && !in_array($kn, $displayKeys, true)) $isCmp = true;
                            break;
                        }
                        if ($s === '=' || $s === '.=') {
                            for ($j = $k - 1; $j >= 0; $j--) { if (is_array($t[$j]) && $t[$j][0] === T_WHITESPACE) continue; break; }
                            // lompati indeks array: $arr['x'] = ...
                            $jj = $j; $dd = 0;
                            while ($jj >= 0) { $sx = is_array($t[$jj]) ? $t[$jj][1] : $t[$jj]; if ($sx === ']') { $dd++; } elseif ($sx === '[') { $dd--; } elseif ($dd === 0) break; $jj--; }
                            $vt = $t[$jj] ?? null;
                            $idx = '';
                            for ($q = $jj + 1; $q <= $j; $q++) $idx .= is_array($t[$q]) ? $t[$q][1] : $t[$q];
                            $vn = is_array($vt) && $vt[0] === T_VARIABLE ? $vt[1] . $idx : '';
                            if (preg_match('/keterangan|\bket\b|\[.ket.\]|desc|keperluan|informasi|uraian|catatan|note|remark/i', $vn)) $isCmp = true;
                            break;
                        }
                    }
                }
                if (!$isCmp && !in_array($fn, $dbFns, true)) {
                    if ($id === T_CONSTANT_ENCAPSED_STRING) {
                        $q = $text[0]; $inner = substr($text, 1, -1);
                        $new = $applyText($inner, true, $q === '"' ? '"' : "'", $hits);
                        $text = $q . $new . $q;
                    } else {
                        // bagian string berinterpolasi: tentukan kutip pembuka
                        $text = $applyText($text, false, '"', $hits);
                    }
                }
            }
            if ($hits) { $fileHits += count($hits); foreach ($hits as $h) $log[] = "$rel\t$line\t$h\t" . $dict[$h]; }
            $out .= $text;
        }
        if ($fileHits === 0) continue;
        // pastikan tetap valid secara sintaks
        if ($out !== $src) {
            $tmp = tempnam(sys_get_temp_dir(), 'tr'); file_put_contents($tmp, $out);
            exec('php8.5 -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $rc); unlink($tmp);
            if ($rc !== 0) { fwrite(STDERR, "SINTAKS RUSAK, dilewati: $rel\n"); continue; }
            if (!$dry) file_put_contents($path, $out);
            $changedFiles++; $total += $fileHits;
        }
    }
}
if ($report) file_put_contents($report, implode("\n", $log) . "\n");
echo ($dry ? '[dry] ' : ''), "$total penggantian di $changedFiles file\n";
