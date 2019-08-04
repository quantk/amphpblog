<?php


namespace QuantFrame\Auth\Token;


interface TokenInterface extends \Serializable
{
    /**
     * @return int
     */
    public function getId(): int;


    /**
     * @return string
     */
    public function getSessid(): string;
}