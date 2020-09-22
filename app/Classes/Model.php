<?php

namespace App\Classes;

abstract class Model
{
    protected $db;
    protected $table;
    protected $pk = 'id';
    protected $model;
    protected $pgn_conf;

    public function __construct()
    {
        $this->db = Config::getContainer('db');
        $this->pgn_conf = Config::get('pagination');
        $this->model = get_class($this);
    }

    public function prepare($sql)
    {
        $this->db->prep($sql);
    }

    public function transaction()
    {
        $this->db->beginTransaction();
    }

    public function commit()
    {
        $this->db->commit();
    }

    public function execute(array $params)
    {
        return $this->db->execute($params);
    }

    public function run(string $sql, array $params = null)
    {
        return $this->db->run($sql, $params);
    }

    public function fetch(string $sql, array $params = null, string $model = null)
    {
        $model = ($model === null) ? $this->model : $model;
        return $this->db->fetch($sql, $params, $model);
    }

    public function all(string $sort_column = null, $sort = 'ASC')
    {
        $order_by = '';
        $params = null;
        $sort = ($sort === 'DESC') ? $sort : 'ASC';
        if ($sort_column !== null && !empty(trim($sort_column))) {
            $order_by = " ORDER BY {$sort_column} {$sort}";
        }
        $sql = "SELECT * FROM {$this->table}{$order_by};";
        return $this->fetch($sql);
    }

    public function find(int $id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->pk} = :id LIMIT 1;";
        $params = [":id" => $id];
        return $this->fetch($sql, $params) === [] ? null : $this->fetch($sql, $params)[0];
    }

    // TODO: prepraviti na simple sql query (where, order, limit ...)
    public function select(array $columns)
    {
        $cols = implode(', ', $columns);
        $sql = "SELECT {$cols} FROM {$this->table};";
        return $this->fetch($sql);
    }

    public function countTable()
    {
        $sql = "SELECT COUNT(*) AS table_count FROM {$this->table}";
        return (int)$this->fetch($sql)[0]->table_count;
    }

    public function insert(array $data)
    {
        foreach ($data as $key => $value) {
            $cols[] = $key;
            $pars[] = ':' . $key;
            $vals[] = $value;
        }
        $params = array_combine($pars, $vals);
        $c = implode(', ', $cols);
        $v = implode(', ', $pars);
        $sql = "INSERT INTO {$this->table} ({$c}) VALUES ({$v});";
        return $this->run($sql, $params);
    }

    public function update(array $data, int $id)
    {
        foreach ($data as $key => $value) {
            $cols[] = $key;
            $pars[] = ':' . $key;
            $vals[] = $value;
        }
        $params = array_combine($pars, $vals);
        $params[":{$this->pk}"] = $id;
        $cv = array_combine($cols, $pars);
        $c = '';
        foreach ($cv as $key => $val) {
            $c .= ", {$key} = {$val}";
        }
        $c = ltrim($c, ', ');
        $sql = "UPDATE {$this->table} SET {$c} WHERE {$this->pk} = :{$this->pk};";
        return $this->run($sql, $params);
    }

    public function deleteOne(int $id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->pk} = :{$this->pk}";
        $params = [":{$this->pk}" => $id];
        return $this->run($sql, $params);
    }

    public function delete(array $where)
    {
        list($column, $operator, $value) = $where;
        $sql = "DELETE FROM {$this->table} WHERE {$column} {$operator} :{$column};";
        $params = [":{$column}" => $value];
        return $this->run($sql, $params);
    }

    public function enumOrSetList($column)
    {
        $sql = "SELECT DATA_TYPE, COLUMN_TYPE
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = :ts AND TABLE_NAME = :tn AND COLUMN_NAME = :cn;";
        $params = [':ts' => Config::get('db.dbname'), ':tn' => $this->table, ':cn' => $column];
        $result = $this->fetch($sql, $params)[0];

        if ($result->DATA_TYPE === 'enum' || $result->DATA_TYPE === 'set') {
            $list = explode(
                ",",
                str_replace(
                    "'",
                    "",
                    substr($result->COLUMN_TYPE, 5, (strlen($result->COLUMN_TYPE) - 6))
                )
            );
        }
        if (is_array($list) && !empty($list)) {
            return $list;
        }

        return null;
    }

    public function paginate(int $page, string $name='page', string $sql = null, array $params = null, int $perpage = null, int $span = null)
    {
        $sql = ($sql !== null) ? $sql : "SELECT * FROM {$this->table};";
        $data = $this->pageData($page, $sql, $params, $perpage);
        $links = $this->pageLinks($page, $name, $perpage, $span);
        return ['data' => $data, 'links' => $links];
    }

    protected function pageData(int $page, string $sql, array $params = null, int $perpage = null)
    {
        $sql = str_replace('SELECT', 'SELECT SQL_CALC_FOUND_ROWS', $sql);
        if ($perpage === null) {
            $perpage = $this->pgn_conf['per_page'];
        }
        $limit = $perpage;
        $offset = ($page - 1) * $perpage;
        $sql = rtrim($sql, ';');
        $sql .= " LIMIT {$limit} OFFSET {$offset};";
        $data = $this->fetch($sql, $params);
        return $data;
    }

    protected function foundRows()
    {
        $sql = "SELECT FOUND_ROWS() AS count;";
        return (int)$this->fetch($sql)[0]->count;
    }

    protected function pageLinks(int $page, string $name = 'page', int $perpage = null, int $span = null)
    {
        $links = [];
        $links['current_page'] = $page;
        if ($perpage === null) {
            $perpage = $this->pgn_conf['per_page'];
        }
        $links['per_page'] = $perpage;
        $span = $span ? $span : $this->pgn_conf['page_span'];
        $count = $this->foundRows();
        $links['total_rows'] = $count;
        $u = Config::getContainer('request')->getUri();

        $query = [];
        parse_str(Config::getContainer('request')->getUri()->getQuery(), $query);

        $url = $u->getBaseUrl() . '/' . $u->getPath();
        $links['url'] = $url;
        $pages = (int)ceil($count / $perpage);
        $links['total_pages'] = $pages;
        $full_span = (($span * 2 + 1) > $pages) ? $pages : $span * 2 + 1;
        $prev = ($page > 2) ? $page - 1 : 1;
        $links['prev_page'] = $prev;
        $next = ($page < $pages) ? $page + 1 : $pages;
        $links['next_page'] = $next;
        $start = $page - $span;
        $end = $page + $span;
        if ($page <= $span + 1) {
            $start = 1;
            $end = $full_span;
        }
        if ($page >= $pages - $span) {
            $start = $pages - $span * 2;
            $end = $pages;
        }
        if ($full_span >= $pages) {
            $start = 1;
            $end = $pages;
        }

        $current_first = ($page === 1) ? true : false;
        $current_last = ($page === $pages) ? true : false;
        $zapis_od = (($page - 1) * $perpage) + 1;
        $zapis_do = ($zapis_od + $perpage) - 1;
        $zapis_do = $zapis_do >= $count ? $count : $zapis_do;
        $links['row_from'] = $zapis_od;
        $links['row_to'] = $zapis_do;
        $buttons = [];
        $query[$name] = 1;
        $qs = $this->buildQueryString($query);
        $buttons[] = [ // prva strana
            'page' => 1,
            'url' => "{$url}?{$qs}",
            'current' => $current_first,
        ];
        $query[$name] = $prev;
        $qs = $this->buildQueryString($query);
        $buttons[] = [ // prethodna strana
            'page' => "<",
            'url' => "{$url}?{$qs}",
            'current' => $current_first,
        ];
        for ($i = $start; $i <= $end; $i++) {
            $current = false;
            if ($page === $i) {
                $current = true;
            }
            $query[$name] = $i;
            $qs = $this->buildQueryString($query);
            $buttons[] = [ // ostale strane
            'page' => $i,
            'url' => "{$url}?{$qs}",
            'current' => $current,
            ];
        }
        $query[$name] = $next;
        $qs = $this->buildQueryString($query);
        $buttons[] = [ // sledeca strana
            'page' => ">",
            'url' => "{$url}?{$qs}",
            'current' => $current_last,
        ];
        $query[$name] = $pages;
        $qs = $this->buildQueryString($query);
        $buttons[] = [ // poslednja strana
            'page' => $pages,
            'url' => "{$url}?{$qs}",
            'current' => $current_last,
        ];
        $links['buttons'] = $buttons;
        $goto = [];
        for ($i=1; $i <= $pages ; $i++) {
            $current = false;
            if ($page === $i) {
                $current = true;
            }
            $query[$name] = $i;
            $qs = $this->buildQueryString($query);
            $goto[] = [
            'page' => $i,
            'url' => "{$url}?{$qs}",
            'current' => $current,
            ];
        }
        $links['select'] = $goto;

        return $links;
    }

    protected function buildQueryString(array $query)
    {
        $q=[];
        foreach ($query as $key => $value) {
            $q[]="{$key}={$value}";
        }
        return implode('&', $q);
    }

    public function hasOne($model_class, $foreign_table_fk)
    {
        $m = new $model_class();
        $sql = "SELECT * FROM {$m->getTable()} WHERE {$foreign_table_fk} = :{$foreign_table_fk};";
        $pk = $this->getPrimaryKey();
        $params = [":{$foreign_table_fk}" => $this->$pk];
        $result = $this->fetch($sql, $params, $model_class);
        dd($result);
        return $result[0];
    }

    public function hasMany($model_class, $foreign_table_fk, $order = null)
    {
        $m = new $model_class();
        $o = $order === null ? '' : ' ORDER BY ' . $order;
        $sql = "SELECT * FROM {$m->getTable()} WHERE {$foreign_table_fk} = :{$foreign_table_fk}{$o};";
        $pk = $this->getPrimaryKey();
        $params = [":{$foreign_table_fk}" => $this->$pk];
        $result = $this->fetch($sql, $params, $model_class);
        return $result;
    }

    public function belongsTo($model_class, $this_table_fk)
    {
        $m = new $model_class();
        $sql = "SELECT * FROM {$m->getTable()} WHERE {$m->getPrimaryKey()} = :{$m->getPrimaryKey()};";
        $params = [":{$m->getPrimaryKey()}" => $this->$this_table_fk];
        $result = $this->fetch($sql, $params, $model_class);
        return $result[0];
    }

    public function belongsToMany($model_class, $pivot_table, $pt_this_table_fk, $pt_foreign_table_fk, $order = null)
    {
        $m = new $model_class();
        $tbl = $m->getTable();
        $o = $order === null ? '' : ' ORDER BY ' . $order;
        $pk = $this->getPrimaryKey();
        $params = [":{$pk}" => $this->$pk];
        $sql = "SELECT {$tbl}.*, {$pivot_table}.*  FROM {$tbl} JOIN {$pivot_table} ON {$tbl}.{$m->getPrimaryKey()} = {$pivot_table}.{$pt_foreign_table_fk} WHERE {$pivot_table}.{$pt_this_table_fk} = :{$pk}{$o};";
        $result = $this->fetch($sql, $params, $model_class);
        return $result;
    }

    /**
     * Vraca naziv tabele Model-a
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Vraca naziv primarnog kljuca tabele Model-a
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->pk;
    }

    public function table()
    {
        return $this->table;
    }

    public function pk()
    {
        return $this->pk;
    }

    public function model()
    {
        return $this->model;
    }

    public function db()
    {
        return $this->db;
    }

    public function lastId()
    {
        return $this->db->lastInsertId();
    }

    public function rowCount()
    {
        return $this->db->rowCount();
    }

    public function columnCount()
    {
        return $this->db->columnCount();
    }

    public function errorCode()
    {
        return $this->db->errorCode();
    }

    public function errorInfo()
    {
        return $this->db->errorInfo();
    }

    public function queryString()
    {
        return $this->db->queryString();
    }

    public function tableFields()
    {
        $sql = "SHOW COLUMNS FROM {$this->table};";
        return $this->fetch($sql);
    }

    public function tableKeys()
    {
        $sql = "SHOW KEYS FROM {$this->table};";
        return $this->fetch($sql);
    }
}
