<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractReactController extends AbstractController
{
    protected function renderPageWithReact(string $title, string $heading, array $data, string $bundleId): Response
    {
        return $this->render('react.html.twig', [
            'title' => $title,
            'heading' => $heading,
            'data' => json_encode($data,  JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'bundleId' => $bundleId,
        ]);
    }
}
