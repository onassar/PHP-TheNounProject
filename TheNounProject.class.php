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
     * @author Oliver Nassar <onassar@gmail.com>
     * @see    https://github.com/onassar/PHP-TheNounProject
     */
    class TheNounProject
    {
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
         * @param  boolean $debug
         * @return void
         */
        public function __construct($key, $secret, $debug = false)
        {
            $this->_key = $key;
            $this->_secret = $secret;
            $this->_debug = $debug;
        }

        /**
         * _get
         * 
         * @access protected
         * @param  string $path
         * @param  array $options (default: array())
         * @return array|false
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
            } catch(OAuthException $e) {
                error_log($exception->getMessage());
                return false;
            }
            return json_decode($this->_connection->getLastResponse(), true);
        }

        /**
         * getCollectionIconsById
         * 
         * @access public
         * @param  string $id
         * @param  array $options
         * @return array
         */
        public function getCollectionIconsById($id, array $options)
        {
            $path = '/collection/' . ($id) . '/icons';
            return $this->_get($path, $options);
        }

        /**
         * getCollectionIconsBySlug
         * 
         * @access public
         * @param  string $slug
         * @param  array $options
         * @return array
         */
        public function getCollectionIconsBySlug($slug, array $options)
        {
            $path = '/collection/' . ($slug) . '/icons';
            return $this->_get($path, $options);
        }

        /**
         * getCollectionById
         * 
         * @access public
         * @param  string $id
         * @return array
         */
        public function getCollectionById($id)
        {
            $path = '/collection/' . ($id);
            return $this->_get($path);
        }

        /**
         * getCollectionBySlug
         * 
         * @access public
         * @param  string $string
         * @return array
         */
        public function getCollectionBySlug($slug)
        {
            $path = '/collection/' . ($slug);
            return $this->_get($path);
        }

        /**
         * getIconById
         * 
         * @access public
         * @param  string $id
         * @return array
         */
        public function getIconById($id)
        {
            $path = '/icon/' . ($id);
            return $this->_get($path);
        }

        /**
         * getIconByTerm
         * 
         * @access public
         * @param  string $term
         * @return array
         */
        public function getIconByTerm($term)
        {
            $path = '/icon/' . ($term);
            return $this->_get($path);
        }

        /**
         * getRecentIcons
         * 
         * @access public
         * @param  array $options
         * @return array
         */
        public function getRecentIcons(array $options)
        {
            $path = '/icons/recent_uploads';
            return $this->_get($path, $options);
        }

        /**
         * getIconsByTerm
         * 
         * @access public
         * @param  string $term
         * @param  array $options
         * @return array
         */
        public function getIconsByTerm($term, array $options)
        {
            $path = '/icons/' . ($term);
            // if ($options['limit_to_public_domain'] === true) {
            //     $options['limit_to_public_domain'] = '1';
            // }
            return $this->_get($path, $options);
        }

        /**
         * getUsage
         * 
         * @access public
         * @return array
         */
        public function getUsage()
        {
            return $this->_get('/oauth/usage');
        }

        /**
         * getUserCollection
         * 
         * @access public
         * @param  string $userId
         * @param  string $slug
         * @return array
         */
        public function getUserCollection($userId, $slug)
        {
            $path = '/user/' . ($userId) . '/collections/' . ($slug);
            return $this->_get($path);
        }

        /**
         * getUserCollections
         * 
         * @access public
         * @param  string $userId
         * @return array
         */
        public function getUserCollections($userId)
        {
            $path = '/user/' . ($userId) . '/collections';
            return $this->_get($path);
        }

        /**
         * getUserUploads
         * 
         * @access public
         * @param  string $username
         * @param  array $options
         * @return array
         */
        public function getUserUploads($username, array $options)
        {
            $path = '/user/' . ($username) . '/uploads';
            return $this->_get($path, $options);
        }
    }

