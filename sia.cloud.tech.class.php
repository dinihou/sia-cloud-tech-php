<?php

class Api_sia_cloud_tech
{
    public $api_key                 = '';
    public $api_secret              = '';
    public $curl                    = null;
    public $progress_callback       = null;
    public $last_error              = null;

    const sia_cloud_tech_endpoint   = 'https://api.sia-cloud.tech';

    const subscription_endpoint     = '/subscription';
    const subscription_method       = '_subscription';

    const cloud_list_endpoint     = '/cloud/files';
    const cloud_list_method       = '_cloud_list';

    const cloud_file_endpoint     = '/cloud/file';
    const cloud_file_method       = '_cloud_file';

    const cloud_delete_endpoint     = '/cloud/file/delete';
    const cloud_delete_method       = '_cloud_delete';

    const uploadstream_endpoint     = '/uploadstream';
    const uploadstream_method       = '_uploadstream';

    const downloadstream_endpoint     = '/cloud/file/downloadstream';
    const downloadstream_method       = '_downloadstream';

    const default_get_method        = '_get';
    const default_post_method       = '_post';

    public function __construct($__api_key = '', $__api_secret = '')
    {
        if ($__api_key && $__api_secret) {
            $this->api_key = $__api_key;
            $this->api_secret = $__api_secret;
        }
        $this->curl = curl_init();
    }
    public function __destruct()
    {
        curl_close($this->curl);
    }

    public function subscription()
    {
        $endpoint = self::sia_cloud_tech_endpoint . self::subscription_endpoint;
        $method = self::subscription_method;

        $reponse = $this->__request($endpoint, $method);
        if ($reponse) {
            return $reponse;
        } else {
            return false;
        }
    }

    public function cloud_list($shared = false, $search = '', $ordercol='', $orderdir ='', $limit = '', $offset = '')
    {
        $endpoint = self::sia_cloud_tech_endpoint . self::cloud_list_endpoint;
        $method = self::cloud_list_method;

        $query = array('shared' => $shared ? 'yes' : 'no','ordercol' => $ordercol,'orderdir' => $orderdir, 'limit' => $limit , 'offset' => $offset);
        $reponse = $this->__request($endpoint, $method, $query);
        if ($reponse) {
            return $reponse;
        } else {
            return false;
        }
    }

    public function cloud_file($path, $shared = false, $owner_email = '')
    {
        $endpoint = self::sia_cloud_tech_endpoint . self::cloud_file_endpoint ;
        $method = self::cloud_file_method;

        if ($path != '' &&  $path[0] != '/') {
            $path = '/' . $path;
        } else 
        if ($path == '') {
            $this->last_error = array ('http_code' => '' , 'http_message' => '$path is empty', '__line' => __LINE__);
            return false;
        }

        $query = array(
            'path' => $path,
            'shared' => $shared ? 'yes' : 'no',
            'email' => $shared ? $owner_email : ''
        );

        $reponse = $this->__request($endpoint, $method, $query);
        if ($reponse) {
            return $reponse;
        } else {
            return false;
        }
    }
    public function cloud_delete($path)
    {
        $endpoint = self::sia_cloud_tech_endpoint . self::cloud_delete_endpoint ;
        $method = self::cloud_delete_method;

        if ($path != '' &&  $path[0] != '/') {
            $path = '/' . $path;
        } else 
        if ($path == '') {
            $this->last_error = array ('http_code' => '' , 'http_message' => '$path is empty', '__line' => __LINE__);
            return false;
        }

        $query = array(
            'path' => $path
        );

        $reponse = $this->__request($endpoint, $method, $query);
        if ($reponse) {
            return $reponse;
        } else {
            return false;
        }
    }
    public function uploadstream($file, $path = '')
    {
        $endpoint = self::sia_cloud_tech_endpoint . self::uploadstream_endpoint;
        $method = self::uploadstream_method;

        if (file_exists($file)) {

            if ($path == '') {
                $path = '/'.basename($file);
            }
            $query = array('path' => $path);
            $option = array('uploadstream_file' => $file);

            $reponse = $this->__request($endpoint, $method, $query, $option);
            if ($reponse) {
                return $reponse;
            } else {
                return false;
            }
        } else {
            $this->last_error = array ('http_code' => '' , 'http_message' => '$file not exists in your system', '__line' => __LINE__);
            return false;
        }
    }

    public function downloadstream($file, $path = '', $shared = false, $owner_email = '')
    {
        $endpoint = self::sia_cloud_tech_endpoint . self::downloadstream_endpoint;
        $method = self::downloadstream_method;


        if ($path == '') {
            $path = '/'.basename($file);
        }
        $query = array(
            'path' => $path,
            'shared' => $shared ? 'yes' : 'no',
            'email' => $shared ? $owner_email : ''
        );
  
        $option = array('downloadstream_file' => $file);

        $reponse = $this->__request($endpoint, $method, $query, $option);
        if ($reponse) {
            return $reponse;
        } else {
            return false;
        }
    }


    # INTERNAL SECTION

    private function __build($__query_array, $numeric_prefix = '', $arg_separator = '&', $enc_type = PHP_QUERY_RFC1738)
    {
        if ($__query_array) {
            $__query_string = http_build_query($__query_array, $numeric_prefix, $arg_separator, $enc_type);
            return $__query_string  != '' ? $__query_string : false;
        } else {
            $this->last_error = array ('http_code' => '' , 'http_message' => 'empty query array', '__line' => __LINE__);
            return false;
        }
    }

    private function __sign($__query_string)
    {
        if ($this->api_secret) {
            $__query_hash = hash_hmac('sha256', $__query_string, $this->api_secret);
            return $__query_hash != '' ? $__query_hash : false;
        } else {
            $this->last_error = array ('http_code' => '' , 'http_message' => 'empty api secret', '__line' => __LINE__);
            return false;
        }
    }
    
    private function __request($__endpoint, $__method, $__query = array(), $__option = null)
    {
        if (is_array($__query)) {
            if (!array_key_exists('timestamp', $__query)) {
                $__query['timestamp'] = time()*1000;
            }

            $__query_build = $this->__build(array_filter($__query));

            if ($__query_build) {
                $__query_signature = $this->__sign($__query_build);
                if ($__query_signature) {
                    $__query_build = $__query_build . '&' . 'signature'.'='.$__query_signature;
                } else {
                    $this->last_error = array ('http_code' => '' , 'http_message' => 'error calculate query signature', '__line' => __LINE__);
                    return false;
                }
            } else {
                $this->last_error = array ('http_code' => '' , 'http_message' => 'error build query array', '__line' => __LINE__);
                return false;
            }
        } else {
            $this->last_error = array ('http_code' => '' , 'http_message' => 'invalid query param', '__line' => __LINE__);
            return false;
        }

        
        curl_setopt_array($this->curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        switch ($__method) {
            case self::default_get_method:
                curl_setopt_array($this->curl, [
                    CURLOPT_URL => $__endpoint.'?'.$__query_build,
                    CURLOPT_CUSTOMREQUEST => "GET",
                ]);
                break;
            case self::default_post_method:
                curl_setopt_array($this->curl, [
                    CURLOPT_URL => $__endpoint,
                    CURLOPT_POST => 1,
                    CURLOPT_POSTFIELDS => $__query_build
                ]);
                break;
            case self::subscription_method:
                curl_setopt_array($this->curl, [
                    CURLOPT_URL => $__endpoint.'?'.$__query_build,
                    CURLOPT_CUSTOMREQUEST => "GET",
                ]);

                break;
            case self::cloud_list_method:
                curl_setopt_array($this->curl, [
                    CURLOPT_URL => $__endpoint.'?'.$__query_build,
                    CURLOPT_CUSTOMREQUEST => "GET",
                ]);
                break;
            case self::cloud_file_method:
                curl_setopt_array($this->curl, [
                    CURLOPT_URL => $__endpoint.'?'.$__query_build,
                    CURLOPT_CUSTOMREQUEST => "GET",
                ]);
                break;
            case self::cloud_delete_method:
                curl_setopt_array($this->curl, [
                    CURLOPT_URL => $__endpoint.'?'.$__query_build,
                    CURLOPT_CUSTOMREQUEST => "POST",
                ]);
                break;
            case self::uploadstream_method:
                ini_set('max_execution_time', 0);
                if (is_array($__option) && array_key_exists('uploadstream_file', $__option)) {
                    $uploadstream_file = $__option ['uploadstream_file'];
                    if (file_exists($uploadstream_file)) {
                        $stream = fopen($uploadstream_file, 'r');
                        if ($stream) {
                            $size = fstat($stream)['size'];
                        } else {
                            $this->last_error = array ('http_code' => '' , 'http_message' => 'fopen() faild', '__line' => __LINE__);
                            return false;
                        }
                    } else {
                        $this->last_error = array ('http_code' => '' , 'http_message' => 'file_exists() faild', '__line' => __LINE__);
                        return false;
                    }
                } else {
                    $this->last_error = array ('http_code' => '' , 'http_message' => 'invalid upload option parameter', '__line' => __LINE__);
                    return false;
                }

                curl_setopt_array($this->curl, [
                    CURLOPT_NOPROGRESS => 0,
                    CURLOPT_URL => $__endpoint.'?'.$__query_build,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_UPLOAD => 1,
                    CURLOPT_HTTPHEADER => array('Transfer-Encoding: chunked'),
                ]);

                curl_setopt($this->curl, CURLOPT_READFUNCTION, function ($ch, $fd, $length) use ($stream) {
                    if (feof($stream) == false) {
                        return fread($stream, $length) ?: false;
                    } else {
                        return false;
                    }
                });
                $__progress = 0;
                $__this = $this;
                curl_setopt($this->curl, CURLOPT_PROGRESSFUNCTION, function ($resource, $download_size, $downloaded, $upload_size, $uploaded) use ($__this,$size,&$__progress) {
                    $progress = floor($uploaded / $size * 100);
                    if ($progress > $__progress) {
                        if (isset($__this->progress_callback)) {
                            ($__this->progress_callback)($progress);
                        }

                        $__progress = $progress;
                        
                        # ob_flush();
                        # flush();
                    }
                });
                
                break;
            case self::downloadstream_method:
                ini_set('max_execution_time', 0);
                if (is_array($__option) && array_key_exists('downloadstream_file', $__option)) {
                    $downloadstream_file = $__option ['downloadstream_file'];
                    $stream = fopen($downloadstream_file, 'w+');
                    if (!$stream) {
                        $this->last_error = array ('http_code' => '' , 'http_message' => 'fopen() faild', '__line' => __LINE__);
                        return false;
                    }
                } else {
                    $this->last_error = array ('http_code' => '' , 'http_message' => 'invalid download option parameter', '__line' => __LINE__);
                    return false;
                }
        

                curl_setopt_array($this->curl, [
                    CURLOPT_NOPROGRESS => 0,
                    CURLOPT_URL => $__endpoint.'?'.$__query_build,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_BINARYTRANSFER => true,
                    CURLOPT_FILE => $stream,
                    CURLOPT_FOLLOWLOCATION => true,
                ]);
                $__progress = 0;
                $__this = $this;
                curl_setopt($this->curl, CURLOPT_PROGRESSFUNCTION, function ($resource, $download_size, $downloaded, $upload_size, $uploaded) use ($__this,&$__progress) {
                    if ($download_size > 0) {
                        $progress = floor($downloaded / $download_size * 100);
                    } else {
                        $progress = $downloaded;
                    }
                    if ($progress > $__progress) {
                        if (isset($__this->progress_callback)) {
                            ($__this->progress_callback)($progress);
                        }
                        $__progress = $progress;
                        
                        # ob_flush();
                        # flush();
                    }
                });
                break;
            default:
                $this->last_error = array ('http_code' => '' , 'http_message' => 'undefined request method', '__line' => __LINE__);
                return false;
                break;
        }
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('X-MBX-APIKEY: '.$this->api_key));
        
        $data = curl_exec($this->curl);
        if (curl_errno($this->curl)) {
            $this->last_error = array ('http_code' => '' , 'http_message' => 'curl execution faild', '__line' => __LINE__);
            return false;
        }

        $http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        if ($http_code >= 200 && $http_code <= 299) {
            return $data ?: true;
        } elseif ($http_code >= 400 && $http_code <= 599) {
            $this->last_error = array ('http_code' => $http_code , 'http_message' => $data , '__line' => __LINE__);
            return false;
        } else {
            $this->last_error = array ('http_code' => $http_code , 'http_message' => $data , '__line' => __LINE__);
            return false;
        }
    }
}