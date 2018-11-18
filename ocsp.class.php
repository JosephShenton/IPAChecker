<?php 
	
	/**
	 * OCSP Checker Class
	 */
	class OCSP
	{

		private static function GetBetween($content,$start,$end){
		    $r = explode($start, $content);
		    if (isset($r[1])){
		        $r = explode($end, $r[1]);
		        return $r[0];
		    }
		    return '';
		}

		private static function fixTerminology($str) {
			return str_replace(array('good', 'bad', 'unknown'), array('Signed', 'Revoked', 'Error'), $str);
		}

		private static function buildCommand($commands) {
			$result = "";
			foreach ($commands as $command_key => $command) {
				$result .= $command."; ";
			}

			return $result;
		}

		private static function str_contains($needle, $haystack) {
		    return strpos($haystack, $needle) !== false;
		}

		private static function ipaName($url) {
			// return basename($url);
			$files = glob("Payload/*.app/Info.plist");
			$info = $files[0];

			$plistParser = new PlistParser();

			if ($plistParser->searchKeyInPlist($info, "CFBundleDisplayName") !== null) {
				$info = $plistParser->searchKeyInPlist($info, "CFBundleDisplayName");
				return $info;
			} else {
				return basename($url);
			}
		}

		private static function resize_image($file, $w, $h, $crop=FALSE) {
		    list($width, $height) = getimagesize($file);
		    $r = $width / $height;
		    if ($crop) {
		        if ($width > $height) {
		            $width = ceil($width-($width*abs($r-$w/$h)));
		        } else {
		            $height = ceil($height-($height*abs($r-$w/$h)));
		        }
		        $newwidth = $w;
		        $newheight = $h;
		    } else {
		        if ($w/$h > $r) {
		            $newwidth = $h*$r;
		            $newheight = $h;
		        } else {
		            $newheight = $w/$r;
		            $newwidth = $w;
		        }
		    }
		    $src = imagecreatefrompng($file);
		    $dst = imagecreatetruecolor($newwidth, $newheight);
		    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		    imagepng($dst, $file);

		    return $dst;
		}

		private static function imageCreateCorners($sourceImageFile, $radius = 30) {
		    # test source image
		    if (file_exists($sourceImageFile)) {
		      $res = is_array($info = getimagesize($sourceImageFile));
		      }
		    else $res = false;
		 
		    # open image
		    if ($res) {
		      $w = $info[0];
		      $h = $info[1];
		 
		      switch ($info['mime']) {
		        case 'image/jpeg': $src = imagecreatefromjpeg($sourceImageFile);
		          break;
		        case 'image/gif': $src = imagecreatefromgif($sourceImageFile);
		          break;
		        case 'image/png': $src = imagecreatefrompng($sourceImageFile);
		          break;
		        default:
		          $res = false;
		        }
		      }
		 
		    # create corners
		    if ($res) {
		 
		      $q = 8; # change this if you want
		      $radius *= $q;
		 
		      # find unique color
		      	do {
		        	$r = rand(0, 255);
		        	$g = rand(0, 255);
		        	$b = rand(0, 255);
		    	}
		      while (imagecolorexact($src, $r, $g, $b) < 0);
		 
		      $nw = $w*$q;
		      $nh = $h*$q;
		 
		      $img = imagecreatetruecolor($nw, $nh);
		      $alphacolor = imagecolorallocatealpha($img, $r, $g, $b, 127);
		      imagealphablending($img, false);
		      imagesavealpha($img, true);
		      imagefilledrectangle($img, 0, 0, $nw, $nh, $alphacolor);
		 
		      imagefill($img, 0, 0, $alphacolor);
		      imagecopyresampled($img, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
		 
		      imagearc($img, $radius-1, $radius-1, $radius*2, $radius*2, 180, 270, $alphacolor);
		      imagefilltoborder($img, 0, 0, $alphacolor, $alphacolor);
		      imagearc($img, $nw-$radius, $radius-1, $radius*2, $radius*2, 270, 0, $alphacolor);
		      imagefilltoborder($img, $nw-1, 0, $alphacolor, $alphacolor);
		      imagearc($img, $radius-1, $nh-$radius, $radius*2, $radius*2, 90, 180, $alphacolor);
		      imagefilltoborder($img, 0, $nh-1, $alphacolor, $alphacolor);
		      imagearc($img, $nw-$radius, $nh-$radius, $radius*2, $radius*2, 0, 90, $alphacolor);
		      imagefilltoborder($img, $nw-1, $nh-1, $alphacolor, $alphacolor);
		      imagealphablending($img, true);
		      imagecolortransparent($img, $alphacolor);
		 
		      # resize image down
		      $dest = imagecreatetruecolor($w + 200, $h + 200);
		      imagealphablending($dest, false);
		      imagesavealpha($dest, true);
		      imagefilledrectangle($dest, 0, 0, $w, $h, $alphacolor);
		      imagecopyresampled($dest, $img, 0, 0, 0, 0, $w + 200, $h + 200, $nw, $nh);
		 
		      # output image
		      $res = $dest;
		      imagedestroy($src);
		      imagedestroy($img);
		    }

		    imagepng($res, $sourceImageFile);
		 
		    return $res;
		}

		private static function getAppIcon() {
			$files = glob("Payload/*.app".'/AppIcon*@3x.png');
			$count = count($files);
			$key = $count - 1;
			$icon = $files[$key];
		   	shell_exec("xcrun -sdk iphoneos pngcrush -revert-iphone-optimizations -q $icon $icon-fixed");
		   	$icon = $files[$key]."-fixed";

		   	self::imageCreateCorners($icon, 35);

	   		$photo_to_paste=$icon;  //image 321 x 400
  			$white_image="background.png"; //873 x 622 

           	$im = imagecreatefrompng($white_image);
           	$condicion = GetImageSize($photo_to_paste); // image format?

           	if($condicion[2] == 1) //gif
           	$im2 = imagecreatefromgif("$photo_to_paste");
			if($condicion[2] == 2) //jpg
			$im2 = imagecreatefromjpeg("$photo_to_paste");
			if($condicion[2] == 3) //png
			$im2 = imagecreatefrompng("$photo_to_paste");

			imagecopy($im, $im2, (imagesx($im)/2)-(imagesx($im2)/2), (imagesy($im)/2)-(imagesy($im2)/2), 0, 0, imagesx($im2), imagesy($im2));

			imagepng($im,$files[$key]."-fixed",9);
			imagedestroy($im);
			imagedestroy($im2);

		   return base64_encode(file_get_contents($files[$key]."-fixed"));
		   // return $files[$key];
		}

		private static function guessService($url) {
			if (self::str_contains("ipas.fun", $url)) {
				return "Ignition";
			} elseif (self::str_contains("cdn.appvalley.vip", $url)) {
				return "AppValley";
			} elseif (self::str_contains("enjoy.ipacdn", $url)) {
				return "TweakBox";
			} elseif (self::str_contains("downapp.", $url)) {
				return "Tutu Helper";
			}  elseif (self::str_contains("qd.leaderhero.com", $url)) {
				return "Panda Helper";
			}   elseif (self::str_contains("185.246.209.74", $url)) {
				return "Top Store";
			} else {
				return "Unknown";
			}
		}

		public static function checkIPA($url) {
			$ipaName = basename($url);
			$commands = array(
				"rm codesign0 >/dev/null 2>/dev/null",
				"rm codesign1 >/dev/null 2>/dev/null",
				"rm codesign2 >/dev/null 2>/dev/null",
				"rm codesign0.pem >/dev/null 2>/dev/null",
				"rm codesign1.pem >/dev/null 2>/dev/null",
				"rm codesign2.pem >/dev/null 2>/dev/null",
				"rm cachain.pem >/dev/null 2>/dev/null",
				"rm entitlements.plist >/dev/null 2>/dev/null",
				"rm iTunesData >/dev/null 2>/dev/null",
				"rm iTunesArtwork >/dev/null 2>/dev/null",
				"rm -rf Payload >/dev/null 2>/dev/null",
				"rm -rf __MACOSX >/dev/null 2>/dev/null",
				"rm *.ipa >/dev/null 2>/dev/null",
				"rm iTunesMetadata.plist >/dev/null 2>/dev/null",
				"clear",
				"curl -s '$url' -L --output '$ipaName'",
				"unzip -q $ipaName",
				"codesign -d --extract-certificates Payload/*.app",
				"openssl x509 -inform DER -in codesign0 -out codesign0.pem",
				"openssl x509 -inform DER -in codesign1 -out codesign1.pem",
				"openssl x509 -inform DER -in codesign2 -out codesign2.pem",
				"cat codesign1.pem codesign2.pem > cachain.pem",
				"certificate=$(openssl x509 -inform DER -in codesign0 -noout -nameopt -oneline -subject | sed 's/.*O=\(.*\)\/C/\1/' | sed 's/=.*//')",
				"openssl ocsp -issuer cachain.pem -cert codesign0.pem -url `openssl x509 -in codesign0.pem -noout -ocsp_uri` -CAfile cachain.pem -header 'host' 'ocsp.apple.com'",
				"rm *.ipa >/dev/null 2>/dev/null"
			);
			$command = self::buildCommand($commands);

			$result = shell_exec($command);

			$certificateStatus = self::certificateStatus("cachain.pem", "codesign0.pem", "codesign0", $url);

			return $certificateStatus;
		}
		
		private static function certificateStatus($cachain, $subjectCertificate, $subjectCompiled, $url) {
			// certificate=$(openssl x509 -inform DER -in codesign0 -noout -nameopt -oneline -subject | sed 's/.*O=\(.*\)\/C/\1/' | sed 's/=.*//')

			// openssl ocsp -issuer cachain.pem -cert codesign0.pem -url `openssl x509 -in codesign0.pem -noout -ocsp_uri` -CAfile cachain.pem -header 'host' 'ocsp.apple.com'

			// $cachain = 'cachain.pem';

			// $subjectCertificate = 'codesign0.pem';

			// $subjectCompiled = 'codesign0';

			$command = "certificate=$(openssl x509 -inform DER -in $subjectCompiled -noout -nameopt -oneline -subject | sed 's/.*O=\(.*\)\/C/\1/' | sed 's/=.*//');  openssl ocsp -issuer $cachain -cert $subjectCertificate -url `openssl x509 -in $subjectCertificate -noout -ocsp_uri` -CAfile $cachain -header 'host' 'ocsp.apple.com'";

			$result = shell_exec($command);
			$str1 = explode("$subjectCertificate: ", $result);
			if (isset($str1[1])) {
				$str2 = explode("\n", $str1[1]);
			} else {
				$str2 = explode("\n", "lols\nsl");
			}
			// var_dump($str1);
			// echo "<br>";
			// var_dump($str2);

			if ($str2[0] !== 'good' && $str2[0] !== 'revoked') {
				$str2[0] = "unknown";
			}

			$r = shell_exec("echo $(openssl x509 -inform DER -in $subjectCompiled -noout -nameopt -oneline -subject)");

			$certificate = self::GetBetween($r, "iPhone Distribution: ", "/");
			if ($certificate == "") {
				$certificate = "Unknown";
			}

			$certificateInfo = array(
				"certificate_ipa" => self::ipaName($url),
				"certificate_status" => self::fixTerminology($str2[0]),
				"certificate_name" => "$certificate",
				"certificate_service" => self::guessService($url),
				"ceritficate_icon" => "data:image/png;base64,".self::getAppIcon()
			);

			$commands = array(
				"rm codesign0 >/dev/null 2>/dev/null",
				"rm codesign1 >/dev/null 2>/dev/null",
				"rm codesign2 >/dev/null 2>/dev/null",
				"rm codesign0.pem >/dev/null 2>/dev/null",
				"rm codesign1.pem >/dev/null 2>/dev/null",
				"rm codesign2.pem >/dev/null 2>/dev/null",
				"rm cachain.pem >/dev/null 2>/dev/null",
				"rm entitlements.plist >/dev/null 2>/dev/null",
				"rm iTunesData >/dev/null 2>/dev/null",
				"rm iTunesArtwork >/dev/null 2>/dev/null",
				"rm -rf Payload >/dev/null 2>/dev/null",
				"rm -rf __MACOSX >/dev/null 2>/dev/null",
				"rm *.ipa >/dev/null 2>/dev/null",
				"rm *.ipa?* >/dev/null 2>/dev/null",
				"rm iTunesMetadata.plist >/dev/null 2>/dev/null",
				"clear"
			);

			$r = self::buildCommand($commands);

			shell_exec($r);

			return $certificateInfo;
		}
	}

?>
