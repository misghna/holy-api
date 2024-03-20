<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Repositories\GridRepository;
use Valitron\Validator;

class Grids
{
    public function __construct(private GridRepository $repository,
                                private Validator $validator)
    {
        $this->validator->mapFieldsRules([
            'name' => ['required'],
            'size' => ['required', 'integer', ['min', 1]]
        ]);
    }

    public function show(Request $request, Response $response, string $id): Response
    {
        $grid = $request->getAttribute('grid');

        $body = json_encode($grid);
    
        $response->getBody()->write($body);
    
        return $response;        
    }

    public function create(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        $this->validator = $this->validator->withData($body);

        if ( ! $this->validator->validate()) {

            $response->getBody()
                     ->write(json_encode($this->validator->errors()));

            return $response->withStatus(422);

        }

        $id = $this->repository->create($body);

        $body = json_encode([
            'message' => 'Grid created',
            'id' => $id
        ]);

        $response->getBody()->write($body);

        return $response->withStatus(201);
    }

    public function update(Request $request, Response $response, string $id): Response
    {
        $body = $request->getParsedBody();

        $this->validator = $this->validator->withData($body);

        if ( ! $this->validator->validate()) {

            $response->getBody()
                     ->write(json_encode($this->validator->errors()));

            return $response->withStatus(422);

        }

        $rows = $this->repository->update((int) $id, $body);

        $body = json_encode([
            'message' => 'Grid updated',
            'rows' => $rows
        ]);

        $response->getBody()->write($body);

        return $response;
    }

    public function delete(Request $request, Response $response, string $id): Response
    {
        $rows = $this->repository->delete($id);

        $body = json_encode([
            'message' => 'Grid deleted',
            'rows' => $rows
        ]);

        $response->getBody()->write($body);

        return $response;
    }
}