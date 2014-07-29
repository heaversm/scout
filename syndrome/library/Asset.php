<?php
class Asset {
	const ERROR_SUCCESS = 0;
	const ERROR_BAD_MIME = -1;
	const ERROR_GENERIC = -2;
	
	public static function move_file($file, $path, $accepted_mimetypes = array(), $overwrite = true) {
		if(($file['tmp_name'] !== '') && ($file['size'] > 0) && is_uploaded_file($file['tmp_name'])) {
			$filesource = $file['tmp_name'];
			$filename = $file['name'];
			$mime = $file['type'];
			$filesize = $file['size'];
			
			if(!empty($accepted_mimetypes) && !in_array($mime, $accepted_mimetypes)) {
				return self::ERROR_BAD_MIME;
			}
			if($overwrite && file_exists($path.$filename)) {
				@unlink($path.$filename);
			}
			$moved = move_uploaded_file($filesource, $path.$filename);
			return $moved ? $filename : self::ERROR_GENERIC;
		}
		return self::ERROR_GENERIC;
	}
}