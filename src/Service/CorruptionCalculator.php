<?php

namespace App\Service;

class CorruptionCalculator
{
    public function calculateCorruption($user_freq, $message_freq, $valid_corruptions) : int
    {
        // User is on the same frequency the message was sent on, there is no corruption
        if ($user_freq == $message_freq)
            return 0;

        $userFreq = (float)$user_freq;
        $messageFreq = (float)$message_freq;

        // Maximum/minimum difference between user and message frequency that will still be shown in chat (<= 40% is shown in chat)
        $visibleRangeLimit = 2;

        // Convert a difference between user and message frequency to a percentage between 0% and 40%
        if ($userFreq < $messageFreq)
        {
            $limit = $messageFreq - $visibleRangeLimit;
            $corruption = $this->map($userFreq, $limit, $messageFreq, 40, 0);
        }
        else
        {
            $limit = $messageFreq + $visibleRangeLimit;
            $corruption = $this->map($userFreq, $messageFreq, $limit, 0, 40);
        }


        // Match calculated corruption to pre-defined levels
        foreach ($valid_corruptions as $valid)
        {
            if ($corruption <= $valid)
                return $valid;
        }

        // Return 100% for all corruption above 40%
        return 100;
    }

    private function map($value, $fromLow, $fromHigh, $toLow, $toHigh) 
    {
        $fromRange = $fromHigh - $fromLow;
        $toRange = $toHigh - $toLow;
        $scaleFactor = $toRange / $fromRange;
    
        // Re-zero the value within the from range
        $tmpValue = $value - $fromLow;

        // Rescale the value to the to range
        $tmpValue *= $scaleFactor;
        
        // Re-zero back to the to range
        return $tmpValue + $toLow;
    }
}