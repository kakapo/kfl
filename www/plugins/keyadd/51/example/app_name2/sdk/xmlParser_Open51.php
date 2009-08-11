<?php
/**
 * Class xmlParser_Open51.
 *
 * Parse a XML document into a nested array.
 * @author Ivan Enderlin <ivan.enderlin@hoa-project.net>
 * @copyright 2007 Ivan Enderlin.
 * @since PHP4
 * @version 0.4
 * @package Xml
 */

class xmlParser_Open51 {
	
	/**
	 * Xml parser container.
	 *
	 * @var resource parser
	 */
	var $parser;
	
	/**
	 * Parse result.
	 *
	 * @var array pOut
	 */
	var $pOut = array ();
	
	/**
	 * Contain the overlap tag temporarily .
	 *
	 * @var array track
	 */
	var $track = array ();
	
	/**
	 * Current tag level.
	 *
	 * @var string tmpLevel
	 */
	var $tmpLevel = '';
	
	/**
	 * Write result.
	 *
	 * @var string wOut
	 */
	var $wOut = '';
	
	/**
	 * Out put encoding
	 *
	 * @var string outencoding
	 */
	var $outencoding = 'GBK';
	
	/**
	 * It's used to judge whether the content of xml tag is "";
	 *
	 * @var string parseFlag
	 */
	var $parseFlag = "";
	
	/**
	 * parse
	 * Set the parser Xml and theses options.
	 * Xml file could be a string, a file, or curl.
	 * When the source is loaded, we run the parse.
	 * After, we clean all the memory and variables,
	 * and return the result in an array.
	 *
	 * @access  public
	 * @param   src       string    Source
	 * @param   encoding  string    Encoding type.
	 * @return  array
	 */
	function xmlParse($src, $encoding = 'UTF-8', $outencoding = "GBK") {
		
		if ($outencoding) {
			$this->outencoding = $outencoding;
		}
		$this->encoding = $encoding ? $encoding : "UTF-8";
		
		// ini;
		// (re)set array;
		$this->pOut = array ();
		$this->parser = xml_parser_create ();
		
		xml_parser_set_option ( $this->parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option ( $this->parser, XML_OPTION_TARGET_ENCODING, $this->encoding );
		
		xml_set_object ( $this->parser, $this );
		xml_set_element_handler ( $this->parser, 'startHandler', 'endHandler' );
		xml_set_character_data_handler ( $this->parser, 'contentHandler' );
		
		if (empty ( $src ))
			trigger_error ( 'Source could not be empty.', E_USER_ERROR );
			
		// parse $data;
		$parse = xml_parse ( $this->parser, $src );
		if (! $parse)
			return trigger_error ( 'XML Error : %s at line %d.', E_USER_ERROR, array (xml_error_string ( xml_get_error_code ( $this->parser ) ), xml_get_current_line_number ( $this->parser ) ) );
			
		// destroy parser;
		xml_parser_free ( $this->parser );
		
		// unset extra vars;
		unset ( $src, $this->track, $this->tmpLevel );
		
		// remove global tag and return the result;
		$arrTmp = $this->pOut [0] [key ( $this->pOut [0] )] [0];
		if (! is_array ( $arrTmp )) {
			$arrTmp = array ();
		}
		reset ( $arrTmp );
		return current ( $arrTmp );
	}
	
	/**
	 * startHandler
	 * Manage the open tag by callback.
	 * The purpose is to create a pointer : {{int ptr}}.
	 * If the pointer exists, we have a multi-tag situation.
	 * Tag name  is stocked like : '<tag>'
	 * Return true but built $this->pOut.
	 *
	 * @access  private
	 * @param   parser  resource    Parser resource.
	 * @param   tag     string      Tag name.
	 * @param   attr    array       Attribut.
	 * @return  bool
	 */
	function startHandler($parser, $tag, $attr) {
		$this->parseFlag = "start";
		// built $this->track;
		$this->track [] = $tag;
		// place pointer to the end;
		end ( $this->track );
		// temp level;
		$this->tmpLevel = key ( $this->track );
		
		// built $this->pOut;
		if (! isset ( $this->pOut [key ( $this->track )] [$tag] )) {
			$this->pOut [key ( $this->track )] [$tag] = '{{' . key ( $this->track ) . '}}';
		}
		return true;
	}
	
	/**
	 * contentHandler
	 * Detect the pointer, or the multi-tag by callback.
	 * If we have a pointer, the method replaces this pointer by the content.
	 * Else we have a multi-tag, the method add a element to this array.
	 * Return true but built $this->pOut.
	 *
	 * @access  private
	 * @param   parser          resource    Parser resource.
	 * @param   contentHandler  string      Tag content.
	 * @return  bool
	 */
	function contentHandler($parser, $contentHandler) {
		$this->parseFlag = "content";
		// remove all spaces;
		if (! preg_match ( '#^\\\\s*$#', $contentHandler )) {
			
			// $contentHandler is a string;
			if (is_string ( $this->pOut [key ( $this->track )] [current ( $this->track )] )) {
				
				// then $contentHandler is a pointer : {{int ptr}}     case 1;
				if (preg_match ( '#{{([0-9]+)}}#', $this->pOut [key ( $this->track )] [current ( $this->track )] )) {
					if ($this->outencoding) {
						$contentHandler = iconv ( $this->encoding, $this->outencoding, $contentHandler );
					}
					$this->pOut [key ( $this->track )] [current ( $this->track )] = $contentHandler;
				} // or then $contentHandler is a multi-tag content      case 2;
else {
					$this->pOut [key ( $this->track )] [current ( $this->track )] = array (0 => $this->pOut [key ( $this->track )] [current ( $this->track )], 1 => $contentHandler );
				}
			} // or $contentHandler is an array;
else {
				
				// then $contentHandler is the multi-tag array         case 1;
				if (isset ( $this->pOut [key ( $this->track )] [current ( $this->track )] [0] ))
					$this->pOut [key ( $this->track )] [current ( $this->track )] [] = $contentHandler;
					
				// or then $contentHandler is a node-tag               case 2;
				else
					$this->pOut [key ( $this->track )] [current ( $this->track )] = array (0 => $this->pOut [key ( $this->track )] [current ( $this->track )], 1 => $contentHandler );
			}
		
		}
		
		return true;
	}
	
	/**
	 * endHandler
	 * Detect the last pointer by callback.
	 * Move the last tags block up.
	 * And reset some temp variables.
	 * Return true but built $this->pOut.
	 *
	 * @access  private
	 * @param   parser  resource    Parser resource.
	 * @param   tag     string      Tag name.
	 * @return  bool
	 */
	function endHandler($parser, $tag) {
		
		if ($this->parseFlag == "start") { // ����Ϊ�յ�ʱ�򲻻���� contentHandler() ����
			$this->pOut [key ( $this->track )] [current ( $this->track )] = "";
			$this->parseFlag == "content";
		}
		// if level--;
		if (key ( $this->track ) == $this->tmpLevel - 1) {
			// search up tag;
			// use array_keys if an empty tag exists (taking the last tag);
			

			// if it's a normal framaset;
			$keyBack = array_keys ( $this->pOut [key ( $this->track )], '{{' . key ( $this->track ) . '}}' );
			$count = count ( $keyBack );
			if ($count != 0) {
				$keyBack = $keyBack {$count - 1};
				// move this level up;
				$this->pOut [key ( $this->track )] [$keyBack] = array ($this->pOut [key ( $this->track ) + 1] );
			} // if we have a multi-tag framaset ($count == 0);
else {
				// if place is set;
				if (isset ( $this->pOut [key ( $this->track )] [current ( $this->track )] [0] )) {
					
					// if it's a string, we built an array;
					if (is_string ( $this->pOut [key ( $this->track )] [current ( $this->track )] ))
						$this->pOut [key ( $this->track )] [current ( $this->track )] = array (0 => $this->pOut [key ( $this->track )] [current ( $this->track )], 1 => $this->pOut [key ( $this->track ) + 1] );
						
					// 					else
					$this->pOut [key ( $this->track )] [current ( $this->track )] [] = $this->pOut [key ( $this->track ) + 1];
				} // 				else
				$this->pOut [key ( $this->track )] [current ( $this->track )] = array (0 => $this->pOut [key ( $this->track )] [current ( $this->track )], 1 => $this->pOut [key ( $this->track ) + 1] );
			}
			
			// kick $this->pOut level out;
			array_pop ( $this->pOut );
			end ( $this->pOut );
		}
		
		// re-temp level;
		$this->tmpLevel = key ( $this->track );
		
		// kick $this->track level out;
		array_pop ( $this->track );
		end ( $this->track );
		
		return true;
	}
}

?>