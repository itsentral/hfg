<?php
/**
 * Normalize direct array reads used as dynamic offsets:
 *   $map[$row['column']] -> $map[(string) ($row['column'] ?? '')]
 *
 * Default mode is dry-run. Pass --write to update files after linting each result.
 */

$root = dirname(__DIR__) . '/application/modules';
$write = in_array('--write', $argv, true);
$filesChanged = 0;
$replacementCount = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    if (preg_match('~/(?:vendor|third_party)/~', str_replace('\\', '/', $path))) {
        continue;
    }

    $source = file_get_contents($path);
    $tokens = token_get_all($source);
    $output = '';
    $count = 0;
    $total = count($tokens);

    for ($i = 0; $i < $total; $i++) {
        $token = $tokens[$i];
        if ($token !== '[' || !isDynamicOffsetContext($tokens, $i)) {
            $output .= tokenText($token);
            continue;
        }

        $match = matchDirectArrayRead($tokens, $i + 1);
        if ($match === null) {
            $output .= '[';
            continue;
        }

        $output .= '[' . $match['leading'] . '(string) (' . $match['expression'] . " ?? '')";
        $i = $match['end'];
        $count++;
    }

    if ($count === 0 || $output === $source) {
        continue;
    }

    $filesChanged++;
    $replacementCount += $count;
    echo relativePath($path) . ': ' . $count . PHP_EOL;

    if (!$write) {
        continue;
    }

    $temp = tempnam(sys_get_temp_dir(), 'hfg-null-offset-');
    file_put_contents($temp, $output);
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($temp) . ' 2>&1', $lintOutput, $lintCode);
    if ($lintCode !== 0) {
        unlink($temp);
        fwrite(STDERR, "Lint failed for " . relativePath($path) . PHP_EOL . implode(PHP_EOL, $lintOutput) . PHP_EOL);
        exit(1);
    }
    unlink($temp);
    file_put_contents($path, $output);
}

echo 'SUMMARY files=' . $filesChanged . ' replacements=' . $replacementCount . ' mode=' . ($write ? 'write' : 'dry-run') . PHP_EOL;

function tokenText($token)
{
    return is_array($token) ? $token[1] : $token;
}

function previousMeaningfulToken(array $tokens, $index)
{
    for ($i = $index - 1; $i >= 0; $i--) {
        if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }
        return $tokens[$i];
    }
    return null;
}

function isDynamicOffsetContext(array $tokens, $index)
{
    $previous = previousMeaningfulToken($tokens, $index);
    return (is_array($previous) && $previous[0] === T_VARIABLE) || $previous === ']';
}

function matchDirectArrayRead(array $tokens, $start)
{
    $i = $start;
    $leading = '';
    while (isset($tokens[$i]) && is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
        $leading .= $tokens[$i][1];
        $i++;
    }
    if (!isset($tokens[$i]) || !is_array($tokens[$i]) || $tokens[$i][0] !== T_VARIABLE) {
        return null;
    }

    $expression = $tokens[$i][1];
    $i++;
    while (isset($tokens[$i]) && is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
        $expression .= $tokens[$i][1];
        $i++;
    }
    if (!isset($tokens[$i]) || $tokens[$i] !== '[') {
        return null;
    }

    $expression .= '[';
    $i++;
    $hasKey = false;
    for (; isset($tokens[$i]); $i++) {
        $token = $tokens[$i];
        if ($token === ']') {
            if (!$hasKey) {
                return null;
            }
            $expression .= ']';
            break;
        }
        if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_CONSTANT_ENCAPSED_STRING, T_LNUMBER, T_VARIABLE], true)) {
            if (in_array($token[0], [T_CONSTANT_ENCAPSED_STRING, T_LNUMBER, T_VARIABLE], true)) {
                if ($hasKey) {
                    return null;
                }
                $hasKey = true;
            }
            $expression .= $token[1];
            continue;
        }
        return null;
    }
    if (!isset($tokens[$i]) || $tokens[$i] !== ']') {
        return null;
    }

    $j = $i + 1;
    $trailing = '';
    while (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
        $trailing .= $tokens[$j][1];
        $j++;
    }
    if (!isset($tokens[$j]) || $tokens[$j] !== ']') {
        return null;
    }

    return ['leading' => $leading, 'expression' => $expression . $trailing, 'end' => $i];
}

function relativePath($path)
{
    return ltrim(str_replace(dirname(__DIR__), '', $path), DIRECTORY_SEPARATOR);
}
