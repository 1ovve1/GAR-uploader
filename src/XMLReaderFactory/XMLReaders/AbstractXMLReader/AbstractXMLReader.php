<?php declare(strict_types=1);

namespace GAR\Uploader\Readers\AbstractXMLReader;

use GAR\Uploader\Readers\AbstractXMLReader\CustomReader;


/**
 * ABSTRACTXMLREADER INTERFACE
 *
 * DEFINES AND IMPLEMENTS SOME METHODS
 * THAT NEEDS TO PARSE XML FILE
 */
abstract class AbstractXMLReader implements \Iterator
{
	/**
	 *  path to concrete zip file
	 * @var string
     */
	protected string $pathToZip;

	/**
	 *  path to cache floder
	 * @var string
     */
	protected string $cachePath;

	/**
	 *  xml-file name
	 * @var string
     */
	protected string $fileName;

	/**
	 *  full path to xml in cache floder
	 * @var string|null
     */
	protected ?string $pathToXml = null;

	/**
	 *  XMLReader object
	 * @var \XMLReader|null
     */
	protected ?\XMLReader $reader = null;

	/**
	 *  elems of xml-file that we will parse
	 * @var array|null
     */
	protected ?array $elems = null;

	/**
	 *  attributes of xml-file that we will parse
	 * @var array|null
     */
	protected ?array $attrs = null;


	/**
	 *  basic constructor using concrete paths to prepare
	 *  path-fields
	 * @param string $pathToZip zip file
	 * @param string $fileName  name of xml file
	 * @param string $cachePath temporary floder
	 */
	function __construct(string $pathToZip, 
						 string $fileName,
						 string $cachePath)
	{
		$this->pathToZip = $pathToZip;
		$this->fileName = $fileName;
		$this->cachePath = $cachePath;
	}

	/**
	 *  remove temp files then process is finished
	 */
	function __destruct()
	{
		if (!is_null($this->pathToXml) && file_exists($this->pathToXml)) {
			unlink($this->pathToXml);
			rmdir($this->cachePath);
		}
	}

	/**
	 * extract xml-file from zip, getting right names 
	 * of xml-file (if we're getting cut string by
	 * the fileName) and init fields if children object
	 * implement interface CustomObject
	 * @return void
	 */
	public function init() : void
	{
		if (is_null($this->pathToXml)) {
				// extracting xml from zip
			$this->pathToXml = $this->extractFileFromZip($this->pathToZip, $this->fileName, $this->cachePath);

			// getting some paths
			$dropPath = explode('/', $this->pathToXml);
			$this->fileName = array_pop($dropPath);
			$this->cachePath = implode('/', $dropPath);

			// getting info about elemetns and attributes
			$chiledClass = get_class($this); 
			if (in_array(CustomReader::class, class_implements($chiledClass))) {
				if (is_null($this->elems)) {
					$this->elems = call_user_func($chiledClass . '::getElements');
				}
				if (is_null($this->attrs)) {
					$this->attrs = call_user_func($chiledClass . '::getAttributes');
				}
			}	
		}
	} 

	/**
	 *  Extracting concrete file from zip archive into temp floder
	 * @param  string $pathToZip  path to zip archive
	 * @param  string $fileName   name of file or path in zip
	 * @param  string $cachePath  path to temp floder
	 * @return string             return full path to extract file
	 */
	public abstract function extractFileFromZip(string $pathToZip, 
												string $fileName, 
												string $cachePath) : string;

    /**
     *  Method for open xml files from the path param
     * @param string $pathToXml path to the concrete xml file
     * @return \XMLReader|bool XMLReader object or false
     */
	public abstract function openXML(string $pathToXml) : \XMLReader|bool;

	/**
	 * 	ITERATORS METHODS
	 */
	
	/**
	 *  findes concrete node element with concrete attributes or return outer xml string
	 * @return array mapping attributes of node
	 */
	public abstract function current(): mixed;

	/**
	 * 	no available
	 * @return null
	 */
	public abstract function key(): mixed;

	/**
	 *  searching next node with xml element or make reader null
	 * @return void
	 */
	public abstract function next(): void;

	/**
	 *  init the xmlreader
	 * @return void
	 */
	public abstract function rewind(): void;

	/**
	 *  check if reader is null
	 * @return bool  flag for end-iteration
	 */
	public abstract function valid(): bool;
}