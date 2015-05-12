<?php
/**
 * Created by PhpStorm.
 * User: vagenas
 * Date: 16/10/2014
 * Time: 12:03 μμ
 */

namespace skroutz;

use xd_v141226_dev\exception;

if ( ! defined( 'WPINC' ) ) {
	exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );
}

class xml extends \xd_v141226_dev\xml {
	/**
	 * @var array
	 */
	protected $skzXMLFields = array(
		'id',
		'name',
		'link',
		'image',
		'category',
		'price_with_vat',
		'instock',
		'availability',
		'manufacturer',
		'mpn',
		'isbn',
		'size',
		'color',
	);

	/**
	 * @var array
	 */
	protected $skzXMLFieldsLengths = array(
		'id'             => 200,
		'name'           => 300,
		'link'           => 1000,
		'image'          => 400,
		'category'       => 250,
		'price_with_vat' => 0,
		'instock'        => 0,
		'availability'   => 60,
		'manufacturer'   => 100,
		'mpn'            => 80,
		'isbn'           => 80,
		'size'           => 500,
		'color'          => 100,
	);

	/**
	 * @var array
	 */
	protected $skzXMLRequiredFields = array(
		'id',
		'name',
		'link',
		'image',
		'category',
		'price_with_vat',
		'instock',
		'availability',
		'manufacturer',
		'mpn',
	);

	/**
	 * @var \SimpleXMLExtended
	 */
	public $simpleXML = null;

	/**
	 * Absolute file path
	 *
	 * @var string
	 */
	public $fileLocation = '';

	/**
	 * @var null
	 */
	public $createdAt = null;
	/**
	 * @var string
	 */
	public $createdAtName = 'created_at';

	/**
	 * @var string
	 */
	protected $rootElemName = 'mywebstore';
	/**
	 * @var string
	 */
	protected $productsElemWrapper = 'products';
	/**
	 * @var string
	 */
	protected $productElemName = 'product';

	/**
	 * @param array $array
	 *
	 * @return bool
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141015
	 */
	public function parseArray( Array $array ) {
		// init simple xml if is not initialized already
		if ( ! $this->simpleXML ) {
			$this->initSimpleXML();
		}

		// parse array
		foreach ( $array as $k => $v ) {
			$this->appendProduct( $v );
		}

		return ! empty( $array ) && $this->saveXML();
	}

	/**
	 * @param array $p
	 *
	 * @return int
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150130
	 */
	public function appendProduct( Array $p ) {
		if ( ! $this->simpleXML ) {
			$this->initSimpleXML();
		}

		$products = $this->simpleXML->children();

		$validated = $this->validateArrayKeys( $p );

		if ( ! empty( $validated ) ) {
			$product = $products->addChild( $this->productElemName );

			foreach ( $validated as $key => $value ) {
				if ( $this->isValidXmlName( $value ) ) {
					$product->addChild( $key, $value );
				} else {
					$product->$key = null;
					$product->$key->addCData( $value );
				}
			}

			return 1;
		}

		return 0;
	}

	/**
	 * @return $this
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141015
	 */
	protected function initSimpleXML() {
		$this->fileLocation = $this->getFileLocation();

		$this->simpleXML = new \SimpleXMLExtended( '<?xml version="1.0" encoding="UTF-8"?><' . $this->rootElemName . '></' . $this->rootElemName . '>' );
		$this->simpleXML->addChild( $this->productsElemWrapper );

		return $this;
	}

	/**
	 * @param array $array
	 *
	 * @return array
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141015
	 */
	protected function validateArrayKeys( Array $array ) {
		foreach ( $this->skzXMLRequiredFields as $fieldName ) {
			if ( ! isset( $array[ $fieldName ] ) || empty( $array[ $fieldName ] ) ) {
				$fields = array();
				foreach ( $this->skzXMLRequiredFields as $f ) {
					if ( ! isset( $array[ $f ] ) || empty( $array[ $f ] ) ) {
						array_push( $fields, $f );
					}
				}
				$name = isset( $array['name'] ) ? $array['name'] : ( isset( $array['id'] ) ? 'with id ' . $array['id'] : '' );
				$this->©diagnostic->forceDBLog(
					'product',
					$array,
					'Product <strong>' . $name . '</strong> not included in XML file because field(s) ' . implode( ', ', $fields ) . ' is/are missing or is invalid'
				);

				return array();
			} else {
				$array[ $fieldName ] = $this->trimField( $array[ $fieldName ], $fieldName );
				if ( is_string( $array[ $fieldName ] ) ) {
					$array[ $fieldName ] = mb_convert_encoding( $array[ $fieldName ], "UTF-8" );
				}
			}
		}

		foreach ( $array as $k => $v ) {
			if ( ! in_array( $k, $this->skzXMLFields ) ) {
				unset( $array[ $k ] );
			}
		}

		return $array;
	}

	protected function isValidXmlName( $name ) {
		try {
			new \DOMElement( $name );

			return true;
		} catch ( \DOMException $e ) {
			return false;
		}
	}

	/**
	 * @param $value
	 * @param $fieldName
	 *
	 * @return bool|string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141015
	 */
	protected function trimField( $value, $fieldName ) {
		if ( ! isset( $this->skzXMLFieldsLengths[ $fieldName ] ) ) {
			return false;
		}

		if ( $this->skzXMLFieldsLengths[ $fieldName ] === 0 ) {
			return $value;
		}

		return substr( (string) $value, 0, $this->skzXMLFieldsLengths[ $fieldName ] );
	}


	/**
	 * @return bool
	 * @throws exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141015
	 */
	protected function loadXML() {
		/**
		 * For now we write it from scratch EVERY TIME
		 */
		$this->fileLocation = $this->getFileLocation();

//		if(file_exists($this->fileLocation)){
//			$this->simpleXML = new \SimpleXMLExtended(simplexml_load_file($this->fileLocation)->asXML());
//			return true;
//		}

		return false;

		try {
			$locate = $this->©dirs_files->locate( $fileLocation, get_home_path() );
		} catch ( exception $e ) {
			return false;
		}

		if ( ! empty( $locate ) && file_exists( $locate ) && is_readable( $locate ) ) {
			$this->simpleXML = simplexml_load_file( $locate );
			if ( $this->simpleXML !== false ) {
				$this->fileLocation = $locate;

				return true;
			}
		} else {
			// Assuming ABSPATH is writable
			$this->fileLocation = $this->getFileLocation;
		}

		return false;
	}

	/**
	 * @param       $prodId
	 * @param array $newValues
	 *
	 * @return bool|mixed
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141015
	 */
	public function updateProductInXML( $prodId, Array $newValues ) {
		$newValues = $this->validateArrayKeys( $newValues );
		if ( empty( $newValues ) ) {
			return false;
		}
		// init simple xml if is not initialized already
		if ( ! $this->simpleXML ) {
			$this->initSimpleXML();
		}

		$p = $this->locateProductNode( $prodId );
		if ( ! $p ) {
			$p = $this->simpleXML->products->addChild( $this->productElemName );
		}
		foreach ( $newValues as $key => $value ) {
			if ( $this->isValidXmlName( $value ) ) {
				$p->addChild( $key, $value );
			} else {
				$p->$key = null;
				$p->$key->addCData( $value );
			}
		}

		return $this->saveXML();
	}

	/**
	 * @param $nodeId
	 *
	 * @return bool
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141015
	 */
	protected function locateProductNode( $nodeId ) {
		if ( ! ( $this->simpleXML instanceof \SimpleXMLElement ) ) {
			return false;
		}

		foreach ( $this->simpleXML->products->product as $k => $p ) {
			if ( $p->id == $nodeId ) {
				return $p;
			}
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141015
	 */
	public function saveXML() {
		if ( ! ( $this->simpleXML instanceof \SimpleXMLExtended ) ) {
			return false;
		}
		$dir = dirname( $this->fileLocation );
		if ( ! file_exists( $dir ) ) {
			mkdir( $dir, 0755, true );
		}

		if ( $this->simpleXML && ! empty( $this->fileLocation ) && ( is_writable( $this->fileLocation ) || is_writable( $dir ) ) ) {
			if ( is_file( $this->fileLocation ) ) {
				unlink( $this->fileLocation );
			}
			$this->simpleXML->addChild( $this->createdAtName, date( 'Y-m-d H:i' ) );

			return $this->simpleXML->asXML( $this->fileLocation );
		}

		return false;
	}

	/**
	 * Print SimpleXMLElement $this->simpleXML to screen
	 *
	 * @throws exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141015
	 */
	public function printXML() {
		if ( headers_sent() ) {
			return;
		}

		if ( ! ( $this->simpleXML instanceof \SimpleXMLExtended ) ) {
			$fileLocation = $this->getFileLocation();
			if ( ! $this->existsAndReadable( $fileLocation ) ) {
				return;
			}
			$this->simpleXML = simplexml_load_file( $fileLocation );
		}

		header( "Content-Type:text/xml" );

		echo $this->simpleXML->asXML();

		exit( 0 );
	}

	/**
	 * Returns the file location based on settings (even if it isn't exists)
	 *
	 * @return string
	 * @throws exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141015
	 */
	public function getFileLocation() {
		$location = $this->©options->get( 'xml_location' );
		$fileName = $this->©options->get( 'xml_fileName' );

		$location = empty( $location ) || $location == '/' ? '' : ( trim( $location, '\\/' ) . '/' );

		return rtrim( ABSPATH, '\\/' ) . '/' . $location . trim( $fileName, '\\/' );
	}

	/**
	 * Get XML file info
	 *
	 * @return array|null
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141015
	 */
	public function getFileInfo() {
		$fileLocation = $this->getFileLocation();

		if ( $this->existsAndReadable( $fileLocation ) ) {
			$info = array();

			$sXML         = simplexml_load_file( $fileLocation );
			$cratedAtName = $this->createdAtName;

			$info[ $this->createdAtName ] = array(
				'value' => end( $sXML->$cratedAtName ),
				'label' => 'Cached File Creation Datetime'
			);

			$info['productCount'] = array(
				'value' => $this->countProductsInFile( $sXML ),
				'label' => 'Number of Products Included'
			);

			$info['cachedFilePath'] = array( 'value' => $fileLocation, 'label' => 'Cached File Path' );

			$info['url'] = array(
				'value' => $this->©url->to_wp_site_uri( str_replace( ABSPATH, '', $fileLocation ) ),
				'label' => 'Cached File Url'
			);

			$info['size'] = array( 'value' => filesize( $fileLocation ), 'label' => 'Cached File Size' );

			return $info;
		} else {
			return null;
		}
	}

	/**
	 * Counts total products in file
	 *
	 * @param $file string|\SimpleXMLExtended|\SimpleXMLElement
	 *
	 * @return int Total products in file
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141015
	 */
	public function countProductsInFile( $file ) {
		if ( $this->existsAndReadable( $file ) ) {
			$sXML = simplexml_load_file( $file );
		} elseif ( $file instanceof \SimpleXMLElement || $file instanceof \SimpleXMLExtended ) {
			$sXML = &$file;
		} else {
			return 0;
		}

		if ( $sXML->getName() == $this->productsElemWrapper ) {
			return $sXML->count();
		} elseif ( $sXML->getName() == $this->rootElemName ) {
			return $sXML->children()->children()->count();
		}

		return 0;
	}

	/**
	 * Checks if file exists and is readable
	 *
	 * @param $file string File location
	 *
	 * @return bool
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 141015
	 */
	protected function existsAndReadable( $file ) {
		return is_string( $file ) && file_exists( $file ) && is_readable( $file );
	}
} 