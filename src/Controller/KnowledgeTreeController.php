<?php

declare(strict_types=1);

namespace App\Controller;

use App\KnowledgeTree\KnowledgeTreeCreator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class KnowledgeTreeController extends AbstractReactController
{
    private KnowledgeTreeCreator $knowledgeTreeCreator;

    public function __construct(KnowledgeTreeCreator $knowledgeTreeCreator)
    {
        $this->knowledgeTreeCreator = $knowledgeTreeCreator;
    }

    /**
     * @Route("/дерево-знаний", name="дерево-знаний")
     */
    public function index(): Response
    {
        $name = 'Дерево знаний';
        [, $tree] = $this->knowledgeTreeCreator->create();
        return $this->renderPageWithReact(
            $name,
            $name,
            $tree,
            'knowledge-tree',
            true,
        );
    }
}
