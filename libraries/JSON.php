<?php

namespace manguto\cms7\libraries;

class JSON {
	static function encode($data, $setHeaderPrintExit = false){
		$json = json_encode($data);
		if($json == false){
			throw new Exception(json_last_error_msg());
		}else{
			if($setHeaderPrintExit){
				self::setHeader();
				print $json;
				exit();
			}else{
				return $json;
			}
		}
	}
	static function setHeader(){
		header('Content-Type: application/json; charset=utf-8');
	}
	
	/**
	 * decodifica uma string json para um array 
	 * @param string $string
	 * @param bool $toArray
	 * @return array
	 */
	static function decode(string $string, bool $toArray = true):array{
		$data_array = json_decode($string, $toArray);
		$return=[];
		foreach ($data_array as $info){
			$name = $info['name'];
			$value = $info['value'];
			$return[$name]=$value;
		}
		return $return;
	}
}

