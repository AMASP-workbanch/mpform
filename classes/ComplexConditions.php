<?php

/**
 *	Private experimental study
 *	
 *	@author		Dietrich Roland Pehlke
 *	@license	GNU General Public License
 *
 */

namespace LEPTON;

class ComplexConditions
{

	/**
	 *	Private internal version of this class
	 */
	const version = "0.1.0.0";
	
	/**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;

	private static $errors = array();
	
	private $text = array(
		'key not found'		=> "Key '%s' not found in source",
		'is null'			=> "Value of '%s* is 0!",
		'is_not_numeric'	=> "Value of '%s' is not numeric!",
		'is_not_given'		=> "Value of '%s' is not one of: %s.",
		'is_less'			=> "Value of '%s' is less than a minimum of %s.",
		'is_greater'		=> "Value of '%s' is greater than a maximum of %s." ,
		'is_no_string'		=> "Value of '%s' is not a string!",
		'is_empty'			=> "Value of '%s' is empty!",
		'pattern not match'	=> "Value of '%s' does not match pattern '%s'!"
	);
	
	/**
	 *	Return the »internal«
	 *
	 *	@param	array	Optional params
	 */
	public static function getInstance( &$aOptions=array() )
    {
        if (null === static::$instance)
        {
            static::$instance = new static();
            
        }
        
        return static::$instance;
    }

	/**
	 *	Public static function to test values thru a given array.
	 *
	 *	@param	array	A given array with the values to test against the $_POST array.
	 *	@return bool 	True, if ALL values match, false if one or more dismatch.
	 *
	 *	@notice:	Form of the "test"-array sould be like
	 *		$example = array(
	 *			'name1'	=> array('type' => 'str', 'values' => array('a','b','c')),
	 *			'page_id'	=> array('type' => 'int'),
	 *			'sec_id'	=> array('type' => 'int', 'values' => array(2,3,5,7,11))
	 *		);
	 *
	 *		supportet types are:
	 *		- not_0		Value as to be NOT 0
	 *		- int		Value has to be numeric
	 *		- str		Value has to be a string 
	 *		- str!		Value has to be not an empty string (at last one char)
	 *		- expr		Value has to match a regExpr ('pattern' has to be set!)
	 *
	 *		if "values" is set, the value itself has to be one of the given values inside the array. 
	 */
	public function testValues( $fields, &$aSource, $bAllErrors=false)
	{
        static::$errors = array();
        $iErrorCounter = 0;
		foreach( $fields as $key=>$options)
		{
			if( false === $this->validateValue( $key, $options, $aSource ) )
			{
				$iErrorCounter++;
				if( $bAllErrors=== false) return false;
			}
		}
		return ( 0 === $iErrorCounter );
	}
	
	/**
	 *	The (logical-) OR version of the method above:
	 *	if one "term"(-test) is true, the nethod results in true
	 *
	 *
	 */
	public function testValuesOR ( $fields, &$aSource )
	{
        static::$errors = array();
        
		foreach( $fields as $key=>$options)
		{
			if( true === static::$instance->validateValue( $key, $options, $aSource ) )
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 *	Function to test one given key against a given assoc. array.
	 *
	 *	@param	string	Any key we're looking for to test.
	 *	@param	array 	Assoc. array with the options for the "requested" value.
	 *	@param	array 	The source
	 *
	 *	@return	bool	True if the type match, otherwise false 
	 * 	
	 */
	public function validateValue( $sName, &$aOptions=array(), &$aSource = array() ) 
	{

		$bRetValue = true;

		if( !isset($aSource[ $sName ]) )
		{
			static::$errors[] = "Key '".$sName."' not exists!";
			$bRetValue = false;
		}
		else 
		{
			switch( $aOptions['type'] )
			{
				case 'not_0':
					if( $aSource[ $sName ] === 0 )
					{
						static::$errors[] = sprintf( $this->text['is_null'], $sName );
						$bRetValue = false;
					}
					break;
				
				case 'int':
					if( !is_numeric( $aSource[ $sName ] )) 
					{
						static::$errors[] = sprintf( $this->text['is_not_numeric'], $sName);
						$bRetValue = false;
					}
					elseif (isset($aOptions['values']))
					{
						if(!in_array( $aSource[ $sName ], $aOptions['values'] ))
						{
							static::$errors[] = sprintf( $this->text['is_not_given'], $sName, implode(",",$aOptions['values']) );
							$bRetValue = false;
						}
					}
					elseif (isset($aOptions['min']))
					{
						if( $aSource[ $sName ] < $aOptions['min']) {
							static::$errors[] = sprintf( $this->text['is_less'], $sName, $aOptions['min'] );
							$bRetValue = false;
						}
						elseif(isset($aOptions['max']))
						{
							if( $aSource[ $sName ] > $aOptions['max'])
							{
								static::$errors[] = sprintf( $this->text['is_greater'], $sName, $aOptions['max'] );
								$bRetValue = false;
							}
						}
					}
					elseif(isset($aOptions['max']))
					{
						if( $aSource[ $sName ] > $aOptions['max'])
						{
							static::$errors[] = sprintf( $this->text['is_greater'], $sName, $aOptions['max'] );
							$bRetValue = false;
						}
					}
					break;
				
				case 'str':
					if(!is_string( $aSource[ $sName ]))
					{
						static::$errors[] = sprintf( $this->text['is_no_string'], $sName );
					}
					elseif (isset($aOptions['values']))
					{
						if(!in_array( $aSource[ $sName ], $aOptions['values'] ))
						{
							static::$errors[] = sprintf( $this->text['is_not_given'], $sName, implode(", ",$aOptions['values'] ));
						}
					}
					break;
			
				case 'str!':
				case 'str not empty':
					if(!is_string( $aSource[ $sName ]))
					{
						static::$errors[] = sprintf( $this->text['is_no_string'], $sName )." [2]";
						$bRetValue = false;
					}
					elseif ( $aSource[ $sName ] === "" )
					{
						static::$errors[] = sprintf( $this->text['is_empty'], $sName )." [2]"; 
						$bRetValue = false;
					}
					break;
				
				case 'expr':
					if(!is_string( $aSource[ $sName ]))
					{
						static::$errors[] = sprintf( $this->text['is_no_string'], $sName )." [3]";
						$bRetValue = false;
					}
					elseif ( 1 !== preg_match($aOptions['pattern'], $aSource[ $sName]) )
					{
						static::$errors[] = sprintf( $this->text['pattern not match'], $sName, $aOptions['pattern'] );
						$bRetValue = false;
					}
					break;
			
				default:
					$bRetValue = false;
			}
		}
		return $bRetValue;
	}
	
	static public function getErrors( $sFiller="<br />") {
		return "\n".implode($sFiller, static::$errors)."\n";
	}
	
	static public function reset() {
		static::$errors = array();
	}

}

?>