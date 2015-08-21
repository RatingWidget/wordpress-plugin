<?php
    /**
     * Copyright 2014 RatingWidget, Inc.
     *
     * Licensed under the GPL v2 (the "License"); you may
     * not use this file except in compliance with the License. You may obtain
     * a copy of the License at
     *
     *     http://choosealicense.com/licenses/gpl-v2/
     *
     * Unless required by applicable law or agreed to in writing, software
     * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
     * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
     * License for the specific language governing permissions and limitations
     * under the License.
     */

    define('RW_API__VERSION', '1');
    define('RW_SDK__PATH', dirname(__FILE__));
    define('RW_SDK__EXCEPTIONS_PATH', RW_SDK__PATH . '/exceptions/');

    if (!function_exists('json_decode'))
        throw new Exception('RatingWidget needs the JSON PHP extension.');

    // Include all exception files.
    $exceptions = array(
        'Exception',
        'InvalidArgumentException',
        'ArgumentNotExistException',
	    'EmptyArgumentException',
	    'OAuthException'
    );

    foreach ($exceptions as $e)
        require RW_SDK__EXCEPTIONS_PATH . $e . '.php';

	abstract class RatingWidgetBase
    {
        const VERSION = '1.0.4';
        const FORMAT = 'json';

        protected $_id;
        protected $_public;
        protected $_secret;
        protected $_scope;

        /**
        * @param string $pScope 'app', 'user' or 'site'
        * @param number $pID Element's id.
        * @param string $pPublic Public key.
        * @param string $pSecret App, User or Site secret key.
        */
        public function __construct($pScope, $pID, $pPublic, $pSecret)
        {
            $this->_id = $pID;
            $this->_public = $pPublic;
            $this->_secret = $pSecret;
            $this->_scope = $pScope;
        }

        function CanonizePath($pPath)
        {
            $pPath = trim($pPath, '/');
            $query_pos = strpos($pPath, '?');
            $query = '';

            if (false !== $query_pos)
            {
                $query = substr($pPath, $query_pos);
                $pPath = substr($pPath, 0, $query_pos);
            }

            // Trim '.json' suffix.
            $format_length = strlen('.' . self::FORMAT);
            $start  = $format_length * (-1); //negative
            if (substr($pPath, $start) === ('.' . self::FORMAT))
                $pPath = substr($pPath, 0, strlen($pPath) - $format_length);

            switch ($this->_scope)
            {
                case 'app':
                    $base = '/apps/' . $this->_id;
                    break;
                case 'user':
                    $base = '/users/' . $this->_id;
                    break;
                case 'site':
                    $base = '/sites/' . $this->_id;
                    break;
                default:
                    throw new RW_Exception('Scope not implemented.');
            }

            return '/v' . RW_API__VERSION . $base . (!empty($pPath) ? '/' : '') . $pPath . '.' . self::FORMAT . $query;
        }

	    abstract function MakeRequest($pCanonizedPath, $pMethod = 'GET', $pParams = array());

	    protected function _Api($pPath, $pMethod = 'GET', $pParams = array())
	    {
		    $pMethod = strtoupper($pMethod);

		    try
		    {
			    $result = $this->MakeRequest($pPath, $pMethod, $pParams);
		    }
		    catch (Exception $e)
		    {
			    // Map to error object.
			    $result = json_encode(array(
				    'error' => array(
					    'type' => 'Unknown',
					    'message' => $e->getMessage() . ' (' . $e->getFile() . ': ' . $e->getLine() . ')',
					    'code' => 'unknown',
					    'http' => 402
				    )
			    ));
		    }

		    return json_decode($result);
	    }

	    /**
	     * Find clock diff between current server to API server.
	     *
	     * @since 1.0.3
	     * @return int Clodk diff in seconds.
	     */
	    public function FindClockDiff()
	    {
		    $time = time();
		    $pong = $this->_Api('/v' . RW_API__VERSION . '/ping.json');
		    return ($time - strtotime($pong->timestamp));
	    }

	    /**
	     * Base64 encoding that doesn't need to be urlencode()ed.
	     * Exactly the same as base64_encode except it uses
	     *   - instead of +
	     *   _ instead of /
	     *   No padded =
	     *
	     * @param string $input base64UrlEncoded string
	     * @return string
	     */
	    protected static function Base64UrlDecode($input)
	    {
		    return base64_decode(strtr($input, '-_', '+/'));
	    }

	    /**
	     * Base64 encoding that doesn't need to be urlencode()ed.
	     * Exactly the same as base64_encode except it uses
	     *   - instead of +
	     *   _ instead of /
	     *
	     * @param string $input string
	     * @return string base64Url encoded string
	     */
	    protected static function Base64UrlEncode($input)
	    {
		    $str = strtr(base64_encode($input), '+/', '-_');
		    $str = str_replace('=', '', $str);
		    return $str;
	    }
    }