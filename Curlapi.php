<?php
namespace AP;

Class Curlapi {

    private $Url = '';
    private $Api = '';
    private $Header = [];
    private $Result;
    private $Success = false;
    private $ResponseCode = false;
    private $Debug = false;

    /**
     * Constructor class with params
     * @param $url string url to api 
     * @param $api string api key or code
     * @author Anil <anil@asmex.digital>
     * @return Object Class $this
     */
    public function __construct( $url = false, $api = false )
    {
        if( !empty( $api ) )
        {
            $this->Api = $api;
        }
        if( !empty( $url ) )
        {
            $this->Url = $url;
        }
    }

    /**
     * Set Url
     *
     * @param string $url
     * @return Object Class $this
     * @author Anil Prajapati <anil@asmex.io>
     * @version 1.0
     */
    public function setUrl( $url )
    {
        $this->Url = $url;
        return $this;
    }

    /**
     * Get Url
     *
     * @return void
     * @author Anil Prajapati <anil@asmex.io>
     * @version 1.0
     */
    public function getUrl()
    {
        return $this->Url;
    }

    /**
     * Set API key
     *
     * @param string $api
     * @return Object Class $this
     * @author Anil Prajapati <anil@asmex.io>
     * @version 1.0
     */
    public function setApi( $api )
    {
        $this->Api = $api;
        return $this;
    }

    /**
     * Get Api 
     *
     * @return void
     * @author Anil Prajapati <anil@asmex.io>
     * @version 1.0
     */
    public function getApi()
    {
        return $this->Api;
    }

    /**
     * Set Request curl header for JSON type and Basic Authentication
     * Returns Class objects for chain methods
     *
     * @param String $header
     * @return Object Class $this
     * @author Anil Prajapati <anil@asmex.io>
     * @version 1.0
     */
    public function setHeader( $header, $length = '', $custom = false, $index = false )
    {
        if( $header == 'json' )
        {
            $this->Header[0] = 'Content-Type: application/json';
        }
        if( $header == 'authorization' )
        {
            $this->Header[1] = 'Authorization: Basic '.base64_encode( 'user: '. $this->getApi() );
        }
        if( $header == 'authorizationbearer' )
        {
            $this->Header[1] = 'Authorization: Bearer '.base64_encode( $this->getApi() );
        }
        if( !empty( $length ) )
        {
            $this->Header[0] = 'Content-Type: application/json';
        }
        if( $header == "multipart-form" )
        {
            $this->Header[0] = 'Content-Type: multipart/form-data';
        }
        if( $custom === true )
        {
            if( $index !== false )
            {
                $this->Header[ $index ] = $header;
            } else 
            {
                $this->Header[] = $header;
            }
        }
        
        return $this;
    }

    /**
     * Get Header 
     *
     * @return void
     * @author Anil Prajapati <anil@asmex.io>
     * @version 1.0
     */
    public function getHeader()
    {
        return $this->Header;
    }

    /**
     * Clear header data
     *
     * @return Object Class $this
     * @author Anil Prajapati <anil@asmex.io>
     * @version 1.0
     */
    public function unsetHeader()
    {
        $this->Header = [];
        return $this;
    }

    /**
     * Send API request
     *
     * @param string $url
     * @param array $postFields
     * @param integer $timeout
     * @param boolean $ssl
     * @param string $dataConversion json|urlencoded
     * @return Object Class $this
     * @author Anil Prajapati <anil@asmex.io>
     * @version 1.0
     */
    public function request( $method = 'GET', $postFields = [], $timeout = 10, $ssl = false, $dataConversion = "json" )
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->getUrl() );

            if( !empty( $this->getHeader() ) )
            {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeader() );
            }
            curl_setopt($ch, CURLOPT_USERAGENT, 'AP-WEBAPI/1.0');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

            switch ($method) {
                case "POST":
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    break;
                case "PUT":
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                    break;
                case "DELETE":
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 
                    break;
                case "GET":
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                    break;
                default:
                   throw new Exception ( 'Unsupported API request.' );
                   break;
            }

            if( !empty( $postFields ) ) 
            {
                if( $dataConversion == "urlencoded" )
                {
                    curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $postFields ) );
                } else {
                    curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $postFields ) );
                }
            }

            if( $this->getDebug() )
            {
                $fp = fopen( PUBLIC_HTML_PATH.'/temp/curl.debug', 'a');
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_STDERR, $fp);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl);
            $this->Result = curl_exec($ch);
            $this->ResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $this->Success = true;
            
            //curl_close($ch);

        } catch ( Exception $e ) {
            $this->Result = $e->getMessage();
            $this->Success = false;
        }
        return $this;
    }

    /**
     * Get API response result
     *
     * @return Object|Array|Boolean Curl result
     * @author Anil Prajapati <anil@asmex.io>
     * @version 1.0
     */
    public function getResponse()
    {
        return $this->Result;
    }

    /**
     * Json Decode API response
     *
     * @return Object|Array|Boolean Curl result
     * @author Anil Prajapati <anil@asmex.io>
     * @version 1.0
     */
    public function decodeResponse( $array = false )
    {
        if( $this->Success ) return json_decode( $this->Result, $array );

        return false;
    }

    /**
     * Status of success|failure API request
     *
     * @return Boolean
     * @author Anil Prajapati <anil@asmex.io>
     * @version 1.0
     */
    public function getStatus()
    {
        return $this->Success;
    }

    /**
     * Status Code of response from API request
     *
     * @return Integer
     * @author Anil Prajapati <anil@asmex.io>
     * @version 1.0
     */
    public function getResponseCode()
    {
        return $this->ResponseCode;
    }

    public function setDebug( $set )
    {
        $this->Debug = $set;
        return $this;
    }
    
    public function getDebug()
    {
        return $this->Debug;
    }

}

?>