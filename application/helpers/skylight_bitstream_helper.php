<?php

    function getBitstreamUri($metadatavalue) {
        $segments = parseBitstreamMetadata($metadatavalue);
        $filename = $segments[1];
        $handle = $segments[3];
        $seq = $segments[4];

        $handle_id = preg_replace('/^.*\//', '',$handle);
        $uri = './record/'.$handle_id.'/'.$seq.'/'.$filename;

        return $uri;
    }

    function getBitstreamFilename($metadatavalue) {
        $segments = parseBitstreamMetadata($metadatavalue);
        $filename = $segments[1];
        return $filename;
    }

    function getBitstreamSequence($metadatavalue) {
        $segments = parseBitstreamMetadata($metadatavalue);
        $seq = $segments[4];
        return $seq;
    }

    function getBitstreamHandle($metadatavalue) {
        $segments = parseBitstreamMetadata($metadatavalue);
        $handle = $segments[3];
        $handle = preg_replace('/^.*\//', '',$handle);
        return $handle;
    }

    function getBitstreamLength($bitstreams, $seq) {
        foreach ($bitstreams as $bitstream) {
            if (getBitstreamSequence($bitstream) == $seq) {
                $segments = parseBitstreamMetadata($bitstream);
                return $segments[2];
            }
        }
    }

    function getBitstreamSize($metadatavalue) {
        $segments = parseBitstreamMetadata($metadatavalue);
        $size = $segments[2];
        if($size > 1024 * 1024 * 1024) {
            $size = round($size / 1024 / 1024 / 1024,2);
            $size .= ' Gb';
        }
        elseif($size > 1024 * 1024) {
            $size = round($size / 1024 / 1024,2);
            $size .= ' Mb';
        }
        elseif($size > 1024) {
            $size = round($size / 1024,2);
            $size .= ' Kb';
        }
        else {
            $size .= 'b';
        }

        return $size;
    }

    function getBitstreamsMimeType($bitstreams, $seq) {
        foreach ($bitstreams as $bitstream) {
            if (getBitstreamSequence($bitstream) == $seq) {
                $segments = parseBitstreamMetadata($bitstream);
                return $segments[0];
            }
        }
    }

    function getBitstreamMimeType($metadatavalue) {
        $segments = parseBitstreamMetadata($metadatavalue);
        $mime = $segments[0];
        return $mime;
    }

    function getBitstreamDescription($metadatavalue) {
        $segments = parseBitstreamMetadata($metadatavalue);
        $description = $segments[6];
        return $description;
    }

    function getBitstreamMD5($bitstreams, $seq) {
        foreach ($bitstreams as $bitstream) {
            if (getBitstreamSequence($bitstream) == $seq) {
                $segments = parseBitstreamMetadata($bitstream);
                return $segments[5];
            }
        }
    }

    function parseBitstreamMetadata($metadatavalue) {
        //$mime, $filename, $bytes, $handle, $seq, $md5, $description
        $segments = explode("##", $metadatavalue);
        return $segments;
    }

?>