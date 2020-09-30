<?php


namespace Rozeo\Checker;


class Result
{
    /**
     * @var string
     */
    private $functionName;
    /**
     * @var int
     */
    private $line;

    public function __construct(string $functionName, int $line)
    {
        $this->functionName = $functionName;
        $this->line = $line;
    }

    /**
     * @return string
     */
    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

}