<?php

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
     * @param string $image_id - Uploadcare image ID
     *
     * @paran int $attachment_id - WP attachment ID
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
            // TODO: Add log
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
                    // TODO: Add log
                }
            }
        } catch ( Throwable $tw ) {
            // TODO: Add log
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
                    if ( ! $file_URL ) {
                        continue;
                    }
                    $this->file_url  = $file_URL;
                    $this->file_name = $uc_file->getOriginalFilename();
                    break;
                } catch ( \Exception $e ) {
                    continue;
                }
            }
            if ( ! $this->file_url ) {
                $this->file_url = $this->uploadcare_URL . $this->image_id . '/';
            }
            $this->set_file_content();
        } catch ( Throwable $tw ) {
            // TODO: Add error log
        }
    }

    /**
     * Set file content
     *
     * @return void
     */
    private function set_file_content() {
        try {
            $client = new GuzzleHttp\Client();
            $res    = $client->request(
                'GET',
                $this->file_url,
            );
            if ( $res->getStatusCode() !== 200 ) {
                throw new Exception( 'Get UC file with Guzzle. Incorrect status code ' . $res->getStatusCode() );
            }

            $this->set_file_name( $res );
            $this->file_content = $res->getBody();
        } catch ( GuzzleException $tw ) {
            // TODO: Add Guzzle log
        } catch ( Throwable $tw ) {
            // TODO: Add common error log
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
