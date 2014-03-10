<?php

namespace QafooLabs\DemoShop\Bepado;

use Bepado\SDK\Rpc;
use Bepado\SDK\Struct\RpcCall;

class Faker
{
    private $url;
    private $apiKey;

    public function __construct($url, $apiKey)
    {
        $this->url = $url;
        $this->apiKey = $apiKey;
    }

    public function fakeProduct()
    {
        $marshaller = new Rpc\Marshaller\CallMarshaller\XmlCallMarshaller(
            new \Bepado\SDK\XmlHelper(),
            new Rpc\Marshaller\Converter\ExceptionToErrorConverter()
        );
        $xml = $marshaller->marshal(
            new RpcCall(
                array(
                    'service' => 'products',
                    'command' => 'toShop',
                    'arguments' => array(
                        array(
                            new \Bepado\SDK\Struct\Change\ToShop\InsertOrUpdate(array(
                                'product' => new \Bepado\SDK\Struct\Product(array(
                                    'shopId' => 'test',
                                    'sourceId' => 'test',
                                    'title' => 'Red herring',
                                    'shortDescription' => 'The idiom "red herring" is used to refer to something that misleads or distracts from the relevant or important issue.',
                                    'longDescription' => '',
                                    'price' => 14.80,
                                    'purchasePrice' => 7.40,
                                    'currency' => 'EUR',
                                    'vat' => 0.19,
                                    'categories' => array('/media'),
                                    'attributes' => array(),
                                    'vendor' => 'William Cobbett',
                                    'ean' => '',
                                    'availability' => 100,
                                    'images' => array(),
                                    'deliveryWorkDays' => 14,
                                    'revisionId' => (string)microtime(true)
                                )),
                                'revision' => (string)microtime(true)
                            )),
                        )
                    )
                )
            )
        );

        $requestDate     = gmdate('D, d M Y H:i:s', time()) . ' GMT';
        $nonce = $this->generateNonce($requestDate, $xml, $this->apiKey);

        $httpClient = new \Bepado\SDK\HttpClient\Stream($this->url);

        $authHeaderContent = 'SharedKey party="bepado",nonce="' . $nonce . '"';

        $headers = array(
            'Authentication: ' .  $authHeaderContent,
            'X-Bepado-Authorization: ' . $authHeaderContent,
            'Date: ' . $requestDate
        );

        $httpResponse = $httpClient->request(
            'POST',
            '',
            $xml,
            $headers
        );

        return $httpResponse->body;
    }

    private function generateNonce($requestDate, $body, $key)
    {
        return hash_hmac('sha512', $requestDate . "\n" . $body, $key);
    }
}
