<?php


namespace Rozeo\Checker;


use InvalidArgumentException;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class Finder
{
    /**
     * @var Parser
     */
    private $ast;

    private $filepath;

    private $findTargets;

    /**
     * @var ResultSet
     */
    private $result;

    public function __construct(string $filepath)
    {
        if (!file_exists($filepath)) {
            throw new InvalidArgumentException("file not found.");
        }

        $this->findTargets = [];
        $this->filepath = $filepath;
        $this->result = new ResultSet($filepath);

        $this->ast = (new ParserFactory)->create(ParserFactory::PREFER_PHP7)
            ->parse(file_get_contents($filepath));
    }

    public function pushTarget(string $name): self
    {
        $this->findTargets[] = $name;
        return $this;
    }

    public function setTargets(array $targets): self
    {
        $this->findTargets = $targets;
        return $this;
    }

    public function execute(): ResultSet
    {
        $this->result->init();

        foreach ($this->ast as $stmt) {
            $this->_recursion($stmt);
        }

        return $this->result;
    }

    protected function _recursion($stmts, string $namespace = '')
    {
        switch (true) {
            case $stmts instanceof Stmt\Namespace_:
                foreach ($stmts->stmts as $s) {
                    $this->_recursion($s, $stmts->name->parts[0]);
                }
                break;

            case $stmts instanceof Stmt\Expression:
                $this->processExpression($namespace, $stmts);
                break;

            default:
                foreach ($stmts->stmts as $s) {
                    $this->_recursion($s, $namespace);
                }
                break;
        }
    }

    protected function processExpression(string $namespace, Stmt\Expression $expression): void
    {
        $functionName = $this->makeFunctionName($expression->expr, $namespace);

        if (($index = array_search($functionName, $this->findTargets)) !== false) {
            $this->result->push(new Result(
                $this->findTargets[$index],
                $expression->getLine()
            ));
        }
    }

    protected function makeFunctionName(Expr $expr, $namespace): string
    {
        if ($expr instanceof Expr\FuncCall) {
            if (($name = $expr->name) instanceof FullyQualified) {
                return $funcName = join("\\", $name->parts);
            }

            return $funcName = join("\\", array_merge(
                $namespace !== '' ? [$namespace]: [], $name->parts
            ));
        }

        if ($expr instanceof Expr\StaticCall) {
            if (($name = $expr->name) instanceof FullyQualified) {
                return  join("\\", $expr->class->parts) . '::' . $expr->name->name;
            }

            return join("\\", array_merge(
                        $namespace !== '' ? [$namespace]: [], $expr->class->parts
                    )) . '::' . $expr->name->name;
        }

        throw new InvalidArgumentException("Not recognized type");
    }
}