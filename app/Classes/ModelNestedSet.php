<?php

namespace App\Classes;

use App\Classes\Model;
use App\Classes\NestedSet;

class ModelNestedSet extends Model
{
    // protected $pk = 'id'; // iz Model-a
    // protected $table = 'taxonomy'; // iz Model-a

    protected $parentIdColumnName = 'parent_id';
    protected $leftColumnName = 'left';
    protected $rightColumnName = 'right';
    protected $levelColumnName = 'level';
    protected $positionColumnName = 'position';


    // KORISTE SE METODE ODAVDE

    // vraca niz objekata u korenu
    // ako objekat ima decu sva njegova deca su u
    // nizu objekata children
    public function getListNS()
    {
        $options = [];
        $options['unlimited'] = true;
        $list = $this->listTaxonomy($options);
        unset($options);
        return $list['items'];
    }

    // vraca niz objekata sa zapisima iz tabele
    // slicno kao all()
    public function getFlatListNS()
    {
        $options = [];
        $options['unlimited'] = true;
        $list = $this->listTaxonomyFlatten($options);
        unset($options);
        return $list['items'];
    }

    // vraca niz objekata gde su sva deca na istom nivou kao i roditelj
    public function getWithChildrenNS($id)
    {
        $options['filter_taxonomy_id'] = $id;
        $list = $this->getTaxonomyWithChildren($options);
        unset($options);
        return $list;
    }

    // vraca niz objekata sa roditeljima i trazenim objektom
    // svi objekti su na istom nivou (ovo je fakticki putanja od korena do trazenog objekta)
    // kada je trazeni objekat koren onda vraca samo njega
    public function getWithParentsNS($id)
    {
        $options['filter_taxonomy_id'] = $id;
        $list = $this->getTaxonomyWithParents($options);
        unset($options);
        return $list;
    }

    // brise objekat, a njegovu decu povlaci za jedan nivo vise
    public function deleteNS(int $id)
    {
        $this->deletePullUpChildren($id);
        $this->rebuild();
    }

    // brise objekat i svu njegovu decu
    public function deleteWithChildrenNS(int $id)
    {
        $this->deleteWithChildren($id);
        $this->rebuild();
    }

    // dodaje novi objekat u rodielja (ako je objekat koren onda je $parend_id = 0)
    public function insertNS(int $parent_id, string $naziv, int $korisnik_id)
    {
        $new_position = $this->getNewPosition($parent_id);
        $data = [
            'parent_id' => $parent_id,
            'naziv' => $naziv,
            'korisnik_id' => $korisnik_id,
            'position' => $new_position,
        ];
        $this->insert($data);
        $result = $this->lastId();
        $this->rebuild();

        return $result;
    }

    // kada se promeni pozicija, ako je vec zauzeta, dobiju se dva objekta sa istom pozicijom - ne menjati poziciju ovako
    // roditelja menja dobro, ali i dalje ina problema sa pozicijom - proveriti prvu slobodnu poziciju kada menja roditelja
    // kod promene roditelja pozicija se postavlja na prvu sledecu poziciju roditelja
    // TODO: srediti promenu pozicije tako sto ce sve pozicije, na istom nivou, posle zadate pozicije da se pomere za 1
    public function updateNS(int $id, string $naziv, int $new_parent_id = null)
    {
        $data = [
            'naziv' => $naziv,
        ];
        if (is_int($new_parent_id)) {
            $isNewParentOK = !$this->isParentUnderMyChildren($id, $new_parent_id);
            if ($isNewParentOK) {
                $data['parent_id'] = $new_parent_id;
                $new_position = $this->getNewPosition($new_parent_id);
                $data['position'] = $new_position;
            }
        }

        $this->update($data, $id);
        $this->rebuild();

        return $isNewParentOK;
    }

    // KORISTE SE METODE DOVDE


    /* ======================== NESTED SET ======================== */

    /**
     * Delete the selected taxonomy ID and pull children's parent ID to the same as selected one.<br>
     * Example: selected taxonomy ID is 4, its parent ID is 2. This method will be pull all children that has parent ID = 4 to 2and delete the taxonomy ID 4.<br>
     * Always run <code>$NestedSet->rebuild()</code> after insert, update, delete to rebuild the correctly level, left, right data.
     *
     * @param int $taxonomy_id The selected taxonomy ID.
     * @param array $where Where array structure will be like this.<br>
     * <pre>
     * array(
     *     'whereString' => '(`columnName` = :value1 AND `columnName2` = :value2)',
     *     'whereValues' => array(':value1' => 'lookup value 1', ':value2' => 'lookup value2'),
     * )</pre>
     * @return boolean Return true on success, false for otherwise.
     */
    public function deletePullUpChildren(int $taxonomy_id, array $where = []): bool
    {
        // get this taxonomy parent id
        $sql = 'SELECT `' . $this->pk . '`, `' . $this->parentIdColumnName . '` FROM `' . $this->table . '`';
        $sql .= ' WHERE `' . $this->pk . '` = :taxonomy_id';
        if (isset($where['whereString']) && is_string($where['whereString'])) {
            $sql .= ' AND ' . $where['whereString'];
        }
        $Sth = $this->db->prepare($sql);
        unset($sql);
        $Sth->bindValue(':taxonomy_id', $taxonomy_id, \PDO::PARAM_INT);
        if (isset($where['whereValues']) && is_array($where['whereValues'])) {
            foreach ($where['whereValues'] as $bindName => $bindValue) {
                $Sth->bindValue($bindName, $bindValue);
            }// endforeach;
            unset($bindName, $bindValue);
        }
        $Sth->execute();
        $row = $Sth->fetch();
        $parent_id = $row->{$this->parentIdColumnName};
        $Sth->closeCursor();
        unset($row, $Sth);

        if ($parent_id == null) {
            $parent_id = 0;
        }

        // update this children first level.
        $sql = 'UPDATE `' . $this->table . '`';
        $sql .= ' SET `' . $this->parentIdColumnName . '` = :parent_id';
        $sql .= ' WHERE `' . $this->parentIdColumnName . '` = :taxonomy_id';
        if (isset($where['whereString']) && is_string($where['whereString'])) {
            $sql .= ' AND ' . $where['whereString'];
        }
        $Sth = $this->db->prepare($sql);
        unset($sql);
        $Sth->bindValue(':parent_id', $parent_id, \PDO::PARAM_INT);
        $Sth->bindValue(':taxonomy_id', $taxonomy_id, \PDO::PARAM_INT);
        if (isset($where['whereValues']) && is_array($where['whereValues'])) {
            foreach ($where['whereValues'] as $bindName => $bindValue) {
                $Sth->bindValue($bindName, $bindValue);
            }// endforeach;
            unset($bindName, $bindValue);
        }
        $Sth->execute();
        $Sth->closeCursor();
        unset($Sth);

        // delete the selected taxonomy ID
        $sql = 'DELETE FROM `' . $this->table . '` WHERE `' . $this->pk . '` = :taxonomy_id';
        if (isset($where['whereString']) && is_string($where['whereString'])) {
            $sql .= ' AND ' . $where['whereString'];
        }
        $Sth = $this->db->prepare($sql);
        $Sth->bindValue(':taxonomy_id', $taxonomy_id, \PDO::PARAM_INT);
        if (isset($where['whereValues']) && is_array($where['whereValues'])) {
            foreach ($where['whereValues'] as $bindName => $bindValue) {
                $Sth->bindValue($bindName, $bindValue);
            }// endforeach;
            unset($bindName, $bindValue);
        }
        $result = $Sth->execute();
        $Sth->closeCursor();
        unset($sql, $Sth);

        return $result;
    }// deletePullUpChildren


    /**
     * Delete the selected taxonomy ID with its ALL children.<br>
     * Always run <code>$NestedSet->rebuild()</code> after insert, update, delete to rebuild the correctly level, left, right data.
     *
     * The columns `left`, `right` must have been built before using this method, otherwise the result will be incorrect.
     *
     * @param int $taxonomy_id The taxonomy ID to delete.
     * @param array $where Where array structure will be like this.<br>
     *                      `parent` or `child` is required if there are ambugious error.
     * <pre>
     * array(
     *     'whereString' => '(`parent`.`columnName` = :value1 AND `parent`.`columnName2` = :value2)',
     *     'whereValues' => array(':value1' => 'lookup value 1', ':value2' => 'lookup value2'),
     * )</pre>
     * @return mixed Return number on success, return false for otherwise.
     */
    public function deleteWithChildren(int $taxonomy_id, array $where = [])
    {
        $options = [];
        $options['filter_taxonomy_id'] = $taxonomy_id;
        $options['unlimited'] = true;
        if (isset($where['whereString'])) {
            $options['where'] = $where;
        }
        $result = $this->getTaxonomyWithChildren($options);
        $i_count = 0;
        unset($options);

        if (is_array($result)) {
            foreach ($result as $row) {
                $sql = 'DELETE FROM `' . $this->table . '` WHERE `' . $this->pk . '` = :taxonomy_id';
                if (isset($where['whereString']) && is_string($where['whereString'])) {
                    $where['whereString'] = str_replace(['`parent`.', '`child`.'], '', $where['whereString']);
                    $sql .= ' AND ' . $where['whereString'];
                }
                $Sth = $this->db->prepare($sql);

                $Sth->bindValue(':taxonomy_id', $row->{$this->pk}, \PDO::PARAM_INT);
                if (isset($where['whereValues']) && is_array($where['whereValues'])) {
                    foreach ($where['whereValues'] as $bindName => $bindValue) {
                        $Sth->bindValue($bindName, $bindValue);
                    }// endforeach;
                    unset($bindName, $bindValue);
                }

                $execute = $Sth->execute();
                $Sth->closeCursor();
                unset($sql, $Sth);

                if ($execute === true) {
                    $i_count++;
                }
            }// endforeach;
            unset($row);
        }

        unset($execute, $result);

        if ($i_count <= 0) {
            return false;
        } else {
            return $i_count;
        }
    }// deleteWithChildren


    /**
     * Get new position for taxonomy in the selected parent.
     *
     * @param int $parent_id The parent ID. If root, set this to 0.
     * @param array $where Where array structure will be like this.<br>
     * <pre>
     * array(
     *     'whereString' => '(`columnName` = :value1 AND `columnName2` = :value2)',
     *     'whereValues' => array(':value1' => 'lookup value 1', ':value2' => 'lookup value2'),
     * )</pre>
     * @return int Return the new position in the same parent.<br>
     *              WARNING! If there are no results or the results according to the conditions cannot be found. It always returns 1.
     */
    public function getNewPosition(int $parent_id, array $where = []): int
    {
        $sql = 'SELECT `' . $this->pk . '`, `' . $this->parentIdColumnName . '`, `' . $this->positionColumnName . '` FROM `' . $this->table . '`';
        $sql .= ' WHERE `' . $this->parentIdColumnName . '` = :parent_id';
        if (isset($where['whereString']) && is_string($where['whereString'])) {
            $sql .= ' AND ' . $where['whereString'];
        }
        $sql .= ' ORDER BY `' . $this->positionColumnName . '` DESC';

        $Sth = $this->db->prepare($sql);

        $Sth->bindValue(':parent_id', $parent_id, \PDO::PARAM_INT);
        if (isset($where['whereValues']) && is_array($where['whereValues'])) {
            foreach ($where['whereValues'] as $bindName => $bindValue) {
                $Sth->bindValue($bindName, $bindValue);
            }// endforeach;
            unset($bindName, $bindValue);
        }

        $Sth->execute();
        $row = $Sth->fetch();
        $Sth->closeCursor();
        unset($sql, $Sth);

        if ($row != null) {
            return (int) ($row->{$this->positionColumnName} + 1);
        } else {
            unset($row);
            return 1;
        }
    }// getNewPosition


    /**
     * Get taxonomy from selected item and fetch its ALL children.<br>
     * Example: There are taxonomy tree like this. Root 1 > 1.1 > 1.1.1, Root 2, Root 3 > 3.1, Root 3 > 3.2 > 3.2.1, Root 3 > 3.2 > 3.2.2, Root 3 > 3.3<br>
     * Assume that selected item is Root 3. So, the result will be Root 3 > 3.1, 3.2 > 3.2.1, 3.2.2, 3.3<br>
     *
     * Warning! Even this method has options for search, custom where conditions
     * but it is recommended that you should set the option to select only specific item.<br>
     * This method is intended to show results from a single target.
     *
     * The columns `left`, `right` must have been built before using this method, otherwise the result will be incorrect.
     *
     * @param array $options Available options: <br>
     *                          `filter_taxonomy_id` (int) The filter taxonomy ID.<br>
     *                          `search` (array) The search array format is..<br>
     *                              `array('columns' => array('name', 'column2', 'column3'), 'searchValue' => 'search string')`<br>
     *                          `where` (array) The custom where conditions. The array format is..<br>
     *                              ``array('whereString' => '(`parent`.`columnName` = :value1 AND `child`.`columnName2` = :value2)', 'whereValues' => array(':value1' => 'lookup value 1', ':value2' => 'lookup value2'))``<br>
     *                              or just only `whereString`.<br>
     *                              ``array('whereString' => '(`parent`.`columnName` = \'value\'))``<br>
     *                          `unlimited` (bool) Set to `true` to do not limit the result.<br>
     *                          `offset` (number) The offset in the query.<br>
     *                          `limit` (number) The limit number in the query.<br>
     *
     * @return mixed Return array object of taxonomy data if found, return `null` if not found.
     */
    public function getTaxonomyWithChildren(array $options = [])
    {
        // remove unwanted options that is available in `listTaxonomy()` method.
        unset(
            $options['filter_parent_id'],
            $options['taxonomy_id_in'],
            $options['no_sort_orders'],
            $options['list_flatten']
        );
        // set required option.
        $options['list_flatten'] = true;
        $result = $this->listTaxonomy($options);

        if (is_array($result) && array_key_exists('items', $result) && $result['items'] != null) {
            return $result['items'];
        } else {
            return null;
        }
    }// getTaxonomyWithChildren


    /**
     * Get taxonomy from selected item and fetch its parent in a line until root item.<br>
     * Example: There are taxonomy tree like this. Root1 > 1.1 > 1.1.1 > 1.1.1.1<br>
     * Assume that you selected at 1.1.1. So, the result will be Root1 > 1.1 > 1.1.1<br>
     * But if you set 'skipCurrent' to true the result will be Root1 > 1.1
     *
     * Warning! Even this method has options for search, custom where conditions
     * but it is recommended that you should set the option to select only specific item.<br>
     * This method is intended to show results from a single target.
     *
     * The columns `left`, `right` must have been built before using this method, otherwise the result will be incorrect.
     *
     * @link http://mikehillyer.com/articles/managing-hierarchical-data-in-mysql/ Original source.
     * @param array $options Available options: <br>
     *                      `filter_taxonomy_id` (int) The filter taxonomy ID.<br>
     *                      `search` (array) The search array format is..<br>
     *                              `array('columns' => array('name', 'column2', 'column3'), 'searchValue' => 'search string')`<br>
     *                      `where` (array) The custom where conditions. The array format is..<br>
     *                              ``array('whereString' => '(`node`.`columnName` = :value1 AND `node`.`columnName2` = :value2)', 'whereValues' => array(':value1' => 'lookup value 1', ':value2' => 'lookup value2'))``<br>
     *                              or just only `whereString`.<br>
     *                              ``array('whereString' => '(`node`.`columnName` = \'value\'))``<br>
     *                      `skipCurrent` (bool) Set to `true` to skip currently selected item.
     * @return mixed Return array object of taxonomy data if found, return null if not found.
     */
    public function getTaxonomyWithParents(array $options = []): array
    {
        $sql = 'SELECT `parent`.*';
        $sql .= ' FROM `' . $this->table . '` AS `node`,';
        $sql .= ' `' . $this->table . '` AS `parent`';
        $sql .= ' WHERE';
        $sql .= ' `node`.`' . $this->leftColumnName . '` BETWEEN `parent`.`' . $this->leftColumnName . '` AND `parent`.`' . $this->rightColumnName . '`';

        if (isset($options['filter_taxonomy_id'])) {
            $sql .= ' AND `node`.`' . $this->pk . '` = :filter_taxonomy_id';
        }

        if (
            isset($options['search']) &&
            is_array($options['search']) &&
            array_key_exists('columns', $options['search']) &&
            is_array($options['search']['columns']) &&
            array_key_exists('searchValue', $options['search'])
        ) {
            $haveSearch = true;
            $sql .= ' AND (';
            $array_keys = array_keys($options['search']['columns']);
            $last_array_key = array_pop($array_keys);
            foreach ($options['search']['columns'] as $key => $column) {
                $sql .= '`node`.`' . $column . '` LIKE :search';
                if ($key !== $last_array_key) {
                    $sql .= ' OR ';
                }
            }// endforeach;
            unset($array_keys, $column, $key, $last_array_key);
            $sql .= ')';
        }

        if (
            isset($options['where']['whereString']) &&
            is_string($options['where']['whereString'])
        ) {
            $sql .= ' AND ' . $options['where']['whereString'];
        }

        $sql .= ' GROUP BY `parent`.`' . $this->pk . '`';
        $sql .= ' ORDER BY `parent`.`' . $this->leftColumnName . '`';

        $Sth = $this->db->prepare($sql);
        if (isset($options['filter_taxonomy_id'])) {
            $Sth->bindValue(':filter_taxonomy_id', $options['filter_taxonomy_id'], \PDO::PARAM_INT);
        }
        if (isset($options['search']) && is_array($options['search']) && array_key_exists('searchValue', $options['search'])) {
            $Sth->bindValue(':search', '%'.$options['search']['searchValue'].'%', \PDO::PARAM_STR);
        }
        if (isset($options['where']['whereValues']) && is_array($options['where']['whereValues'])) {
            foreach ($options['where']['whereValues'] as $placeholder => $value) {
                $Sth->bindValue($placeholder, $value);
            }// endforeach;
            unset($placeholder, $value);
        }
        $Sth->execute();
        $result = $Sth->fetchAll();
        $Sth->closeCursor();

        if (isset($options['skipCurrent']) && $options['skipCurrent'] === true) {
            unset($result[count($result)-1]);
        }
        unset($haveSearch, $sql, $Sth);

        if ($result !== false && $result !== null) {
            return $result;
        } else {
            return null;
        }
    }// getTaxonomyWithParents


    /**
     * Rebuild children into array.
     *
     * @internal This method was called from `getTreeWithChildren()`.
     * @param array $array The array data that was get while running `getTreeWithChildren()`. This data contains 'children' object property but empty, it will be added here.
     * @return array Return added correct id of the children to data.
     */
    protected function getTreeRebuildChildren(array $array): array
    {
        foreach ($array as $id => $row) {
            if (isset($row->{$this->parentIdColumnName})) {
                $array[$row->{$this->parentIdColumnName}]->children[$id] = $id;
            }
        }

        return $array;
    }// getTreeRebuildChildren


    /**
     * Get the data nest tree with children.<br>
     * Its result will be look like this...<pre>
     * Array(
     *     [0] => stdClass Object
     *         (
     *             [id] => 0
     *             [children] => Array
     *                 (
     *                     [1] => 1
     *                     [2] => 2
     *                     [3] => 3
     *                 )
     *         )
     *     [1] => stdClass Object
     *         (
     *             [id] => 1
     *             [parent_id] => 0
     *             [level] => 1
     *             [children] => Array
     *                 (
     *                 )
     *         )
     *     [2] => stdClass Object
     *         (
     *             [id] => 2
     *             [parent_id] => 0
     *             [level] => 1
     *             [children] => Array
     *                 (
     *                     [4] => 4
     *                     [5] => 5
     *                 )
     *         )
     *     [3] => stdClass Object
     *         (
     *             [id] => 3
     *             [parent_id] => 0
     *             [level] => 1
     *             [children] => Array
     *                 (
     *                 )
     *         )
     *     [4] => stdClass Object
     *         (
     *             [id] => 4
     *             [parent_id] => 2
     *             [level] => 2
     *             [children] => Array
     *                 (
     *                 )
     *         )
     *     [5] => stdClass Object
     *         (
     *             [id] => 5
     *             [parent_id] => 2
     *             [level] => 2
     *             [children] => Array
     *                 (
     *                 )
     *         )
     * )</pre>
     *
     * Usually, this method is for get taxonomy tree data in the array format that suit for loop/nest loop verify level.
     *
     * @since 1.0
     * @internal This method was called from `rebuild()`.
     * @param array $where Where array structure will be like this.<br>
     * <pre>
     * array(
     *     'whereString' => '(`columnName` = :value1 AND `columnName2` = :value2)',
     *     'whereValues' => array(':value1' => 'lookup value 1', ':value2' => 'lookup value2'),
     * )</pre>
     * @return array Return formatted array structure as seen in example of docblock.
     */
    protected function getTreeWithChildren(array $where = []): array
    {
        $sql = 'SELECT *';
        $sql .= ' FROM `' . $this->table . '`';
        if (isset($where['whereString']) && is_string($where['whereString'])) {
            $sql .= ' WHERE ' . $where['whereString'];
        }
        $sql .= ' ORDER BY `' . $this->positionColumnName . '` ASC';
        $Sth = $this->db->prepare($sql);

        if (isset($where['whereValues']) && is_array($where['whereValues'])) {
            foreach ($where['whereValues'] as $bindName => $bindValue) {
                $Sth->bindValue($bindName, $bindValue);
            }// endforeach;
            unset($bindName, $bindValue);
        }

        $Sth->execute();
        $result = $Sth->fetchAll();
        unset($Sth);

        // create a root node to hold child data about first level items
        $root = new \stdClass;
        $root->{$this->pk} = 0;
        $root->children = [];

        $array = [$root];
        unset($root);

        // populate the array and create an empty children array
        foreach ($result as $row) {
            $array[$row->{$this->pk}] = $row;
            $array[$row->{$this->pk}]->children = [];
        }

        // now process the array and build the child data
        $array = $this->getTreeRebuildChildren($array);

        return $array;
    }// getTreeWithChildren


    /**
     * Detect that is this taxonomy's parent setting to be under this taxonomy's children or not.<br>
     * For example: Root 1 > 1.1 > 1.1.1 > 1.1.1.1 > 1.1.1.1.1<br>
     * Assume that you editing 1.1.1 and its parent is 1.1. Now you change its parent to 1.1.1.1.1 which is under its children.<br>
     * The parent of 1.1.1 must be root, Root 1, 1.1 and never go under that.
     *
     * @param int $taxonomy_id The taxonomy ID that is chaging the parent.
     * @param int $parent_id The selected parent ID to check.
     * @param array $where Where array structure will be like this.<br>
     * <pre>
     * array(
     *     'whereString' => '(`node`.`columnName` = :value1 AND `node`.`columnName2` = :value2)',
     *     'whereValues' => array(':value1' => 'lookup value 1', ':value2' => 'lookup value2'),
     * )</pre>
     * @return bool Return `true` if its parent is under its children (INCORRECT changes).<br>
     *                      Return `true` if search result was not found (INCORRECT changes).<br>
     *                      Return `false` if its parent is not under its children (CORRECT changes).
     */
    public function isParentUnderMyChildren(int $taxonomy_id, int $parent_id, array $where = []): bool
    {
        if ($parent_id == '0') {
            // if parent is root, always return false because it is correctly!
            return false;
        }

        // check for selected parent that must not under this taxonomy.
        $options = [];
        $options['filter_taxonomy_id'] = $parent_id;
        if (isset($where['whereString'])) {
            $options['where'] = $where;
        }
        $taxonomy_parents = $this->getTaxonomyWithParents($options);

        if (is_array($taxonomy_parents) && !empty($taxonomy_parents)) {
            foreach ($taxonomy_parents as $row) {
                if ($row->{$this->parentIdColumnName} == $taxonomy_id) {
                    unset($row, $taxonomy_parents);
                    return true;
                }
            }// endforeach;
            unset($row, $taxonomy_parents);
            return false;
        }

        unset($taxonomy_parents);
        return true;
    }// isParentUnderMyChildren


    /**
     * List taxonomy.
     *
     * The columns `left`, `right` must have been built before using this method, otherwise the result will be incorrect.
     *
     * @param array $options Available options: <br>
     *                          `filter_taxonomy_id` (int) The filter taxonomy ID.<br>
     *                          `filter_parent_id` (int) The filter parent ID.<br>
     *                          `search` (array) The search array format is..<br>
     *                              `array('columns' => array('name', 'column2', 'column3'), 'searchValue' => 'search string')`<br>
     *                          `taxonomy_id_in` (array) The taxonomy ID to look with `IN()` MySQL function.<br>
     *                              The array values must be integer, example `array(1,3,4,5)`.<br>
     *                              This will be flatten the result even `list_flatten` was not set.<br>
     *                          `where` (array) The custom where conditions. The array format is..<br>
     *                              ``array('whereString' => '(`parent`.`columnName` = :value1 AND `parent`.`columnName2` = :value2)', 'whereValues' => array(':value1' => 'lookup value 1', ':value2' => 'lookup value2'))``<br>
     *                              or just only `whereString`.<br>
     *                              ``array('whereString' => '(`parent`.`columnName` = \'value\'))``<br>
     *                          `no_sort_orders` (bool) Set to `true` to do not sort order the result.<br>
     *                          `unlimited` (bool) Set to `true` to do not limit the result.<br>
     *                          `offset` (number) The offset in the query.<br>
     *                          `limit` (number) The limit number in the query.<br>
     *                          `list_flatten` (bool) Set to `true` to list the result flatten.
     * @return array Return array with 'total' and 'items' as keys.
     */
    public function listTaxonomy(array $options = []): array
    {
        // create query SQL statement ------------------------------------------------------
        $sql = 'SELECT * FROM `' . $this->table . '` AS `parent`';

        if (isset($options['filter_taxonomy_id']) || isset($options['filter_parent_id']) || isset($options['search'])) {
            // if there is filter or search, there must be inner join to select all of filtered children.
            $sql .= ' INNER JOIN `' . $this->table . '` AS `child`';
            $sql .= ' ON `child`.`' . $this->leftColumnName . '` BETWEEN `parent`.`' . $this->leftColumnName . '` AND `parent`.`' . $this->rightColumnName . '`';
        }

        $sql .= ' WHERE 1';

        if (isset($options['taxonomy_id_in']) && is_array($options['taxonomy_id_in']) && !empty($options['taxonomy_id_in'])) {
            // Due to IN() and NOT IN() cannot using bindValue directly.
            // read more at http://stackoverflow.com/questions/17746667/php-pdo-for-not-in-query-in-mysql
            // and http://stackoverflow.com/questions/920353/can-i-bind-an-array-to-an-in-condition
            if (is_array($options['taxonomy_id_in'])) {
                // loop remove non-number for safety.
                foreach ($options['taxonomy_id_in'] as $key => $eachTaxonomyId) {
                    if (!is_numeric($eachTaxonomyId) || $eachTaxonomyId != intval($eachTaxonomyId)) {
                        unset($options['taxonomy_id_in'][$key]);
                    }
                }// endforeach;
                unset($eachTaxonomyId, $key);
                // build value for use with `IN()` function. Example: 1,3,4,5.
                $taxonomy_id_in = implode(',', $options['taxonomy_id_in']);
                $sql .= ' AND `' . $this->pk . '` IN (' . $taxonomy_id_in . ')';
            }
        }

        if (isset($options['filter_taxonomy_id'])) {
            $sql .= ' AND `parent`.`' . $this->pk . '` = :filter_taxonomy_id';
        }
        if (isset($options['filter_parent_id'])) {
            $sql .= ' AND `parent`.`' . $this->parentIdColumnName . '` = :filter_parent_id';
        }

        if (
            isset($options['search']) &&
            is_array($options['search']) &&
            array_key_exists('columns', $options['search']) &&
            is_array($options['search']['columns']) &&
            array_key_exists('searchValue', $options['search'])
        ) {
            // if found search array with its columns and search value.
            $sql .= ' AND (';
            $array_keys = array_keys($options['search']['columns']);
            $last_array_key = array_pop($array_keys);
            foreach ($options['search']['columns'] as $key => $column) {
                $sql .= '`parent`.`' . $column . '` LIKE :search';
                if ($key !== $last_array_key) {
                    $sql .= ' OR ';
                }
            }// endforeach;
            unset($array_keys, $column, $key, $last_array_key);
            $sql .= ')';
        }

        if (
            isset($options['where']['whereString']) &&
            is_string($options['where']['whereString'])
        ) {
            $sql .= ' AND ' . $options['where']['whereString'];
        }

        // group, sort and order
        if (!isset($options['no_sort_orders']) || (isset($options['no_sort_orders']) && $options['no_sort_orders'] === false)) {
            if (isset($options['filter_taxonomy_id']) || isset($options['filter_parent_id']) || isset($options['search'])) {
                $sql .= ' GROUP BY `child`.`' . $this->pk . '`';
                $order_by = '`child`.`' . $this->leftColumnName . '` ASC';
            } elseif (isset($taxonomy_id_in) && $taxonomy_id_in != null) {
                $order_by = 'FIELD(`' . $this->pk . '`,' . $taxonomy_id_in . ')';
            } else {
                $order_by = '`parent`.`' . $this->leftColumnName . '` ASC';
            }
            if (isset($order_by)) {
                $sql .= ' ORDER BY ' . $order_by;
                unset($order_by);
            }
        }
        unset($taxonomy_id_in);
        // end create query SQL statement -------------------------------------------------

        // prepare and get 'total' count.
        $Sth = $this->db->prepare($sql);
        $this->listTaxonomyBindValues($Sth, $options);
        $Sth->execute();
        $result = $Sth->fetchAll();
        $output['total'] = count($result);
        unset($Sth);

        // re-create query and prepare. second step is for set limit and fetch all items.
        if (!isset($options['unlimited']) || (isset($options['unlimited']) && $options['unlimited'] === false)) {
            if (!isset($options['offset']) || (isset($options['offset']) && !is_numeric($options['offset']))) {
                $options['offset'] = 0;
            }
            if (!isset($options['limit']) || isset($options['limit']) && (!is_numeric($options['limit']) || $options['limit'] > '10000')) {
                $options['limit'] = 20;
            }

            $sql .= ' LIMIT '.$options['offset'].', '.$options['limit'];

            $Sth = $this->db->prepare($sql);
            $this->listTaxonomyBindValues($Sth, $options);
            $Sth->execute();
            $result = $Sth->fetchAll();
            unset($Sth);
        }

        unset($sql);

        // populate tree with children.
        if (
            $output['total'] > 0 &&
            is_array($result) &&
            (
                !isset($options['list_flatten']) ||
                (isset($options['list_flatten']) && $options['list_flatten'] !== true)
            )
        ) {
            $result = $this->listTaxonomyBuildTreeWithChildren($result, $options);
        }// endif; populate tree with children.

        // set 'items' result
        $output['items'] = $result;

        unset($result);
        return $output;
    }// listTaxonomy


    /**
     * Bind taxonomy values for listTaxonomy() method.
     *
     * @internal This method was called from `listTaxonomy()`.
     * @param \PDOStatement $Sth PDO statement class object.
     * @param array $options Available options: <br>
     *                          `filter_taxonomy_id` (int) The filter taxonomy ID.<br>
     *                          `filter_parent_id` (int) The filter parent ID.<br>
     *                          `search` (array) The search array format is..<br>
     *                              `array('columns' => array('name', 'column2', 'column3'), 'searchValue' => 'search string')`<br>
     *                          `where` (array) The custom where conditions. The array format is..<br>
     *                              ``array('whereString' => '(`parent`.`columnName` = :value1 AND `parent`.`columnName2` = :value2)', 'whereValues' => array(':value1' => 'lookup value 1', ':value2' => 'lookup value2'))``<br>
     *                              or just only `whereString`.<br>
     *                              ``array('whereString' => '(`parent`.`columnName` = \'value\'))``
     */
    protected function listTaxonomyBindValues(\PDOStatement $Sth, array $options = [])
    {
        if (isset($options['filter_taxonomy_id'])) {
            $Sth->bindValue(':filter_taxonomy_id', $options['filter_taxonomy_id'], \PDO::PARAM_INT);
        }
        if (isset($options['filter_parent_id'])) {
            $Sth->bindValue(':filter_parent_id', $options['filter_parent_id'], \PDO::PARAM_INT);
        }
        if (isset($options['search']) && is_array($options['search']) && array_key_exists('searchValue', $options['search'])) {
            $Sth->bindValue(':search', '%'.$options['search']['searchValue'].'%', \PDO::PARAM_STR);
        }
        if (isset($options['where']['whereValues']) && is_array($options['where']['whereValues'])) {
            foreach ($options['where']['whereValues'] as $placeholder => $value) {
                $Sth->bindValue($placeholder, $value);
            }// endforeach;
            unset($placeholder, $value);
        }
    }// listTaxonomyBindValues


    /**
     * Build tree data with children.
     *
     * @internal This method was called from `listTaxonomy()`.
     * @param array $result The array item get from fetchAll() method using the PDO.
     * @param array $options Available options: <br>
     *                          `taxonomy_id_in` (array) The taxonomy ID to look with `IN()` MySQL function. The array values must be integer, example `array(1,3,4,5)`.<br>
     *                          `list_flatten` (bool) Set to `true` to list the result flatten.
     * @return array Return array data of formatted values.
     */
    protected function listTaxonomyBuildTreeWithChildren(array $result, array $options = []): array
    {
        if (isset($options['list_flatten']) && $options['list_flatten'] === true) {
            return $result;
        }

        $items = [];
        foreach ($result as $row) {
            $items[$row->{$this->parentIdColumnName}][] = $row;
        }// endforeach;

        if (!isset($options['taxonomy_id_in'])) {
            // without taxonomy_id_in option exists, this result can format to be heirarchical.
            foreach ($result as $row) {
                if (isset($items[$row->{$this->pk}])) {
                    $row->children = $items[$row->{$this->pk}];
                }
            }// endforeach;

            $result = ($items[0] ?? array_shift($items));// this is important ([0]) for prevent duplicate items
        }

        unset($items, $row);
        return $result;
    }// listTaxonomyBuildTreeWithChildren


    /**
     * List taxonomy as flatten not tree.<br>
     * All parameters or arguments are same as `listTaxonomy()` method.
     *
     * @param array $options Available options: <br>
     *                          `filter_taxonomy_id` (int) The filter taxonomy ID.<br>
     *                          `filter_parent_id` (int) The filter parent ID.<br>
     *                          `search` (array) The search array format is..<br>
     *                              `array('columns' => array('name', 'column2', 'column3'), 'searchValue' => 'search string')`<br>
     *                          `taxonomy_id_in` (array) The taxonomy ID to look with `IN()` MySQL function.<br>
     *                              The array values must be integer, example `array(1,3,4,5)`.<br>
     *                              This will be flatten the result even `list_flatten` was not set.<br>
     *                          `where` (array) The custom where conditions. The array format is..<br>
     *                              ``array('whereString' => '(`parent`.`columnName` = :value1 AND `parent`.`columnName2` = :value2)', 'whereValues' => array(':value1' => 'lookup value 1', ':value2' => 'lookup value2'))``<br>
     *                              or just only `whereString`.<br>
     *                              ``array('whereString' => '(`parent`.`columnName` = \'value\'))``<br>
     *                          `no_sort_orders` (bool) Set to `true` to do not sort order the result.<br>
     *                          `unlimited` (bool) Set to `true` to do not limit the result.<br>
     *                          `offset` (number) The offset in the query.<br>
     *                          `limit` (number) The limit number in the query.<br>
     *                          `list_flatten` (bool) Set to `true` to list the result flatten.
     * @return array Return array with 'total' and 'items' as keys.
     */
    public function listTaxonomyFlatten(array $options = []): array
    {
        $options['list_flatten'] = true;
        $result = $this->listTaxonomy($options);

        if (is_array($result) && (!array_key_exists('total', $result) || !array_key_exists('items', $result))) {
            if (
                !array_key_exists('total', $result) &&
                array_key_exists('items', $result) &&
                is_array($result['items'])
            ) {
                $result['total'] = count($result['items']);
            } elseif (!array_key_exists('total', $result)) {
                $result['total'] = 0;
            }

            if (!array_key_exists('items', $result)) {
                $result['items'] = [];
            }

            return $result;
        }

        return $result;
    }// listTaxonomyFlatten


    /**
     * Rebuilds the tree data and save it to the database.<br>
     * This will be rebuild the level, left, right values.
     *
     * The columns `left`, `right` must have been built before using this method, otherwise the result will be incorrect.
     *
     * @param array $where Where array structure will be like this.<br>
     * <pre>
     * array(
     *     'whereString' => '(`columnName` = :value1 AND `columnName2` = :value2)',
     *     'whereValues' => array(':value1' => 'lookup value 1', ':value2' => 'lookup value2'),
     * )</pre>
     */
    public function rebuild(array $where = [])
    {
        // get taxonomy tree data in the array format that suit for loop/nest loop verify level.
        $data = $this->getTreeWithChildren($where);

        $n = 0; // need a variable to hold the running n tally
        $level = 0; // need a variable to hold the running level tally

        // verify the level data. this method will be alter the $data value because it will be called as reference.
        // so, it doesn't need to use `$data = $this->rebuildGenerateTreeData()`;
        $this->rebuildGenerateTreeData($data, 0, 0, $n);

        foreach ($data as $id => $row) {
            if ($id == '0') {
                continue;
            }

            $sql = 'UPDATE `' . $this->table . '`';
            $sql .= ' SET';
            $sql .= ' `' . $this->levelColumnName . '` = :level,';
            $sql .= ' `' . $this->leftColumnName . '` = :left,';
            $sql .= ' `' . $this->rightColumnName . '` = :right';
            $sql .= ' WHERE `' . $this->pk . '` = :id';

            if (isset($where['whereString'])) {
                $sql .= ' AND ' . $where['whereString'];
            }

            $Sth = $this->db->prepare($sql);
            $Sth->bindValue(':level', $row->{$this->levelColumnName}, \PDO::PARAM_INT);
            $Sth->bindValue(':left', $row->{$this->leftColumnName}, \PDO::PARAM_INT);
            $Sth->bindValue(':right', $row->{$this->rightColumnName}, \PDO::PARAM_INT);
            $Sth->bindValue(':id', $row->{$this->pk}, \PDO::PARAM_INT);

            if (isset($where['whereValues']) && is_array($where['whereValues'])) {
                foreach ($where['whereValues'] as $bindName => $bindValue) {
                    $Sth->bindValue($bindName, $bindValue);
                }// endforeach;
                unset($bindName, $bindValue);
            }

            $Sth->execute();
            unset($sql, $Sth);
        }// endforeach;

        unset($data, $id, $row);
    }// rebuild


    /**
     * Rebuild taxonomy level, left, right for tree data.<br>
     * This method will be alter the $arr value. It will be set level, left, right value.
     *
     * This method modify variables via argument reference without return anything.
     *
     * @internal This method was called from `rebuild()`.
     * @param array $array The data array, will be call as reference and modify its value.
     * @param int $id The ID of taxonomy.
     * @param int $level The level of taxonomy.
     * @param int $n The tally or count number, will be call as reference and modify its value.
     */
    protected function rebuildGenerateTreeData(array &$array, int $id, int $level, int &$n)
    {
        $array[$id]->{$this->levelColumnName} = $level;
        $array[$id]->{$this->leftColumnName} = $n++;

        // loop over the node's children and process their data
        // before assigning the right value
        foreach ($array[$id]->children as $child_id) {
            $this->rebuildGenerateTreeData($array, $child_id, $level + 1, $n);
        }

        $array[$id]->{$this->rightColumnName} = $n++;
    }// rebuildGenerateTreeData


    /**
     * Restore the columns name to its default property's value.
     *
     * @since 1.0
     * @return void
     */
    public function restoreColumnsName()
    {
        $this->pk = 'id';
        $this->parentIdColumnName = 'parent_id';
        $this->leftColumnName = 'left';
        $this->rightColumnName = 'right';
        $this->levelColumnName = 'level';
        $this->positionColumnName = 'position';
    }// restoreColumnsName
}
