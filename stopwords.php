<?php

class Stopwords {

	private static $raw_files_path = "stopwords/raw/*.txt";
	private static $all_file_path = "stopwords/all.txt";

	public function __construct() {
		//Get all raw text files with stopwords
		$files = glob(self::$raw_files_path);
		$output = self::$all_file_path;

		//Start fresh with stopwords text file
		file_put_contents($output, "");

		//Loop all raw text files
		foreach($files as $file) {
			$content = file_get_contents($file);
			file_put_contents($output, $content, FILE_APPEND);
		}

		//Ensure unique stopwords, resave to output file
		$output_lines = file_get_contents($output);
		file_put_contents($output, "");
		$output_words = preg_split('~\r\n|\r|\n~', $output_lines);
		$output_words = array_unique($output_words);
		asort($output_words);
		foreach($output_words as $output_word) {
			file_put_contents($output, $output_word."\n", FILE_APPEND);
		}
	}

	public static function all() {
		$output = self::$all_file_path;

		$output_lines = file_get_contents($output);
		return array_filter(preg_split('~\r\n|\r|\n~', $output_lines));
	}



}