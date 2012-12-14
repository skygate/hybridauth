<?php
/*!
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html 
*/

/**
 * HybridAuth storage manager
 */
class Hybrid_Storage_Sqlite 
{
    protected $db = false;
    
	function __construct()
	{ 
        try {
            $this->db = new SQLite3( '/home/ariel/Dokumenty/tumblr.db' );
        } catch ( Exception $e ) {
            die( $e );
        }       

		//$this->config( "php_session_id", session_id() );
		$this->config( "version", Hybrid_Auth::$version );
	}

	public function config( $key, $value = NULL ) 
	{ 
		$key = strtolower( $key );
        
        if( $value ) {
            $this->CreateElement( $key, serialize( $value ) );
        }else{
            return unserialize( $this->findString( $key ) );
        }
	}
    
    public function get( $key ) 
	{
		$key = strtolower( $key ); 

        return @unserialize( $this->findString( $key ) );

		return NULL; 
	}

	public function set( $key, $value )
	{
		 $key = strtolower( $key ); 
        
         $this->CreateElement( $key, serialize( $value ) );
	}


    function clear()
	{ 
        return NULL;
	} 
    
    /**
     * Delete all elements in db
     */
    function clearAll( $providerId )
    {
        $this->clearTable( $providerId );
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
     * Create element in db
     * @param type $key
     * @param type $value
     */
    function CreateElement( $key, $value )
    {       
        try {
            $delete = $this->db->prepare( 'DELETE FROM tumblroptions WHERE tumblr_key = :key;' );
            $delete->bindParam( ':key', $key );
            $delete->execute();

            $stmt = $this->db->prepare( "INSERT INTO tumblroptions (tumblr_key, tumblr_value) VALUES (:tumblr_key, :tumblr_value);" );
            $stmt->bindParam( ':tumblr_key', $key );
            $stmt->bindParam( ':tumblr_value', $value );

            $stmt->execute();
        } catch ( Exception $e ) {
            die ( $e );
        }
    }    
    
    /**
     * Find element on key
     * @param type $key
     * @return type
     */
    function findString( $key )
    {        
        $sql    = 'SELECT * FROM tumblroptions WHERE tumblr_key = "'.$key.'";';        
        $result = $this->db->query( $sql );    

        foreach( $result->fetchArray( SQLITE3_BOTH ) as $key=>$value )
        {
            $val = $value;
        }        
        
         return $val;
    }
    
    /**
     * Delete all element in table
     */
    function clearTable( $providerId )
    {
        $sql    = 'SELECT tumblr_key FROM tumblroptions WHERE tumblr_key LIKE \'%.tumblr.%\';';        
        $result = $this->db->query($sql);    
        
        while ( $row = $result->fetchArray() ) {
            $delete = $this->db->prepare( 'DELETE FROM tumblroptions WHERE tumblr_key = :key;' );
            $delete->bindParam( ':key', $row[0] );
            if( $delete->execute() ){
                echo 'Delete element ' . $row[0];
            }
        }
    }
    
    /**
     * Delete one element on key
     * @param type $key
     */
    function deleteOnKey( $key )
    {
        $delete = $this->db->prepare( 'DELETE FROM tumblroptions WHERE tumblr_key = :key;' );
        $delete->bindParam( ':key', $key );
        $delete->execute();
    }
}
