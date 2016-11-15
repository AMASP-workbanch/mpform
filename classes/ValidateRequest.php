<?php

/**
 * WebsiteBaker CMS module: mpForm
 * ===============================
 * This module allows you to create customised online forms, such as a feedback form with file upload and customizable email notifications. mpForm allows forms over one or more pages, loops of forms, conditionally displayed sections within a single page, and many more things.  User input for the same session_id will become a single row in the submitted table.  Since Version 1.1.0 many ajax helpers enable you to speed up the process of creating forms with this module. Since 1.2.0 forms can be imported and exported directly in the module.
 *  
 * @category            page
 * @module              mpform
 * @version             1.3.1.2
 * @authors             Frank Heyne, NorHei(heimsath.org), Christian M. Stefan (Stefek), Martin Hecht (mrbaseman) and others
 * @copyright           (c) 2009 - 2016, Website Baker Org. e.V.
 * @url                 http://forum.websitebaker.org/index.php/topic,28496.0.html
 * @url                 https://github.com/WebsiteBaker-modules/mpform
 * @url                 https://forum.wbce.org/viewtopic.php?id=661
 * @license             GNU General Public License
 * @platform            2.8.x
 * @requirements        probably php >= 5.3?
 *
 **/
 
namespace mpForm;

class ValidateRequest
{

	/**
	 *	Private internal version of this class
	 */
	const version = "0.1.0.0";
	
	/**
     * @var Singleton The reference to *Singleton* instance of this class
     */
    private static $instance;

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
	 *
	 *		if "values" is set, the value itself has to be one of the given values inside the array. 
	 */
	static public function testValues( $fields )
	{
	
		if (null === static::$instance)
        {
            static::$instance = new static();
        }
		foreach( $fields as $key=>$options)
		{
			if( false === static::$instance->validateValue( $key, $options, $_POST ) )
			{
				return false;
			}
		}
		return true;
	}
	
	static public function validateValue( $sName, &$options=array(), &$aSource = array() ) 
	{
		$bRetValue = true;

		if( !isset($aSource[ $sName ]) )
		{
			$bRetValue = false;
		}

		switch( $options['type'] )
		{
			case 'not_0':
				if( $aSource[ $sName ] === 0 ) $bRetValue = false;
				break;
				
			case 'int':
				if( !is_numeric( $aSource[ $sName ] )) 
				{
					$bRetValue = false;
				}
				elseif (isset($options['values']))
				{
					if(!in_array( $aSource[ $sName ], $options['values'] ))
					{
						$bRetValue = false;
					}
				}	
				break;
				
			case 'str':
				if(!is_string( $aSource[ $sName ]))
				{
					$bRetValue = false;
				}
				elseif (isset($options['values']))
				{
					if(!in_array( $aSource[ $sName ], $options['values'] ))
					{
						$bRetValue = false;
					}
				}
				break;
			
			default:
				$bRetValue = false;
		}
		
		return $bRetValue;
	}
}

?>