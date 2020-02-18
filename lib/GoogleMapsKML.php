<?php
namespace manguto\cms5\lib;

class GoogleMapsKML extends GoogleMaps
{

    
    
    //const color = 'ff0051e6';
    //const styleURL_ini = 'icon-seq2-0';
    //const styleURL_end = '0288D1-labelson-nodesc';
    
    
    const color = 'ffd18802';
    const styleURL_ini = 'icon-seq2-0';    
    const styleURL_end = 'E65100-labelson-nodesc';
    // ================================================================================== v1
    /*
     * const model = '<?xml version="1.0" encoding="utf-8" ?>
     * <kml xmlns="http://www.opengis.net/kml/2.2">
     * <Document id="root_doc">
     * <Folder>
     * <name>{$name}</name>{$placemarks}
     * </Folder>
     * </Document>
     * </kml>';
     *
     * const placemark = '
     * <Placemark>
     * <name>{$name}</name>
     * <Point>
     * <coordinates>{$coordinates}</coordinates>
     * </Point>
     * </Placemark>';
     */
    // ================================================================================== v2
    const model = '<?xml version="1.0" encoding="UTF-8" ?>
<kml xmlns="http://www.opengis.net/kml/2.2">
    <Document>        
        <name>{$name}</name>
    {$styles}
    {$placemarks}
    </Document>
</kml>';

    const style = '<Style id="'.self::styleURL_ini.'-{$i}-'.self::styleURL_end.'">
            <IconStyle>
                <color>'.self::color.'</color>
                <scale>1</scale>
                <Icon>
                    <href>images/icon-{$j}.png</href>
                </Icon>
            </IconStyle>
            <BalloonStyle>
                <text><![CDATA[<h3>$[name]</h3>]]></text>
            </BalloonStyle>
        </Style>';

    const placemark = '<Placemark>
            <name>{$name}</name>
            <description>{$description}</description>
            <styleUrl>#'.self::styleURL_ini.'-{$i}-'.self::styleURL_end.'</styleUrl>
            <Point>
                <coordinates>
                    {$coordinates}
                </coordinates>
            </Point>
        </Placemark>';

    // ==================================================================================
    private $name = '';

    private $placemark_array = [];

    // ==================================================================================
    public function __construct($name)
    {
        $this->name = $name;
    }

    // ==================================================================================
    /**
     * Adiciona um placemark no objeto
     *
     * @param string $name
     * @param string $coordinates
     * @throws Exception
     */
    public function addPlacemark(array $parameters)
    {
        $placemark = [];
        foreach ($parameters as $k => $v) {
            $placemark[$k] = $v;
        }
        $this->placemark_array[] = $placemark;
    }

    /**
     * Obterm o conteudo do objeto no formato KML
     *
     * @return string
     */
    public function getContent(): string
    {
        $return = self::getRawContent();
        { // placemarks contents
            $return = str_replace('{$styles}', $this->getStylesContent(), $return);
            $return = str_replace('{$placemarks}', $this->getPlacemarksContent(), $return);
        }
        return $return;
    }

    private function getRawContent()
    {
        $return = self::model;
        {
            $return = str_replace('{$name}', $this->name, $return);
        }
        return $return;
    }

    private function getStylesContent()
    {
        $styles = [];
        for ($i = 0; $i < sizeof($this->placemark_array); $i ++) {
            $style_content = chr(9).self::style;
            {
                $style_content = str_replace('{$i}', $i, $style_content);
                $style_content = str_replace('{$j}', $i+1, $style_content);
            }
            $styles[] = $style_content;
        }
        $styles = implode(chr(10), $styles);
        return $styles;
    }

    private function getPlacemarksContent()
    {
        $placemarks = [];
        foreach ($this->placemark_array as $i=>$placemark) {
            $placemark_content = chr(9).self::placemark;
            {
                $placemark_content = str_replace('{$i}', $i, $placemark_content);
                foreach ($placemark as $key => $value) {
                    $placemark_content = str_replace("{\$$key}", trim($value), $placemark_content);
                }
            }
            $placemarks[] = $placemark_content;
        }
        $placemarks = implode(chr(10), $placemarks);
        return $placemarks;
    }

    // ==================================================================================
    // ==================================================================================
    // ==================================================================================
}

?>