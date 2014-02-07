<?php
/**
 * Created by PhpStorm.
 * User: kim
 * Date: Sep 19, 2011
 * Time: 4:00:43 PM
 * To change this template use File | Settings | File Templates.
 */
 
class Skylight_utilities {

    var $lightBox = false;
    var $lightBoxMimes = array('image/jpeg', 'image/gif', 'image/png');
    var $fields = array('');

    public function __construct($params = array())
	{
		if (count($params) > 0)
		{
			$this->initialize($params);
		}

        $CI =& get_instance();
        $this->lightBox = $CI->config->item('skylight_lightbox');
        $this->lightBoxMimes = $CI->config->item('skylight_lightbox_mimes');
        $this->fields = $CI->config->item('skylight_fields');

		log_message('debug', "skylight Skylight Utilities Initialized");
	}

    /**
     *
     * <a rel="lightbox" class="bitstream_link" target="_blank" href="<?php echo getBitstreamUri($bitstream); ?>"><?php echo getBitstreamFilename($bitstream); ?></a>
     * @param  $metadatavalue
     * @return a formatted bitstream link */
    function getBitstreamLink($metadatavalue) {

        $uri = getBitstreamUri($metadatavalue);
        $mime = getBitstreamMimeType($metadatavalue);
        $filename = getBitstreamFilename($metadatavalue);
        $desc = getBitstreamDescription($metadatavalue);
        $seq = getBitstreamSequence($metadatavalue);

        // old class: bitstream_link
        $link ='<a ';

        if($this->lightBox == true && in_array($mime, $this->lightBoxMimes)) {
            // Lightbox is enabled and this is a valid mime type to show in a light box
                   $link .= 'class="cboxElement" rel="'.$seq.'" ';
                    if($desc != '' && $desc != null) {
                        $link .= 'title="'.$desc.'" ';
                    }
            $link .= 'href="'.$uri.'">'.$filename.'</a>';

            $link .= '<script>$(document).ready(function(){
                $("a[rel=\''.$seq.'\']").colorbox({width: "800px", height: "600px"});
        });</script>';
        }
        else {
            $link .= 'href="'.$uri.'">'.$filename.'</a>';
        }



        return $link;

    }

        function getBitstreamLinkedImage($metadatavalue) {

        $uri = getBitstreamUri($metadatavalue);
        $mime = getBitstreamMimeType($metadatavalue);
        $filename = getBitstreamFilename($metadatavalue);
        $desc = getBitstreamDescription($metadatavalue);
        $seq = getBitstreamSequence($metadatavalue);

        // old class: bitstream_link
        $link ='<a ';

        if($this->lightBox == true && in_array($mime, $this->lightBoxMimes)) {
            // Lightbox is enabled and this is a valid mime type to show in a light box
                   $link .= 'class="cboxElement" rel="'.$seq.'" ';
                    if($desc != '' && $desc != null) {
                        $link .= 'title="'.$desc.'" ';
                    }
            $link .= 'href="'.$uri.'"><img src="'.$uri.'"/></a>';

            $link .= '<script>$(document).ready(function(){
                $("a[rel=\''.$seq.'\']").colorbox({width: "800px", height: "600px"});
        });</script>';
        }
        else {
            $link .= 'href="'.$uri.'">'.$filename.'</a>';
        }



        return $link;

    }

    function getBitstreamThumbLink($metadatavalue, $thumbmetadatavalue, $desc) {

        $uri = getBitstreamUri($metadatavalue);

        $mime = getBitstreamMimeType($metadatavalue);

        $filename = getBitstreamFilename($metadatavalue);

        //$desc = getBitstreamDescription($metadatavalue);
        $seq = getBitstreamSequence($metadatavalue);

        $thumburi = getBitstreamUri($thumbmetadatavalue);

        // old class: bitstream_link
        $link ='<a ';

        if($this->lightBox == true && in_array($mime, $this->lightBoxMimes)) {
            // Lightbox is enabled and this is a valid mime type to show in a light box
                   $link .= 'class="cboxElement" rel="'.$seq.'" ';
                    if($desc != '' && $desc != null) {
                        $link .= 'title="'.$desc.'" ';
                    }
            $link .= 'href="'.$uri.'"><img  style="float:right;" src="'.$thumburi.'"/></a>';
//
                    $link .= '<script>$(document).ready(function(){
                $("a[rel=\''.$seq.'\']").colorbox({width: "400px", top: "100px"});
        });</script>';
        }
        else {
            $link .= 'href="'.$uri.'"><img style="float:right;" src="'.$thumburi.'"/></a>';
        }



        return $link;

    }

//SR 21/11/13 New method to allow configurable thumbnail images.
//New parameters: width, distance from top, style.
    function getBitstreamThumbLinkParameterised($metadatavalue, $thumbmetadatavalue, $desc, $width, $top, $style) {

        $uri = getBitstreamUri($metadatavalue);

        $mime = getBitstreamMimeType($metadatavalue);

        $filename = getBitstreamFilename($metadatavalue);

        //$desc = getBitstreamDescription($metadatavalue);
        $seq = getBitstreamSequence($metadatavalue);

        $thumburi = getBitstreamUri($thumbmetadatavalue);

        // old class: bitstream_link
        $link ='<a ';

        if($this->lightBox == true && in_array($mime, $this->lightBoxMimes)) {
            // Lightbox is enabled and this is a valid mime type to show in a light box
            $link .= 'class="cboxElement" rel="'.$seq.'" ';
            if($desc != '' && $desc != null) {
                $link .= 'title="'.$desc.'" ';
            }
            $link .= 'href="'.$uri.'"><img '.$style.' src="'.$thumburi.'" /></a>';
//
            $link .= '<script>$(document).ready(function(){
                $("a[rel=\''.$seq.'\']").colorbox({width: "'.$width.'" , top: "'.$top.'"});
        });</script>';
        }
        else {
            $link .= 'href="'.$uri.'"><img style="float:right;" src="'.$thumburi.'"/></a>';
        }



        return $link;

    }
        function getGalleryLink($metadatavalue, $thumbmetadatavalue, $desc, $index) {

        $uri = getBitstreamUri($metadatavalue);
        $mime = getBitstreamMimeType($metadatavalue);
        $filename = getBitstreamFilename($metadatavalue);
        //$desc = getBitstreamDescription($metadatavalue);
        $seq = getBitstreamSequence($metadatavalue); 

        $thumburi = getBitstreamUri($thumbmetadatavalue);

        // old class: bitstream_link
       // $link ='<div style="float: right; width: 170px; background-color: #cdc8b1; text-align: center;"><a ';
        $link ='<div style="float: right; width: 170px; background-color: #444; text-align: center; border: 2px solid #cdc8b1;"><a ';

        if($this->lightBox == true && in_array($mime, $this->lightBoxMimes)) {
            // Lightbox is enabled and this is a valid mime type to show in a light box
                   $link .= 'class="cboxElement" rel="1" ';
                    if($desc != '' && $desc != null) {
                        $link .= 'title="'.$desc.'" ';
                    }
            $link .= 'href="'.$uri.'"><img style="height:90px;" src="'.$thumburi.'"/></a></div>';

            $link .= '<script>$(document).ready(function(){
                $("a[rel=\'1\']").colorbox({width: "400px", top: "100px"});
        });</script>';
        }
        else {
            $link .= 'href="'.$uri.'"><img style="float:left;" src="'.$thumburi.'"/></a>';
        }



        return $link;

    }

    function getField($label) {
        $configured_fields = $this->fields;
        if(array_key_exists($label,$configured_fields)) {
            return str_replace('.','',$configured_fields[$label]);
        }
        else {
            return null;
        }
    }

    function getRawField($label) {
        $configured_fields = $this->fields;
        if(array_key_exists($label,$configured_fields)) {
            return $configured_fields[$label];
        }
        else {
            return null;
        }
    }

}
