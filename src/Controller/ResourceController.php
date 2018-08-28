<?php
/**
 * Voucher Pool (https://voucherpool.erdemkose.com)
 *
 * @link      https://github.com/erdemkose/voucherpool
 * @copyright Copyright (c) 2018 Erdem Köse
 * @license   https://github.com/erdemkose/voucherpool/blob/master/LICENSE (MIT License)
 */

namespace VoucherPool\Controller;

use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Container\ContainerInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * ResourceController
 *
 * @package VoucherPool\Controller
 */
abstract class ResourceController
{
    /**
     * Each extending class should set this field to the related resource class
     * e.g: \VoucherPool\Model\Recipient
     * @var string
     */
    protected $className;

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * ResourceController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $this->container->get('logger');
    }

    /**
     * This function is called by Slim Framework for the defined routes
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     *
     * @throws \RuntimeException if the method is not supported
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        switch($request->getMethod()) {
            case 'GET':
                return $this->get($request, $response, $args);
            case 'POST':
                return $this->create($request, $response, $args);
            case 'DELETE':
                return $this->delete($request, $response, $args);
        }

        throw new \UnexpectedValueException('Unsupported method: '. $request->getMethod(), 405);
    }

    /**
     * Queries for the resources and returns as array
     *
     * @param Builder $query
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return array
     *
     * @throws ModelNotFoundException if the resource could not be found
     */
    protected function getResourcesArray(Builder $query, Request $request, Response $response, array $args): array
    {
        if(isset($args['id'])) {
            $id = (int) $args['id'];
            $resource = ($this->className)::find($id);

            if($resource == null) {
                throw new ModelNotFoundException('Resource could not be found!', 404);
            }

            return $resource;
        } else {
            $draw = $request->getQueryParam('draw', 1);
            $start = $request->getQueryParam('start', 0);
            $length = $request->getQueryParam('length', 20);
            $search = $request->getQueryParam('search', ['value' => '']);

            $recordsTotal = $query->count();

            $searchQuery = $query->search($search['value']);
            $recordsFiltered = $searchQuery->count();
            $resources = $searchQuery->offset($start)->limit($length)->get();

            return [
                'draw' => $draw,
                'start' => $start,
                'length' => $length,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $resources,
            ];
        }
    }

    /**
     * Default implementation for REST GET method
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function get(Request $request, Response $response, array $args): Response
    {
        $query = ($this->className)::where('1', '1');
        $resources = $this->getResourcesArray($query, $request, $response, $args);
        return $response->withJson($resources, 200);
    }

    /**
     * Default implementation for REST POST method
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     *
     * @throws \BadMethodCallException if a resource id is given
     * @throws \InvalidArgumentException if the given attributes are invalid
     * @throws \RuntimeException if an error occurs while creating the new resource
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        if(isset($args['id'])) {
            throw new \BadMethodCallException('POST method MUST NOT target a specific resource', 400);
        }

        try
        {
            $attributes = $request->getParsedBody();

            // The values in the $attributes are validated in the setters of the related Model
            $resource = ($this->className)::create($attributes);

            $this->logger->info("Created new {$this->className}", [
                $request->getMethod(),
                $request->getUri()->getPath(),
                $request->getParams(),
            ]);

            return $response->withJson($resource, 201);
        }
        catch(\InvalidArgumentException|\LengthException|\OutOfRangeException $exception)
        {
            throw new \InvalidArgumentException($exception->getMessage(), 400);
        }
        catch(\Exception $exception)
        {
            $parts = explode('\\', $this->className);
            $resourceClass = end($parts);
            throw new \RuntimeException("$resourceClass already exists!", 400);
        }
    }

    /**
     * Default implementation for REST DELETE method
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     *
     * @throws \BadMethodCallException if a resource id is not given
     * @throws \InvalidArgumentException if the given id is invalid
     * @throws ModelNotFoundException if the resource could not be found
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        if(isset($args['id']) === false) {
            throw new \BadMethodCallException('DELETE method MUST target a specific resource', 400);
        }

        $id = (int) $args['id'];

        if($id > 0) {
            $return = ($this->className)::destroy($id);
            if($return > 0) {
                $this->logger->info("Deleted model: {$this->className} ($id)");

                // Return with an empty body
                return $response->withStatus(204, 'DELETED');
            } else {
                throw new ModelNotFoundException('Resource could not be found!', 404);
            }
        } else {
            throw new \InvalidArgumentException("Invalid resource id!", 400);
        }
    }
}