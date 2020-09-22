<?php

namespace App\Classes;

use App\Classes\Db;
use App\Classes\Config;

class Validator
{
    protected $items;
    protected $db;
    protected $convert = false;
    protected $errors = [];
    protected $rules = [
        'required',
        'len',
        'minlen',
        'maxlen',
        'email',
        'alnum', // alphanumeric + space
        'match_field',
        'unique',
        'multi_unique',
        'min',
        'max',
        'equal',
        'greater_than',
        'less_than',
        'jmbg',
    ];
    protected $messages = [
        'required' => "Polje :field je obavezno",
        'len' => "Polje :field mora da ima tačno :option karaktera",
        'minlen' => "Polje :field mora da ima najmanje :option karaktera",
        'maxlen' => "Polje :field mora da ima najviše :option karaktera",
        'email' => "Polje :field mora da sadrži ispravnu email adresu",
        'alnum' => "Polje :field sme da sadrži samo slova i brojeve",
        'match_field' => "Polja :field i :option moraju da budu ista",
        'unique' => "U bazi već postoji :field sa istom vrednošću",
        'multi_unique' => "U bazi već postoji ovakav zapis",
        'max' => "Polje :field mora da bude broj ne veći od :option",
        'min' => "Polje :field mora da bude broj ne manji od :option",
        'equal' => "Polje :field mora da bude jednako :option",
        'greater_than' => "Polje :field mora da bude veće od :option",
        'less_than' => "Polje :field mora da bude manje od :option",
        'jmbg' => "Polje :field mora da bude ispravan JMBG",
    ];

    public function __construct()
    {
        $this->db = Config::getContainer('db');
        if (Config::get('cyrillic')) {
            $this->convert = true;
        }
    }

    public function validate(array $data, array $rules)
    {
        $data = $this->sanitize($data);
        $this->items = array_map('trim', $this->sanitize($data));
        foreach ($this->items as $item => $value) {
            if (in_array($item, array_keys($rules))) {
                $this->val([
                    'field' => $item,
                    'value' => $value,
                    'rules' => $rules[$item],
                ]);
            }
        }
        if ($this->hasErrors()) {
            $_SESSION['errors'] = $this->getErrors();
        }
    }

    protected function val($item)
    {
        $field = $item['field'];
        $value = $item['value'];
        foreach ($item['rules'] as $rule => $option) {
            if (in_array($rule, $this->rules)) {
                if (!call_user_func_array([$this, $rule], [$field, $value, $option])) {
                    $text = str_replace(
                        [':field', ':option'],
                        ['[' . ucfirst(str_replace(['-', '_'], ' ', $field)) . ']', '[' . ucfirst($option) . ']'],
                        $this->messages[$rule]
                    );
                    $this->errors[$field][] = $this->convert ? latinicaUCirilicu($text) : $text;
                }
            }
        }
    }

    protected function required($field, $value, $option)
    {
        return trim($value) !== '';
    }

    protected function len($field, $value, $option)
    {
        return mb_strlen($value, 'UTF-8') === $option;
    }

    protected function minlen($field, $value, $option)
    {
        return mb_strlen($value, 'UTF-8') >= $option;
    }

    protected function maxlen($field, $value, $option)
    {
        return mb_strlen($value, 'UTF-8') <= $option;
    }

    protected function email($field, $value, $option)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    protected function alnum($field, $value, $option)
    {
        return preg_match("/^[\p{L}\p{Z}A-Za-z0-9 ]+$/ui", $value);
    }

    protected function match_field($field, $value, $option)
    {
        return $value === $this->items[$option];
    }

    protected function unique($field, $value, $option)
    {
        // $option - tabela.kolona#id_col:id_val
        $id_val = null;
        if (strpos($option, '#') === false) {
            $tmp = explode('.', $option);
            $table = $tmp[0];
            $column = $tmp[1];
        } else {
            $tmp = explode('.', $option);
            $tmp1 = explode('#', $tmp[1]);
            $table = $tmp[0];
            $column = $tmp1[0];
            $tmp2 = explode(':', $tmp1[1]);
            $id_col = $tmp2[0];
            $id_val = (int)$tmp2[1];
        }

        $wheres = [];
        $params = [];

        $wheres[] = "$column = :{$column}";
        $params[":{$column}"] = $value;

        if ($id_val !== null) {
            $wheres[] = "{$id_col} <> :{$id_col}";
            $params[":{$id_col}"] = $id_val;
        }

        $where = implode(' AND ', $wheres);
        $sql = "SELECT COUNT(*) AS broj FROM $table WHERE {$where};";
        $res = $this->db->fetch($sql, $params);
        return (int)$res[0]->broj > 0 ? false : true;
    }

    protected function multi_unique($field, $value, $option)
    {
        // $option - tab.col1,col2,col3#id_col:id_val
        $id_val = null;
        if (strpos($option, '#') === false) {
            $tmp = explode('.', $option);
            $table = $tmp[0];
            $columns = explode(',', $tmp[1]);
        } else {
            $tmp = explode('.', $option);
            $tmp1 = explode('#', $tmp[1]);
            $table = $tmp[0];
            $columns = explode(',', $tmp1[0]);
            $tmp2 = explode(':', $tmp1[1]);
            $id_col = $tmp2[0];
            $id_val = (int)$tmp2[1];
        }
        $wheres = [];
        $params = [];
        foreach ($columns as $col) {
            $wheres[] = "$col = :{$col}";
            $params[":{$col}"] = $this->items[$col];
        }
        if ($id_val !== null) {
            $wheres[] = "{$id_col} <> :{$id_col}";
            $params[":{$id_col}"] = $id_val;
        }
        $where = implode(' AND ', $wheres);
        $sql = "SELECT COUNT(*) AS broj FROM {$table} WHERE {$where};";
        $res = $this->db->fetch($sql, $params);
        return (int)$res[0]->broj > 0 ? false : true;
    }

    protected function max($field, $value, $option)
    {
        return $value <= $option;
    }

    protected function min($field, $value, $option)
    {
        return $value >= $option;
    }

    protected function equal($field, $value, $option)
    {
        return (string)$value === (string)$option;
    }

    protected function greater_than($field, $value, $option)
    {
        return (int)$value > (int)$option;
    }

    protected function less_than($field, $value, $option)
    {
        return (int)$value < (int)$option;
    }

    protected function jmbg($field, $value, $option)
    {
        $len = strlen($value);
        if ($len != 13) {
            return false;
        }
        $niz = str_split($value);
        $ok = true;
        $zbir = 0;
        foreach ($niz as $k=>$v) {
            if (!is_numeric($v)) {
                return false;
            }
            $niz[$k]=(int)$v;
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

    public function sanitize(array $data)
    {
        return filter_var_array($data, FILTER_SANITIZE_STRING);
    }

    public function hasErrors()
    {
        return count($this->errors) > 0 ? true : false;
    }

    public function getErrors(string $key = null)
    {
        if ($key) {
            return isset($this->errors[$key]) ? $this->errors[$key] : null;
        } else {
            return $this->hasErrors() ? $this->errors : null;
        }
    }

    public function getFirstError(string $key)
    {
        return isset($this->errors[$key][0]) ? $this->errors[$key][0] : null;
    }

    public function addError($field, $text)
    {
        $this->errors[$field][] = $text;
    }
}
