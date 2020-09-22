<?php

function latinicaUCirilicu(string $tekst, bool $cirilica_u_latinicu = false)
{
    $latinica = [
        'Đ', 'Dj', 'DJ', 'Lj', 'LJ', 'Nj', 'NJ', 'Dž', 'DŽ',
        'A', 'B', 'V', 'G', 'D', 'E', 'Ž', 'Z', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'Ć', 'U', 'F', 'H', 'C', 'Č', 'Š',
        'đ', 'dj', 'lj', 'nj', 'dž',
        'a', 'b', 'v', 'g', 'd', 'e', 'ž', 'z', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'ć', 'u', 'f', 'h', 'c', 'č', 'š',
    ];
    $cirilica = [
        'Ђ', 'Ђ', 'Ђ', 'Љ', 'Љ', 'Њ', 'Њ', 'Џ', 'Џ',
        'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ж', 'З', 'И', 'Ј', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'Ћ', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш',
        'ђ', 'ђ', 'љ', 'њ', 'џ',
        'а', 'б', 'в', 'г', 'д', 'е', 'ж', 'з', 'и', 'ј', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'ћ', 'у', 'ф', 'х', 'ц', 'ч', 'ш',
    ];

    if ($cirilica_u_latinicu) {
        return str_replace($cirilica, $latinica, $tekst);
    } else {
        return str_replace($latinica, $cirilica, $tekst);
    }
}

function isValidJMBG(string $jmbg)
{
    $len = strlen($jmbg);

    if ($len != 13) {
        return false;
    }

    $niz = str_split($jmbg);
    $ok = true;
    $zbir = 0;

    foreach ($niz as $k => $v) {
        if (!is_numeric($v)) {
            return false;
        }

        $niz[$k] = (int) $v;
    }

    $zbir = $niz[0] * 7
        + $niz[1] * 6
        + $niz[2] * 5
        + $niz[3] * 4
        + $niz[4] * 3
        + $niz[5] * 2
        + $niz[6] * 7
        + $niz[7] * 6
        + $niz[8] * 5
        + $niz[9] * 4
        + $niz[10] * 3
        + $niz[11] * 2;

    $ostatak = $zbir % 11;

    if ($ostatak === 1) {
        return false;
    }

    $kontrolni = 11 - $ostatak;

    if ($ostatak == 0) {
        $kontrolni = 0;
    }

    if ($kontrolni != $niz[12]) {
        return false;
    }

    return true;
}

function dd($var, $print = false, $die = true, $backtrace = false)
{
    echo '<h3 style="color:#900">VARIABLE</h1>';
    echo '<pre style="background-color:#fdd; color:#000; padding:1rem;">';

    if ($print) {
        print_r($var);
    } else {
        var_dump($var);
    }

    echo '</pre>';

    if (gettype($var) === 'object') {
        echo '<h3 style="color:#090">OBJECT: ' . get_class($var) . '</h1>';
        echo '<pre style="background-color:#dfd; color:#000; padding:1rem;">';
        print_r(get_class_methods($var));
        echo '</pre>';
    }

    if ($backtrace) {
        echo '<h3 style="color:#009">BACKTRACE</h1>';
        echo '<pre style="background-color:#ddf; color:#000; padding:1rem;">';
        print_r(debug_backtrace());
        echo '</pre>';
    }

    if ($die) {
        die();
    }
}
