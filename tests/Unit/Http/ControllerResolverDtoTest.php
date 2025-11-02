<?php

declare(strict_types=1);

namespace Hyperdrive\Tests\Unit\Http;

use Hyperdrive\Http\ControllerResolver;
use Hyperdrive\Contracts\Container\ContainerInterface;
use Hyperdrive\Http\Request;
use Hyperdrive\Http\Response;
use Hyperdrive\Routing\Route;
use Hyperdrive\Dto\DataTransferObject;
use Hyperdrive\Http\Controller;
use PHPUnit\Framework\TestCase;

class CreateProductDto extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $description,
        public float $price,
        public int $stock = 0,
        public array $tags = []
    ) {}

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'int',
            'tags' => 'array'
        ];
    }
}

class DtoTestController extends Controller
{
    public function create(CreateProductDto $dto): Response
    {
        return $this->json([
            'product' => $dto->toArray(),
            'valid' => $dto->validate(),
            'errors' => $dto->getErrors()
        ]);
    }

    public function createWithRequest(CreateProductDto $dto, Request $request): Response
    {
        return $this->json([
            'product' => $dto->toArray(),
            'path' => $request->getPath()
        ]);
    }
}

class ControllerResolverDtoTest extends TestCase
{
    private ControllerResolver $resolver;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->resolver = new ControllerResolver();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function test_can_inject_dto_into_controller(): void
    {
        $route = new Route(
            'POST',
            '/products',
            DtoTestController::class,
            'create'
        );

        $requestData = [
            'name' => 'Laptop',
            'description' => 'Gaming laptop',
            'price' => 999.99,
            'stock' => 10,
            'tags' => ['electronics', 'gaming']
        ];

        $request = new Request('POST', '/products');
        $request->setData($requestData);

        $response = $this->resolver->resolve($route, $request, $this->container);

        $this->assertInstanceOf(Response::class, $response);
        $data = $response->getData();

        $this->assertEquals($requestData, $data['product']);
        $this->assertTrue($data['valid']);
        $this->assertEmpty($data['errors']);
    }
}
