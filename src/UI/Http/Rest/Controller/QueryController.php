<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller;

use App\UI\Http\Rest\Response\Collection;
use App\UI\Http\Rest\Response\JsonApiFormatter;
use Broadway\ReadModel\SerializableReadModel;
use League\Tactician\CommandBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class QueryController
{
    protected function jsonCollection(Collection $collection, bool $isImmutable = false): JsonResponse
    {
        $response = JsonResponse::create($this->formatter->collection($collection));

        if ($isImmutable && $collection->limit() === count($collection->data())) {
            $aYear = 60 * 60 * 24 * 365;
            $response
                ->setMaxAge($aYear)
                ->setSharedMaxAge($aYear);
        }

        return $response;
    }

    protected function json(SerializableReadModel $serializableReadModel): JsonResponse
    {
        return JsonResponse::create($this->formatter->one($serializableReadModel));
    }

    protected function route(string $name, array $params = []): string
    {
        return $this->router->generate($name, $params);
    }

    protected function ask($query)
    {
        return $this->queryBus->handle($query);
    }

    public function __construct(CommandBus $queryBus, JsonApiFormatter $formatter, UrlGeneratorInterface $router
    )
    {
        $this->queryBus = $queryBus;
        $this->formatter = $formatter;
        $this->router = $router;
    }

    /**
     * @var JsonApiFormatter
     */
    private $formatter;

    /**
     * @var CommandBus
     */
    private $queryBus;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;
}