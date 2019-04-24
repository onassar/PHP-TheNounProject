<?php

    // dependecy checks
    if (in_array('OAuth', get_loaded_extensions()) === false) {
        $msg = 'OAuth extension needs to be installed.';
        throw new Exception($msg);
    }

    /**
     * TheNounProject
     * 
     * PHP OAuth wrapper for The Noun Project, using PECL OAuth library
     * 
     * @note    icon_url urls expire 24 hours after received
     * @link    https://api.thenounproject.com/
     * @link    https://github.com/onassar/PHP-TheNounProject
     * @link    https://pecl.php.net/package/oauth
     * @link    http://php.net/manual/en/book.oauth.php
     * @author  Oliver Nassar <onassar@gmail.com>
     */
    class TheNounProject
    {
        /**
         * _base
         * 
         * @access  protected
         * @var     string (default: 'https://api.thenounproject.com')
         */
        protected $_base = 'https://api.thenounproject.com';

        /**
         * _connection
         * 
         * @access  protected
         * @var     null|OAuth (default: null)
         */
        protected $_connection = null;

        /**
         * _debug
         * 
         * @access  protected
         * @var     bool (default: false)
         */
        protected $_debug = false;

        /**
         * _key
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_key = null;

        /**
         * _maxPerPage
         * 
         * @access  protected
         * @var     int (default: 100)
         */
        protected $_maxPerPage = 100;

        /**
         * _secret
         * 
         * @access  protected
         * @var     null|string (default: null)
         */
        protected $_secret = null;

        /**
         * __construct
         * 
         * @access  public
         * @param   string $key
         * @param   string $secret
         * @param   bool $debug (default: false)
         * @return  void
         */
        public function __construct(string $key, string $secret, bool $debug = false)
        {
            $this->_key = $key;
            $this->_secret = $secret;
            $this->_debug = $debug;
        }

        /**
         * _get
         * 
         * @access  protected
         * @param   string $path
         * @param   array $params (default: array())
         * @return  null|array
         */
        protected function _get(string $path, array $params = array()): ?array
        {
            $params = $this->_getCleanedParams($params);
            $this->_setupConnection();
            $url = ($this->_base) . ($path);
            $method = OAUTH_HTTP_METHOD_GET;
            $headers = array();
            $response = $this->_requestURL($url, $params, $method, $headers);
            $response = json_decode($response, true);
            return $response;
        }

        /**
         * _getCleanedParams
         * 
         * @access  protected
         * @param   array $params
         * @return  array
         */
        protected function _getCleanedParams(array $params): array
        {
            $key = 'limit_to_public_domain';
            if (isset($params[$key]) === true) {
                $params[$key] = (int) $params[$key];
            }
            return $params;
        }

        /**
         * _getNormalizeCollectionIconsResponse
         * 
         * Normalizes the response when icons don't return collection details.
         * Maybe this should not be done here, but it seemed silly for the
         * response to not include the collections that icons are part of.
         * 
         * @access  protected
         * @param   int $collectionId
         * @param   array $icons
         * @return  array
         */
        protected function _getNormalizeCollectionIconsResponse(int $collectionId, array $icons): array
        {
            foreach ($icons as &$icon) {
                if (isset($icon['collections']) === true) {
                    continue;
                }
                $icon['collections'] = array();
                array_push($icon['collections'], array(
                    'id' => $collectionId
                ));
            }
            return $icons;
        }

        /**
         * _getPostHeaders
         * 
         * @access  protected
         * @return  array
         */
        protected function _getPostHeaders(): array
        {
            $headers = array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            );
            return $headers;
        }

        /**
         * _post
         * 
         * @access  protected
         * @param   string $path
         * @param   array $data (default: array())
         * @param   bool $live (default: true)
         * @return  null|array
         */
        protected function _post(string $path, array $data = array(), bool $live = true): ?array
        {
            $this->_setupConnection();
            $url = ($this->_base) . ($path);
            if ($live === false) {
                $url = ($url) . '?test=1';
            }
            $params = json_encode($data);
            $method = OAUTH_HTTP_METHOD_POST;
            $headers = $this->_getPostHeaders();
            $response = $this->_requestURL($url, $params, $method, $headers);
            $response = json_decode($response, true);
            return $response;
        }

        /**
         * _requestURL
         * 
         * @access  protected
         * @param   string $path
         * @param   string|array $params
         * @param   string $method
         * @param   array $headers
         * @return  null|string
         */
        protected function _requestURL(string $url, $params, string $method, array $headers): ?string
        {
            $connection = $this->_connection;
            try {
                $connection->fetch($url, $params, $method, $headers);
            } catch(OAuthException $exception) {
                // $msg = $exception->getMessage();
                // error_log($msg);
                return null;
            }
            $response = $connection->getLastResponse();
            if (is_string($response) === false) {
                return null;
            }
            return $response;
        }

        /**
         * _setupConnection
         * 
         * @access  protected
         * @return  void
         */
        protected function _setupConnection(): void
        {
            if (is_null($this->_connection) === true) {
                $key = $this->_key;
                $secret = $this->_secret;
                $method = OAUTH_SIG_METHOD_HMACSHA1;
                $authType = OAUTH_AUTH_TYPE_URI;
                $connection = new OAuth($key, $secret, $method, $authType);
                $this->_connection = $connection;
                if ($this->_debug === true) {
                    $this->_connection->enableDebug();
                }
            }
            $nonce = rand();
            $this->_connection->setNonce($nonce);
        }

        /**
         * getAllCollections
         * 
         * @access  public
         * @param   array $options (default: array())
         * @return  null|array
         */
        public function getAllCollections(array $options = array()): ?array
        {
            $path = '/collections';
            $response = $this->_get($path, $options);
            if ($response === null) {
                return null;
            }
            if (isset($response['collections']) === false) {
                return null;
            }
            return $response['collections'];
        }

        /**
         * getCollectionById
         * 
         * @access  public
         * @param   int $id
         * @return  null|array
         */
        public function getCollectionById(int $id): ?array
        {
            $path = '/collection/' . ($id);
            $response = $this->_get($path);
            if ($response === null) {
                return null;
            }
            if (isset($response['collection']) === false) {
                return null;
            }
            return $response['collection'];
        }

        /**
         * getCollectionBySlug
         * 
         * @access  public
         * @param   string $string
         * @return  null|array
         */
        public function getCollectionBySlug(string $slug): ?array
        {
            $path = '/collection/' . ($slug);
            $response = $this->_get($path);
            if ($response === null) {
                return null;
            }
            if (isset($response['collection']) === false) {
                return null;
            }
            return $response['collection'];
        }

        /**
         * getCollectionIconsById
         * 
         * Requests icons for a specific collection, and for consistency with
         * other API calls, if the response for an icon doesn't contain the
         * it's associated collections, I scafold in the id of the current
         * lookup.
         * 
         * @access  public
         * @param   int $id
         * @param   array $options (default: array())
         * @return  null|array
         */
        public function getCollectionIconsById(int $id, array $options = array()): ?array
        {
            $path = '/collection/' . ($id) . '/icons';
            $response = $this->_get($path, $options);
            if ($response === null) {
                return null;
            }
            if (isset($response['icons']) === false) {
                return null;
            }
            $icons = $response['icons'];
            $icons = $this->_getNormalizeCollectionIconsResponse($id, $icons);
            return $icons;
        }

        /**
         * getCollectionIconsBySlug
         * 
         * @access  public
         * @param   string $slug
         * @param   array $options (default: array())
         * @return  null|array
         */
        public function getCollectionIconsBySlug(string $slug, array $options = array()): ?array
        {
            $path = '/collection/' . ($slug) . '/icons';
            $response = $this->_get($path, $options);
            if ($response === null) {
                return null;
            }
            if (isset($response['icons']) === false) {
                return null;
            }
            return $response['icons'];
        }

        /**
         * getIconById
         * 
         * @access  public
         * @param   int $id
         * @return  null|array
         */
        public function getIconById(int $id): ?array
        {
            $path = '/icon/' . ($id);
            $response = $this->_get($path);
            if ($response === null) {
                return null;
            }
            if (isset($response['icon']) === false) {
                return null;
            }
            return $response['icon'];
        }

        /**
         * getIconByTerm
         * 
         * @access  public
         * @param   string $term
         * @return  null|array
         */
        public function getIconByTerm(string $term): ?array
        {
            $path = '/icon/' . ($term);
            $response = $this->_get($path);
            if ($response === null) {
                return null;
            }
            if (isset($response['icon']) === false) {
                return null;
            }
            return $response['icon'];
        }

        /**
         * getIconsByTerm
         * 
         * @access  public
         * @param   string $term
         * @param   array $options (default: array())
         * @return  null|array
         */
        public function getIconsByTerm(string $term, array $options = array()): ?array
        {
            $path = '/icons/' . ($term);
            $response = $this->_get($path, $options);
            if ($response === null) {
                return null;
            }
            if (isset($response['icons']) === false) {
                return null;
            }
            return $response['icons'];
        }

        /**
         * getRecentIcons
         * 
         * @access  public
         * @param   array $options (default: array())
         * @return  null|array
         */
        public function getRecentIcons(array $options = array()): ?array
        {
            $path = '/icons/recent_uploads';
            $response = $this->_get($path, $options);
            if ($response === null) {
                return null;
            }
            if (isset($response['icons']) === false) {
                return null;
            }
            return $response['icons'];
        }

        /**
         * getUsage
         * 
         * @access  public
         * @return  null|array
         */
        public function getUsage(): ?array
        {
            $path = '/oauth/usage';
            $response = $this->_get($path);
            if ($response === null) {
                return null;
            }
            return $response;
        }

        /**
         * getUserCollection
         * 
         * @access  public
         * @param   int $userId
         * @param   string $slug
         * @return  null|array
         */
        public function getUserCollection(int $userId, string $slug): ?array
        {
            $path = '/user/' . ($userId) . '/collections/' . ($slug);
            $response = $this->_get($path);
            if ($response === null) {
                return null;
            }
            if (isset($response['collection']) === false) {
                return null;
            }
            return $response['collection'];
        }

        /**
         * getUserCollections
         * 
         * @access  public
         * @param   int $userId
         * @return  null|array
         */
        public function getUserCollections(int $userId): ?array
        {
            $path = '/user/' . ($userId) . '/collections';
            $response = $this->_get($path);
            if ($response === null) {
                return null;
            }
            if (isset($response['collections']) === false) {
                return null;
            }
            return $response['collections'];
        }

        /**
         * getUserUploads
         * 
         * @access  public
         * @param   string $username
         * @param   array $options (default: array())
         * @return  null|array
         */
        public function getUserUploads(string $username, array $options = array()): ?array
        {
            $path = '/user/' . ($username) . '/uploads';
            $response = $this->_get($path, $options);
            if ($response === null) {
                return null;
            }
            if (isset($response['uploads']) === false) {
                return null;
            }
            return $response['uploads'];
        }

        /**
         * notify
         * 
         * @access  public
         * @param   string $type
         * @param   array $data (default: array())
         * @param   bool $live (default: true)
         * @return  null|array
         */
        public function notify(string $type, array $data = array(), bool $live = true): ?array
        {
            $path = '/notify/' . ($type);
            $response = $this->_post($path, $data, $live);
            if ($response === null) {
                return null;
            }
            return $response;
        }
    }
