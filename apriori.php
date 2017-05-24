<?php

#Helpfull links: http://stats.stackexchange.com/questions/16456/apriori-algorithm-in-plain-english

set_time_limit(0);
ini_set('memory_limit', '9000M');

class Apriori
{
    private static $transactions = [];
    private static $concepts = [];
    private static $combinations = [];
    private static $stopwords = [];

    private static $total_transactions = 0; //Also known as $d

    //Constructor for Apriori; $transactions
    public function __construct($data)
    {

        //Transactions Array passed
        if (isset($data["transactions"])) {
            $transactions = $data["transactions"];
        }
        //Text string Passed
        if (isset($data["text"])) {
            // var_dump($data["text"]); exit();

            self::$stopwords = self::get_stopwords();
            $transactions = self::parse_text($data["text"]);
        }
        //Text file Passed
        if (isset($data["file"])) {
            // var_dump($data["text"]); exit();

            self::$stopwords = self::get_stopwords();
            $transactions = self::parse_file($data["file"]);
        }
        //Transactions are Empty
        if (empty($transactions)) {
            echo "Transactions are Empty, what are you going to do about it?";
            //exit();
        }

        //Set Transactions
        self::$transactions = $transactions;
        //Set Amount of Transactions
        self::$total_transactions = count($transactions);

        //Remove duplicates from Transactions
        self::$transactions = self::remove_duplicates(self::$transactions);
        //Find concepts from Transactions
        self::$concepts = self::find_concepts(self::$transactions);
        //Find combinations from Concepts
        self::$combinations = self::find_combinations(self::$concepts);
        //Find support for Combinations
        self::$combinations = self::find_support(self::$combinations, self::$transactions);
        //Find confidence for Concepts
        self::$combinations = self::find_confidence(self::$combinations, self::$transactions);
    }

    public static function get_associations()
    {
        return self::$combinations;
    }

    public static function get_stopwords()
    {
        //Read stop words from File
        $ds = file_get_contents("unrelevant_words.txt");
        //Split by new Line
        return preg_split('~\r\n|\r|\n~', $ds);
    }

    public static function is_stopword($term)
    {
        return (in_array($term, self::$stopwords));
    }

    public static function is_word($term)
    {
        return (!in_array($term, self::$stopwords));
    }

    public static function get_file($file)
    {
        //Read data from File
        return file_get_contents($file);
    }

    public static function parse_text($text)
    {
        //Lower case the Text
        $ds = strtolower($text);
        //Replace comma's
        $ds = str_replace(", ", " ", $ds);
        //Split lines by Dot character
        $ds = preg_split("~\.(\s)?~", $ds);
        //Loop all Lines
        foreach ($ds as $key => $dsl) {
            //Remove whitespace from Left and Right
            $ds[$key] = rtrim(ltrim($dsl));
            //Remove complete empty Lines
            if ($dsl == "") unset($ds[$key]);
        }
        //Loop all cleaned Lines
        foreach ($ds as $key => $dsl) {
            //Explode by space; Transaction with Concepts
            $ds[$key] = explode(" ", $dsl);

            //Maintain normal words, remove Stopwords from Transaction
            $ds[$key] = array_filter($ds[$key], "self::is_word");

            //Remove Transaction, if is Empty
            if (empty($ds[$key])) unset($ds[$key]);
        }
        //Return Transactions
        return $ds;
    }

    public static function parse_file($file)
    {
        $ds = self::get_file($file);
        $ds = self::parse_text($ds);

        //Return Transactions
        return $ds;
    }

    //Makes one Array from all Transactions
    public static function array_flatten($input)
    {
        $output = array();
        if (is_array($input)) {
            foreach ($input as $element) {
                $output = array_merge($output, self::array_flatten($element));
            }
        } else {
            $output[] = $input;
        }
        return $output;
    }

    //Remove duplicates from all Transactions
    public static function remove_duplicates($transactions)
    {
        //Loop all Transactions
        foreach ($transactions as $key => $transaction) {
            //Remove duplicates from Transaction
            $transactions[$key] = array_unique($transaction);
        }
        return $transactions;
    }

    //Find unique Concepts from all Transactions
    public static function find_concepts($transactions)
    {
        //Unique Concepts; tomaat, kaas, ui
        $concepts = array_unique(                //Take only unique Concepts
            self::array_flatten($transactions)    //Flatten all Transactions
        );
        return $concepts;
    }

    //Find all "two concept pair" combinations
    public static function find_combinations($concepts)
    {
        $combinations = [];
        //Loop all Concepts
        foreach ($concepts as $concept) {
            foreach ($concepts as $co_concept) {
                //Concept is not himself
                if ($concept != $co_concept) {
                    //Make a "two concept pair"
                    $pair = array($concept, $co_concept);
                    //Place new "pair" in Combinations
                    $combinations[] = $pair;
                }
            }
        }
        return $combinations;
    }

    //Find the support for all Combinations
    public static function find_support($combinations, $transactions)
    {
        foreach ($combinations as $key => $combination) {
            //Set the Support iterator to Zero
            $support_iterator = 0;

            //Loop all Transactions
            foreach ($transactions as $transaction) {
                //Combination is present in the Transaction
                if (
                    in_array($combination[0], $transaction) &&        //Check if First concept is present in the Transaction
                    in_array($combination[1], $transaction)        //Check if Second concept is present in the Transaction
                ) {

                    //Add Iterator
                    $support_iterator++;
                }
                //Eventually checked all Transactions
            }

            //Support = "Amount of Transactions containing two concept pairs" devided by "Amount of Transactions"
            $support = $support_iterator / self::$total_transactions;
            //Set the Containments with Amount of Transactions containing the Pairs
            $combinations[$key]["containment"] = $support_iterator;
            //Set the Support for the specific Combination
            $combinations[$key]["support"] = $support;
        }
        return $combinations;
    }

    public static function find_confidence($combinations, $transactions)
    {
        //Loop all Combinations
        foreach ($combinations as $key => $combination) {
            //Set the Confidence iterator to Zero
            $confidence_iterator = 0;

            //Amount of Transactions containing two concept pairs
            $containment = $combination["containment"];
            //Amount of Transactions containing the first Concept
            foreach ($transactions as $transaction) {
                if (in_array($combination[0], $transaction)) {
                    $confidence_iterator++;
                }
            }
            $combinations[$key]["appearance"] = $confidence_iterator;
            //Confidence = "Amount of Transactions containing two concept pairs" devided by "Amount of Transactions containing a Concept"
            $combinations[$key]["confidence"] = $containment / $confidence_iterator;
        }
        return $combinations;
    }
}