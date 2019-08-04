<?php


namespace QuantFrame\Database\ActiveRecord\Builder;


use Amp\Promise;
use Aura\SqlQuery\Common\DeleteInterface;

class Delete implements BaseQuery
{
    /**
     * @var DeleteInterface|\Aura\SqlQuery\Common\Delete
     */
    private $delete;
    /**
     * @var Query
     */
    private $query;

    /**
     * Select constructor.
     * @param Query $query
     * @param DeleteInterface $delete
     */
    public function __construct(
        Query $query,
        DeleteInterface $delete
    )
    {
        $this->delete = $delete;
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
        return $this->delete->getStatement();
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
        $this->delete->bindValue($name, $value);

        return $this;
    }

    /**
     * @param array $bindValues
     * @return $this
     */
    public function bindValues(array $bindValues)
    {
        $this->delete->bindValues($bindValues);

        return $this;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->delete->getBindValues();
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
        $this->delete->where($cond);
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
        $this->delete->orWhere($cond);
        return $this;
    }
}