<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html 
*/

use SkyMigration\Document\TumblrConfig;
use SkyBase\Utils\DateUtils;

/**
 * HybridAuth storage manager
 */
class Hybrid_Storage_MongoDB
{
   /**
     * DocumentRepository to work with the session storage
     * 
     * @var \Doctrine\ODM\MongoDB\DocumentRepository 
     */
    protected $repository;
    
    /**
     * Provider id (facebook, tumblr or more)
     * 
     * @var string
     */
    protected $providerId;
    
    /**
     * Creates the new MongoDB storage attached to a particular repository
     * 
     * @param \Doctrine\ODM\MongoDB\DocumentRepository $repository
     * @param string $providerId
     */
	function __construct($repository, $providerId = NULL)
	{ 
        $this->repository = $repository;
        $this->providerId = $providerId;
      
		$this->config( "version", Hybrid_Auth::$version, $providerId );
	}

    /**
     * If only the key was given, function returns it's value.
     * If both the key and the value were given, it stores the
     * value under the given key.
     * 
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
	public function config( $key, $value = NULL, $providerId = NULL ) 
	{         
        if( $value ) {
            $this->CreateElement( $key, $value, $providerId );
        } else {
            return $this->findString( $key, $providerId );
        }
	}
    
    /**
     * Finds a string
     * 
     * @param string $key
     */
    public function get( $key ) 
	{
        return $this->findString( $key );
	}

    /**
     * Stores a value in a config
     * 
     * @param string $key
     * @param mixed $value
     */
	public function set( $key, $value, $providerId = null)
	{
         $this->CreateElement( $key, $value, $providerId = null);
	}

    /**
     * Clears the Hybrid Auth session
     */
    function clear()
	{ 
        $tumblrDocument = $this->repository->findOneBy(["type" => $this->providerId]);
        $tumblrDocument->hauthSession = [];
	} 
    
    /**
     * Delete all elements in db
     */
    function clearAll( $providerId )
    {
        return($this->clearTable( $providerId ));
    }

    /**
     * Delete one element on key
     * @param type $key
     */
    function delete( $key )
	{
		$key = strtolower( $key );  

		$this->deleteOnKey( $key );
	}
     
    /**
     * Delete one element on key
     * @param type $key
     */
    function deleteMatch( $key )
	{
		$key = strtolower( $key ); 

		$this->deleteOnKey( $key );
	}
     

    function getSessionData()
	{
//		if( isset( $_SESSION["HA::STORE"] ) ){ 
//			return serialize( $_SESSION["HA::STORE"] ); 
//		}

		return NULL; 
	}

    /*	function restoreSessionData( $sessiondata = NULL )
	{ 
        $this->fnSimpleXMLCreate(unserialize($sessiondata));
		$_SESSION["HA::STORE"] = unserialize( $sessiondata );
	} 
     */
    
    /**
     * Stores session token in configuration
     * 
     * @param type $key
     * @param type $value
     */
    function CreateElement( $key, $value, $providerId = null)
    {   
        $key = strtolower(  str_replace('.', '|', $key) );

        try {
            if (!$providerId) {
                $providerId = $this->providerId; 
            }

            $storageDocument = $this->repository->findOneBy(["type"=> $providerId]);

            if (!$storageDocument) {
                // Empty case (document not found, we need to create it)
                $storageDocument = new TumblrConfig();
                $storageDocument->type = $this->providerId ;
                $storageDocument->date = (new DateUtils())->dateTime();
            }
            
            if (!$storageDocument->hauthSession) {
                $storageDocument->hauthSession = [];
            }
            $storageDocument->hauthSession[$key] = $value;
                        
            $this->repository->getDocumentManager()->persist($storageDocument);
            $this->repository->getDocumentManager()->flush();
            

        } catch ( Exception $e ) {
            die ( $e );
        }
    }    
    
    /**
     * Find element on key
     * @param type $key
     * @throws Exception
     * @return type
     */
    function findString( $key, $providerId = null)
    {       
        $key = strtolower( str_replace('.', '|', $key) );

        if (!$providerId) {
            $providerId = $this->providerId; 
        }
        
        $storageDocument = $this->repository->findOneBy(["type" => $providerId]);

        if (array_key_exists($key, $storageDocument->hauthSession) ) {
            return $storageDocument->hauthSession[$key];
        }        
    }
    
    /**
     * Delete all element in table
     */
    function clearTable( $providerId )
    {
        try {
            $providerId = strtolower( trim( $providerId ) );
        
            $storageDocument = $this->repository->findOneBy(["type" => $providerId]);
            $storageDocument->hauthSession = [];
            $this->repository->getDocumentManager()->flush();
            
            return true;
        } catch (Exception $exc) {
            return false;
        }        
    }
    
    /**
     * Delete one element on key
     * @param type $key
     * @throws Exception
     */
    function deleteOnKey( $key, $providerId = null)
    {
        if (!$providerId) {
            $providerId = $this->providerId; 
        }
        
        $key = str_replace('.', '|', $key);
       
        $storageDocument = $this->repository->findOneBy(["type" => $providerId]);
        if (array_key_exists($key, $storageDocument->hauthSession) ) {
            unset($storageDocument->hauthSession[$key]);
            $this->repository->getDocumentManager()->flush();
        }
    }       
}