<?php

namespace DataMat\GrinningCat;

use DataMat\GrinningCat\Clients\HttpClient;
use DataMat\GrinningCat\Clients\WSClient;
use DataMat\GrinningCat\Endpoints\AbstractEndpoint;
use Symfony\Component\PropertyInfo\Extractor\ConstructorExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


/**
 * @method \DataMat\GrinningCat\Endpoints\AdminsEndpoint admins()
 * @method \DataMat\GrinningCat\Endpoints\AgenticWorkflowEndpoint agenticWorkflow()
 * @method \DataMat\GrinningCat\Endpoints\AuthEndpoint auth()
 * @method \DataMat\GrinningCat\Endpoints\AuthHandlerEndpoint authHandler()
 * @method \DataMat\GrinningCat\Endpoints\ChunkerEndpoint chunker()
 * @method \DataMat\GrinningCat\Endpoints\ContextRetrieverEndpoint contextRetriever()
 * @method \DataMat\GrinningCat\Endpoints\ConversationEndpoint conversation()
 * @method \DataMat\GrinningCat\Endpoints\CustomEndpoint custom()
 * @method \DataMat\GrinningCat\Endpoints\EmbedderEndpoint embedder()
 * @method \DataMat\GrinningCat\Endpoints\FileManagerEndpoint fileManager()
 * @method \DataMat\GrinningCat\Endpoints\IngestionEndpoint ingestion()
 * @method \DataMat\GrinningCat\Endpoints\LargeLanguageModelEndpoint largeLanguageModel()
 * @method \DataMat\GrinningCat\Endpoints\MemoryEndpoint memory()
 * @method \DataMat\GrinningCat\Endpoints\MessageEndpoint message()
 * @method \DataMat\GrinningCat\Endpoints\PluginsEndpoint plugins()
 * @method \DataMat\GrinningCat\Endpoints\RabbitHoleEndpoint rabbitHole()
 * @method \DataMat\GrinningCat\Endpoints\UsersEndpoint users()
 * @method \DataMat\GrinningCat\Endpoints\UtilsEndpoint utils()
 * @method \DataMat\GrinningCat\Endpoints\VectorDatabaseEndpoint vectorDatabase()
 * @method \DataMat\GrinningCat\Endpoints\HealthCheckEndpoint healthCheck()
 */
class GrinningCatClient
{
    private WSClient $wsClient;
    private HttpClient $httpClient;
    private Serializer $serializer;

    public function __construct(WSClient $wsClient, HttpClient $httpClient, ?string $token = null)
    {
        $this->wsClient = $wsClient;
        $this->httpClient = $httpClient;

        if ($token) {
            $this->addToken($token);
        }

        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();
        $typeExtractor = new PropertyInfoExtractor(typeExtractors: [
            new ConstructorExtractor([$phpDocExtractor, $reflectionExtractor]),
            $phpDocExtractor,
            $reflectionExtractor
        ]);

        $objectNormalizer = new ObjectNormalizer(
            null,
            new CamelCaseToSnakeCaseNameConverter(),
            null,
            propertyTypeExtractor: $typeExtractor,
        );

        $encoder = new JsonEncoder();

        $this->serializer = new Serializer([$objectNormalizer, new ArrayDenormalizer()], [$encoder]);
    }

    public function addToken(string $token): self
    {
        $this->wsClient->setToken($token);
        $this->httpClient->setToken($token);
        return $this;
    }

    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    public function getWsClient(): WSClient
    {
        return $this->wsClient;
    }

    public function getSerializer(): Serializer
    {
        return $this->serializer;
    }

    public function __call(string $method, $args): AbstractEndpoint
    {
        return GrinningCatFactory::build(
            __NAMESPACE__ . GrinningCatUtility::classize($method),
            $this
        );
    }
}
