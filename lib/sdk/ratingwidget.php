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
    if (!function_exists('curl_init'))
        throw new Exception('RatingWidget needs the CURL PHP extension.');

    require_once(dirname(__FILE__) . '/RatingWidgetBase.php');

    define('RW_SDK__USER_AGENT', 'rw-php-' . RatingWidgetBase::VERSION);

    class RatingWidget extends RatingWidgetBase
    {
	    const VERSION = '1.0.3';

        /**
        * Default options for curl.
        */
        public static $CURL_OPTS = array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_USERAGENT      => RW_SDK__USER_AGENT,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
            )
        );

        /**
        * @param string $pScope 'app', 'user' or 'site'
        * @param number $pID Element's id.
        * @param string $pPublic Public key.
        * @param string $pSecret App, User or Site secret key.
        */
        public function __construct($pScope, $pID, $pPublic, $pSecret)
        {
            parent::__construct($pScope, $pID, $pPublic, $pSecret);
        }

	    private function _Api($pPath, $pMethod = 'GET', $pParams = array())
	    {
		    $pMethod = strtoupper($pMethod);

		    try
		    {
			    $result = $this->MakeRequest($pPath, $pMethod, $pParams);
		    }
		    catch (\Exception $e)
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

	    private $_clock_diff = 0;

	    /**
	     * Set clock diff for all API calls.
	     *
	     * @since 1.0.3
	     * @param $pSeconds
	     */
	    public function SetClockDiff($pSeconds)
	    {
		    $this->_clock_diff = $pSeconds;
	    }

	    /**
	     * @return bool True if successful connectivity to the API endpoint using ping.json endpoint.
	     */
	    public function Test()
	    {
		    $pong = $this->_Api('/v' . RW_API__VERSION . '/ping.json');
		    return (is_object($pong) && isset($pong->api) && 'pong' === $pong->api);
	    }

	    public function Api($pPath, $pMethod = 'GET', $pParams = array())
	    {
		    return $this->_Api($this->CanonizePath($pPath), $pMethod, $pParams);
	    }

        public function SignRequest($pResource, &$opts)
        {
            $eol = "\n";
            $content_md5 = '';
            $now = (time() - $this->_clock_diff);
            $date = date('r', $now);

            if (isset($opts[CURLOPT_POST]) && 0 < $opts[CURLOPT_POST])
            {
                $content_md5 = md5($opts[CURLOPT_POSTFIELDS]);
                $opts[CURLOPT_HTTPHEADER][] = 'Content-MD5: ' . $content_md5;
            }

            $opts[CURLOPT_HTTPHEADER][] = 'Date: ' . $date;

            $string_to_sign = implode($eol, array(
                $opts[CURLOPT_CUSTOMREQUEST],
                $content_md5,
                'application/json',
                $date,
                $pResource
            ));

            // Add authorization header.
            $opts[CURLOPT_HTTPHEADER][] = 'Authorization: RW ' . $this->_id . ':' . $this->_public . ':' . self::Base64UrlEncode(hash_hmac('sha256', $string_to_sign, $this->_secret));
        }

	    /**
	     * Makes an HTTP request. This method can be overridden by subclasses if
	     * developers want to do fancier things or use something other than curl to
	     * make the request.
	     *
	     * @param $pCanonizedPath The URL to make the request to
	     * @param string $pMethod HTTP method
	     * @param array $params The parameters to use for the POST body
	     * @param null $ch Initialized curl handle
	     *
	     * @return mixed
	     * @throws RW_Exception
	     */
        protected function MakeRequest($pCanonizedPath, $pMethod = 'GET', $params = array(), $ch = null)
        {
            if (!$ch)
                $ch = curl_init();

            $opts = self::$CURL_OPTS;

            if (!is_array($opts[CURLOPT_HTTPHEADER]))
                $opts[CURLOPT_HTTPHEADER] = array();

            if ('POST' === $pMethod || 'PUT' === $pMethod)
            {
                $opts[CURLOPT_POST] = count($params);
                $opts[CURLOPT_POSTFIELDS] = json_encode($params);
                $opts[CURLOPT_RETURNTRANSFER] = true;
            }

            $opts[CURLOPT_URL] = $this->GetUrl($pCanonizedPath);
//            $opts[CURLOPT_URL] = 'http://localhost:8080/api/?path=' . $pCanonizedPath;
            $opts[CURLOPT_CUSTOMREQUEST] = $pMethod;

            $resource = explode('?', $pCanonizedPath);
            $this->SignRequest($resource[0], $opts);

            // disable the 'Expect: 100-continue' behaviour. This causes CURL to wait
            // for 2 seconds if the server does not support this header.
            $opts[CURLOPT_HTTPHEADER][] = 'Expect:';

	        if ('https' === substr(strtolower($pCanonizedPath), 0, 5))
	        {
		        $opts[CURLOPT_SSL_VERIFYHOST] = false;
		        $opts[CURLOPT_SSL_VERIFYPEER] = false;
	        }

            curl_setopt_array($ch, $opts);
            $result = curl_exec($ch);

            /*if (curl_errno($ch) == 60) // CURLE_SSL_CACERT
            {
                self::errorLog('Invalid or no certificate authority found, using bundled information');
                curl_setopt($ch, CURLOPT_CAINFO,
                dirname(__FILE__) . '/fb_ca_chain_bundle.crt');
                $result = curl_exec($ch);
            }*/

            // With dual stacked DNS responses, it's possible for a server to
            // have IPv6 enabled but not have IPv6 connectivity.  If this is
            // the case, curl will try IPv4 first and if that fails, then it will
            // fall back to IPv6 and the error EHOSTUNREACH is returned by the
            // operating system.
            if ($result === false && empty($opts[CURLOPT_IPRESOLVE]))
            {
                $matches = array();
                $regex = '/Failed to connect to ([^:].*): Network is unreachable/';
                if (preg_match($regex, curl_error($ch), $matches))
                {
                    if (strlen(@inet_pton($matches[1])) === 16)
                    {
                        self::errorLog('Invalid IPv6 configuration on server, Please disable or get native IPv6 on your server.');
                        self::$CURL_OPTS[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
                        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
                        $result = curl_exec($ch);
                    }
                }
            }

            if ($result === false)
            {
                $e = new RW_Exception(array(
                    'error' => array(
                        'code' => curl_errno($ch),
                        'message' => curl_error($ch),
                        'type' => 'CurlException',
                    ),
                ));

                curl_close($ch);
                throw $e;
            }

            curl_close($ch);

            return $result;
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

        /**
        * Get specified rating Rich-Snippets data.
        *
        * Note:
        *   Rich-Snippets data is daily cached (24 hour cache) on local disk
        *   because Google crawling frequency is lower than that for 99% of the
        *   sites.
        *
        * @param mixed $pRatingExternalID
        */
        public function GetRichSnippetData($pRatingExternalID)
        {
            $cached_file_path = dirname(__FILE__) . '/ratings.json';

            // Daily cache.
            if (!file_exists($cached_file_path) || 24 * 60 * 60 < (time() - filemtime($cached_file_path)))
            {
                // Get ratings rich-snippets data.
                $ratings = $this->Api('/ratings/rich-snippets.json');

                if (false !== $ratings)
                    // Cache ratings data.
                    file_put_contents($cached_file_path, json_encode($ratings));
                else if (file_exists($cached_file_path))
                    // If has local cached version - fall back from request failure.
                    $ratings = json_decode(file_get_contents($cached_file_path));

            }
            else
            {
                // Read cached data from disk.
                $ratings = json_decode(file_get_contents($cached_file_path));
            }

            $votes = 0;
            $avg_rate = 0;

            if (false !== $ratings)
            {
                foreach ($ratings->ratings as $rating)
                {
                    if ($pRatingExternalID == $rating->external_id)
                    {
                        $votes = $rating->approved_count;
                        $avg_rate = $rating->avg_rate;
                        break;
                    }
                }
            }

            return array('votes' => $votes, 'avg_rate' => $avg_rate);
        }

        public function EchoAggregateRating($pRatingExternalID, $pMinVotes = 1, $pMinAvgRate = 0)
        {
            $snippet_data = $this->GetRichSnippetData($pRatingExternalID);

            if ($pMinVotes > $snippet_data['votes'])
                return;
            if ($pMinAvgRate > $snippet_data['avg_rate'])
                return;

            echo
'<!-- schema.org rating data -->
<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
    <meta itemprop="worstRating" content="0" />
    <meta itemprop="bestRating" content="5" />
    <meta itemprop="ratingValue" content="' . $snippet_data['avg_rate'] . '" />
    <meta itemprop="ratingCount" content="' . $snippet_data['votes'] . '" />
</div>';
        }
    }