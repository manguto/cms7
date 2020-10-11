<?php
namespace manguto\cms7\libraries;

class Images
{

    static function resize(string $src,string $src_new='', $width = 0, $height = '', int $quality=80)
    {        
        {//pre-pare
            $width=intval($width);
            $height=intval($height);
        }
        {//parameters
            {//originals
                list ($width_orig, $height_orig) = getimagesize($src);
            }            
            {//wanted
                $width = $width==0 ? $width_orig : $width;                
            }
            {//ratio
                $ratio_orig = $width_orig / $height_orig;
            }
            {//height                
                //height defined?  
                if($height==0){
                    //proportional ratio
                    $height = $width / $ratio_orig;
                }else{
                    //desproportional ratio
                    $height = $height;
                }                
            }
        }
        
        {// resample
            $image_p = imagecreatetruecolor($width, $height);
            $image = @imagecreatefromjpeg($src);
            {//resize
                if($width!=$width_orig || $height!=$height){
                    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);                    
                }else{
                    imagecopy($image_p, $image, 0, 0, 0, 0, $width, $height);                    
                }
            }
        }
        
        {//save OR show
            if($src_new!=''){
                // output
                imagejpeg($image_p, $src_new, $quality);
            }else{
                self::setHeaderContentType($src);
                print imagejpeg($image_p, null, $quality);
            }
        }
                
    }
    
    static function setHeaderContentType($src){
        
        {//extension get
            $ext = File::getExtension($src);
            $ext = strtolower($ext);
        }
        // content type
        header('Content-Type: image/'.$ext);
    }
}

?>