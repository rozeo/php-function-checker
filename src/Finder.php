<?php


namespace Rozeo\Checker;


use InvalidArgumentException;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;
use PhpParser\ParserFactory;

class Finder
{
    /**
     * @var \PhpParser\Parser
     */
    private $ast;

    private $filepath;

    private $namespaceStack;

    public function __construct(string $filepath)
    {
        if (!file_exists($filepath)) {
            throw new InvalidArgumentException("file not found.");
        }

        $this->namespaceStack = [];

        $this->filepath = $filepath;

        $this->ast = (new ParserFactory)->create(ParserFactory::PREFER_PHP7)
            ->parse(file_get_contents($filepath));
    }

    public function findFunctions(array $names) {
        foreach ($this->ast as $_) {
            $this->_recursion($_, $names);
        }
    }

    protected function _recursion($stmts, array $names, string $namespace = '')
    {
        switch (true) {
            case $stmts instanceof Stmt\Namespace_:
                foreach ($stmts->stmts as $s) {
                    $this->_recursion($s, $names, $stmts->name->parts[0]);
                }
                break;

            case $stmts instanceof Stmt\Expression:
                if ($stmts->expr instanceof Expr\FuncCall) {
                    if (($name = $stmts->expr->name) instanceof FullyQualified) {
                        $funcName = join("\\", $name->parts);
                    } else {
                        $funcName = join("\\", array_merge(
                            $namespace !== '' ? [$namespace]: [], $name->parts
                        ));
                    }

                    if (($index = array_search($funcName, $names)) !== false) {
                        echo "find function [$names[$index]] in " . $this->filepath . ":" . $stmts->getLine() . "\n";
                    }
                }

                if ($stmts->expr instanceof Expr\StaticCall) {
                    if (($name = $stmts->expr->name) instanceof FullyQualified) {
                        $funcName = join("\\", $stmts->expr->class->parts) . '::' . $stmts->expr->name->name;
                    } else {
                        $funcName = join("\\", array_merge(
                            $namespace !== '' ? [$namespace]: [], $stmts->expr->class->parts
                            )) . '::' . $stmts->expr->name->name;
                    }

                    if (($index = array_search($funcName, $names)) !== false) {
                        echo "find function [$names[$index]] in " . $this->filepath . ":" . $stmts->getLine() . "\n";
                    }
                }
                break;


            default:
                foreach ($stmts->stmts as $s) {
                    $this->_recursion($s, $names, $namespace);
                }
                break;
        }
    }
}