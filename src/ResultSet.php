<?php


namespace Rozeo\Checker;


use Iterator;

class ResultSet implements Iterator
{
    /**
     * @var string
     */
    private $filepath;

    /**
     * @var Result[]
     */
    private $results;

    /**
     * @var int
     */
    private $iteration;

    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
        $this->init();
    }

    public function push(Result $result): self
    {
        $this->results[] = $result;
        return $this;
    }

    /**
     * @return array|Result[]
     */
    public function get(): array
    {
        return $this->results;
    }

    public function getFilePath(): string
    {
        return $this->filepath;
    }

    public function init(): self
    {
        $this->results = [];
        $this->iteration = 0;
        return $this;
    }

    public function current(): Result
    {
        return $this->results[$this->iteration];
    }

    public function next(): void
    {
        $this->iteration++;
    }

    public function key(): int
    {
        return $this->iteration;
    }

    public function valid(): bool
    {
        return $this->iteration < count($this->results);
    }

    public function rewind()
    {
        $this->iteration = 0;
    }
}