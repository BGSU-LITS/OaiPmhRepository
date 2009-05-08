<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @author John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2009 John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once('OaiPmhRepository/XmlUtilities.php');
require_once('OaiPmhRepository/OaiIdentifier.php');
require_once('OaiPmhRepository/UtcDateTime.php');

/**
 * Abstract class on which all other metadata format handlers are based.
 * Includes logic for all metadata-independent record output.
 *
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
 */
abstract class OaiPmhRepository_Metadata_Abstract
{   
    /**
     * Item object for this record.
     */
    protected $item;
    
    /**
     * Parent DOMElement element for XML output.
     */
    protected $parentElement;
    
    /**
     * Owner DOMDocument of parent element.
     */
    protected $document;
    
    /**
     * Metadata_Abstract constructor
     *
     * Sets base class properties.
     *
     * @param Item item Item object whose metadata will be output.
     * @param DOMElement element Parent element for XML output.
     */
    public function __construct($item, $element)
    {
        $this->item = $item;
        $this->parentElement = $element;
        $this->document = $element->ownerDocument;
    }
    
    /**
     * Appends the record to the XML response.
     *
     * Adds both the header and metadata elements as children of a record
     * element, which is appended to the document.
     *
     * @uses appendHeader
     * @uses appendMetadata
     */
    public function appendRecord()
    {
        $record = $this->document->createElement('record');
        $this->parentElement->appendChild($record);
        
        // Sets the parent of the next append functions
        $this->parentElement = $record;
        $this->appendHeader();
        $this->appendMetadata();
    }
    
    /**
     * Appends the record's header to the XML response.
     *
     * Adds the identifier, datestamp and setSpec to a header element, and
     * appends in to the document.  
     *
     * @uses appendHeader
     * @uses appendMetadata
     */
    public function appendHeader()
    {
        $headerData['identifier'] = 
            OaiPmhRepository_OaiIdentifier::itemToOaiId($this->item->id);
        $headerData['datestamp'] =
            OaiPmhRepository_UtcDateTime::dbToUtc($this->item->modified);
        
        $collectionId = $this->item->collection_id;
        if ($collectionId)
            $headerData['setSpec'] = $collectionId;
        
        OaiPmhRepository_XmlUtilities::createElementWithChildren(
            $this->parentElement, header, $headerData);
    }
    
    /**
     * Appends a metadataFormat element to the document. 
     *
     * Declares the metadataPrefix, schema URI, and namespace for the oai_dc
     * metadata format.
     */    
    public function declareMetadataFormat()
    {
        $elements = array( 'metadataPrefix'    => $this->metadataPrefix,
                           'schema'            => $this->metadataSchemaUri,
                           'metadataNamespace' => $this->metadataNamespaceUri );
        OaiPmhRepository_XmlUtilities::createElementWithChildren(
            $this->parentElement, 'metadataFormat', $elements);
    }
    
    abstract public function appendMetadata();
}
?>
