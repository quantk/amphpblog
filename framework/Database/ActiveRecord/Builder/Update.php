<?php


namespace QuantFrame\Database\ActiveRecord\Builder;


use Amp\Promise;
use Aura\SqlQuery\Common\UpdateInterface;

class Update implements BaseQuery
{
    /**
     * @var UpdateInterface|\Aura\SqlQuery\Common\Update
     */
    private $update;
    /**
     * @var Query
     */
    private $query;

    /**
     * Select constructor.
     * @param Query $query
     * @param UpdateInterface $update
     */
    public function __construct(
        Query $query,
        UpdateInterface $update
    )
    {
        $this->update = $update;
        $this->query  = $query;
    }

    /**
     * @return Promise<array>
     */
    public function execute()
    {
        return $this->query->execute($this);
    }

    /**
     * @return string
     */
    public function toSql()
    {
        return $this->update->getStatement();
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
        $this->update->bindValue($name, $value);

        return $this;
    }

    /**
     * @param array $bindValues
     * @return $this
     */
    public function bindValues(array $bindValues)
    {
        $this->update->bindValues($bindValues);

        return $this;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->update->getBindValues();
    }

    /**
     *
     * Sets the table to update.
     *
     * @param string $table The table to update.
     *
     * @return $this
     *
     */
    public function table($table)
    {
        $this->update->table($table);
        return $this;
    }

    /**
     *
     * Adds a WHERE condition to the query by AND. If the condition has
     * ?-placeholders, additional arguments to the method will be bound to
     * those placeholders sequentially.
     *
     * @param string $cond The WHERE condition.
     * @param mixed ...$bind arguments to bind to placeholders
     *
     * @return $this
     *
     */
    public function where($cond)
    {
        $this->update->where($cond);
        return $this;
    }

    /**
     *
     * Adds a WHERE condition to the query by OR. If the condition has
     * ?-placeholders, additional arguments to the method will be bound to
     * those placeholders sequentially.
     *
     * @param string $cond The WHERE condition.
     * @param mixed ...$bind arguments to bind to placeholders
     *
     * @return $this
     *
     * @see where()
     *
     */
    public function orWhere($cond)
    {
        $this->update->orWhere($cond);
        return $this;
    }

    /**
     *
     * Sets one column value placeholder; if an optional second parameter is
     * passed, that value is bound to the placeholder.
     *
     * @param string $col The column name.
     *
     * @return $this
     *
     */
    public function col($col)
    {
        $this->update->col($col);
        return $this;
    }

    /**
     *
     * Sets multiple column value placeholders. If an element is a key-value
     * pair, the key is treated as the column name and the value is bound to
     * that column.
     *
     * @param array $cols A list of column names, optionally as key-value
     *                    pairs where the key is a column name and the value is a bind value for
     *                    that column.
     *
     * @return $this
     *
     */
    public function cols(array $cols)
    {
        $this->update->cols($cols);

        return $this;
    }

    /**
     *
     * Sets a column value directly; the value will not be escaped, although
     * fully-qualified identifiers in the value will be quoted.
     *
     * @param string $col The column name.
     *
     * @param string $value The column value expression.
     *
     * @return $this
     *
     */
    public function set($col, $value)
    {
        $this->update->set($col, $value);

        return $this;
    }
}