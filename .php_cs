<?php

$finder = PhpCsFixer\Finder::create()
    ->notPath('/assets')
    ->notPath('/runtime')
    ->filter(function (SplFileInfo $fileInfo) {
        return $fileInfo->getFilename() !== 'requirements.php';
    })
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setRules([
        '@PSR1' => true,
        '@PSR2' => true,
        '@Symfony' => true,
        'phpdoc_to_comment' => false,
        'phpdoc_order' => true,

        'phpdoc_var_without_name' => false,

        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'phpdoc_no_alias_tag' => [],
    ])
    ->setFinder($finder);
