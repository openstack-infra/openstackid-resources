<?php namespace utils;
/**
 * Copyright 2015 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\File\UploadedFile;
/**
 * Class ParseMultiPartFormDataInputStream
 * @package utils
 */
final class ParseMultiPartFormDataInputStream
{
    /**
     * @abstract Raw input stream
     */
    protected $input;

    /**
     * ParseMultiPartFormDataInputStream constructor.
     * @param $input
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * @return array
     */
    public function getInput(){

        $boundary = $this->boundary();

        if (!strlen($boundary)) {
            return [
                'parameters' => $this->parse(),
                'files'      => []
            ];
        }

        $blocks = $this->split($boundary);

        return $this->blocks($blocks);
    }

    /**
     * @function boundary
     * @returns string
     */
    private function boundary()
    {
        if(!isset($_SERVER['CONTENT_TYPE'])) {
            return null;
        }

        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        return $matches[1];
    }

    /**
     * @function parse
     * @returns array
     */
    private function parse()
    {
        parse_str(urldecode($this->input), $result);
        return $result;
    }

    /**
     * @function split
     * @param $boundary string
     * @returns array
     */
    private function split($boundary)
    {
        $result = preg_split("/-+$boundary/", $this->input);
        array_pop($result);
        return $result;
    }

    /**
     * @function blocks
     * @param $array array
     * @returns array
     */
    private function blocks($array)
    {
        $results = [
            'parameters' => [],
            'files'      => []
        ];

        foreach($array as $key => $value)
        {
            if (empty($value))
                continue;

            $block = $this->decide($value);

            foreach ($block['parameters'] as $key => $val ) {
                $results['parameters'][$key] = $val;
            }

            foreach ( $block['files'] as $key => $val ) {
                $results['files'][$key] = $val;
            }
        }

        return $results;
    }

    /**
     * @function decide
     * @param $string string
     * @returns array
     */
    private function decide($string)
    {
        if (strpos($string, 'application/octet-stream') !== FALSE)
        {
            return [
                'parameters' => $this->file($string),
                'files' => []
            ];
        }

        if (strpos($string, 'filename') !== FALSE)
        {
            return [
                'parameters' => [],
                'files' => $this->file_stream($string)
            ];
        }

        return [
            'parameters' => $this->parameter($string),
            'files' => []
        ];
    }

    /**
     * @function file
     *
     * @param $string
     *
     * @return array
     */
    private function file($string)
    {
        preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $string, $match);
        return [
            $match[1] => ($match[2] !== NULL ? $match[2] : '')
        ];
    }

    /**
     * @function file_stream
     *
     * @param $string
     *
     * @return array
     */
    private function file_stream($data)
    {
        $result = [];
        $data = ltrim($data);

        $idx = strpos( $data, "\r\n\r\n" );
        if ( $idx === FALSE ) {
            Log::warning( "ParseMultiPartFormDataInputStream.file_stream(): Could not locate header separator in data:" );
            Log::warning( $data );
        } else {
            $headers = substr( $data, 0, $idx );
            $content = substr( $data, $idx + 4, -2 ); // Skip the leading \r\n and strip the final \r\n

            $name = '-unknown-';
            $filename = '-unknown-';
            $filetype = 'application/octet-stream';

            $header = strtok( $headers, "\r\n" );
            while ( $header !== FALSE ) {
                if ( substr($header, 0, strlen("Content-Disposition: ")) == "Content-Disposition: " ) {
                    // Content-Disposition: form-data; name="attach_file[TESTING]"; filename="label2.jpg"
                    if ( preg_match('/name=\"([^\"]*)\"/', $header, $nmatch ) ) {
                        $name = $nmatch[1];
                    }
                    if ( preg_match('/filename=\"([^\"]*)\"/', $header, $nmatch ) ) {
                        $filename = $nmatch[1];
                    }
                } elseif ( substr($header, 0, strlen("Content-Type: ")) == "Content-Type: " ) {
                    // Content-Type: image/jpg
                    $filetype = trim( substr($header, strlen("Content-Type: ")) );
                } else {
                    Log::debug( "PARSEINPUTSTREAM: Skipping Header: " . $header );
                }

                $header = strtok("\r\n");
            }

            if ( substr($data, -2) === "\r\n" ) {
                $data = substr($data, 0, -2);
            }

            $path = sys_get_temp_dir() . '/php' . substr( sha1(rand()), 0, 6 );

            $bytes = file_put_contents( $path, $content );

            if ( $bytes !== FALSE ) {
                $file = new UploadedFile( $path, $filename, $filetype, $bytes, UPLOAD_ERR_OK );
                $result = array( $name => $file );
            }
        }

        return $result;
    }

    /**
     * @function parameter
     *
     * @param $string
     *
     * @return array
     */
    private function parameter($string)
    {
        $data = [];
        $string = trim($string);
        if(empty($string)) return $data;

        if ( preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $string, $match) ) {
            if (preg_match('/^(.*)\[\]$/i', $match[1], $tmp)) {
                $data[$tmp[1]][] = (count($match) >=2 && $match[2] !== NULL ? $match[2] : '');
            } else {
                $data[$match[1]] = (count($match) >=2 && $match[2] !== NULL ? $match[2] : '');
            }
        }

        return $data;
    }

    /**
     * @function merge
     * @param $array array
     *
     * Ugly ugly ugly
     *
     * @returns array
     */
    private function merge($array)
    {
        $results = [
            'parameters' => [],
            'files' => []
        ];

        if (count($array['parameters']) > 0) {
            foreach($array['parameters'] as $key => $value) {
                foreach($value as $k => $v) {
                    if (is_array($v)) {
                        foreach($v as $kk => $vv) {
                            $results['parameters'][$k][] = $vv;
                        }
                    } else {
                        $results['parameters'][$k] = $v;
                    }
                }
            }
        }

        if (count($array['files']) > 0) {
            foreach($array['files'] as $key => $value) {
                foreach($value as $k => $v) {
                    if (is_array($v)) {
                        foreach($v as $kk => $vv) {
                            if(is_array($vv) && (count($vv) === 1)) {
                                $results['files'][$k][$kk] = $vv[0];
                            } else {
                                $results['files'][$k][$kk][] = $vv[0];
                            }
                        }
                    } else {
                        $results['files'][$k][$key] = $v;
                    }
                }
            }
        }

        return $results;
    }

    function parse_parameter( &$params, $parameter, $value ) {
        if ( strpos($parameter, '[') !== FALSE ) {
            $matches = array();
            if ( preg_match( '/^([^[]*)\[([^]]*)\](.*)$/', $parameter, $match ) ) {
                $name = $match[1];
                $key = $match[2];
                $rem = $match[3];

                if ( $name !== '' && $name !== NULL ) {
                    if ( ! isset($params[$name]) || ! is_array($params[$name]) ) {
                        $params[$name] = array();
                    } else {
                    }
                    if ( strlen($rem) > 0 ) {
                        if ( $key === '' || $key === NULL ) {
                            $arr = array();
                            $this->parse_parameter( $arr, $rem, $value );
                            $params[$name][] = $arr;
                        } else {
                            if ( !isset($params[$name][$key]) || !is_array($params[$name][$key]) ) {
                                $params[$name][$key] = array();
                            }
                            $this->parse_parameter( $params[$name][$key], $rem, $value );
                        }
                    } else {
                        if ( $key === '' || $key === NULL ) {
                            $params[$name][] = $value;
                        } else {
                            $params[$name][$key] = $value;
                        }
                    }
                } else {
                    if ( strlen($rem) > 0 ) {
                        if ( $key === '' || $key === NULL ) {
                            // REVIEW Is this logic correct?!
                            $this->parse_parameter( $params, $rem, $value );
                        } else {
                            if ( ! isset($params[$key]) || ! is_array($params[$key]) ) {
                                $params[$key] = array();
                            }
                            $this->parse_parameter( $params[$key], $rem, $value );
                        }
                    } else {
                        if ( $key === '' || $key === NULL ) {
                            $params[] = $value;
                        } else {
                            $params[$key] = $value;
                        }
                    }
                }
            } else {
                Log::warning( "ParseMultiPartFormDataInputStream.parse_parameter() Parameter name regex failed: '" . $parameter . "'" );
            }
        } else {
            $params[$parameter] = $value;
        }
    }
}