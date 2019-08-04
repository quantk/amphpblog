<?php


namespace QuantFrame\Auth;


class Credentials
{
    /**
     * @var string
     */
    public $username;
    /**
     * @var string
     */
    public $password;

    /**
     * Credentials constructor.
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param string $username
     * @param string $password
     * @return Credentials
     */
    public static function create(string $username, string $password)
    {
        return new static($username, $password);
    }
}