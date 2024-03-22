<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Uploadcare\Configuration;
use Uploadcare\Api;

/**
 * Class UCFileModel
 */
final class UCFileModel {

    /**
     * Link to CDN server
     *
     * @var string
     */
    private string $uploadcare_URL = 'https://ucarecdn.com/';

    /**
     * @var int
     */
    private int $attachment_id = 0;

    /**
     * Uploadcare Image ID
     *
     * @var string
     */
    private string $image_id = '';

    /**
     * Available API keys (actual keys + history)
     *
     * @var array
     */
    private array $available_api_keys = array();

    /**
     * Uploadcare URL to original file
     *
     * @var string
     */
    private string $file_url = '';

    /**
     * File name
     *
     * @var string
     */
    private string $file_name = '';

    /**
     * File content from original URL
     *
     * @var string
     */
    private string $file_content;

    /**
     * @var Client|null
     */
    private ?Client $guzzle_http_client = null;

    /**
     * URL for Uploadcare API
     *
     * @var string
     */
    private string $upload_API_URL = 'https://upload.uploadcare.com';

    /**
     * @param string $image_id - Uploadcare image ID.
     *
     * @paran int $attachment_id - WP attachment ID.
     * @throws Exception
     */
    public function __construct( string $image_id, int $attachment_id ) {
        if ( ! trim( $image_id ) || $attachment_id <= 0 ) {
            throw new Exception( 'Invalid arguments passed to the constructor' );
        }
        $this->image_id      = $image_id;
        $this->attachment_id = $attachment_id;
        $this->set_available_api_keys();
        $this->set_file_data();
    }

    /**
     * Returns file name
     *
     * @return string
     */
    public function get_file_name(): string {
        return $this->file_name;
    }

    /**
     * Returns file content
     *
     * @return string
     */
    public function get_file_content(): string {
        return $this->file_content;
    }

    /**
     * Check if file exists and valid
     *
     * @return bool
     */
    public function is_file_valid(): bool {
        if ( $this->file_name && $this->file_content ) {
            return true;
        }

        return false;
    }

    /**
     * Get lazy loaded Guzzle HTTP client instance
     *
     * @return Client
     */
    private function get_guzzle_http_client(): Client {
        if ( is_null( $this->guzzle_http_client ) ) {
            $this->guzzle_http_client = new GuzzleHttp\Client();
        }

        return $this->guzzle_http_client;
    }

    /**
     * Set api keys from DB
     *
     * @return void
     */
    private function set_available_api_keys(): void {
        try {
            $uploadcare_public_key = sanitize_text_field( get_option( 'uploadcare_public' ) );
            $uploadcare_secret_key = sanitize_text_field( get_option( 'uploadcare_secret' ) );

            if ( $uploadcare_public_key && $uploadcare_secret_key ) {
                $this->available_api_keys[] = array(
                    'public_key' => $uploadcare_public_key,
                    'secret_key' => $uploadcare_secret_key
                );
            }
        } catch ( Throwable $tw ) {
            ULog( $tw->getMessage() );
        }

        try {
            global $wpdb;
            $query = 'SELECT * FROM ' . $wpdb->prefix . 'options WHERE option_name LIKE "uploadcare_public_%"';

            $saved_keys = $wpdb->get_results( $query, ARRAY_A );
            foreach ( $saved_keys as $option_data ) {
                try {
                    $key_data = unserialize( $option_data['option_value'] );
                    if ( ! empty( $key_data['public_key'] ) && ! empty( $key_data['secret_key'] ) ) {
                        $this->available_api_keys[] = array(
                            'public_key' => sanitize_text_field( $key_data['public_key'] ),
                            'secret_key' => sanitize_text_field( $key_data['secret_key'] )
                        );
                    }
                } catch ( Throwable $tw ) {
                    ULog( $tw->getMessage() );
                }
            }
        } catch ( Throwable $tw ) {
            ULog( $tw->getMessage() );
        }
    }

    /**
     * Set file information from Uploadcare
     *
     * @return void
     */
    private function set_file_data() {
        try {
            foreach ( $this->available_api_keys as $keys ) {
                $configuration = Configuration::create(
                    $keys['public_key'],
                    $keys['secret_key'],
                    [ 'framework' => UploadcareUserAgent() ],
                );
                $api           = new Api( $configuration );
                try {
                    $uc_file  = $api->file()->fileInfo( $this->image_id );
                    $file_URL = $uc_file->getOriginalFileUrl();
                    if ( $file_URL ) {
                        $this->file_url  = $file_URL;
                        $this->file_name = $uc_file->getOriginalFilename();
                        break;
                    }
                } catch ( Exception $e ) {
                    continue;
                }
            }
            if ( ! $this->file_url ) {
                $this->file_url = $this->uploadcare_URL . $this->image_id . '/';
            }
            $this->set_file_content();
        } catch ( Throwable $tw ) {
            ULog( $tw->getMessage() );
        }
    }

    /**
     * Returns file info by "info" API method. @see https://uploadcare.com/api-refs/upload-api/#tag/Upload/operation/fromURLUploadStatus
     *
     * @param string $public_key - Uploadcare API public key.
     * @param string $image_id - Uploadcare file ID.
     *
     * @return bool
     */
    private function set_file_name_from_info_api_endpoint( string $public_key, string $image_id ): bool {
        try {
            $res = $this->get_guzzle_http_client()->request(
                'GET',
                $this->upload_API_URL . '/info/',
                array(
                    'query' => array(
                        'pub_key' => $public_key,
                        'file_id' => $image_id
                    ),
                ),
            );

            if ( $res->getStatusCode() !== 200 ) {
                throw new Exception( 'Get UC file with Guzzle. Incorrect status code ' . $res->getStatusCode() );
            }

            $response_body = json_decode( $res->getBody()->getContents(), true );
            if ( is_array( $response_body ) && array_key_exists( 'original_filename', $response_body ) && $response_body['original_filename'] ) {
                $this->file_name = sanitize_file_name( $response_body['original_filename'] );

                return true;
            }
        } catch ( GuzzleException $tw ) {
            // Debug point
            return false;
        } catch ( Throwable $tw ) {
            ULog( $tw->getMessage() );

            return false;
        }

        return false;
    }

    /**
     * Set file content
     *
     * @return void
     */
    private function set_file_content() {
        try {
            $res = $this->get_guzzle_http_client()->request(
                'GET',
                $this->file_url,
            );
            if ( $res->getStatusCode() !== 200 ) {
                throw new Exception( 'Get UC file with Guzzle. Incorrect status code ' . $res->getStatusCode() );
            }

            $this->set_file_name( $res );
            $this->file_content = $res->getBody();
        } catch ( GuzzleException $tw ) {
            // Debug point
        } catch ( Throwable $tw ) {
            ULog( $tw->getMessage() );
        }

    }

    /**
     * Set file name
     *
     * @param Response $response
     *
     * @return void
     * @throws Exception
     */
    private function set_file_name( ResponseInterface $response ): void {
        if ( $this->file_name ) {
            return;
        }

        // Try to set file name from Uploadcare meta
        if ( $response->hasHeader( 'Content-Disposition' ) ) {
            $content_description = $response->getHeader( 'Content-Disposition' );
            if ( $content_description ) {
                $description = array_shift( $content_description );
                if ( $description ) {
                    $name_position_start = strrpos( $description, 'filename=' );
                    if ( ! is_bool( $name_position_start ) ) {
                        $name_start = $name_position_start + strlen( 'filename=' );
                        $file_name  = substr( $description, $name_start );
                        $name_end   = strpos( $file_name, ';' );
                        if ( is_bool( $name_end ) ) {
                            $this->file_name = trim( urldecode( $file_name ) );
                        } else {
                            $this->file_name = trim( urldecode( substr( $description, $name_end ) ) );
                        }
                    }
                }
            }
        }

        // Try to set file name from WP attachment metadata
        if ( ! $this->file_name ) {
            $meta_file_URL = trim( get_post_meta( $this->attachment_id, '_wp_attached_file', true ) );
            if ( $meta_file_URL ) {
                $file_name = basename( $meta_file_URL );
                if ( $file_name && ! is_bool( strpos( $file_name, '.' ) ) ) {
                    $this->file_name = $file_name;
                }
            }
        }

        // Try to set filename based on '/info' Uploadcare API endpoint
        // @see https://uploadcare.com/api-refs/upload-api/#tag/Upload/operation/fileUploadInfo
        if ( ! $this->file_name ) {
            foreach ( $this->available_api_keys as $keys ) {
                try {
                    if ( $this->set_file_name_from_info_api_endpoint( $keys['public_key'], $this->image_id ) ) {
                        break;
                    }
                } catch ( Exception $e ) {
                    continue;
                }
            }
        }

        // Try to set file name based on MIME type
        if ( ! $this->file_name && $response->hasHeader( 'Content-Type' ) ) {
            $extension    = '';
            $content_type = $response->getHeader( 'Content-Type' );
            if ( $content_type ) {
                $mime_type = array_shift( $content_type );
                $extension = MimeExtensionHelper::get_extension_based_on_mime_type( $mime_type );
                if ( ! $extension ) {
                    throw new Exception( 'File type not recognized' );
                }
            }
            $this->file_name = $this->image_id . '.' . $extension;
        }

        if ( ! $this->file_name ) {
            throw new Exception( 'Can\'t set file name' );
        }
    }
}
