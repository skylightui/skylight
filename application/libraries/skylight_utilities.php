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

    public function __construct($params = array())
	{
		if (count($params) > 0)
		{
			$this->initialize($params);
		}

        $CI =& get_instance();
        $this->lightBox = $CI->config->item('skylight_lightbox');
        $this->lightBoxMimes = $CI->config->item('skylight_lightbox_mimes');

		log_message('debug', "skylight Solr Client Initialized");
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
        $link ='<a class="cboxElement" ';

        if($this->lightBox == true && in_array($mime, $this->lightBoxMimes)) {
            // Lightbox is enabled and this is a valid mime type to show in a light box
                   $link .= 'rel="'.$seq.'" ';
                    if($desc != '' && $desc != null) {
                        $link .= 'title="'.$desc.'" ';
                    }
        }

        $link .= 'href="'.$uri.'">'.$filename.'</a>';

        $link .= '<script>$(document).ready(function(){
                $("a[rel=\''.$seq.'\']").colorbox({width: "800px", height: "600px"});
        });</script>';

        return $link;

    }

}
