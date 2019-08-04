<?php /** @noinspection UnserializeExploitsInspection */


namespace QuantFrame\Auth\Token;


class UserToken implements TokenInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $sessid;

    /**
     * UserToken constructor.
     * @param int $id
     * @param string $sessid
     */
    public function __construct(int $id, string $sessid)
    {
        $this->id     = $id;
        $this->sessid = $sessid;
    }

    /**
     * String representation of object
     * @link https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([$this->id, $this->sessid]);
    }

    /**
     * Constructs the object
     * @link https://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        /** @var int|null $id */
        /** @var string|null $sessid */
        /** @var array $unserialize */
        $unserialize = unserialize($serialized);
        [$id, $sessid] = $unserialize;

        if ($id === null || $sessid === null) {
            throw new \RuntimeException('Error unserializing token');
        }

        $this->id     = (int)$id;
        $this->sessid = (string)$sessid;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSessid(): string
    {
        return $this->sessid;
    }
}