<?php


namespace QuantFrame\Database\ActiveRecord\Builder;


use Amp\Promise;
use Aura\SqlQuery\Common\InsertInterface;

class Insert implements BaseQuery
{

    /**
     * @var InsertInterface|\Aura\SqlQuery\Common\Insert
     */
    private $insert;
    /**
     * @var Query
     */
    private $query;

    /**
     * Select constructor.
     * @param Query $query
     * @param InsertInterface|\Aura\SqlQuery\Common\Insert $insert
     */
    public function __construct(
        Query $query,
        InsertInterface $insert
    )
    {
        $this->insert = $insert;
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
        return $this->insert->getStatement();
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
        $this->insert->bindValue($name, $value);

        return $this;
    }

    /**
     * @param array $bindValues
     * @return $this
     */
    public function bindValues(array $bindValues)
    {
        $this->insert->bindValues($bindValues);

        return $this;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->insert->getBindValues();
    }

    /**
     * @param string $into
     * @return $this
     */
    public function into(string $into)
    {
        $this->insert->into($into);

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
        $this->insert->col($col);
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
        $this->insert->cols($cols);

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
        $this->insert->set($col, $value);

        return $this;
    }

    /**
     *
     * Adds multiple rows for bulk insert.
     *
     * @param array $rows An array of rows, where each element is an array of
     * column key-value pairs. The values are bound to placeholders.
     *
     * @return $this
     *
     */
    public function addRows(array $rows)
    {
        /** @psalm-suppress PossiblyUndefinedMethod */
        $this->insert->addRows($rows);

        return $this;
    }

    /**
     *
     * Add one row for bulk insert; increments the row counter and optionally
     * adds columns to the new row.
     *
     * When adding the first row, the counter is not incremented.
     *
     * After calling `addRow()`, you can further call `col()`, `cols()`, and
     * `set()` to work with the newly-added row. Calling `addRow()` again will
     * finish off the current row and start a new one.
     *
     * @param array $cols An array of column key-value pairs; the values are
     * bound to placeholders.
     *
     * @return $this
     *
     */
    public function addRow(array $cols = array())
    {
        /** @psalm-suppress PossiblyUndefinedMethod */
        $this->insert->addRow($cols);

        return $this;
    }
}