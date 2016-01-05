<?php

    // dependecy checks
    if (!in_array('OAuth', get_loaded_extensions())) {
        throw new Exception('OAuth extension needs to be installed.');
    }

    /**
     * TheNounProject
     * 
     * PHP OAuth wrapper for The Noun Project, using PECL OAuth library
     * 
     * @note   icon_url urls expire 24 hours after received
     * @author Oliver Nassar <onassar@gmail.com>
     * @see    https://github.com/onassar/PHP-TheNounProject
     * @see    https://pecl.php.net/package/oauth
     * @see    http://php.net/manual/en/book.oauth.php
     */
    class TheNounProject
    {
        /**
         * _associative
         * 
         * @var    boolean
         * @access protected
         */
        protected $_associative;

        /**
         * _base
         * 
         * @var    string
         * @access protected
         */
        protected $_base = 'http://api.thenounproject.com';

        /**
         * _connection
         * 
         * @var    OAuth
         * @access protected
         */
        protected $_connection;

        /**
         * _debug
         * 
         * @var    boolean
         * @access protected
         */
        protected $_debug;

        /**
         * _key
         * 
         * @var    string
         * @access protected
         */
        protected $_key;

        /**
         * _secret
         * 
         * @var    string
         * @access protected
         */
        protected $_secret;

        /**
         * __construct
         * 
         * @access public
         * @param  string $key
         * @param  string $secret
         * @param  boolean $debug (default: false)
         * @param  boolean $associative (default: true)
         * @return void
         */
        public function __construct(
            $key,
            $secret,
            $debug = false,
            $associative = true
        ) {
            $this->_key = $key;
            $this->_secret = $secret;
            $this->_debug = $debug;
            $this->_associative = $associative;
        }

        /**
         * _get
         * 
         * @access protected
         * @param  string $path
         * @param  array $options (default: array())
         * @return false|array|stdClass
         */
        public function _get($path, array $options = array())
        {
            if (is_null($this->_connection)) {
                $this->_connection = (new OAuth(
                    $this->_key,
                    $this->_secret,
                    OAUTH_SIG_METHOD_HMACSHA1,
                    OAUTH_AUTH_TYPE_URI
                ));
                if ($this->_debug === true) {
                    $this->_connection->enableDebug();
                }
                $this->_connection->setNonce(rand());
            }
            try {
                $this->_connection->fetch(
                    ($this->_base) . ($path),
                    $options,
                    OAUTH_HTTP_METHOD_GET
                );
            } catch(OAuthException $exception) {
                error_log($exception->getMessage());
                return false;
            }
            return json_decode(
                $this->_connection->getLastResponse(),
                $this->_associative
            );
        }

        /**
         * getAllCollections
         * 
         * @access public
         * @param  array $options (default: array())
         * @return false|array|stdClass
         */
        public function getAllCollections(array $options = array())
        {
            $path = '/collections';
            $response = $this->_get($path, $options);
            if ($response === false) {
                return false;
            }
            return $this->_associative
                ? $response['collections']
                : $response->collections;
        }

        /**
         * getCollectionById
         * 
         * @access public
         * @param  string $id
         * @return false|array|stdClass
         */
        public function getCollectionById($id)
        {
            $path = '/collection/' . ($id);
            $response = $this->_get($path);
            if ($response === false) {
                return false;
            }
            return $this->_associative
                ? $response['collection']
                : $response->collection;
        }

        /**
         * getCollectionBySlug
         * 
         * @access public
         * @param  string $string
         * @return false|array|stdClass
         */
        public function getCollectionBySlug($slug)
        {
            $path = '/collection/' . ($slug);
            $response = $this->_get($path);
            if ($response === false) {
                return false;
            }
            return $this->_associative
                ? $response['collection']
                : $response->collection;
        }

        /**
         * getCollectionIconsById
         * 
         * @access public
         * @param  string $id
         * @param  array $options (default: array())
         * @return false|array|stdClass
         */
        public function getCollectionIconsById($id, array $options = array())
        {
            $path = '/collection/' . ($id) . '/icons';
            $response = $this->_get($path, $options);
            if ($response === false) {
                return false;
            }
            return $this->_associative
                ? $response['icons']
                : $response->icons;
        }

        /**
         * getCollectionIconsBySlug
         * 
         * @access public
         * @param  string $slug
         * @param  array $options (default: array())
         * @return false|array|stdClass
         */
        public function getCollectionIconsBySlug(
            $slug,
            array $options = array()
        ) {
            $path = '/collection/' . ($slug) . '/icons';
            $response = $this->_get($path, $options);
            if ($response === false) {
                return false;
            }
            return $this->_associative
                ? $response['icons']
                : $response->icons;
        }

        /**
         * getIconById
         * 
         * @access public
         * @param  string $id
         * @return false|array|stdClass
         */
        public function getIconById($id)
        {
            $path = '/icon/' . ($id);
            $response = $this->_get($path);
            if ($response === false) {
                return false;
            }
            return $this->_associative
                ? $response['icon']
                : $response->icon;
        }

        /**
         * getIconByTerm
         * 
         * @access public
         * @param  string $term
         * @return false|array|stdClass
         */
        public function getIconByTerm($term)
        {
            $path = '/icon/' . ($term);
            $response = $this->_get($path);
            if ($response === false) {
                return false;
            }
            return $this->_associative
                ? $response['icon']
                : $response->icon;
        }

        /**
         * getIconsByTerm
         * 
         * @access public
         * @param  string $term
         * @param  array $options (default: array())
         * @return false|array|stdClass
         */
        public function getIconsByTerm($term, array $options = array())
        {
            $path = '/icons/' . ($term);
            if (isset($options['limit_to_public_domain']) === true) {
                $options['limit_to_public_domain'] = (int) $options['limit_to_public_domain'];
            }
            $response = $this->_get($path, $options);
            if ($response === false) {
                return false;
            }
            return $this->_associative
                ? $response['icons']
                : $response->icons;
        }

        /**
         * getRecentIcons
         * 
         * @access public
         * @param  array $options (default: array())
         * @return false|array|stdClass
         */
        public function getRecentIcons(array $options = array())
        {
            $path = '/icons/recent_uploads';
            $response = $this->_get($path, $options);
            if ($response === false) {
                return false;
            }
            return $this->_associative
                ? $response['icons']
                : $response->icons;
        }

        /**
         * getUsage
         * 
         * @access public
         * @return false|array|stdClass
         */
        public function getUsage()
        {
            $path = '/oauth/usage';
            $response = $this->_get($path);
            if ($response === false) {
                return false;
            }
            return $response;
        }

        /**
         * getUserCollection
         * 
         * @access public
         * @param  string $userId
         * @param  string $slug
         * @return false|array|stdClass
         */
        public function getUserCollection($userId, $slug)
        {
            $path = '/user/' . ($userId) . '/collections/' . ($slug);
            $response = $this->_get($path);
            if ($response === false) {
                return false;
            }
            return $this->_associative
                ? $response['collection']
                : $response->collection;
        }

        /**
         * getUserCollections
         * 
         * @access public
         * @param  string $userId
         * @return false|array|stdClass
         */
        public function getUserCollections($userId)
        {
            $path = '/user/' . ($userId) . '/collections';
            $response = $this->_get($path);
            if ($response === false) {
                return false;
            }
            return $this->_associative
                ? $response['collections']
                : $response->collections;
        }

        /**
         * getUserUploads
         * 
         * @access public
         * @param  string $username
         * @param  array $options (default: array())
         * @return false|array|stdClass
         */
        public function getUserUploads($username, array $options = array())
        {
            $path = '/user/' . ($username) . '/uploads';
            $response = $this->_get($path, $options);
            if ($response === false) {
                return false;
            }
            return $this->_associative
                ? $response['uploads']
                : $response->uploads;
        }
    }
