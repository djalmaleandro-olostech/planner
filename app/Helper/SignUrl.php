<?php

namespace App\Helper;

use Aws\CloudFront\CloudFrontClient;
use Aws\Exception\AwsException;
use Hyperf\Config\ConfigFactory;
use Hyperf\Context\ApplicationContext;

class SignUrl
{
    protected CloudFrontClient $cloudFrontClient;
    private \Hyperf\Config\Config $config;

    public function __construct()
    {
        $this->cloudFrontClient = new CloudFrontClient([
            'profile' => 'default',
            'version' => '2014-11-06',
            'region' => 'us-east-1'
        ]);

        $config = new ConfigFactory;
        $this->config = $config(ApplicationContext::getContainer());
    }

    public function generateUrl(string $img, string $mediaType, ?int $expires = null): ?string
    {
        if (empty($img)) {
            return null;
        }

        $url = $this->generateCloudFrontUrl($img, $mediaType);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        try {
            return $this->cloudFrontClient->getSignedUrl([
                'url'         => $url,
                'expires'     => (null === $expires) ? time() + 86400: $expires,
                'private_key' => $this->config->get("cloudfront.default.cf_private_key"),
                'key_pair_id' => $this->config->get("cloudfront.default.cf_access_key_id"),
            ]);
        } catch (AwsException $e) {
            return $url;
        }
    }

    public function generateUnsignedUrl(string $img, $mediaType): ?string
    {
        if (empty($img)) {
            return null;
        }

        return $this->generateCloudFrontUrl($img, $mediaType, false );
    }

    private function generateCloudFrontUrl(string $img, string $mediaType, $signed = true): string
    {

        if ($signed) {
            $host = $this->config->get("cloudfront.default.cloudfront_domain_signed");
        } else {
            $host = match ($mediaType) {
                'cover', 'preview' => $this->config->get("cloudfront.default.cloudfront_domain_cover"),
                'hls' => $this->config->get("cloudfront.default.cloudfront_domain_hls"),
                'audio', 'high_quality', 'downloadable', 'raw', 'low_quality' => $this->config->get("cloudfront.default.cloudfront_domain_video"),
                default => $this->config->get("cloudfront.default.cloudfront_domain")
            };
        }

        $host = rtrim(sprintf("https://%s", $host), '/');

        return sprintf("%s/%s", $host, ltrim($img, '/'));

    }

    public function generateUrlChooser(string $img, string $mediaType, ?int $expires = null, ?string $uuid = null): ?string{

        if (null == $uuid){
            return $this->generateUrl($img, $mediaType, $expires);
        } else {
            return $this->generateUnsignedUrl($img, $mediaType);
        }

    }
}