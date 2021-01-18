<?php

namespace App\Service;

class MessageCorruptor
{
    public function corruptMessage(string $message) : Array
    {
        // At some point percentages are just too scrambled to differ from each other, 
        // but earlier percentages make a difference
        $percentages = Array(0, 1, 2, 5, 10, 15, 20, 30, 40);

        $scrambled = Array();
        foreach ($percentages as $percentage)
        {
            $scrambled += [ $percentage => $this->scramble($message, $percentage) ];
        }

        return $scrambled;
    }
    
    // percentage is 0-100
    private function scramble(string $message, int $percentage) : string
    {
        if ($percentage == 0)
            return $message;

        // Remove spaces
        $spaceless = str_replace(" ", "", $message);

        // Get indexes of space characters (to insert them back later)
        $spaceIndexes = array_keys(str_split($message), " ");

        // Round to higher number to prevent abuse with short strings where toScramble would equal zero
        $length = strlen($spaceless);
        $toScramble = ceil($length * $percentage / 100);

        // Shuffle existing characters (low percentage) or generate random characters (high percentage)
        $preserve = $percentage <= 15;

        // Force preserve disable if low toScramble value or string is very short
        if ($toScramble < 2 || $length < 10)
            $preserve = false;

        // Force preserve disable if low number of unique characters
        if (count(array_unique(str_split(strtolower($spaceless)))) < 5)
            $preserve = false;

        // Shuffle 'toScramble' of characters in spaceless version of message
        $scrambled = $this->shuffle($spaceless, $toScramble, $preserve);

        // Insert spaces back
        foreach ($spaceIndexes as $spaceIndex)
        {
            //substr_replace($oldstr, $str_to_insert, $pos, 0);
            $scrambled = substr_replace($scrambled, " ", $spaceIndex, 0);
        }

        // Trim duplicate spaces
        $scrambled = preg_replace("/\s+/", " ", $scrambled);

        return $scrambled;
    }

    private function shuffle(string $message, int $toScramble, bool $preserve) : string
    {
        $messageChar = str_split($message);

        // Pick 'toScramble' random characters to shuffle
        $keysToShuffle = array_rand($messageChar, $toScramble);
        
        // Force an array
        if (!is_array($keysToShuffle))
            $keysToShuffle = Array($keysToShuffle);

        if ($preserve)
        {
            // Shuffle existing characters

            // Pick chars to shuffle
            $chars = Array();
            foreach ($keysToShuffle as $key)
            {
                $chars[] = $messageChar[$key];
            }

            // Replace with random characters instead if all picked chars are the same
            if (count(array_unique($chars)) == 1)
                return $this->shuffle($message, $toScramble, false);

            // Shuffle chars and insert them back into string
            shuffle($chars);
            for ($i = 0; $i < count($chars); $i++)
            {
                $messageChar[$keysToShuffle[$i]] = $chars[$i];
            }

            // Replace with random characters instead if shuffle had no effect
            if (implode("", $messageChar) == $message)
                return $this->shuffle($message, $toScramble, false);
        }
        else
        {
            // Replace with random characters

            foreach ($keysToShuffle as $key)
            {
                $messageChar[$key] = $this->randomLetter();
            }
        }

        return implode("", $messageChar);
    }

    private function randomLetter() : string
    {
        $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz ";
        $charactersLength = strlen($characters);

        return $characters[rand(0, $charactersLength - 1)];
    }
}