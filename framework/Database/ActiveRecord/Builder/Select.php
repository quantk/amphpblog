<?php


namespace QuantFrame\Database\ActiveRecord\Builder;


use Amp\Promise;
use Aura\SqlQuery\Common\SelectInterface;

/**
 * Class Select
 * @package QuantFrame\Database\ActiveRecord\Builder
 */
final class Select implements BaseQuery
{
    /**
     * @var \Aura\SqlQuery\Common\Select|SelectInterface
     */
    private $select;
    /**
     * @var Query
     */
    private $query;

    /**
     * Select constructor.
     * @param Query $query
     * @param SelectInterface $select
     */
    public function __construct(
        Query $query,
        SelectInterface $select
    )
    {
        $this->select = $select;
        $this->query  = $query;
    }

    /**
     * @return string
     */
    public function toSql()
    {
        return $this->select->getStatement();
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->select->getBindValues();
    }

    /**
     * @param array $spec
     * @return $this
     */
    public function orderBy(array $spec)
    {
        $this->select->orderBy($spec);

        return $this;
    }

    /**
     * @param string $cond
     * @return Select
     */
    public function where(string $cond)
    {
        $this->select->where($cond);

        return $this;
    }

    /**
     * @param string $cond
     * @return Select
     */
    public function orWhere(string $cond)
    {
        $this->select->orWhere($cond);

        return $this;
    }

    /**
     *
     * Binds a single value to the query.
     *
     * @param string $name The placeholder name or number.
     *
     * @param mixed $value The value to bind to the placeholder.
     *
     * @return $this
     *
     */
    public function bindValue(string $name, $value)
    {
        $this->select->bindValue($name, $value);

        return $this;
    }

    /**
     * @param array $bindValues
     * @return $this
     */
    public function bindValues(array $bindValues)
    {
        $this->select->bindValues($bindValues);

        return $this;
    }

    /**
     *
     * Sets the number of rows per page.
     *
     * @param int $paging The number of rows to page at.
     *
     * @return $this
     *
     */
    public function setPaging($paging)
    {
        $this->select->setPaging($paging);

        return $this;
    }

    /**
     *
     * Makes the select FOR UPDATE (or not).
     *
     * @param bool $enable Whether or not the SELECT is FOR UPDATE (default
     * true).
     *
     * @return $this
     *
     */
    public function forUpdate($enable = true)
    {
        $this->select->forUpdate($enable);

        return $this;
    }

    /**
     *
     * Makes the select DISTINCT (or not).
     *
     * @param bool $enable Whether or not the SELECT is DISTINCT (default
     * true).
     *
     * @return $this
     *
     */
    public function distinct($enable = true)
    {
        $this->select->distinct($enable);

        return $this;
    }

    /**
     *
     * Adds columns to the query.
     *
     * Multiple calls to cols() will append to the list of columns, not
     * overwrite the previous columns.
     *
     * @param array $cols The column(s) to add to the query.
     *
     * @return $this
     *
     */
    public function cols(array $cols)
    {
        $this->select->cols($cols);

        return $this;
    }

    /**
     *
     * Adds a FROM element to the query; quotes the table name automatically.
     *
     * @param string $spec The table specification; "foo" or "foo AS bar".
     *
     * @return $this
     *
     */
    public function from($spec)
    {
        $this->select->from($spec);

        return $this;
    }

    /**
     *
     * Adds a raw unquoted FROM element to the query; useful for adding FROM
     * elements that are functions.
     *
     * @param string $spec The table specification, e.g. "function_name()".
     *
     * @return $this
     *
     */
    public function fromRaw($spec)
    {
        $this->select->fromRaw($spec);

        return $this;
    }

    /**
     *
     * Adds an aliased sub-select to the query.
     *
     * @param string|Select $spec If a Select object, use as the sub-select;
     * if a string, the sub-select string.
     *
     * @param string $name The alias name for the sub-select.
     *
     * @return $this
     *
     */
    public function fromSubSelect($spec, $name)
    {
        /** @psalm-suppress ImplicitToStringCast */
        $this->select->fromSubSelect(is_string($spec) ? $spec : $spec->_getAuraSelect(), $name);

        return $this;
    }

    /**
     * @return SelectInterface
     * @internal
     */
    public function _getAuraSelect()
    {
        return $this->select;
    }

    /**
     *
     * Adds a JOIN table and columns to the query.
     *
     * @param string $join The join type: inner, left, natural, etc.
     *
     * @param string $spec The table specification; "foo" or "foo AS bar".
     *
     * @param string $cond Join on this condition.
     *
     * @return $this
     *
     * @throws \Aura\SqlQuery\Exception
     */
    public function join($join, $spec, $cond = null)
    {
        $this->select->join($join, $spec, $cond);

        return $this;
    }

    /**
     *
     * Adds a JOIN to an aliased subselect and columns to the query.
     *
     * @param string $join The join type: inner, left, natural, etc.
     *
     * @param string|Select $spec If a Select
     * object, use as the sub-select; if a string, the sub-select
     * command string.
     *
     * @param string $name The alias name for the sub-select.
     *
     * @param string $cond Join on this condition.
     *
     * @return $this
     *
     * @throws \Aura\SqlQuery\Exception
     */
    public function joinSubSelect($join, $spec, $name, $cond = null)
    {
        /** @psalm-suppress ImplicitToStringCast */
        $this->select->joinSubSelect($join, is_string($spec) ? $spec : $spec->_getAuraSelect(), $name, $cond);

        return $this;
    }

    /**
     *
     * Adds grouping to the query.
     *
     * @param array $spec The column(s) to group by.
     *
     * @return $this
     *
     */
    public function groupBy(array $spec)
    {
        $this->select->groupBy($spec);

        return $this;
    }

    /**
     *
     * Adds a HAVING condition to the query by AND; if a value is passed as
     * the second param, it will be quoted and replaced into the condition
     * wherever a question-mark appears.
     *
     * Array values are quoted and comma-separated.
     *
     * {{code: php
     *     // simplest but non-secure
     *     $select->having("COUNT(id) = $count");
     *
     *     // secure
     *     $select->having('COUNT(id) = ?', $count);
     *
     *     // equivalent security with named binding
     *     $select->having('COUNT(id) = :count');
     *     $select->bind('count', $count);
     * }}
     *
     * @param string $cond The HAVING condition.
     *
     * @return $this
     *
     */
    public function having($cond)
    {
        $this->select->having($cond);

        return $this;
    }

    /**
     *
     * Adds a HAVING condition to the query by AND; otherwise identical to
     * `having()`.
     *
     * @param string $cond The HAVING condition.
     *
     * @return $this
     *
     * @see having()
     *
     */
    public function orHaving($cond)
    {
        $this->select->orHaving($cond);

        return $this;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit)
    {
        $this->select->limit($limit);

        return $this;
    }

    /**
     *
     * Sets the limit and count by page number.
     *
     * @param int $page Limit results to this page number.
     *
     * @return $this
     *
     */
    public function page($page)
    {
        $this->select->page($page);

        return $this;
    }

    /**
     *
     * Takes the current select properties and retains them, then sets
     * UNION for the next set of properties.
     *
     * @return $this
     *
     */
    public function union()
    {
        $this->select->union();

        return $this;
    }

    /**
     *
     * Takes the current select properties and retains them, then sets
     * UNION ALL for the next set of properties.
     *
     * @return $this
     *
     */
    public function unionAll()
    {
        $this->select->unionAll();

        return $this;
    }

    /**
     * @return Promise
     */
    public function get(): Promise
    {
        return $this->query->execute($this);
    }

    /**
     * @return Promise
     */
    public function first()
    {
        return $this->query->execute($this, [
            'first' => true
        ]);
    }
}