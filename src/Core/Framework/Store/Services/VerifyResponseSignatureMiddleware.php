<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Store\Services;

use Psr\Http\Message\ResponseInterface;
use Shopware\Core\Framework\Store\Exception\StoreSignatureValidationException;

/**
 * @package merchant-services
 *
 * @internal
 */
class VerifyResponseSignatureMiddleware implements MiddlewareInterface
{
    private const SHOPWARE_SIGNATURE_HEADER = 'X-Shopware-Signature';

    private OpenSSLVerifier $openSslVerifier;

    public function __construct(OpenSSLVerifier $openSslVerifier)
    {
        $this->openSslVerifier = $openSslVerifier;
    }

    public function __invoke(ResponseInterface $response): ResponseInterface
    {
        $signatureHeaderName = self::SHOPWARE_SIGNATURE_HEADER;
        $header = $response->getHeader($signatureHeaderName);
        if (!isset($header[0])) {
            throw new StoreSignatureValidationException(sprintf('Signature not found in header "%s"', $signatureHeaderName));
        }

        $signature = $header[0];

        if (empty($signature)) {
            throw new StoreSignatureValidationException(sprintf('Signature not found in header "%s"', $signatureHeaderName));
        }

        if (!$this->openSslVerifier->isSystemSupported()) {
            return $response;
        }

        if ($this->openSslVerifier->isValid($response->getBody()->getContents(), $signature)) {
            $response->getBody()->rewind();

            return $response;
        }

        throw new StoreSignatureValidationException('Signature not valid');
    }
}
